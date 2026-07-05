# run_all_verify.md — Add a "Run all" button to the /verify dashboard

A prompt for an AI agent working on **Lotto2026** (`src/` is the Laravel root).

Mirror the phase-3-verify dashboard's **"Run all"** button. There, the grid
loads with empty (`—`) verdicts and runs nothing until you click **Run all**,
which executes the matrix, fills verdicts in place, and shows a ✅/❌/⛔ summary
with a `Running…` loading state.

Lotto2026's `/verify` does the opposite: it runs the **whole 33-fixture matrix
server-side on every page load** and server-renders the result — so every
refresh re-seeds the DB (rolled-back transaction) and re-runs everything. Fix
that: render instantly, run on click.

> The backend already has everything. Do **not** add routes or runner code.
> Reuse: `GET /verify/manifest` (cheap fixture list, no DB), `GET /verify/run`
> (runs all → `{pass, counts, results}`), `GET /verify/run/{endpoint}/{fixture}`
> (one result). All are `config('app.debug')`-gated in `VerifyController`.

## Goal

`/verify` renders the fixture grid immediately from the manifest with `—`
verdicts; the matrix runs only when the user clicks **Run all** (or a per-row
run). One source of truth stays the server — the button just calls the existing
endpoint.

## What to change

### 1. Backend — one tweak (`src/app/Http/Controllers/VerifyController.php`)

`dashboard()` must stop running the matrix on load. Replace the `->run()` call
with the cheap manifest:

```php
public function dashboard(): \Illuminate\View\View
{
    abort_unless(config('app.debug'), 403);
    $manifest = (new \App\Verify\VerifyRunner())->manifest();
    return view('verify', compact('manifest'));
}
```

Leave `manifest()`, `runAll()`, `runOne()` untouched. No route changes.

### 2. Frontend (`src/resources/views/verify.blade.php`)

The page is plain HTML + inline `<style>` today — keep it that way. **No build
step, no framework, no CDN** — one inline `<script>` with `fetch`.

- Render one row per `$manifest` entry, grouped by `endpoint` (reuse the
  existing `.endpoint-group` row pattern). Each fixture row starts with a `—`
  verdict cell and an empty checks cell. Add `data-row="{{ $m['endpoint'] }}::{{ $m['fixture'] }}"`
  to each `<tr>` so JS can find it. Keep the `probe` tag for `type === 'probe'`.
- Add a **Run all** button near the counts. Optionally a small "run" link per
  row that calls `/verify/run/{endpoint}/{fixture}`.
- Inline JS:
  - `runAll()`: set button text `Running…` + `disabled`; `fetch('/verify/run',
    { headers: { Accept: 'application/json' } })`; on success iterate
    `json.results`, find each row by `[data-row="${r.endpoint}::${r.fixture}"]`,
    and paint it (below); update the summary counts from `json.counts`; restore
    the button. On fetch/HTTP error, show a BLOCKED-style banner and restore the
    button.
  - Paint a row: set the verdict badge (`.verdict.PASS|FAIL|BLOCKED`), render
    each check as a chip (`.check.ok|fail|warn`, `title` = `check.detail`), and
    if `verdict !== 'PASS'` and `r.body` is present, show the `.body-dump`
    (pretty-printed JSON).
- **Reuse the existing CSS classes** (`.verdict.*`, `.check.*`, `.counts`,
  `.count.pass|fail|blocked`, `.body-dump`, `.type-probe`). Do not restyle.

## Guardrails

- Vanilla JS only — the page is already dependency-free; keep it (ponytail).
- Keep the `config('app.debug')` gate; the button hits the same gated endpoint.
- Don't reimplement run logic in JS. The button calls `/verify/run`; the server
  remains the single source of truth shared by the dashboard, the JSON endpoint,
  and `php artisan verify:run`.
- Escape/encode any response text inserted into the DOM (use `textContent`, not
  `innerHTML`, for check details and body dumps) — the body may contain
  attacker-influenced values.

## Acceptance

1. Load `/verify` → every fixture shows `—`; no matrix ran on load (no seed).
2. Click **Run all** → button shows `Running…`, then verdicts + check chips +
   summary fill in. Counts match `docker exec lotto_app php artisan verify:run`
   (33 PASS currently).
3. Drop `success` from one controller response → **Run all** → that row flips to
   FAIL with the `envelope` check detail and a body dump. Revert.
4. Clicking **Run all** twice re-runs cleanly (verdicts reset to `—` or repaint,
   no duplicate rows).
