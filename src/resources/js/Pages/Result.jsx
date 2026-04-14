// Screen: result | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useCallback, useRef } from 'react';
import NavBar from '@/Components/NavBar';
import colors from '@/theme/colors';

// ─── Constants ──────────────────────────────────────────────

const SESSIONS = [
    { key: 'morning', label: 'ព្រឹក' },
    { key: 'noon',    label: 'ថ្ងៃ' },
    { key: 'evening', label: 'ល្ងាច' },
];

// ─── Helpers ─────────────────────────────────────────────────

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ─── Print utility ───────────────────────────────────────────

function handlePrint(grid, selectedDate, selectedSession) {
    const sessionLabel = SESSIONS.find(s => s.key === selectedSession)?.label ?? selectedSession;
    const rows = grid.map(r =>
        `<tr><td style="padding:6px 12px;border:1px solid #ccc;font-size:18px;text-align:center">${r.position}</td>` +
        `<td style="padding:6px 16px;border:1px solid #ccc;font-size:18px;font-weight:bold;text-align:center">${r.number ?? '—'}</td></tr>`
    ).join('');
    const win = window.open('', '_blank');
    win.document.write(`
        <html><head><title>Result ${selectedDate}</title></head>
        <body style="font-family:sans-serif;padding:20px">
            <h2 style="text-align:center;color:#DC143C">HT ភ្នាក់ — Results</h2>
            <p style="text-align:center">${formatDate(selectedDate)} &nbsp;|&nbsp; ${sessionLabel}</p>
            <table style="margin:0 auto;border-collapse:collapse;min-width:200px">
                <thead><tr>
                    <th style="padding:8px 12px;background:#DC143C;color:white;border:1px solid #ccc">បុំស្ដំ</th>
                    <th style="padding:8px 12px;background:#DC143C;color:white;border:1px solid #ccc">លេខ</th>
                </tr></thead>
                <tbody>${rows}</tbody>
            </table>
        </body></html>
    `);
    win.document.close();
    win.print();
}

// ─── Main Page ───────────────────────────────────────────────

export default function Result({ dates, grid, filters }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    const isAdmin = user?.role === 'admin';

    const selectedDate    = filters?.date    ?? '';
    const selectedSession = filters?.session ?? 'morning';

    // Form for admin — pre-populated from grid
    const { data, setData, post, processing, errors, reset } = useForm({
        result_date: selectedDate,
        session:     selectedSession,
        results:     (grid ?? []).map(r => ({ position: r.position, number: r.number ?? '' })),
    });

    // Update a single number field
    const handleNumberChange = (idx, value) => {
        const updated = [...data.results];
        updated[idx] = { ...updated[idx], number: value };
        setData('results', updated);
    };

    const navigate = useCallback((params) => {
        router.get(route('result'), { ...filters, ...params }, {
            preserveState:  false,
            preserveScroll: false,
        });
    }, [filters]);

    const handleDateSelect    = (date)    => navigate({ date, session: selectedSession });
    const handleSessionSelect = (session) => navigate({ date: selectedDate, session });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('result.store'), { preserveScroll: true });
    };

    const inputRefs = useRef([]);

    return (
        <>
            <Head title="Result — HT ភ្នាក់" />
            <NavBar />

            <div className="pt-12 md:pt-14 pb-16 md:pb-0 flex flex-col md:flex-row h-screen overflow-hidden" style={{ backgroundColor: '#fff' }}>

                {/* ── Mobile: horizontal date scroller ────────── */}
                <div className="flex md:hidden border-b border-gray-200 flex-shrink-0 overflow-x-auto"
                    style={{ backgroundColor: colors.surface }}>
                    {dates && dates.length > 0 ? (
                        dates.map((d) => {
                            const isSelected = d === selectedDate;
                            return (
                                <button
                                    key={d}
                                    onClick={() => handleDateSelect(d)}
                                    className="flex-shrink-0 px-3 py-2 text-xs font-medium whitespace-nowrap border-b-2 transition-colors"
                                    style={{
                                        borderBottomColor: isSelected ? colors.primary : 'transparent',
                                        backgroundColor: isSelected ? colors.primary : 'transparent',
                                        color: isSelected ? '#fff' : '#374151',
                                    }}
                                >
                                    {formatDate(d)}
                                </button>
                            );
                        })
                    ) : (
                        <p className="p-2 text-xs text-gray-400">No dates yet</p>
                    )}
                </div>

                {/* ── Desktop: left panel date list ───────────── */}
                <aside className="hidden md:flex w-40 flex-shrink-0 border-r border-gray-200 flex-col"
                    style={{ backgroundColor: colors.surface }}>
                    <div className="px-3 py-2 border-b border-gray-200">
                        <h2 className="text-xs font-bold uppercase tracking-wide text-gray-500">Dates</h2>
                    </div>
                    <div className="flex-1 overflow-y-auto">
                        {dates && dates.length > 0 ? (
                            dates.map((d) => {
                                const isSelected = d === selectedDate;
                                return (
                                    <button
                                        key={d}
                                        onClick={() => handleDateSelect(d)}
                                        className="w-full text-left px-3 py-2.5 text-sm font-medium border-b border-gray-100 transition-colors"
                                        style={{
                                            backgroundColor: isSelected ? colors.primary : 'transparent',
                                            color: isSelected ? '#fff' : '#374151',
                                        }}
                                    >
                                        {formatDate(d)}
                                    </button>
                                );
                            })
                        ) : (
                            <p className="p-3 text-xs text-gray-400">No dates yet</p>
                        )}
                    </div>
                </aside>

                {/* ── Right panel ─────────────────────────────── */}
                <main className="flex-1 flex flex-col overflow-hidden">

                    {/* Header bar */}
                    <div className="px-3 md:px-4 py-2 md:py-3 border-b border-gray-200 flex items-center justify-between flex-shrink-0"
                        style={{ backgroundColor: colors.surface }}>
                        <div>
                            <h1 className="text-sm md:text-base font-bold" style={{ color: colors.accent }}>
                                Results
                            </h1>
                            <p className="text-xs text-gray-500 mt-0.5">
                                {selectedDate ? formatDate(selectedDate) : 'Select a date'}
                            </p>
                        </div>

                        {/* Print button */}
                        <button
                            onClick={() => handlePrint(grid ?? [], selectedDate, selectedSession)}
                            className="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold text-white transition-opacity hover:opacity-80"
                            style={{ backgroundColor: colors.primary }}
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print
                        </button>
                    </div>

                    {/* Session radio */}
                    <div className="px-3 md:px-4 py-2 md:py-3 border-b border-gray-200 flex items-center gap-2 md:gap-4 flex-shrink-0"
                        style={{ backgroundColor: '#fff' }}>
                        <span className="text-xs md:text-sm font-semibold text-gray-600">Session:</span>
                        {SESSIONS.map((s) => {
                            const active = s.key === selectedSession;
                            return (
                                <label key={s.key}
                                    className="flex items-center gap-1.5 cursor-pointer text-sm font-medium">
                                    <input
                                        type="radio"
                                        name="session"
                                        value={s.key}
                                        checked={active}
                                        onChange={() => handleSessionSelect(s.key)}
                                        className="accent-[#DC143C]"
                                    />
                                    <span style={{ color: active ? colors.accent : '#374151' }}>
                                        {s.label}
                                    </span>
                                </label>
                            );
                        })}
                    </div>

                    {/* Result table */}
                    <form onSubmit={handleSubmit} className="flex-1 flex flex-col overflow-auto">
                        <div className="flex-1 overflow-auto max-w-sm mx-auto w-full px-4 py-4">
                            <table className="w-full border-collapse rounded-lg overflow-hidden shadow-sm">
                                <thead>
                                    <tr style={{ backgroundColor: colors.accent }}>
                                        <th className="py-3 px-6 text-center text-white font-bold text-base">
                                            បុំស្ដំ
                                        </th>
                                        <th className="py-3 px-6 text-center text-white font-bold text-base">
                                            លេខ
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {(grid ?? []).length === 0 ? (
                                        <tr>
                                            <td colSpan={2} className="py-12 text-center text-gray-400 text-sm">
                                                No results for this session
                                            </td>
                                        </tr>
                                    ) : (
                                        (grid ?? []).map((row, idx) => {
                                            const isEven = idx % 2 === 0;
                                            return (
                                                <tr
                                                    key={row.position}
                                                    style={{ backgroundColor: isEven ? '#fff' : colors.surface }}
                                                    className="border-b border-gray-100"
                                                >
                                                    {/* Position column */}
                                                    <td className="py-3 px-6 text-center">
                                                        <span className="inline-flex items-center justify-center w-10 h-10 rounded-full text-white text-lg font-black"
                                                            style={{ backgroundColor: colors.accent }}>
                                                            {row.position}
                                                        </span>
                                                    </td>

                                                    {/* Number column */}
                                                    <td className="py-3 px-6 text-center">
                                                        {isAdmin ? (
                                                            <input
                                                                ref={el => inputRefs.current[idx] = el}
                                                                type="text"
                                                                inputMode="numeric"
                                                                maxLength={10}
                                                                value={data.results[idx]?.number ?? ''}
                                                                onChange={e => handleNumberChange(idx, e.target.value)}
                                                                onKeyDown={e => {
                                                                    if (e.key === 'Enter') {
                                                                        e.preventDefault();
                                                                        inputRefs.current[idx + 1]?.focus();
                                                                    }
                                                                }}
                                                                placeholder="—"
                                                                className="w-20 text-center text-2xl font-black border-2 rounded-lg py-1 outline-none transition-colors"
                                                                style={{
                                                                    borderColor: data.results[idx]?.number ? colors.primary : '#E5E7EB',
                                                                    color: colors.primaryDark,
                                                                }}
                                                            />
                                                        ) : (
                                                            <span className="text-2xl font-black"
                                                                style={{ color: row.number ? colors.primaryDark : '#D1D5DB' }}>
                                                                {row.number ?? '—'}
                                                            </span>
                                                        )}
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    )}
                                </tbody>
                            </table>

                            {/* Validation error */}
                            {errors.results && (
                                <p className="mt-2 text-sm text-red-600 text-center">{errors.results}</p>
                            )}

                            {/* Save button — admin only */}
                            {isAdmin && (
                                <div className="mt-6 flex justify-center">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="px-8 py-3 rounded-xl text-white font-bold text-base shadow-md transition-opacity hover:opacity-80 disabled:opacity-50"
                                        style={{ backgroundColor: colors.accent }}
                                    >
                                        {processing ? (
                                            <span className="flex items-center gap-2">
                                                <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                                                </svg>
                                                Saving…
                                            </span>
                                        ) : 'Save Results'}
                                    </button>
                                </div>
                            )}

                            {/* Read-only message for non-admin */}
                            {!isAdmin && (
                                <p className="mt-4 text-center text-xs text-gray-400">
                                    Read-only — only admins can enter results.
                                </p>
                            )}
                        </div>
                    </form>
                </main>
            </div>
        </>
    );
}
