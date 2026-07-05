# verify.md — Runtime verification for HT ភ្នាក់ (Lotto2026)

Runtime observation at the HTTP surface — not a replacement for PHPUnit tests, a thin layer on top.

> Reuse before you build. Tests under `tests/Feature/` and `BaseTestCase` helpers already exist. The verify layer drives the *running app*; PHPUnit asserts offline. Different jobs, shared setup.

## Mapping

| Concept | This project |
|---|---|
| Verifiable unit | One API endpoint or web route |
| Fixture | Named scenario: method + path + headers + body + expected shape |
| Probe fixture 🔍 | Adversarial: wrong role, missing token, bad input |
| Invariant | Predicate over the response (status, JSON shape, business rule) |
| `/verify/manifest` | Every endpoint × fixture × verifier as JSON |
| `/verify/run` | Full matrix — verdict grid |
| `/verify/run/{endpoint}/{fixture}` | One scenario, structured result |
| `/verify` | HTML dashboard (same data) |
| `php artisan verify:run` | Headless CI path, exits non-zero on any FAIL |

## Verdict taxonomy

`PASS | FAIL | BLOCKED | SKIP` — checks as `ok ✅ | fail ❌ | warn ⚠️ | probe 🔍`

- **BLOCKED** = couldn't observe (app down, migration missing, no seed) — distinct from **FAIL** = observed and wrong.
- **When in doubt, FAIL.**
- Every endpoint must have ≥1 probe fixture; zero probes = happy-path only.

## Endpoints to cover

### Mobile API (`/api/v1/` — Sanctum Bearer token)

| Endpoint | Key fixtures | Probes |
|---|---|---|
| `POST /login` | valid staff login → token | wrong password, missing fields |
| `GET /me` | returns role + user data | no token, expired token |
| `GET /bets` | staff sees own bets only | no token, wrong role |
| `POST /bets` | staff creates bet, session open | closed session, amount < 100, amount not multiple of 100 |
| `GET /records` | staff record list | no token |
| `GET /results` | all roles read | no token |
| `POST /results` | admin creates result | staff/master attempt → 403 |
| `GET /report/summary` | admin + master | staff attempt → 403 |
| `GET /settings` | all roles | no token |

### Web routes (session auth, Inertia/Livewire — check HTTP status + Inertia component)

| Route | Roles | Probes |
|---|---|---|
| `GET /home` | staff/master/admin | unauthenticated → redirect login |
| `GET /record` | all | unauthenticated → redirect |
| `GET /result` | all read; admin write | staff POST → 403 |
| `GET /setting` | admin + master | staff → 403 |
| `GET /report` | admin + master | staff → 403 |

## Role scoping invariants (must verify, not just test)

- **staff** bets response contains only `user_id = auth()->id()` rows — never another staff's bets. ✅ verified by `scope:staff` on `Bets/scope_staff_isolation` (seeds a 2nd staff's ticket and asserts it never leaks).
- **master** `/bets` returns only their own rows. ✅ verified by `scope:master`.
- **master** *report* contains only their own staff's data. ⏳ deferred — `RecordResource` exposes `user_name` only `whenLoaded('user')`, no owner id, so it isn't machine-checkable yet. Add owner id to the resource, then add `scope:*` on `/records`.
- **admin** sees all. (baseline)

## Response envelope contract (API only)

Every `/api/v1/` response carries a `success` boolean; successes add an optional
`message` and a `data` payload, errors add a `message`:

```json
// success
{ "success": true, "message": "OK", "data": ... }
// error
{ "success": false, "message": "Invalid credentials." }
```

Errors use **real HTTP status codes** (401 unauthenticated, 403 forbidden, 422
validation with an `errors` object) — not a body status field. The `envelope`
verifier asserts `success` is a present boolean. If a controller drops it, the
check fails in all three surfaces: dashboard, `GET /verify/run`, and
`php artisan verify:run`.

## Auth constraints to probe

- Missing `Authorization` header → 401
- Revoked / malformed token → 401
- Correct token, wrong role middleware → 403
- Staff token hitting admin-only route → 403

## What to build

1. **Specs** — `app/Verify/Specs/` — one file per endpoint group (`Auth`, `Bets`, `Records`, `Results`, `Report`, `Settings`). Each file: fixtures array + invariants array. Start with already-tested slices.

2. **Verifiers** — `app/Verify/Verifiers/`: `StatusVerifier`, `SchemaVerifier`, `EnvelopeContractVerifier`, `AuthVerifier`, `RoleScopeVerifier`. Add new kind = add class, register it. No controller changes.

3. **Routes** — behind `APP_DEBUG=true` / local env only:
   - `GET /verify` — HTML dashboard
   - `GET /verify/manifest` — JSON
   - `GET /verify/run` — full matrix
   - `GET /verify/run/{endpoint}/{fixture}` — single scenario

4. **Artisan command** — `php artisan verify:run` — same matrix code, exits non-zero on any FAIL.

5. **Reuse** `BaseTestCase` factories (`User::factory()->admin()`, `.master()`, `.staff()`) and `apiToken()` for fixture setup — don't reinvent the HTTP harness.

## Guardrails

- Never expose `/verify` routes in production (`APP_DEBUG` guard).
- Captured response body/headers go on every failing check — not a paraphrase.
- No abstraction with one implementation. 4 verifier classes + 1 command + 1 config array = done.
- Never commit `.env` with real credentials; test fixtures use factories + `RefreshDatabase`.

## Acceptance test

Break one envelope (drop `message` from a controller response). Confirm `envelope-contract` check fails with a precise diagnosis in all three surfaces: HTML dashboard, `GET /verify/run`, `php artisan verify:run` (non-zero exit). Then revert.

## To run:
docker compose exec app php artisan verify:run
# or filter:
docker compose exec app php artisan verify:run --endpoint=Auth
# browser:
http://localhost:8080/verify