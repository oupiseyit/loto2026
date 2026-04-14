# Lotto — Laravel Test Generator

Generate PHPUnit feature tests for the **HT Lotto App** — covering both web (Inertia) and API (Sanctum) layers.

## How to use
```
/lotto-test <screen> [layer]
```

| Argument | Values | Default |
|---|---|---|
| `screen` | `auth` `home` `record` `result` `report` `setting` `account` `all` | required |
| `layer` | `web` `api` `both` | `both` |

**Examples:**
```
/lotto-test auth both
/lotto-test home api
/lotto-test report web
/lotto-test all both
```

---

## Reference Files
- `lotto-roles.md` — 3 roles: admin / master / staff and their permissions
- `lotto-api.md` — API endpoint map and response shapes
- `lotto-stack.md` — Inertia web vs API architecture

---

## Test Setup & Conventions

### Base test class
```php
// tests/Feature/BaseTestCase.php
abstract class BaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $master;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin  = User::factory()->admin()->create();
        $this->master = User::factory()->master()->create();
        $this->staff  = User::factory()->staff()->create(['created_by' => $this->master->id]);
    }

    // Helpers
    protected function actingAsAdmin():  static { return $this->actingAs($this->admin); }
    protected function actingAsMaster(): static { return $this->actingAs($this->master); }
    protected function actingAsStaff():  static { return $this->actingAs($this->staff); }

    protected function apiToken(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function apiAs(User $user): static
    {
        return $this->withToken($this->apiToken($user));
    }
}
```

### User factory states
```php
// database/factories/UserFactory.php
public function admin():  static { return $this->state(['role' => 'admin']); }
public function master(): static { return $this->state(['role' => 'master']); }
public function staff():  static { return $this->state(['role' => 'staff']); }
```

### File locations
```
tests/Feature/
  Web/
    AuthWebTest.php
    HomeWebTest.php
    RecordWebTest.php
    ResultWebTest.php
    ReportWebTest.php
    SettingWebTest.php
    AccountWebTest.php
  Api/
    AuthApiTest.php
    HomeApiTest.php
    RecordApiTest.php
    ResultApiTest.php
    ReportApiTest.php
    SettingApiTest.php
    AccountApiTest.php
```

### Run tests
```bash
# All tests
docker compose exec app php artisan test

# Single screen
docker compose exec app php artisan test --filter=HomeApiTest

# By layer
docker compose exec app php artisan test tests/Feature/Api/
docker compose exec app php artisan test tests/Feature/Web/

# With coverage
docker compose exec app php artisan test --coverage
```

---

## Auth Tests

### Web (`tests/Feature/Web/AuthWebTest.php`)
```php
class AuthWebTest extends BaseTestCase
{
    // Login page renders
    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))
             ->assertStatus(200)
             ->assertInertia(fn($page) => $page->component('Login'));
    }

    // Valid login redirects to home
    public function test_valid_credentials_redirect_to_home(): void
    {
        $this->post(route('login'), [
            'username' => $this->staff->username,
            'password' => 'password',
        ])->assertRedirect(route('home'));
    }

    // Invalid login returns error
    public function test_invalid_credentials_return_error(): void
    {
        $this->post(route('login'), [
            'username' => 'wrong',
            'password' => 'wrong',
        ])->assertSessionHasErrors('username');
    }

    // Guests cannot access protected routes
    public function test_guest_is_redirected_from_home(): void
    {
        $this->get(route('home'))->assertRedirect(route('login'));
    }

    // Logout clears session
    public function test_logout_clears_session_and_redirects(): void
    {
        $this->actingAsStaff()
             ->post(route('logout'))
             ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
```

### API (`tests/Feature/Api/AuthApiTest.php`)
```php
class AuthApiTest extends BaseTestCase
{
    // Login returns token + user + role
    public function test_login_returns_token_and_user(): void
    {
        $this->postJson('/api/v1/login', [
            'username' => $this->staff->username,
            'password' => 'password',
        ])->assertOk()
          ->assertJsonStructure([
              'success', 'data' => ['token', 'user' => ['id', 'name', 'role', 'balance']],
          ])
          ->assertJsonPath('data.user.role', 'staff');
    }

    // Invalid credentials return 401
    public function test_invalid_credentials_return_401(): void
    {
        $this->postJson('/api/v1/login', [
            'username' => 'no-user',
            'password' => 'wrong',
        ])->assertUnauthorized()
          ->assertJsonPath('success', false);
    }

    // /me returns authenticated user
    public function test_me_returns_current_user(): void
    {
        $this->apiAs($this->admin)
             ->getJson('/api/v1/me')
             ->assertOk()
             ->assertJsonPath('data.role', 'admin');
    }

    // Unauthenticated request returns 401
    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    // Logout revokes token
    public function test_logout_revokes_token(): void
    {
        $token = $this->apiToken($this->staff);

        $this->withToken($token)
             ->postJson('/api/v1/logout')
             ->assertOk();

        // Token is now invalid
        $this->withToken($token)
             ->getJson('/api/v1/me')
             ->assertUnauthorized();
    }
}
```

---

## Home / Betting Tests

### Web (`tests/Feature/Web/HomeWebTest.php`)
```php
class HomeWebTest extends BaseTestCase
{
    // Home page renders with correct Inertia component
    public function test_home_page_renders_for_all_roles(): void
    {
        foreach ([$this->admin, $this->master, $this->staff] as $user) {
            $this->actingAs($user)
                 ->get(route('home'))
                 ->assertOk()
                 ->assertInertia(fn($page) => $page->component('Home'));
        }
    }

    // Staff only sees own bets
    public function test_staff_only_sees_own_bets(): void
    {
        $ownBet   = Bet::factory()->create(['user_id' => $this->staff->id]);
        $otherBet = Bet::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAsStaff()
             ->get(route('home'))
             ->assertInertia(fn($page) => $page
                 ->has('bets', 1)
                 ->where('bets.0.id', $ownBet->id)
             );
    }

    // Admin sees all bets
    public function test_admin_sees_all_bets(): void
    {
        Bet::factory(3)->create(['user_id' => $this->staff->id]);
        Bet::factory(2)->create(['user_id' => $this->master->id]);

        $this->actingAsAdmin()
             ->get(route('home'))
             ->assertInertia(fn($page) => $page->has('bets', 5));
    }
}
```

### API (`tests/Feature/Api/HomeApiTest.php`)
```php
class HomeApiTest extends BaseTestCase
{
    // Staff can place a bet
    public function test_staff_can_place_bet(): void
    {
        $this->apiAs($this->staff)
             ->postJson('/api/v1/bets', [
                 'session'  => 'morning',
                 'bet_type' => 'ABCD',
                 'letter'   => 'A',
                 'position' => 'X',
                 'number'   => '25',
                 'amount'   => 5000,
             ])->assertCreated()
               ->assertJsonPath('success', true)
               ->assertJsonStructure(['data' => ['id', 'number', 'amount']]);
    }

    // Validation rejects missing fields
    public function test_bet_validation_rejects_missing_fields(): void
    {
        $this->apiAs($this->staff)
             ->postJson('/api/v1/bets', [])
             ->assertUnprocessable()
             ->assertJsonPath('success', false)
             ->assertJsonStructure(['errors' => ['session', 'bet_type', 'number', 'amount']]);
    }

    // Staff cannot see other users' bets
    public function test_staff_cannot_see_others_bets(): void
    {
        Bet::factory(3)->create(['user_id' => $this->admin->id]);
        Bet::factory(2)->create(['user_id' => $this->staff->id]);

        $this->apiAs($this->staff)
             ->getJson('/api/v1/bets')
             ->assertOk()
             ->assertJsonCount(2, 'data');
    }

    // Staff can delete own bet
    public function test_staff_can_delete_own_bet(): void
    {
        $bet = Bet::factory()->create(['user_id' => $this->staff->id]);

        $this->apiAs($this->staff)
             ->deleteJson("/api/v1/bets/{$bet->id}")
             ->assertOk();

        $this->assertModelMissing($bet);
    }

    // Staff cannot delete another user's bet
    public function test_staff_cannot_delete_others_bet(): void
    {
        $bet = Bet::factory()->create(['user_id' => $this->admin->id]);

        $this->apiAs($this->staff)
             ->deleteJson("/api/v1/bets/{$bet->id}")
             ->assertForbidden();
    }
}
```

---

## Report Tests _(admin + master only)_

### Web (`tests/Feature/Web/ReportWebTest.php`)
```php
class ReportWebTest extends BaseTestCase
{
    // Staff cannot access report
    public function test_staff_cannot_access_report(): void
    {
        $this->actingAsStaff()
             ->get(route('report'))
             ->assertForbidden();
    }

    // Admin sees report with all staff data
    public function test_admin_sees_full_report(): void
    {
        $this->actingAsAdmin()
             ->get(route('report'))
             ->assertOk()
             ->assertInertia(fn($page) => $page
                 ->component('Report')
                 ->has('summary')
                 ->has('summary.total_bets')
             );
    }

    // Master sees only own staff report
    public function test_master_sees_own_staff_report_only(): void
    {
        $otherStaff = User::factory()->staff()->create(['created_by' => $this->admin->id]);
        Bet::factory(5)->create(['user_id' => $otherStaff->id]);
        Bet::factory(3)->create(['user_id' => $this->staff->id]);

        $this->actingAsMaster()
             ->get(route('report'))
             ->assertInertia(fn($page) => $page
                 ->where('summary.total_bets', 3) // only master's own staff
             );
    }
}
```

### API (`tests/Feature/Api/ReportApiTest.php`)
```php
class ReportApiTest extends BaseTestCase
{
    // Staff gets 403
    public function test_staff_cannot_access_report_api(): void
    {
        $this->apiAs($this->staff)
             ->getJson('/api/v1/reports/summary')
             ->assertForbidden();
    }

    // Admin gets full summary
    public function test_admin_gets_full_summary(): void
    {
        $this->apiAs($this->admin)
             ->getJson('/api/v1/reports/summary')
             ->assertOk()
             ->assertJsonStructure([
                 'data' => ['total_bets', 'total_amount', 'total_win', 'net', 'by_session'],
             ]);
    }

    // Master summary scoped to own staff
    public function test_master_summary_scoped_to_own_staff(): void
    {
        Bet::factory(5)->create(['user_id' => $this->staff->id]); // master's staff
        Bet::factory(3)->create(['user_id' => $this->admin->id]); // not master's staff

        $this->apiAs($this->master)
             ->getJson('/api/v1/reports/summary')
             ->assertOk()
             ->assertJsonPath('data.total_bets', 5);
    }

    // Date filter works
    public function test_report_filters_by_date_range(): void
    {
        Bet::factory()->create(['user_id' => $this->admin->id, 'created_at' => '2025-07-01']);
        Bet::factory()->create(['user_id' => $this->admin->id, 'created_at' => '2025-07-05']);

        $this->apiAs($this->admin)
             ->getJson('/api/v1/reports/summary?from=2025-07-01&to=2025-07-03')
             ->assertJsonPath('data.total_bets', 1);
    }
}
```

---

## Account / User Management Tests

### API (`tests/Feature/Api/AccountApiTest.php`)
```php
class AccountApiTest extends BaseTestCase
{
    // Master can create staff
    public function test_master_can_create_staff(): void
    {
        $this->apiAs($this->master)
             ->postJson('/api/v1/users', [
                 'name'     => 'New Staff',
                 'username' => 'newstaff01',
                 'password' => 'password123',
                 'role'     => 'staff',
             ])->assertCreated()
               ->assertJsonPath('data.role', 'staff');

        $this->assertDatabaseHas('users', [
            'username'   => 'newstaff01',
            'created_by' => $this->master->id,
        ]);
    }

    // Master cannot create another master
    public function test_master_cannot_create_master_account(): void
    {
        $this->apiAs($this->master)
             ->postJson('/api/v1/users', [
                 'name'     => 'New Master',
                 'username' => 'newmaster01',
                 'password' => 'password123',
                 'role'     => 'master',
             ])->assertForbidden();
    }

    // Admin can create master
    public function test_admin_can_create_master(): void
    {
        $this->apiAs($this->admin)
             ->postJson('/api/v1/users/master', [
                 'name'     => 'New Master',
                 'username' => 'newmaster01',
                 'password' => 'password123',
             ])->assertCreated()
               ->assertJsonPath('data.role', 'master');
    }

    // Master cannot edit staff they did not create
    public function test_master_cannot_edit_others_staff(): void
    {
        $otherStaff = User::factory()->staff()->create(['created_by' => $this->admin->id]);

        $this->apiAs($this->master)
             ->putJson("/api/v1/users/{$otherStaff->id}", ['name' => 'Hacked'])
             ->assertForbidden();
    }

    // Staff can change own password
    public function test_staff_can_change_own_password(): void
    {
        $this->apiAs($this->staff)
             ->putJson('/api/v1/account/password', [
                 'current_password'      => 'password',
                 'password'              => 'newpassword123',
                 'password_confirmation' => 'newpassword123',
             ])->assertOk();
    }

    // Wrong current password returns 422
    public function test_wrong_current_password_returns_422(): void
    {
        $this->apiAs($this->staff)
             ->putJson('/api/v1/account/password', [
                 'current_password'      => 'wrongpassword',
                 'password'              => 'newpassword123',
                 'password_confirmation' => 'newpassword123',
             ])->assertUnprocessable()
               ->assertJsonPath('success', false);
    }
}
```

---

## Result Tests

### API (`tests/Feature/Api/ResultApiTest.php`)
```php
class ResultApiTest extends BaseTestCase
{
    // All roles can read results
    public function test_all_roles_can_read_results(): void
    {
        Result::factory(3)->create();

        foreach ([$this->admin, $this->master, $this->staff] as $user) {
            $this->apiAs($user)
                 ->getJson('/api/v1/results?date='. today()->toDateString() .'&session=morning')
                 ->assertOk();
        }
    }

    // Only admin can enter results
    public function test_only_admin_can_enter_results(): void
    {
        $payload = ['session' => 'morning', 'position' => 'A', 'number' => '25'];

        $this->apiAs($this->master)->postJson('/api/v1/results', $payload)->assertForbidden();
        $this->apiAs($this->staff)->postJson('/api/v1/results', $payload)->assertForbidden();
        $this->apiAs($this->admin)->postJson('/api/v1/results', $payload)->assertCreated();
    }
}
```

---

## Setting Tests

### API (`tests/Feature/Api/SettingApiTest.php`)
```php
class SettingApiTest extends BaseTestCase
{
    // Staff and admin can read settings
    public function test_all_roles_can_read_settings(): void
    {
        foreach ([$this->admin, $this->master, $this->staff] as $user) {
            $this->apiAs($user)->getJson('/api/v1/settings')->assertOk();
        }
    }

    // Master cannot access settings
    public function test_master_cannot_update_settings(): void
    {
        $this->apiAs($this->master)
             ->putJson('/api/v1/settings', ['device' => '80MM'])
             ->assertForbidden();
    }

    // Only admin can update commission
    public function test_only_admin_can_update_commission(): void
    {
        $this->apiAs($this->staff)->putJson('/api/v1/settings/commission', ['type' => 'custom'])->assertForbidden();
        $this->apiAs($this->master)->putJson('/api/v1/settings/commission', ['type' => 'custom'])->assertForbidden();
        $this->apiAs($this->admin)->putJson('/api/v1/settings/commission', ['type' => 'custom'])->assertOk();
    }
}
```

---

## Code Generation Instructions

When generating tests for a screen:

1. **Always extend** `BaseTestCase` — never `TestCase` directly.
2. **Test all 3 roles** for each endpoint/route — admin, master, staff.
3. **Test both happy path and failure cases:**
   - Valid request → correct response shape
   - Invalid/missing fields → 422 with `errors` keys
   - Wrong role → 403 Forbidden
   - Unauthenticated → 401 Unauthorized
4. **Web tests** use `assertInertia()` — verify component name and prop structure.
5. **API tests** use `assertJsonPath()` and `assertJsonStructure()` — verify `success`, `data`, `message`.
6. **Use factories** — never hardcode IDs or raw `DB::insert()`.
7. **Use `assertModelMissing()`** for delete assertions — not `assertDatabaseMissing`.
8. **Scope tests:** master sees own-staff data only; staff sees own data only — always write a test that proves the boundary.
9. **Name tests descriptively** using snake_case: `test_master_cannot_edit_others_staff`.
10. Always run: `docker compose exec app php artisan test --filter=<TestClass>` to verify before reporting done.

---

## Argument: `$ARGUMENTS`

Generate tests for the screen and layer specified.

**Parsing rules:**
- **Word 1** → screen: `auth` `home` `record` `result` `report` `setting` `account` `all`
- **Word 2** → layer: `web` `api` `both` _(default: both)_

**Output order:**
1. `tests/Feature/BaseTestCase.php` — if not already generated
2. `database/factories/UserFactory.php` — factory states (admin/master/staff)
3. Web test file (`tests/Feature/Web/<Screen>WebTest.php`) — if layer is `web` or `both`
4. API test file (`tests/Feature/Api/<Screen>ApiTest.php`) — if layer is `api` or `both`
5. Bash command to run the generated tests inside Docker
