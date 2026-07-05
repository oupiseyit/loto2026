<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verify — HT ភ្នាក់</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: ui-monospace, monospace; font-size: 13px; background: #fff; color: #1a1a1a; padding: 24px; }
h1 { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 4px; }
.meta { color: #888; margin-bottom: 20px; font-size: 12px; }
.toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap; }
.counts { display: flex; gap: 12px; }
.count { padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 14px; }
.count.pass    { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.count.fail    { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.count.blocked { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.btn-run   { padding: 8px 20px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; border: 1px solid #4f46e5; background: #4f46e5; color: #fff; font-family: inherit; }
.btn-replay{ padding: 8px 20px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; border: 1px solid #0891b2; background: #0891b2; color: #fff; font-family: inherit; }
.btn-run:disabled, .btn-replay:disabled { opacity: .55; cursor: not-allowed; }
.btn-row { font-size: 10px; color: #4f46e5; background: none; border: none; cursor: pointer; font-family: inherit; padding: 0; text-decoration: underline; }
.btn-row:disabled { opacity: .4; cursor: not-allowed; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 6px 10px; color: #888; font-size: 11px; text-transform: uppercase; letter-spacing: .08em; border-bottom: 1px solid #e5e7eb; }
td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
tr:hover td { background: #f9fafb; }
tr.row-active td  { background: #eff6ff !important; }
.verdict { font-weight: 700; font-size: 11px; padding: 2px 7px; border-radius: 4px; display: inline-block; }
.verdict.PASS    { background: #f0fdf4; color: #166534; }
.verdict.FAIL    { background: #fef2f2; color: #991b1b; }
.verdict.BLOCKED { background: #fffbeb; color: #92400e; }
.verdict.pending { background: #f3f4f6; color: #9ca3af; }
.verdict.running { background: #dbeafe; color: #1e40af; }
.type-probe { font-size: 10px; color: #4f46e5; background: #eef2ff; padding: 1px 5px; border-radius: 3px; }
.checks { margin-top: 4px; display: flex; flex-wrap: wrap; gap: 4px; }
.check { font-size: 11px; padding: 1px 6px; border-radius: 3px; }
.check.ok   { color: #166534; background: #f0fdf4; }
.check.fail { color: #991b1b; background: #fef2f2; font-weight: 700; }
.check.warn { color: #92400e; background: #fffbeb; }
.body-dump { margin-top: 6px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; padding: 8px; color: #991b1b; font-size: 11px; white-space: pre-wrap; max-height: 120px; overflow-y: auto; }
.endpoint-group { color: #9ca3af; font-size: 11px; padding: 10px 10px 4px; text-transform: uppercase; letter-spacing: .1em; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
.banner { padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; display: none; }
</style>
</head>
<body>

<h1>⚡ HT ភ្នាក់ — Verify Dashboard</h1>
<div class="meta">{{ now()->format('Y-m-d H:i:s') }} · APP_ENV={{ config('app.env') }} · <a href="/verify/run" style="color:#4f46e5;">JSON</a> · <a href="/verify/manifest" style="color:#4f46e5;">Manifest</a></div>

<div id="err-banner" class="banner"></div>

<div class="toolbar">
    <div class="counts">
        <div class="count pass"    id="ct-pass">— PASS</div>
        <div class="count fail"    id="ct-fail">— FAIL</div>
        <div class="count blocked" id="ct-blocked">— BLOCKED</div>
    </div>
    <button class="btn-run"    id="btn-run"    onclick="runAll()">Run all</button>
    <button class="btn-replay" id="btn-replay" onclick="location.href='/verify/replay'">▶ Replay all</button>
</div>

@php $grouped = collect($manifest)->reject(fn($m) => str_starts_with($m['path'], '/api/'))->groupBy('endpoint'); @endphp

<table>
<thead><tr>
    <th>Endpoint / Fixture</th>
    <th>Verdict</th>
    <th>Checks</th>
</tr></thead>
<tbody>
@foreach ($grouped as $ep => $rows)
    <tr><td colspan="3" class="endpoint-group">{{ $ep }}</td></tr>
    @foreach ($rows as $m)
    <tr data-row="{{ $m['endpoint'] }}::{{ $m['fixture'] }}">
        <td>
            {{ $m['fixture'] }}
            @if ($m['type'] === 'probe') <span class="type-probe">probe</span> @endif
            <button class="btn-row" onclick="runOne('{{ addslashes($m['endpoint']) }}','{{ addslashes($m['fixture']) }}',this)">run</button>
        </td>
        <td><span class="verdict pending">—</span></td>
        <td><div class="checks"></div></td>
    </tr>
    @endforeach
@endforeach
</tbody>
</table>

<script>
function paintRow(r) {
    var tr = document.querySelector('[data-row="' + r.endpoint + '::' + r.fixture + '"]');
    if (!tr) return;
    var vSpan = tr.querySelector('.verdict');
    vSpan.className = 'verdict ' + r.verdict;
    vSpan.textContent = r.verdict;

    var checksDiv = tr.querySelector('.checks');
    checksDiv.textContent = '';
    (r.checks || []).forEach(function(c) {
        var s = document.createElement('span');
        s.className = 'check ' + c.result;
        s.title = c.detail || '';
        s.textContent = c.name;
        checksDiv.appendChild(s);
    });

    var dump = tr.querySelector('.body-dump');
    if (dump) dump.remove();
    if (r.verdict !== 'PASS' && r.body) {
        var d = document.createElement('div');
        d.className = 'body-dump';
        try { d.textContent = JSON.stringify(JSON.parse(r.body), null, 2); }
        catch(e) { d.textContent = r.body; }
        checksDiv.after(d);
    }
}

function resetRows() {
    document.querySelectorAll('[data-row]').forEach(function(tr) {
        tr.classList.remove('row-active');
        tr.querySelector('.verdict').className = 'verdict pending';
        tr.querySelector('.verdict').textContent = '—';
        tr.querySelector('.checks').textContent = '';
        var dump = tr.querySelector('.body-dump');
        if (dump) dump.remove();
    });
}

function updateCounts(counts) {
    document.getElementById('ct-pass').textContent    = (counts.PASS    || 0) + ' PASS';
    document.getElementById('ct-fail').textContent    = (counts.FAIL    || 0) + ' FAIL';
    document.getElementById('ct-blocked').textContent = (counts.BLOCKED || 0) + ' BLOCKED';
}

function showErr(msg) {
    var b = document.getElementById('err-banner');
    b.style.display = 'block';
    b.textContent = msg;
}
function clearErr() {
    var b = document.getElementById('err-banner');
    b.style.display = 'none';
    b.textContent = '';
}

function setBusy(busy) {
    document.getElementById('btn-run').disabled = busy;
    document.querySelectorAll('.btn-row').forEach(function(b) { b.disabled = busy; });
}

/* ── Run all (batch) ─────────────────────────────────────────── */
function runAll() {
    setBusy(true);
    document.getElementById('btn-run').textContent = 'Running…';
    clearErr();
    resetRows();

    fetch('/verify/run', { headers: { Accept: 'application/json' } })
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function(json) {
            (json.results || []).forEach(paintRow);
            updateCounts(json.counts || {});
        })
        .catch(function(e) { showErr('Run failed: ' + e.message); })
        .finally(function() {
            setBusy(false);
            document.getElementById('btn-run').textContent = 'Run all';
        });
}

/* ── Per-row run ─────────────────────────────────────────────── */
function runOne(endpoint, fixture, btn) {
    btn.disabled = true;
    var tr = document.querySelector('[data-row="' + endpoint + '::' + fixture + '"]');
    if (tr) {
        tr.querySelector('.verdict').className = 'verdict running';
        tr.querySelector('.verdict').textContent = '…';
    }

    fetch('/verify/run/' + encodeURIComponent(endpoint) + '/' + encodeURIComponent(fixture),
          { headers: { Accept: 'application/json' } })
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function(r) { paintRow(r); })
        .catch(function() {
            if (tr) {
                tr.querySelector('.verdict').className = 'verdict BLOCKED';
                tr.querySelector('.verdict').textContent = 'ERR';
            }
        })
        .finally(function() { btn.disabled = false; });
}
</script>

</body>
</html>
