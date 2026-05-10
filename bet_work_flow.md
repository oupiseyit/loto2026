# Bet Workflow — Staff Role Analysis

## Overview

Staff is the lowest-privilege role. They can **submit bets**, **view their own records**, and **change their own password**. They cannot view reports, manage other users, or create/edit lottery results.

---

## Data Model

### Tables involved

```
users
  id | name | username | role (staff) | created_by (master's id) | is_active

tickets
  id | user_id | session | bet_date | total_amount | invoice_number | status | win_amount

bets
  id | ticket_id | user_id | bet_type | letter | position | number | amount | is_winner | win_amount

bet_time_settings
  id | session_key | session_name | result_time | group_type | group1_types | group1_cutoff
     | group2_types | group2_cutoff | is_active | sort_order

bet_categories
  id | name (P1-P8, Lo23/Lo25/Lo27) | type (1=P, 2=LO) | status | sort_order
```

### Relationships

```
User (staff)
  └── has many → Ticket  (user_id)
        └── has many → Bet  (ticket_id)
```

### Money rules

- All amounts: `DECIMAL(12,2)` — never FLOAT
- `user_id` is **denormalized** on `bets` to allow fast scoping without joins

---

## Ticket Status Lifecycle

```
[draft] ──submit──► [pending] ──result calculated──► [won] or [lost]
                         │
                    (invoice assigned)
```

| Status    | Meaning                                              |
|-----------|------------------------------------------------------|
| `draft`   | Bets being entered, not yet submitted                 |
| `pending` | Submitted; awaiting result calculation               |
| `won`     | Result calculated; at least one bet won              |
| `lost`    | Result calculated; no winning bets                   |

---

## Sessions & Cutoff Times

Controlled by `BetTimeSetting`. Each session has two **letter groups**, each with its own cutoff time.

| Session     | `session_key` |
|-------------|---------------|
| Morning     | `morning`     |
| Noon        | `noon`        |
| Evening     | `evening`     |

- If the current time is past a group's cutoff, bets for that letter group are **rejected**.
- Settings are **cached** and busted via `BetTimeSetting::bustCache()` when admin changes them.

---

## Bet Types & Fields

| Field      | Allowed Values                                              |
|------------|-------------------------------------------------------------|
| `letter`   | Values from `bet_time_settings.group_type` for the session  |
| `number`   | 2-digit string                                              |
| `amount`   | Decimal, min 0.01                                           |

> `bet_type` stored in the `bets` table is **derived automatically** from the selected letter:
> LO category names (`Lo23`, `Lo25`, `Lo27`) → `bet_type='LO'`; all others (`P1`–`P8`) → `bet_type='ABCD'`.
> The UI no longer exposes `bet_type` as a separate selector — `group_type` from the active session drives the available choices.

---

## Bet Time (from `bet_time_settings`)

Each session row in `bet_time_settings` defines **when** bets are accepted and **when** the result is announced.

| Column           | Type      | Purpose                                                    |
|------------------|-----------|------------------------------------------------------------|
| `session_key`    | string    | Internal key used in requests (`morning`, `noon`, `evening`) |
| `session_name`   | string    | Display name shown in UI (e.g. `ព្រឹក`, `ថ្ងៃ`, `ល្ងាច`)   |
| `result_time`    | time      | Time the lottery result is announced (`HH:MM:SS`)          |
| `group1_types`   | json      | Letter types in group 1 (close earlier)                    |
| `group1_cutoff`  | time      | Betting closes for group 1 at this time                    |
| `group2_types`   | json      | Letter types in group 2 (close later)                      |
| `group2_cutoff`  | time      | Betting closes for group 2 at this time                    |
| `is_active`      | boolean   | Whether this session is available for betting              |
| `sort_order`     | tinyint   | Display order in UI                                        |

### Session status logic (`BetTimeSetting::sessionStatus()`)

Determined by comparing current time (`now()`) against stored times:

```
now < group1_cutoff - 10min  →  open          (all bets accepted)
now ≥ group1_cutoff - 10min  →  closing_soon  (warning shown)
now ≥ group1_cutoff          →  partial       (group 1 letters closed)
now ≥ group2_cutoff          →  closed        (all betting closed)
now ≥ result_time            →  done          (result announced)
```

### Letter-level cutoff check (`BetTimeSetting::isBetAllowed()`)

```php
// group1_types letters use group1_cutoff
// all other letters use group2_cutoff
$cutoff = inGroup1($letter) ? $this->group1_cutoff : $this->group2_cutoff;
return now() < $cutoff;
```

This is checked **on every addBet() call** (Livewire web) and **on submitBets()** (re-validation).

---

## Web Workflow (Livewire BetForm)

**Route:** `GET /home` → `HomeController::index()` → renders `BetForm` Livewire component

### Step-by-step

```
Staff opens /home
    │
    ▼
BetForm mounts
    │ loads draft Ticket for today's session (if any)
    │ populates bets[] from existing draft
    ▼
Staff selects session / bet_type / letter / position / number / amount
    │
    ▼
Staff clicks "Add Bet"  →  BetForm::addBet()
    │ 1. Validates: number not empty, amount > 0
    │ 2. Checks BetTimeSetting cutoff for selected letter group
    │ 3. If no draft Ticket → create Ticket (status='draft', user_id=auth()->id())
    │ 4. Create Bet record (ticket_id, user_id, bet_type, letter, position, number, amount)
    │ 5. Update Ticket.total_amount = sum of all bets
    ▼
Staff repeats for more bets
    │
    ▼
Staff clicks "Submit"  →  BetForm::submitBets()
    │ 1. Re-validates cutoff times for all bets
    │ 2. Generates invoice_number: INV-{Ymd}-{padded_count}
    │ 3. Updates Ticket: status='pending', invoice_number, total_amount
    │ 4. Clears draft state / resets form
    ▼
Flash success: invoice number + total amount
```

### Remove a bet (before submit)

```
BetForm::removeBet(betId)
    │ 1. Deletes Bet record
    │ 2. If no bets remain → delete Ticket (cleanup draft)
    │ 3. Sync total_amount on Ticket
```

---

## API Workflow (Mobile)

**Auth:** Laravel Sanctum Bearer token

### Submit bets — `POST /api/v1/bets`

Request body:
```json
{
  "session": "morning",
  "bet_date": "2026-05-10",
  "bets": [
    { "bet_type": "ABCD", "letter": "A", "position": "X", "number": "25", "amount": 5000 },
    { "bet_type": "LO",   "letter": "Lo23", "position": "W", "number": "45", "amount": 3000 }
  ]
}
```

Processing flow:
```
BetRequest validates input
    │ session must be an active session_key
    │ bet_date required date
    │ bets[] array, min 1 item
    │ each bet: bet_type, letter, position, number, amount
    ▼
BetController::store()
    │ 1. PlaceBetDTO::fromRequest($request)
    │ 2. Generate invoice_number
    │ 3. Create Ticket (status='pending', user_id=auth()->id(), total_amount from DTO)
    │ 4. Create Bet records via BetItemDTO::toModelArray(ticketId, userId)
    ▼
Response 201:
{
  "invoice_number": "INV-20260510-0001",
  "total_amount": 8000
}
```

> Note: API bets go directly to `pending` — no draft step.

### List own tickets — `GET /api/v1/bets`

```
BetController::index()
    │ scope: Ticket::with('bets')->where('user_id', auth()->id())
    │ optional filters: ?date=2026-05-10 &session=morning
    │ paginated (20 per page)
```

### Get one ticket — `GET /api/v1/bets/{id}`

```
BetController::show()
    │ Ticket::with('bets')->find($id)
    │ returns 403 if ticket.user_id !== auth()->id()
```

---

## Viewing Records

**Web:** `GET /record` → `RecordController::index()`  
**API:** `GET /api/v1/records` → `Api\V1\RecordController::index()`

### Staff scope (always applied)

```php
->where('user_id', auth()->id())
```

### Filters available

| Filter    | Values                                 |
|-----------|----------------------------------------|
| `date`    | Required; date string                  |
| `session` | Optional; `morning` / `noon` / `evening` |
| `tab`     | `all` / `morning` / `noon` / `evening` / `winning` |

### Win calculation (performed in RecordController)

```
For selected date + session:
    1. Fetch Result records (lottery winning numbers)
    2. For each Bet in scope:
         if bet.number matches any result position number:
             bet.is_winner = true
             bet.win_amount = bet.amount × multiplier
    3. Ticket.win_amount = sum of winning bets
    4. Ticket.status = 'won' if win_amount > 0 else 'lost'
```

---

## Role Scoping Summary

| Action                        | Staff                              | Master                                    | Admin       |
|-------------------------------|------------------------------------|-------------------------------------------|-------------|
| Submit bets                   | Own only (`user_id=auth()->id()`)  | Own only                                  | Any         |
| List tickets                  | Own only                           | Own only                                  | All         |
| View records                  | Own only                           | All staff under them (`created_by`)       | All         |
| View reports                  | **Forbidden**                      | Allowed (own staff)                       | Allowed     |
| Create / edit results         | **Forbidden**                      | **Forbidden**                             | Allowed     |
| Manage staff accounts         | **Forbidden**                      | Own staff (create/update)                 | All         |
| View win amounts              | Own bets only                      | Own staff's bets                          | All         |

---

## Key Files

| Layer              | Path                                                                 | Purpose                              |
|--------------------|----------------------------------------------------------------------|--------------------------------------|
| Model              | [app/Models/Ticket.php](src/app/Models/Ticket.php)                   | Ticket with `submitted()` scope      |
| Model              | [app/Models/Bet.php](src/app/Models/Bet.php)                         | Bet with ticket/user relations       |
| Model              | [app/Models/BetTimeSetting.php](src/app/Models/BetTimeSetting.php)   | Session cutoff logic, cache          |
| Model              | [app/Models/BetCategory.php](src/app/Models/BetCategory.php)         | P / LO category definitions          |
| Web Controller     | [app/Http/Controllers/HomeController.php](src/app/Http/Controllers/HomeController.php)         | Dashboard (staff sees BetForm)       |
| Web Controller     | [app/Http/Controllers/RecordController.php](src/app/Http/Controllers/RecordController.php)     | Bet history + win calculation        |
| API Controller     | [app/Http/Controllers/Api/V1/BetController.php](src/app/Http/Controllers/Api/V1/BetController.php)   | index / store / show                 |
| API Controller     | [app/Http/Controllers/Api/V1/RecordController.php](src/app/Http/Controllers/Api/V1/RecordController.php) | Paginated records + totals           |
| Livewire           | [app/Livewire/BetForm.php](src/app/Livewire/BetForm.php)             | Interactive bet entry (web)          |
| Request            | [app/Http/Requests/BetRequest.php](src/app/Http/Requests/BetRequest.php)                 | Shared validation for web + API      |
| DTO                | [app/DTOs/PlaceBetDTO.php](src/app/DTOs/PlaceBetDTO.php)             | Bet submission data object           |
| DTO                | [app/DTOs/BetItemDTO.php](src/app/DTOs/BetItemDTO.php)               | Individual bet item                  |
| Routes (web)       | [routes/web.php](src/routes/web.php)                                 | `/home`, `/record`                   |
| Routes (API)       | [routes/api.php](src/routes/api.php)                                 | `/v1/bets`, `/v1/records`            |
| Migration          | [database/migrations/2026_04_14_153355_create_tickets_table.php](src/database/migrations/2026_04_14_153355_create_tickets_table.php) | Tickets schema   |
| Migration          | [database/migrations/2026_04_14_153356_create_bets_table.php](src/database/migrations/2026_04_14_153356_create_bets_table.php)     | Bets schema      |
| Migration          | [database/migrations/2026_05_04_000001_create_bet_time_settings_table.php](src/database/migrations/2026_05_04_000001_create_bet_time_settings_table.php) | Session cutoffs |

---

## Security Rules (Staff-specific)

1. `user_id` on Ticket and Bet is always set to `auth()->id()` — **never** taken from the request body.
2. `BetController::show()` returns `403` if `ticket.user_id !== auth()->id()`.
3. Reports (`/report`, `/api/v1/report/*`) return `403` for staff — enforced in controller, not just route middleware.
4. Result creation/editing (`POST /result`, `PUT /result/{id}`) restricted to admin by route middleware.
5. Staff cannot elevate their own role — role is set server-side on user creation only.
