// Screen: report | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL
// Access: admin + master only — staff gets 403

import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import NavBar from '@/Components/NavBar';
import colors from '@/theme/colors';

// ─── Helpers ─────────────────────────────────────────────────

function fmt(num) {
    return Number(num || 0).toLocaleString();
}

const SESSION_LABEL = {
    morning: 'ព្រឹក',
    noon:    'ថ្ងៃ',
    evening: 'ល្ងាច',
};

// ─── Summary card ─────────────────────────────────────────────

function SummaryCard({ label, value, accent }) {
    return (
        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1">
            <p className="text-xs font-semibold uppercase tracking-wide text-gray-400">{label}</p>
            <p className="text-2xl font-black" style={{ color: accent ? colors.accent : colors.primaryDark }}>
                {value}
            </p>
        </div>
    );
}

// ─── Pagination ───────────────────────────────────────────────

function Pagination({ links }) {
    if (!links || links.length <= 3) return null;
    return (
        <div className="flex justify-center gap-1 mt-4">
            {links.map((link, i) => (
                <button
                    key={i}
                    disabled={!link.url}
                    onClick={() => link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })}
                    className="px-3 py-1 rounded text-sm font-medium border transition-colors disabled:opacity-40"
                    style={
                        link.active
                            ? { backgroundColor: colors.primary, color: '#fff', borderColor: colors.primary }
                            : { backgroundColor: '#fff', color: '#374151', borderColor: '#D1D5DB' }
                    }
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </div>
    );
}

// ─── Print utility ────────────────────────────────────────────

function handlePrint(rows, totals, filters, staffList) {
    const staffName = filters.staffId
        ? staffList.find(s => s.id === filters.staffId)?.name ?? 'All Staff'
        : 'All Staff';

    const tableRows = rows.map(r =>
        `<tr>
            <td>${r.user_name}</td>
            <td>${SESSION_LABEL[r.session] ?? r.session}</td>
            <td style="text-align:right">${r.ticket_count}</td>
            <td style="text-align:right">${Number(r.total_amount).toLocaleString()}</td>
            <td style="text-align:right">${Number(r.total_win).toLocaleString()}</td>
            <td style="text-align:right">${Number(r.net).toLocaleString()}</td>
        </tr>`
    ).join('');

    const win = window.open('', '_blank');
    win.document.write(`
        <html><head><title>Report</title>
        <style>body{font-family:sans-serif;padding:20px} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ccc;padding:6px 10px;font-size:13px} th{background:#DC143C;color:white}</style>
        </head><body>
            <h2 style="color:#DC143C;text-align:center">HT ភ្នាក់ — Sales Report</h2>
            <p style="text-align:center">${filters.from} → ${filters.to} | ${staffName}</p>
            <p><strong>Total Bets:</strong> ${totals?.total_tickets ?? 0} &nbsp;|&nbsp;
               <strong>Amount:</strong> ${Number(totals?.total_amount ?? 0).toLocaleString()} &nbsp;|&nbsp;
               <strong>Win:</strong> ${Number(totals?.total_win ?? 0).toLocaleString()} &nbsp;|&nbsp;
               <strong>Net:</strong> ${Number(totals?.net ?? 0).toLocaleString()}</p>
            <table>
                <thead><tr><th>Staff</th><th>Session</th><th>Bets</th><th>Amount</th><th>Win</th><th>Net</th></tr></thead>
                <tbody>${tableRows}</tbody>
            </table>
        </body></html>
    `);
    win.document.close();
    win.print();
}

// ─── Main Page ───────────────────────────────────────────────

export default function Report({ staff_list, rows, totals, filters }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    // Local filter state (applied on submit)
    const [from,    setFrom]    = useState(filters?.from     ?? '');
    const [to,      setTo]      = useState(filters?.to       ?? '');
    const [staffId, setStaffId] = useState(filters?.staffId  ?? '');
    const [session, setSession] = useState(filters?.session  ?? '');

    const applyFilters = (e) => {
        e.preventDefault();
        router.get(route('report'), { from, to, staff_id: staffId || undefined, session: session || undefined }, {
            preserveState:  true,
            preserveScroll: true,
        });
    };

    const tableRows = rows?.data  ?? [];
    const links     = rows?.links ?? [];

    return (
        <>
            <Head title="Report — HT ភ្នាក់" />
            <NavBar />

            <div className="pt-14 min-h-screen" style={{ backgroundColor: '#F9FAFB' }}>
                <div className="max-w-6xl mx-auto px-4 py-6 space-y-5">

                    {/* ── Filter bar ──────────────────────────── */}
                    <form
                        onSubmit={applyFilters}
                        className="bg-white rounded-2xl shadow-sm border border-gray-100 p-4"
                    >
                        <div className="flex flex-wrap gap-3 items-end">
                            {/* From */}
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide">From</label>
                                <input
                                    type="date"
                                    value={from}
                                    onChange={e => setFrom(e.target.value)}
                                    className="border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017]"
                                    style={{ borderColor: '#E5E7EB' }}
                                />
                            </div>

                            {/* To */}
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide">To</label>
                                <input
                                    type="date"
                                    value={to}
                                    onChange={e => setTo(e.target.value)}
                                    className="border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017]"
                                    style={{ borderColor: '#E5E7EB' }}
                                />
                            </div>

                            {/* Staff */}
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide">Staff</label>
                                <select
                                    value={staffId}
                                    onChange={e => setStaffId(e.target.value)}
                                    className="border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] bg-white min-w-[140px]"
                                    style={{ borderColor: '#E5E7EB' }}
                                >
                                    <option value="">All Staff</option>
                                    {(staff_list ?? []).map(s => (
                                        <option key={s.id} value={s.id}>{s.name}</option>
                                    ))}
                                </select>
                            </div>

                            {/* Session */}
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide">Session</label>
                                <select
                                    value={session}
                                    onChange={e => setSession(e.target.value)}
                                    className="border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] bg-white"
                                    style={{ borderColor: '#E5E7EB' }}
                                >
                                    <option value="">All</option>
                                    <option value="morning">ព្រឹក</option>
                                    <option value="noon">ថ្ងៃ</option>
                                    <option value="evening">ល្ងាច</option>
                                </select>
                            </div>

                            {/* Buttons */}
                            <div className="flex gap-2 ml-auto">
                                <button
                                    type="button"
                                    onClick={() => handlePrint(tableRows, totals, filters ?? {}, staff_list ?? [])}
                                    className="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-opacity hover:opacity-80"
                                    style={{ backgroundColor: colors.primaryDark }}
                                >
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Print
                                </button>
                                <button
                                    type="submit"
                                    className="px-5 py-2 rounded-lg text-sm font-semibold text-white transition-opacity hover:opacity-80"
                                    style={{ backgroundColor: colors.accent }}
                                >
                                    Apply
                                </button>
                            </div>
                        </div>
                    </form>

                    {/* ── Summary cards ───────────────────────── */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <SummaryCard
                            label="Total Bets"
                            value={fmt(totals?.total_tickets ?? 0)}
                        />
                        <SummaryCard
                            label="Total Amount"
                            value={fmt(totals?.total_amount ?? 0)}
                        />
                        <SummaryCard
                            label="Total Win"
                            value={fmt(totals?.total_win ?? 0)}
                            accent
                        />
                        <SummaryCard
                            label="Net"
                            value={fmt(totals?.net ?? 0)}
                        />
                    </div>

                    {/* ── Data table ──────────────────────────── */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                            <h2 className="text-sm font-bold text-gray-700">
                                Detail — {filters?.from} → {filters?.to}
                            </h2>
                            <span className="text-xs text-gray-400">
                                {rows?.total ?? 0} rows
                            </span>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-sm border-collapse">
                                <thead>
                                    <tr style={{ backgroundColor: colors.accent }}>
                                        <th className="px-4 py-3 text-left text-white font-semibold">Staff</th>
                                        <th className="px-4 py-3 text-center text-white font-semibold">Session</th>
                                        <th className="px-4 py-3 text-right text-white font-semibold">Bets</th>
                                        <th className="px-4 py-3 text-right text-white font-semibold">Amount</th>
                                        <th className="px-4 py-3 text-right text-white font-semibold">Win</th>
                                        <th className="px-4 py-3 text-right text-white font-semibold">Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {tableRows.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="py-16 text-center text-gray-400 text-sm">
                                                <div className="flex flex-col items-center gap-3">
                                                    <div className="w-16 h-16 rounded-full flex items-center justify-center opacity-10 text-4xl font-black"
                                                        style={{ backgroundColor: colors.primary, color: '#fff' }}>
                                                        HT
                                                    </div>
                                                    <p>No data for selected filters</p>
                                                </div>
                                            </td>
                                        </tr>
                                    ) : (
                                        tableRows.map((row, idx) => {
                                            const isEven = idx % 2 === 0;
                                            return (
                                                <tr
                                                    key={`${row.user_id}-${row.session}`}
                                                    className="border-b border-gray-100 hover:bg-yellow-50 transition-colors"
                                                    style={{ backgroundColor: isEven ? '#fff' : colors.surface }}
                                                >
                                                    <td className="px-4 py-3 font-medium text-gray-800">
                                                        {row.user_name}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        <span className="text-xs font-semibold px-2 py-0.5 rounded-full"
                                                            style={{
                                                                backgroundColor:
                                                                    row.session === 'morning' ? '#FFF3CD' :
                                                                    row.session === 'noon'    ? '#D1ECF1' : '#D6EAF8',
                                                                color:
                                                                    row.session === 'morning' ? '#856404' :
                                                                    row.session === 'noon'    ? '#0C5460' : '#154360',
                                                            }}>
                                                            {SESSION_LABEL[row.session] ?? row.session}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3 text-right text-gray-600">
                                                        {fmt(row.ticket_count)}
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-semibold text-gray-800">
                                                        {fmt(row.total_amount)}
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-semibold"
                                                        style={{ color: Number(row.total_win) > 0 ? '#16A34A' : '#9CA3AF' }}>
                                                        {Number(row.total_win) > 0 ? fmt(row.total_win) : '—'}
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-bold"
                                                        style={{ color: Number(row.net) >= 0 ? colors.primaryDark : '#DC2626' }}>
                                                        {fmt(row.net)}
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    )}
                                </tbody>
                                {tableRows.length > 0 && (
                                    <tfoot>
                                        <tr style={{ backgroundColor: colors.surface, borderTop: `2px solid ${colors.primary}` }}>
                                            <td className="px-4 py-3 font-bold text-gray-700" colSpan={2}>
                                                Total
                                            </td>
                                            <td className="px-4 py-3 text-right font-bold text-gray-700">
                                                {fmt(totals?.total_tickets ?? 0)}
                                            </td>
                                            <td className="px-4 py-3 text-right font-bold text-gray-800">
                                                {fmt(totals?.total_amount ?? 0)}
                                            </td>
                                            <td className="px-4 py-3 text-right font-bold text-green-600">
                                                {fmt(totals?.total_win ?? 0)}
                                            </td>
                                            <td className="px-4 py-3 text-right font-bold"
                                                style={{ color: colors.primaryDark }}>
                                                {fmt(totals?.net ?? 0)}
                                            </td>
                                        </tr>
                                    </tfoot>
                                )}
                            </table>
                        </div>

                        {links.length > 3 && (
                            <div className="px-5 py-3 border-t border-gray-100">
                                <Pagination links={links} />
                            </div>
                        )}
                    </div>

                </div>
            </div>
        </>
    );
}
