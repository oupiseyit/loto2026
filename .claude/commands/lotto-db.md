# Lotto — Database Schema

MySQL 8.0. All tables use `id` (BIGINT UNSIGNED AUTO_INCREMENT PK), `created_at`, `updated_at` timestamps.

---

## Tables Overview

| Table | Purpose |
|---|---|
| `users` | Admin / master / staff accounts |
| `bets` | Individual bet entries (one row per number per ticket) |
| `tickets` | Bet session grouping (one ticket = one submission) |
| `results` | Winning numbers per session per date |
| `settings` | Per-user app preferences (printer, commission) |

---

## `users`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT UNSIGNED | PK |
| `name` | VARCHAR(100) | Display name |
| `username` | VARCHAR(50) | Unique login name |
| `password` | VARCHAR(255) | Bcrypt hash |
| `role` | ENUM('admin','master','staff') | Access level |
| `created_by` | BIGINT UNSIGNED NULL | FK → `users.id`; NULL for admin; master's id for staff |
| `is_active` | TINYINT(1) | Default `1`; soft-disable without delete |
| `remember_token` | VARCHAR(100) NULL | Laravel web remember-me |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Indexes:** `UNIQUE(username)`, `INDEX(created_by)`, `INDEX(role)`

**Relationships:**
- `User hasMany User` (master → staff via `created_by`)
- `User hasMany Ticket`
- `User hasOne Setting`

---

## `tickets`

One ticket = one bet submission (one press of the Submit button).

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT UNSIGNED | PK |
| `user_id` | BIGINT UNSIGNED | FK → `users.id` |
| `session` | ENUM('morning','noon','evening') | ព្រឹក / ថ្ងៃ / ល្ងាច |
| `bet_date` | DATE | The date being bet on |
| `total_amount` | DECIMAL(12,2) | Sum of all bets in this ticket |
| `invoice_number` | VARCHAR(30) | Auto-generated, unique per ticket |
| `status` | ENUM('pending','won','lost') | Default `pending` |
| `win_amount` | DECIMAL(12,2) | Default `0.00`; set after result |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Indexes:** `INDEX(user_id)`, `INDEX(bet_date)`, `INDEX(session)`, `UNIQUE(invoice_number)`

**Relationships:**
- `Ticket belongsTo User`
- `Ticket hasMany Bet`

---

## `bets`

One row per number entered within a ticket.

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT UNSIGNED | PK |
| `ticket_id` | BIGINT UNSIGNED | FK → `tickets.id` |
| `user_id` | BIGINT UNSIGNED | FK → `users.id` (denormalized for fast scoping) |
| `bet_type` | ENUM('ABCD','LO') | Bet category |
| `letter` | VARCHAR(5) | e.g. `A`, `B`, `AB`, `ABCD` |
| `position` | VARCHAR(5) | `X`, `W`, `H`, `W*` |
| `number` | VARCHAR(10) | The bet number entered |
| `amount` | DECIMAL(10,2) | Bet amount in KHR |
| `is_winner` | TINYINT(1) | Default `0`; set after result |
| `win_amount` | DECIMAL(12,2) | Default `0.00` |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Indexes:** `INDEX(ticket_id)`, `INDEX(user_id)`, `INDEX(number)`, `INDEX(bet_date)` *(composite: `user_id, bet_date`)*

**Relationships:**
- `Bet belongsTo Ticket`
- `Bet belongsTo User`

---

## `results`

Winning numbers entered by admin after each session draw.

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT UNSIGNED | PK |
| `result_date` | DATE | Draw date |
| `session` | ENUM('morning','noon','evening') | |
| `position` | VARCHAR(5) | `A`, `B`, `C`, `D`, etc. |
| `number` | VARCHAR(10) | Winning number |
| `entered_by` | BIGINT UNSIGNED | FK → `users.id` (admin only) |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Indexes:** `UNIQUE(result_date, session, position)`, `INDEX(result_date)`, `INDEX(session)`

**Relationships:**
- `Result belongsTo User` (via `entered_by`)

---

## `settings`

Per-user preferences (one row per user, created on first save).

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT UNSIGNED | PK |
| `user_id` | BIGINT UNSIGNED | FK → `users.id`, UNIQUE |
| `bluetooth_enabled` | TINYINT(1) | Default `0` |
| `printer_size` | ENUM('58mm','80mm') | Default `58mm` |
| `logo_mode` | ENUM('logo','text') | Default `logo` |
| `commission_mode` | ENUM('default','custom') | Default `default`; admin only |
| `commission_value` | DECIMAL(5,2) NULL | Custom commission %; NULL = use default |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Indexes:** `UNIQUE(user_id)`

**Relationships:**
- `Setting belongsTo User`

---

## Eloquent Scoping Rules

```php
// Staff — own records only
->where('user_id', auth()->id())

// Master — own + staff they created
->whereHas('user', fn($q) => $q
    ->where('id', auth()->id())
    ->orWhere('created_by', auth()->id())
)

// Admin — no scope (sees all)
```

For tickets/bets, scope through the `user_id` column (denormalized — no join needed).

---

## Migration Order

Run in this order to satisfy FK constraints:

1. `create_users_table` (with `created_by` nullable FK)
2. `create_tickets_table`
3. `create_bets_table`
4. `create_results_table`
5. `create_settings_table`

---

## Seeder — First Admin

```php
// database/seeders/AdminSeeder.php
User::create([
    'name'       => 'Admin',
    'username'   => 'admin',
    'password'   => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'change_me')),
    'role'       => 'admin',
    'created_by' => null,
    'is_active'  => 1,
]);
```

Run: `docker compose exec app php artisan db:seed --class=AdminSeeder`

> Change the password immediately after first login.

---

## Key Design Notes

- `user_id` is denormalized onto `bets` to avoid joins when scoping large datasets
- `invoice_number` on tickets is human-readable for receipts (e.g. `INV-20260414-0001`)
- `is_active` on users allows disabling accounts without cascade-deleting bet history
- `commission_value` is NULL when `commission_mode = 'default'` — app reads system default instead
- All money columns use `DECIMAL(12,2)` — never `FLOAT` for currency
