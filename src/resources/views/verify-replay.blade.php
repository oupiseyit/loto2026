<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Replay — HT ភ្នាក់ Verify</title>
<style>
:root {
  --bg: #f7f8fa; --panel: #fff; --panel2: #eef0f4; --border: #d8dce4;
  --text: #1b1f27; --dim: #6a7183; --accent: #3a66e0;
  --pass: #1f9d63; --fail: #d4384e; --warn: #c28a12;
  --blocked: #7b4fd6; --probe: #2a8fb3;
  --mono: ui-monospace, SFMono-Regular, Menlo, monospace;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { min-height: 100%; }
body { background: var(--bg); color: var(--text); font: 14px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
a { color: var(--accent); text-decoration: none; }
a:hover { text-decoration: underline; }
button { font: inherit; background: var(--panel2); color: var(--text); border: 1px solid var(--border); border-radius: 6px; padding: 6px 14px; cursor: pointer; }
button:hover:not(:disabled) { border-color: var(--accent); }
button:disabled { opacity: .4; cursor: not-allowed; }

.replay-page { max-width: 1280px; margin: 0 auto; padding: 24px 24px 80px; }

/* Header */
.replay-header { margin-bottom: 18px; }
.replay-header-top { display: flex; align-items: baseline; gap: 14px; margin-bottom: 8px; }
.replay-header-top h1 { font-size: 20px; }
.replay-counter { font: 500 14px/1 var(--mono); color: var(--dim); font-variant-numeric: tabular-nums; }
.replay-controls { display: flex; align-items: center; gap: 8px; margin: 8px 0 10px; flex-wrap: wrap; }
.replay-progress { height: 5px; background: var(--panel2); border: 1px solid var(--border); border-radius: 4px; overflow: hidden; margin: 8px 0; }
.replay-progress-bar { height: 100%; background: var(--accent); transition: width 200ms ease; width: 0%; }
.replay-tally { display: flex; gap: 14px; font-variant-numeric: tabular-nums; font-size: 13.5px; margin-top: 6px; }
.s-pass { color: var(--pass); } .s-fail { color: var(--fail); } .s-blocked { color: var(--blocked); }

/* Body layout */
.replay-body { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 20px; align-items: start; }
@media (max-width: 860px) { .replay-body { grid-template-columns: 1fr; } }

/* Sidebar */
.step-list { position: sticky; top: 16px; max-height: calc(100vh - 40px); overflow-y: auto; background: var(--panel); border: 1px solid var(--border); border-radius: 10px; padding: 10px; }
.step-group { margin-bottom: 10px; }
.step-group:last-child { margin-bottom: 0; }
.step-group-head { font-size: 11px; text-transform: uppercase; letter-spacing: .07em; color: var(--dim); padding: 5px 8px 4px; font-weight: 600; }
.step-row { display: flex; align-items: stretch; gap: 3px; margin: 2px 0; }
.step-jump { flex: 1; display: flex; align-items: center; gap: 8px; text-align: left; border: 1px solid transparent; background: transparent; border-radius: 6px; padding: 5px 8px; font-size: 12.5px; min-width: 0; cursor: pointer; }
.step-jump:hover { background: var(--panel2); border: 1px solid transparent; }
.step-row.active .step-jump { background: color-mix(in srgb, var(--accent) 10%, var(--panel)); border-color: color-mix(in srgb, var(--accent) 35%, var(--border)); }
.step-status { width: 1.4em; flex-shrink: 0; text-align: center; font-size: 13px; }
.step-name { font-family: var(--mono); font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.step-row.v-pass .step-name { color: var(--pass); }
.step-row.v-fail .step-name { color: var(--fail); }
.step-row.v-blocked .step-name { color: var(--blocked); }

/* Replay main well */
.replay-main { background: linear-gradient(180deg, #e9ecf2 0%, #e2e6ee 100%); border: 1px solid var(--border); border-radius: 14px; padding: 28px; min-height: 400px; }

/* App-frame (browser window mockup) */
.app-frame { background: var(--panel); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 2px 4px rgba(16,24,40,.08), 0 20px 48px -12px rgba(16,24,40,.28); transition: box-shadow 300ms ease; }
.app-frame.v-pass    { box-shadow: 0 0 0 3px color-mix(in srgb, var(--pass) 35%, transparent), 0 20px 48px -12px rgba(16,24,40,.28); }
.app-frame.v-fail    { box-shadow: 0 0 0 3px color-mix(in srgb, var(--fail) 45%, transparent), 0 20px 48px -12px rgba(16,24,40,.28); }
.app-frame.v-blocked { box-shadow: 0 0 0 3px color-mix(in srgb, var(--blocked) 45%, transparent), 0 20px 48px -12px rgba(16,24,40,.28); }

.app-frame-titlebar { display: flex; align-items: center; gap: 7px; height: 38px; padding: 0 14px; background: linear-gradient(to bottom, #fafbfc, var(--panel2)); border-bottom: 1px solid var(--border); }
.tl-dot { width: 11px; height: 11px; border-radius: 50%; flex-shrink: 0; }
.tl-dot.r { background: #ff5f57; } .tl-dot.y { background: #febc2e; } .tl-dot.g { background: #28c840; }
.frame-title { margin-left: 10px; display: flex; align-items: baseline; gap: 8px; font-size: 13.5px; min-width: 0; overflow: hidden; }
.frame-title .kind { font-size: 9.5px; text-transform: uppercase; letter-spacing: .06em; color: var(--dim); border: 1px solid var(--border); border-radius: 4px; padding: 2px 5px; background: #fff; }
.frame-title .dim { color: var(--dim); font-family: var(--mono); font-size: 12.5px; }
.probe-chip { font-size: 11px; color: var(--probe); border: 1px solid color-mix(in srgb, var(--probe) 40%, var(--border)); border-radius: 4px; padding: 1px 6px; background: color-mix(in srgb, var(--probe) 8%, #fff); }

.frame-desc { padding: 9px 16px; font-size: 13px; color: var(--dim); background: #fcfcfd; border-bottom: 1px solid var(--border); font-family: var(--mono); display: flex; align-items: center; gap: 10px; }
.role-chip { font-size: 11px; color: var(--probe); background: color-mix(in srgb, var(--probe) 10%, #fff); border: 1px solid color-mix(in srgb, var(--probe) 30%, var(--border)); border-radius: 4px; padding: 1px 7px; margin-left: auto; }

.frame-body { position: relative; background: #fff; padding: 28px 24px 24px; min-height: 200px; display: flex; flex-direction: column; gap: 16px; }
.frame-body-label { position: absolute; top: 8px; right: 12px; font: 600 10px/1 var(--mono); text-transform: uppercase; letter-spacing: .1em; color: rgba(106,113,131,.5); user-select: none; }

.http-status { font: 700 42px/1 var(--mono); font-variant-numeric: tabular-nums; }
.http-status.s1 { color: var(--dim); }
.http-status.s2 { color: var(--pass); }
.http-status.s3 { color: var(--probe); }
.http-status.s4 { color: var(--warn); }
.http-status.s5 { color: var(--fail); }

.checks-row { display: flex; flex-wrap: wrap; gap: 6px; }
.chip { font: 12px/1 var(--mono); padding: 3px 9px; border-radius: 5px; }
.chip.ok      { background: color-mix(in srgb, var(--pass) 12%, #fff); color: var(--pass); border: 1px solid color-mix(in srgb, var(--pass) 30%, var(--border)); }
.chip.fail    { background: color-mix(in srgb, var(--fail) 12%, #fff); color: var(--fail); border: 1px solid color-mix(in srgb, var(--fail) 30%, var(--border)); font-weight: 700; }
.chip.warn    { background: color-mix(in srgb, var(--warn) 12%, #fff); color: var(--warn); border: 1px solid color-mix(in srgb, var(--warn) 30%, var(--border)); }
.body-dump { background: color-mix(in srgb, var(--fail) 6%, #fff); border: 1px solid color-mix(in srgb, var(--fail) 25%, var(--border)); border-radius: 6px; padding: 10px 12px; font: 12px/1.5 var(--mono); color: var(--fail); white-space: pre-wrap; max-height: 140px; overflow-y: auto; }

/* BetForm mockup */
.bet-form-mockup { margin-top: 14px; border-top: 1px solid var(--border); padding-top: 14px; display: flex; flex-direction: column; gap: 10px; }
.bfm-section { display: flex; flex-direction: column; gap: 4px; }
.bfm-section-label { font: 600 10px/1 var(--mono); text-transform: uppercase; letter-spacing: .08em; color: var(--dim); }
.bfm-chips { display: flex; gap: 4px; flex-wrap: wrap; }
.bfm-chip { font: 12px/1 var(--mono); padding: 4px 10px; border-radius: 5px; border: 1px solid var(--border); background: var(--panel2); color: var(--dim); transition: background 60ms, color 60ms, border-color 60ms; }
.bfm-chip.sel-pos  { background: #14532d; color: #fff; border-color: #14532d; }
.bfm-chip.sel-type { background: #7c2d12; color: #fff; border-color: #7c2d12; font-weight: 700; }
.bfm-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.bfm-field { border: 1px solid var(--border); border-radius: 6px; padding: 7px 11px; font: 13px/1.4 var(--mono); color: var(--dim); background: #fff; min-height: 32px; transition: border-color 120ms; }
.bfm-field.typing   { border-color: var(--accent); }
.bfm-field.has-value{ border-color: #7c2d12; color: var(--text); }
.bfm-cursor { display: inline-block; width: 1.5px; height: .85em; background: var(--accent); margin-left: 1px; vertical-align: middle; animation: bfm-blink .65s steps(1) infinite; }
@keyframes bfm-blink { 50% { opacity: 0; } }

.running-state { display: flex; align-items: center; gap: 10px; color: var(--dim); padding: 16px 0; font-size: 13px; }
.spinner { width: 13px; height: 13px; border: 2px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; display: inline-block; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

.frame-status { border-top: 1px solid var(--border); padding: 12px 16px; background: #fcfcfd; }
.frame-status.running { display: flex; align-items: center; gap: 10px; color: var(--dim); }
.frame-status.v-pass    { background: color-mix(in srgb, var(--pass) 6%, #fcfcfd); }
.frame-status.v-fail    { background: color-mix(in srgb, var(--fail) 6%, #fcfcfd); }
.frame-status.v-blocked { background: color-mix(in srgb, var(--blocked) 6%, #fcfcfd); }
.status-line { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.verdict-badge { display: inline-block; font: 600 11px/1 var(--mono); padding: 4px 8px; border-radius: 5px; letter-spacing: .04em; }
.verdict-badge.PASS    { background: color-mix(in srgb, var(--pass) 18%, transparent); color: var(--pass); }
.verdict-badge.FAIL    { background: color-mix(in srgb, var(--fail) 18%, transparent); color: var(--fail); }
.verdict-badge.BLOCKED { background: color-mix(in srgb, var(--blocked) 18%, transparent); color: var(--blocked); }
.status-sub { margin-left: auto; font-size: 12px; color: var(--dim); }

/* Summary */
.replay-summary { background: var(--panel); border: 1px solid var(--border); border-radius: 10px; padding: 22px 24px; }
.replay-summary h2 { margin: 0 0 10px; font-size: 20px; }
.summary-line { display: flex; gap: 16px; font-size: 16px; font-variant-numeric: tabular-nums; margin-bottom: 16px; }
.summary-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.summary-table th { text-align: left; color: var(--dim); font-weight: 500; border-bottom: 1px solid var(--border); padding: 4px 8px 6px; }
.summary-table td { padding: 7px 8px; border-bottom: 1px solid var(--border); font-family: var(--mono); font-size: 12.5px; }
.summary-table tr:last-child td { border-bottom: none; }
</style>
</head>
<body>

@php
$steps = collect($manifest)
    ->reject(fn($m) => str_starts_with($m['path'], '/api/'))
    ->values()
    ->toArray();
@endphp

<div class="replay-page">
  <div class="replay-header">
    <div class="replay-header-top">
      <a href="/verify">← dashboard</a>
      <h1>Replay <span id="replay-counter">— / {{ count($steps) }}</span></h1>
    </div>
    <div class="replay-controls" id="replay-controls"></div>
    <div class="replay-progress"><div class="replay-progress-bar" id="progress-bar"></div></div>
    <div class="replay-tally">
      <span class="s-pass"    id="t-pass">✅ 0</span>
      <span class="s-fail"    id="t-fail">❌ 0</span>
      <span class="s-blocked" id="t-blocked">⛔ 0</span>
    </div>
  </div>

  <div class="replay-body">
    <aside class="step-list" id="step-list" aria-label="All fixtures"></aside>
    <div  class="replay-main" id="replay-main"></div>
  </div>
</div>

<script>
var STEPS = @json($steps);
var DWELL = 900; // ms hold after verdict before advancing

// ── State ───────────────────────────────────────────────────────
var idx      = 0;
var playing  = true;
var done     = false;
var running  = false;
var results  = {};
var counts   = { PASS: 0, FAIL: 0, BLOCKED: 0 };
var advTimer = null;

function key(s) { return s.endpoint + '::' + s.fixture; }

// ── Sidebar ─────────────────────────────────────────────────────
function buildSidebar() {
    var aside = document.getElementById('step-list');
    var groups = [], map = {};
    STEPS.forEach(function(s, i) {
        if (!map[s.endpoint]) { map[s.endpoint] = []; groups.push(s.endpoint); }
        map[s.endpoint].push({ i: i, s: s });
    });
    groups.forEach(function(ep) {
        var g = document.createElement('div');
        g.className = 'step-group';
        var h = document.createElement('div');
        h.className = 'step-group-head';
        h.textContent = ep;
        g.appendChild(h);
        map[ep].forEach(function(row) {
            var stepRow = document.createElement('div');
            stepRow.className = 'step-row';
            stepRow.id = 'sr-' + row.i;
            var btn = document.createElement('button');
            btn.className = 'step-jump';
            btn.onclick = (function(i) { return function() { jumpTo(i); }; })(row.i);
            var st = document.createElement('span');
            st.className = 'step-status';
            st.id = 'ss-' + row.i;
            st.textContent = '○';
            var nm = document.createElement('span');
            nm.className = 'step-name';
            nm.textContent = (row.s.type === 'probe' ? '🔍 ' : '') + row.s.fixture;
            btn.appendChild(st); btn.appendChild(nm);
            stepRow.appendChild(btn);
            g.appendChild(stepRow);
        });
        aside.appendChild(g);
    });
}

function setStepVerdict(i, verdict) {
    var st = document.getElementById('ss-' + i);
    var sr = document.getElementById('sr-' + i);
    if (!st || !sr) return;
    sr.className = 'step-row' + (verdict ? ' v-' + verdict.toLowerCase() : '');
    st.textContent = verdict === 'PASS' ? '✅' : verdict === 'FAIL' ? '❌' : verdict === 'BLOCKED' ? '⛔' : verdict === 'running' ? '▸' : '○';
}

function setActiveStep(i) {
    document.querySelectorAll('.step-row').forEach(function(r) { r.classList.remove('active'); });
    var sr = document.getElementById('sr-' + i);
    if (sr) { sr.classList.add('active'); sr.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }
}

// ── Progress ─────────────────────────────────────────────────────
function updateProgress(n, total) {
    document.getElementById('replay-counter').textContent = n + ' / ' + total;
    document.getElementById('progress-bar').style.width = (total ? Math.round(n / total * 100) : 0) + '%';
}
function updateTally() {
    document.getElementById('t-pass').textContent    = '✅ ' + (counts.PASS    || 0);
    document.getElementById('t-fail').textContent    = '❌ ' + (counts.FAIL    || 0);
    document.getElementById('t-blocked').textContent = '⛔ ' + (counts.BLOCKED || 0);
}

// ── Controls ─────────────────────────────────────────────────────
function renderControls() {
    var ctrl = document.getElementById('replay-controls');
    ctrl.innerHTML = '';

    // Record button — always present
    var recBtn = document.createElement('button');
    recBtn.id = 'btn-record';
    recBtn.type = 'button';
    var isRec = _recRecorder && _recRecorder.state !== 'inactive';
    recBtn.textContent = isRec ? '⏹ Stop recording' : '🔴 Record clips';
    if (isRec) { recBtn.style.color = 'var(--fail)'; recBtn.style.borderColor = 'var(--fail)'; }
    recBtn.onclick = toggleRecord;
    ctrl.appendChild(recBtn);

    if (done) {
        var rb = document.createElement('button');
        rb.textContent = '↻ Replay again';
        rb.onclick = restart;
        ctrl.appendChild(rb);
        return;
    }
    var pb = document.createElement('button');
    pb.id = 'btn-play';
    pb.textContent = playing ? '❚❚ Pause' : '▶ Play';
    pb.onclick = togglePlay;
    var sb = document.createElement('button');
    sb.textContent = '⏭ Skip';
    sb.onclick = skip;
    var eb = document.createElement('button');
    eb.textContent = '⏹ Stop';
    eb.onclick = stop;
    ctrl.appendChild(pb); ctrl.appendChild(sb); ctrl.appendChild(eb);
}

// ── App-frame rendering ───────────────────────────────────────────
function e(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function renderFrame(step, result) {
    var main = document.getElementById('replay-main');
    var vc   = result ? 'v-' + result.verdict.toLowerCase() : '';
    var el   = document.createElement('div');
    el.className = 'app-frame ' + vc;

    // Titlebar
    el.innerHTML =
        '<div class="app-frame-titlebar">' +
            '<span class="tl-dot r"></span><span class="tl-dot y"></span><span class="tl-dot g"></span>' +
            '<div class="frame-title">' +
                '<span class="kind">' + e(step.type) + '</span>' +
                '<strong>' + e(step.endpoint) + '</strong>' +
                '<span class="dim">/ ' + e(step.fixture) + '</span>' +
                (step.type === 'probe' ? '<span class="probe-chip">🔍 probe</span>' : '') +
            '</div>' +
        '</div>' +
        // Body
        '<div class="frame-body">' +
            '<div id="frame-body-inner"></div>' +
        '</div>' +
        // Status bar
        '<div class="frame-status ' + (result ? vc : 'running') + '" id="frame-status"></div>';

    main.innerHTML = '';
    main.appendChild(el);

    // Fill body + status via textContent (safe from XSS)
    var inner = document.getElementById('frame-body-inner');
    var status = document.getElementById('frame-status');

    if (!result) {
        // Loading
        if (step.livewire_calls && step.livewire_calls.length) {
            inner.appendChild(renderBetFormMockup(step));
        }
        status.className = 'frame-status running';
        var sp2 = document.createElement('span'); sp2.className = 'spinner';
        var t2  = document.createElement('span'); t2.textContent = ' running checks…';
        t2.style.color = 'var(--dim)'; t2.style.fontSize = '13px';
        status.appendChild(sp2); status.appendChild(t2);
        return;
    }

    // BetForm mockup — shown whenever the step has Livewire calls
    if (step.livewire_calls && step.livewire_calls.length) {
        inner.appendChild(renderBetFormMockup(step));
    }

    // Body dump on failure/blocked
    if (result.verdict !== 'PASS' && result.body) {
        var dump = document.createElement('div');
        dump.className = 'body-dump';
        try { dump.textContent = JSON.stringify(JSON.parse(result.body), null, 2); }
        catch(err) { dump.textContent = result.body; }
        inner.appendChild(dump);
    }

    // Status bar
    var ok   = (result.checks || []).filter(function(c) { return c.result === 'ok'; }).length;
    var fail = (result.checks || []).filter(function(c) { return c.result === 'fail'; }).length;
    status.className = 'frame-status ' + vc;
    var sl = document.createElement('div'); sl.className = 'status-line';
    var vb = document.createElement('span');
    vb.className = 'verdict-badge ' + result.verdict;
    vb.textContent = result.verdict;
    sl.appendChild(vb);
    if (ok)   { var s1 = document.createElement('span'); s1.textContent = '✅ ' + ok;   sl.appendChild(s1); }
    if (fail) { var s2 = document.createElement('span'); s2.textContent = '❌ ' + fail; sl.appendChild(s2); }
    var sub = document.createElement('span');
    sub.className = 'status-sub';
    sub.textContent = (result.checks || []).length + ' checks';
    sl.appendChild(sub);
    status.appendChild(sl);
}

function renderBetFormMockup(step) {
    var mode    = (step.livewire_updates && step.livewire_updates.betMode) || 1;
    var call    = step.livewire_calls[0] || {};
    var params  = call.params || [];
    var isRange = call.method === 'addRangeBet';
    var number  = params[0] || '';
    var amount  = params[1] || '';

    var POS_GROUPS  = ['Lo23','Lo25','Lo27','P1','P2','P3','P4','P5','P6','P7','P8'];
    var TYPE_LABELS = ['Normal','<','=','>','X'];

    var wrap = document.createElement('div');
    wrap.className = 'bet-form-mockup';

    // ── Position Group ──────────────────────────────────────────────
    var posSection = document.createElement('div');
    posSection.className = 'bfm-section';
    var posLabel = document.createElement('div');
    posLabel.className = 'bfm-section-label';
    posLabel.textContent = 'POSITION GROUP';
    var posChips = document.createElement('div');
    posChips.className = 'bfm-chips';
    var posEls = POS_GROUPS.map(function(pg) {
        var c = document.createElement('span');
        c.className = 'bfm-chip';
        c.textContent = pg;
        posChips.appendChild(c);
        return c;
    });
    posSection.appendChild(posLabel);
    posSection.appendChild(posChips);
    wrap.appendChild(posSection);

    // ── Bet Type ────────────────────────────────────────────────────
    var typeSection = document.createElement('div');
    typeSection.className = 'bfm-section';
    var typeLabel = document.createElement('div');
    typeLabel.className = 'bfm-section-label';
    typeLabel.textContent = 'BET TYPE';
    var typeChips = document.createElement('div');
    typeChips.className = 'bfm-chips';
    var typeEls = TYPE_LABELS.map(function(lbl) {
        var c = document.createElement('span');
        c.className = 'bfm-chip';
        c.textContent = lbl;
        typeChips.appendChild(c);
        return c;
    });
    typeSection.appendChild(typeLabel);
    typeSection.appendChild(typeChips);
    wrap.appendChild(typeSection);

    // ── Number + Amount (side by side) ───────────────────────────────
    var twoCol = document.createElement('div');
    twoCol.className = 'bfm-two-col';

    var numSection = document.createElement('div');
    numSection.className = 'bfm-section';
    var numLbl = document.createElement('div');
    numLbl.className = 'bfm-section-label';
    numLbl.textContent = isRange ? 'START NUMBER' : 'NUMBER';
    var numField = document.createElement('div');
    numField.className = 'bfm-field';
    numSection.appendChild(numLbl);
    numSection.appendChild(numField);

    var amtSection = document.createElement('div');
    amtSection.className = 'bfm-section';
    var amtLbl = document.createElement('div');
    amtLbl.className = 'bfm-section-label';
    amtLbl.textContent = 'AMOUNT';
    var amtField = document.createElement('div');
    amtField.className = 'bfm-field';
    amtSection.appendChild(amtLbl);
    amtSection.appendChild(amtField);

    twoCol.appendChild(numSection);
    twoCol.appendChild(amtSection);
    wrap.appendChild(twoCol);

    // ── Clear + Add buttons ──────────────────────────────────────────
    var btnRow = document.createElement('div');
    btnRow.style.cssText = 'display:flex;gap:8px;';

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.textContent = 'Clear';
    clearBtn.style.cssText = 'flex:1;padding:10px;border-radius:8px;font:700 13px/1 var(--mono);border:2px solid #2A5F47;color:#2A5F47;background:#FAF7F2;cursor:default;';

    var addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.textContent = 'Add';
    addBtn.style.cssText = 'flex:1;padding:10px;border-radius:8px;font:700 13px/1 var(--mono);border:none;color:#fff;background:#D4A017;cursor:default;';

    btnRow.appendChild(clearBtn);
    btnRow.appendChild(addBtn);
    wrap.appendChild(btnRow);

    // ── Animation chain ──────────────────────────────────────────────
    // Seeded by step index so same fixture always lands on same position group
    var seed = (number.charCodeAt(0) || 0) + (amount.charCodeAt(0) || 0) + mode;
    var randPosIdx = seed % POS_GROUPS.length;

    function clrPos()  { posEls.forEach(function(e)  { e.className = 'bfm-chip'; }); }
    function clrType() { typeEls.forEach(function(e) { e.className = 'bfm-chip'; }); }

    function makeCursor() {
        var c = document.createElement('span');
        c.className = 'bfm-cursor';
        return c;
    }

    function typeInto(field, text, onDone) {
        field.className = 'bfm-field typing';
        field.textContent = '';
        var cur = makeCursor();
        field.appendChild(cur);
        var i = 0;
        (function tick() {
            if (i < text.length) {
                field.insertBefore(document.createTextNode(text[i++]), cur);
                setTimeout(tick, 130);
            } else {
                cur.remove();
                field.className = 'bfm-field has-value';
                if (onDone) onDone();
            }
        })();
    }

    // Phase 1: cycle position groups → settle
    var pc = 0;
    (function cyclePos() {
        clrPos();
        posEls[pc % posEls.length].className = 'bfm-chip sel-pos';
        pc++;
        if (pc < 10) { setTimeout(cyclePos, 70); }
        else {
            clrPos();
            posEls[randPosIdx].className = 'bfm-chip sel-pos';
            // Phase 2: cycle bet types → settle
            setTimeout(function() {
                var tc = 0;
                (function cycleBetType() {
                    clrType();
                    typeEls[tc % typeEls.length].className = 'bfm-chip sel-type';
                    tc++;
                    if (tc < 8) { setTimeout(cycleBetType, 70); }
                    else {
                        clrType();
                        typeEls[mode - 1].className = 'bfm-chip sel-type';
                        // Phase 3: type number → Phase 4: type amount
                        setTimeout(function() {
                            typeInto(numField, number, function() {
                                setTimeout(function() { typeInto(amtField, amount, null); }, 180);
                            });
                        }, 250);
                    }
                })();
            }, 250);
        }
    })();

    return wrap;
}

function renderSummary() {
    var main = document.getElementById('replay-main');
    var div  = document.createElement('div');
    div.className = 'replay-summary';

    var h2 = document.createElement('h2'); h2.textContent = 'Summary';
    var sl = document.createElement('div'); sl.className = 'summary-line';
    var sp = document.createElement('span'); sp.className = 's-pass';    sp.textContent = '✅ ' + (counts.PASS||0);
    var sf = document.createElement('span'); sf.className = 's-fail';    sf.textContent = '❌ ' + (counts.FAIL||0);
    var sb = document.createElement('span'); sb.className = 's-blocked'; sb.textContent = '⛔ ' + (counts.BLOCKED||0);
    var so = document.createElement('span'); so.style.cssText = 'color:var(--dim);font-size:13px;align-self:center';
    so.textContent = '/ ' + STEPS.length;
    sl.appendChild(sp); sl.appendChild(sf); sl.appendChild(sb); sl.appendChild(so);

    var tbl = document.createElement('table'); tbl.className = 'summary-table';
    tbl.innerHTML = '<thead><tr><th>Endpoint</th><th>Fixture</th><th>HTTP</th><th>Verdict</th></tr></thead>';
    var tbody = document.createElement('tbody');
    STEPS.forEach(function(s) {
        var r = results[key(s)];
        var tr = document.createElement('tr');
        var tdEp = document.createElement('td'); tdEp.textContent = s.endpoint;
        var tdFx = document.createElement('td'); tdFx.textContent = s.fixture + (s.type === 'probe' ? ' 🔍' : '');
        var tdHt = document.createElement('td'); tdHt.textContent = (r && r.http) ? r.http : '—'; tdHt.style.color = 'var(--dim)';
        var tdVd = document.createElement('td');
        if (r) {
            var vb = document.createElement('span');
            vb.className = 'verdict-badge ' + r.verdict;
            vb.textContent = r.verdict;
            tdVd.appendChild(vb);
        } else {
            tdVd.textContent = '—'; tdVd.style.color = 'var(--dim)';
        }
        tr.appendChild(tdEp); tr.appendChild(tdFx); tr.appendChild(tdHt); tr.appendChild(tdVd);
        tbody.appendChild(tr);
    });
    tbl.appendChild(tbody);
    div.appendChild(h2); div.appendChild(sl); div.appendChild(tbl);
    main.innerHTML = '';
    main.appendChild(div);
}

// ── Core playback ─────────────────────────────────────────────────
async function runStep(i) {
    if (i >= STEPS.length) { finish(); return; }
    var s = STEPS[i];
    running = true;
    setActiveStep(i);
    setStepVerdict(i, 'running');
    renderFrame(s, null);
    updateProgress(i + 1, STEPS.length);

    var r;
    try {
        var res = await fetch(
            '/verify/run/' + encodeURIComponent(s.endpoint) + '/' + encodeURIComponent(s.fixture),
            { headers: { Accept: 'application/json' } }
        );
        if (!res.ok) throw new Error('HTTP ' + res.status);
        r = await res.json();
    } catch(err) {
        r = { endpoint: s.endpoint, fixture: s.fixture, verdict: 'BLOCKED', http: null, checks: [], body: err.message };
    }

    results[key(s)] = r;
    counts[r.verdict] = (counts[r.verdict] || 0) + 1;
    setStepVerdict(i, r.verdict);
    renderFrame(s, r);
    updateTally();
    running = false;

    if (playing) {
        advTimer = setTimeout(function() { advance(); }, DWELL);
    }
}

function advance() {
    if (advTimer) { clearTimeout(advTimer); advTimer = null; }
    var next = idx + 1;
    if (next >= STEPS.length) { finish(); return; }
    idx = next;
    runStep(idx);
}

function jumpTo(i) {
    if (advTimer) { clearTimeout(advTimer); advTimer = null; }
    playing = false;
    done = false;
    idx = i;
    renderControls();
    runStep(idx);
}

function togglePlay() {
    if (done) return;
    playing = !playing;
    renderControls();
    if (playing && !running && !advTimer) { advance(); }
    if (!playing && advTimer) { clearTimeout(advTimer); advTimer = null; }
}

function skip() {
    if (done) return;
    if (advTimer) { clearTimeout(advTimer); advTimer = null; }
    if (!running) advance();
}

function stop() {
    if (advTimer) { clearTimeout(advTimer); advTimer = null; }
    finish();
}

function finish() {
    if (advTimer) { clearTimeout(advTimer); advTimer = null; }
    if (_recRecorder && _recRecorder.state !== 'inactive') { _recRecorder.stop(); }
    playing = false; done = true;
    document.getElementById('progress-bar').style.width = '100%';
    document.getElementById('replay-counter').textContent = STEPS.length + ' / ' + STEPS.length;
    document.querySelectorAll('.step-row').forEach(function(r) { r.classList.remove('active'); });
    renderControls();
    renderSummary();
}

function restart() {
    if (advTimer) { clearTimeout(advTimer); advTimer = null; }
    results = {}; counts = { PASS: 0, FAIL: 0, BLOCKED: 0 };
    idx = 0; playing = true; done = false; running = false;
    STEPS.forEach(function(_, i) { setStepVerdict(i, null); });
    updateTally(); renderControls();
    runStep(0);
}

// ── Record clips ─────────────────────────────────────────────────
var _recChunks = [], _recRecorder = null, _recStream = null;

function _recMime() {
    var types = ['video/webm;codecs=vp9', 'video/webm', 'video/mp4'];
    for (var i = 0; i < types.length; i++) {
        if (MediaRecorder.isTypeSupported(types[i])) return types[i];
    }
    return '';
}

function _recWarn(msg) {
    var btn = document.getElementById('btn-record');
    if (!btn) return;
    btn.textContent = '⚠ ' + msg;
    setTimeout(function() { if (document.getElementById('btn-record')) document.getElementById('btn-record').textContent = '🔴 Record clips'; }, 3000);
}

async function toggleRecord() {
    if (_recRecorder && _recRecorder.state !== 'inactive') {
        _recRecorder.stop();
        return;
    }
    if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
        _recWarn('Not supported');
        return;
    }
    try {
        _recStream = await navigator.mediaDevices.getDisplayMedia({ video: { frameRate: 30 }, audio: false });
    } catch(e) {
        if (e.name !== 'AbortError' && e.name !== 'NotAllowedError') {
            _recWarn(e.message || 'Permission denied');
        }
        return;
    }
    var mime = _recMime();
    _recChunks = [];
    _recRecorder = new MediaRecorder(_recStream, mime ? { mimeType: mime } : {});
    _recRecorder.ondataavailable = function(ev) { if (ev.data.size) _recChunks.push(ev.data); };
    _recRecorder.onstop = function() {
        _recStream.getTracks().forEach(function(t) { t.stop(); });
        var ext = (mime || '').indexOf('mp4') !== -1 ? 'mp4' : 'webm';
        var blob = new Blob(_recChunks, { type: mime || 'video/webm' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'verify-replay.' + ext;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        _recRecorder = null;
        renderControls();
    };
    _recStream.getVideoTracks()[0].onended = function() {
        if (_recRecorder && _recRecorder.state !== 'inactive') _recRecorder.stop();
    };
    _recRecorder.start();
    renderControls();
}

// ── Keyboard ─────────────────────────────────────────────────────
document.addEventListener('keydown', function(e) {
    if (e.key === ' ')           { e.preventDefault(); togglePlay(); }
    else if (e.key === 'ArrowRight') { e.preventDefault(); skip(); }
    else if (e.key === 'Escape')     { e.preventDefault(); stop(); }
});

// ── Boot ──────────────────────────────────────────────────────────
buildSidebar();
renderControls();
if (STEPS.length > 0) runStep(0);
else document.getElementById('replay-main').innerHTML = '<p style="color:var(--dim);padding:20px">No fixtures.</p>';
</script>
</body>
</html>
