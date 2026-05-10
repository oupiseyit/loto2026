# Bet Time Setting — Feature Specification

> Source: handwritten note `Sample/sample_time_bet_result.jpg`  
> Stack: Laravel 13 + Livewire 3 + Alpine.js + Tailwind CSS

---

## Overview

This feature introduces **fully dynamic, admin-managed lottery sessions**. An admin can create, edit, reorder, and delete sessions at any time. Each session has a result announcement time and configurable bet cutoff windows per bet-letter group. `BetForm` and API endpoints always load sessions from the database — nothing is hardcoded.

---

## Session Schedule

Sessions are **fully dynamic** — stored in `bet_time_settings` table, managed by admin. The default seed data matches the handwritten notes (4 sessions), but admin can add, edit, reorder, or delete any session.

**Default seed data:**

| session_key | Label | Result Time | Group 1 Closes | Group 2 Closes |
|---|---|---|---|---|
| `morning` | Morning (ព្រឹក) | **10:30** | A + F + Lo → **10:10** | B, C, D → **10:20** |
| `noon` | Noon (ថ្ងៃ) | **13:30** | A + F + Lo → **13:10** | B, C, D → **13:20** |
| `afternoon` | Afternoon (រសៀល) | **16:30** | A + F + Lo → **16:10** | B, C, D → **16:20** |
| `evening` | Evening (ល្ងាច) | **18:30** | Lo → **18:10** | A, B, C, D → **18:20** |

> `session_key` is a URL-safe slug (e.g. `morning`, `afternoon`). It is stored on `tickets.session` and `results.session` as a `VARCHAR` — **not an ENUM** — so admin can add new sessions without a schema migration.

---

## Cutoff Rules Logic

### Standard Pattern (morning / noon / afternoon)

```
result_time − 20 min  →  A, F, Lo bets CLOSE
result_time − 10 min  →  B, C, D bets CLOSE
result_time           →  Result announced
```

### Evening Exception

The evening session (18:30) reverses the group order:

```
18:10  →  Lo bets CLOSE         (20 min before result)
18:20  →  A, B, C, D bets CLOSE (10 min before result)
18:30  →  Result announced
```

> **Why the difference:** Lo (lottery number) bets for the evening session have an earlier cutoff because results from an external source must be confirmed before ABCD bets close.

### Session Status States

| State | Condition | UI Indicator |
|---|---|---|
| `open` | Before Group 1 cutoff | Green badge |
| `closing_soon` | Within 10 min of Group 1 cutoff | Amber badge |
| `partial` | Group 1 closed, Group 2 still open | Orange badge |
| `closed` | All groups closed, result pending | Red badge |
| `done` | Result announced | Gray badge |

---

## Bet Validation Rules

### Minimum Bet Amount by Number Length

The minimum bet amount depends on how many digits the number has:

| Number Type | Example | Minimum Amount |
|---|---|---|
| 2-digit number | `12` | **1,000** KHR |
| 3-digit number | `759` | **500** KHR |

> Applied to both `ABCD` and `LO` bet types.

### F Letter — Special Rules

The letter **F** (noon session only) has distinct rules:

| Rule | Value |
|---|---|
| Accepted number range | 0 – 10,000 |
| Special number `000` payout trigger | Win amount > 10,000 |
| Payout multiplier | Bet × 800 (e.g. 100 bet → 80,000 win) |

---

## Database Schema

### New Table: `bet_time_settings`

`session_key` is a free-form slug chosen by admin (e.g. `morning`, `vip_night`). **No ENUM** — admin can create any session without a schema migration.

```sql
CREATE TABLE bet_time_settings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_key     VARCHAR(50)  NOT NULL,                -- slug: 'morning', 'noon', etc.
    session_name    VARCHAR(100) NOT NULL,                -- display label: 'Morning', 'ព្រឹក'
    result_time     TIME         NOT NULL,                -- e.g. 10:30:00
    group1_types    JSON         NOT NULL,                -- e.g. ["A","F","LO"]
    group1_cutoff   TIME         NOT NULL,                -- e.g. 10:10:00
    group2_types    JSON         NOT NULL,                -- e.g. ["B","C","D"]
    group2_cutoff   TIME         NOT NULL,                -- e.g. 10:20:00
    is_active       BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    UNIQUE KEY uq_session_key (session_key)
);
```

**Default seed data (`BetTimeSettingSeeder`):**

| session_key | session_name | result_time | group1_types | group1_cutoff | group2_types | group2_cutoff |
|---|---|---|---|---|---|---|
| `morning` | Morning | 10:30 | `["A","F","LO"]` | 10:10 | `["B","C","D"]` | 10:20 |
| `noon` | Noon | 13:30 | `["A","F","LO"]` | 13:10 | `["B","C","D"]` | 13:20 |
| `afternoon` | Afternoon | 16:30 | `["A","F","LO"]` | 16:10 | `["B","C","D"]` | 16:20 |
| `evening` | Evening | 18:30 | `["LO"]` | 18:10 | `["A","B","C","D"]` | 18:20 |

### `tickets.session` — Change ENUM → VARCHAR

The existing column is `ENUM('morning','noon','evening')`. Replace with `VARCHAR(50)` so it accepts any `session_key` an admin creates:

```sql
ALTER TABLE tickets
  MODIFY session VARCHAR(50) NOT NULL;

-- Add FK-like index (soft reference — no hard FK to allow orphan safety)
ALTER TABLE tickets ADD INDEX idx_session (session);
```

Same change required on `results.session`:

```sql
ALTER TABLE results
  MODIFY session VARCHAR(50) NOT NULL;
ALTER TABLE results ADD INDEX idx_session (session);
```

> No hard `FOREIGN KEY` constraint — if admin deletes a session, historical tickets and results keep their `session_key` string intact for record keeping.

### Caching

Cache the active session list to avoid a DB hit on every `BetForm` render:

```php
// Cache key: 'bet_time_settings.active'
// TTL: 5 minutes (clear on any admin save/delete)
Cache::remember('bet_time_settings.active', 300, fn () =>
    BetTimeSetting::where('is_active', true)->orderBy('sort_order')->get()
);
```

---

## Code Changes Required

### 1. New Migrations

| File | Purpose |
|---|---|
| `<ts>_create_bet_time_settings_table.php` | Create `bet_time_settings` with `session_key` VARCHAR |
| `<ts>_change_tickets_session_to_varchar.php` | Drop ENUM, add VARCHAR(50) on `tickets.session` |
| `<ts>_change_results_session_to_varchar.php` | Drop ENUM, add VARCHAR(50) on `results.session` |

### 2. New Model: `BetTimeSetting`

**File:** `src/app/Models/BetTimeSetting.php`

```php
class BetTimeSetting extends Model
{
    protected $fillable = [
        'session_key', 'session_name', 'result_time',
        'group1_types', 'group1_cutoff',
        'group2_types', 'group2_cutoff',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'group1_types' => 'array',
        'group2_types' => 'array',
        'is_active'    => 'boolean',
    ];

    // All active sessions ordered — cached 5 min
    public static function active(): Collection
    {
        return Cache::remember('bet_time_settings.active', 300, fn () =>
            static::where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    // Status of this session for a given letter/type at the current time
    public function statusFor(string $letterOrType): string
    {
        $now = now()->format('H:i:s');
        $inGroup1 = in_array(strtoupper($letterOrType), $this->group1_types);
        $cutoff   = $inGroup1 ? $this->group1_cutoff : $this->group2_cutoff;

        if ($now >= $this->result_time)    return 'done';
        if ($now >= $cutoff)               return 'closed';
        if ($now >= date('H:i:s', strtotime($cutoff) - 600)) return 'closing_soon';
        return 'open';
    }
}
```

### 3. Update `BetForm.php` (Livewire)

**File:** `src/app/Livewire/BetForm.php`

Load sessions from DB instead of hardcoded array. Add cutoff check:

```php
// Load dynamic sessions for session-tab UI
#[Computed]
public function availableSessions(): Collection
{
    return BetTimeSetting::active();
}

private function isBetAllowed(string $sessionKey, string $letter, string $betType): bool
{
    $setting = BetTimeSetting::active()->firstWhere('session_key', $sessionKey);
    if (!$setting) return true;

    $now      = now()->format('H:i:s');
    $inGroup1 = in_array(strtoupper($letter),   $setting->group1_types)
             || in_array(strtoupper($betType),   $setting->group1_types);
    $cutoff   = $inGroup1 ? $setting->group1_cutoff : $setting->group2_cutoff;

    return $now < $cutoff;
}
```

Validate in `addBet()` and re-validate in `submitBets()`.

### 4. Update Session Tabs in `bet-form.blade.php`

**File:** `src/resources/views/livewire/bet-form.blade.php`

Replace hardcoded `['morning','noon','evening']` loop with dynamic sessions:

```blade
@foreach ($this->availableSessions as $s)
    <button wire:click="$set('session', '{{ $s->session_key }}')" ...>
        {{ $s->session_name }}
        {{-- status badge --}}
    </button>
@endforeach
```

### 5. Update `BetRequest.php`

**File:** `src/app/Http/Requests/BetRequest.php`

Replace hardcoded `in:morning,noon,evening` with DB-driven rule:

```php
use Illuminate\Validation\Rule;

'session' => [
    'required',
    'string',
    Rule::in(BetTimeSetting::active()->pluck('session_key')),
],
```

### 6. Update `ResultPage.php` (Livewire)

**File:** `src/app/Livewire/ResultPage.php`

Replace hardcoded session→positions map. For now all sessions share the same positions (A–D), except `noon` adds F/I/N. Store extra positions per session in `bet_time_settings` or keep the noon override:

```php
// Dynamic: load session list from DB
public function availableSessions(): Collection
{
    return BetTimeSetting::active();
}

// Positions still controlled by letters known per session
// (noon uses A,B,C,D,F,I,N — others use A,B,C,D)
```

### 7. New Livewire Component: `BetTimeSettings` (Admin CRUD)

**File:** `src/app/Livewire/BetTimeSettings.php`

Full CRUD — admin can create, edit, reorder, toggle, and delete sessions:

```php
class BetTimeSettings extends Component
{
    public Collection $sessions;
    public ?BetTimeSetting $editing = null;

    // Form fields
    public string $session_key   = '';
    public string $session_name  = '';
    public string $result_time   = '';
    public array  $group1_types  = [];
    public string $group1_cutoff = '';
    public array  $group2_types  = [];
    public string $group2_cutoff = '';
    public bool   $is_active     = true;

    public function mount(): void
    {
        $this->sessions = BetTimeSetting::orderBy('sort_order')->get();
    }

    public function openCreate(): void { $this->reset(...); $this->editing = new BetTimeSetting; }
    public function openEdit(int $id): void { $this->editing = BetTimeSetting::findOrFail($id); /* fill form */ }

    public function save(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        // validate + upsert + bust cache
        Cache::forget('bet_time_settings.active');
        $this->sessions = BetTimeSetting::orderBy('sort_order')->get();
        $this->editing  = null;
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        BetTimeSetting::findOrFail($id)->delete();
        Cache::forget('bet_time_settings.active');
        $this->sessions = BetTimeSetting::orderBy('sort_order')->get();
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $s = BetTimeSetting::findOrFail($id);
        $s->update(['is_active' => !$s->is_active]);
        Cache::forget('bet_time_settings.active');
        $this->sessions = BetTimeSetting::orderBy('sort_order')->get();
    }
}
```

**View:** `src/resources/views/livewire/bet-time-settings.blade.php`
**Include in settings page:** `src/resources/views/setting/bet-time-section.blade.php`

### 8. Update Language Files

**Files:** `src/lang/en.json`, `km.json`, `vi.json`, `th.json`

Add keys:

```json
"bet_closed":         "Betting closed for this session",
"session_open":       "Open",
"session_closing":    "Closing soon",
"session_closed":     "Closed",
"session_partial":    "Partially closed",
"bet_time_settings":  "Bet Time Settings",
"session_key":        "Session Key",
"session_name":       "Session Name",
"result_time":        "Result Time",
"cutoff_group1":      "Cutoff (Group 1)",
"cutoff_group2":      "Cutoff (Group 2)",
"add_session":        "Add Session",
"no_sessions":        "No sessions configured"
```

---

## Admin UI — Bet Time Settings (Full CRUD)

The Settings screen (`/setting`) gets a new **admin-only** section. Admin can add, edit, toggle, reorder, and delete sessions — no code deploy needed.

```
┌──────────────────────────────────────────────────────────────────┐
│  Bet Time Settings                    [admin only]  [+ Add]       │
│  ──────────────────────────────────────────────────────────────  │
│  ↕  Key          Name        Result  G1 Types  G1 Cut  G2 Types  G2 Cut  Active  Actions  │
│  ↕  morning      Morning     10:30   A,F,LO    10:10   B,C,D     10:20   ✓       [Edit][Del]  │
│  ↕  noon         Noon        13:30   A,F,LO    13:10   B,C,D     13:20   ✓       [Edit][Del]  │
│  ↕  afternoon    Afternoon   16:30   A,F,LO    16:10   B,C,D     16:20   ✓       [Edit][Del]  │
│  ↕  evening      Evening     18:30   LO        18:10   A,B,C,D   18:20   ✓       [Edit][Del]  │
└──────────────────────────────────────────────────────────────────┘
```

**Add / Edit modal fields:**

| Field | Input type | Notes |
|---|---|---|
| Session Key | text | Slug, unique, e.g. `morning` — cannot change after creation |
| Session Name | text | Display label, e.g. `Morning` / `ព្រឹក` |
| Result Time | time picker | When result is announced |
| Group 1 Types | checkbox multi | Letters / bet types (A, B, C, D, F, I, N, LO) |
| Group 1 Cutoff | time picker | Bets in Group 1 close at this time |
| Group 2 Types | checkbox multi | Remaining letters |
| Group 2 Cutoff | time picker | Bets in Group 2 close at this time |
| Active | toggle | Inactive sessions are hidden from BetForm |
| Sort Order | auto (drag) | Controls tab display order in BetForm |

**Delete rule:** Cannot delete a session that has existing `tickets` or `results` records referencing its `session_key`. Show error: *"Session 'morning' has X tickets. Deactivate instead."*

---

## Enforcement Logic — BetForm Flow

```
User selects session → BetForm loads BetTimeSetting for that session
User clicks "Add Bet"
    └── isBetAllowed(session, letter, betType)?
        ├── Yes → add to bet list
        └── No  → flash error "Betting for A in morning session has closed."

User clicks "Submit"
    └── re-validate all bets in draft against current time
        ├── All still valid → submit ticket (status: pending)
        └── Any expired    → show error, remove expired bets, require review
```

Session tabs show live status badge (open / closing soon / partial / closed) updated via Livewire polling every 60 seconds:

```php
#[Polling(1000 * 60)] // every 60 seconds
public function refreshSessionStatus(): void
{
    // triggers re-render of status badges
}
```

---

## Migration Order

```bash
docker compose exec app php artisan migrate
# 1. create_bet_time_settings_table        (session_key VARCHAR, not ENUM)
# 2. change_tickets_session_to_varchar     (drop ENUM, add VARCHAR(50))
# 3. change_results_session_to_varchar     (drop ENUM, add VARCHAR(50))

docker compose exec app php artisan db:seed --class=BetTimeSettingSeeder
# inserts the 4 default sessions (morning/noon/afternoon/evening)
```

> After this migration, adding a new session requires **only** an admin UI action — no schema change, no code deploy.

---

## Implementation Checklist

### Database
- [ ] Migration: `create_bet_time_settings_table` — `session_key` VARCHAR, no ENUM
- [ ] Migration: `change_tickets_session_to_varchar` — drop ENUM → VARCHAR(50)
- [ ] Migration: `change_results_session_to_varchar` — drop ENUM → VARCHAR(50)
- [ ] Seeder: `BetTimeSettingSeeder` — 4 default rows

### Model & Cache
- [ ] Model: `BetTimeSetting.php` — `active()` cached method + `statusFor()` helper
- [ ] Cache bust in all write paths (save, delete, toggleActive)

### Admin CRUD UI
- [ ] Livewire: `BetTimeSettings.php` — create / edit / delete / toggleActive
- [ ] View: `resources/views/livewire/bet-time-settings.blade.php` — table + modal
- [ ] View: `resources/views/setting/bet-time-section.blade.php` — include in settings
- [ ] Guard: show section and allow write only for `admin` role
- [ ] Delete guard: block delete if session has existing tickets/results

### BetForm Enforcement
- [ ] `BetForm.php` — `availableSessions()` computed from `BetTimeSetting::active()`
- [ ] `BetForm.php` — `isBetAllowed()` cutoff check in `addBet()` + `submitBets()`
- [ ] `bet-form.blade.php` — replace hardcoded session array with `$this->availableSessions`
- [ ] `bet-form.blade.php` — session status badges (open / closing_soon / partial / closed)

### Validation
- [ ] `BetRequest.php` — `Rule::in(BetTimeSetting::active()->pluck('session_key'))`
- [ ] `ResultPage.php` — load session list from `BetTimeSetting::active()` (not hardcoded)
- [ ] `routes/api.php` + API controllers — same dynamic session validation

### Language
- [ ] `lang/en.json` — add 12 new keys
- [ ] `lang/km.json` — Khmer translations
- [ ] `lang/vi.json` — Vietnamese translations
- [ ] `lang/th.json` — Thai translations
