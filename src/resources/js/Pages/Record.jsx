// Screen: record | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

import { Head, router, usePage } from '@inertiajs/react';
import { useState, useCallback } from 'react';
import NavBar from '@/Components/NavBar';
import colors from '@/theme/colors';

// ─── Constants ──────────────────────────────────────────────

const TABS = [
    { key: 'all',     label: 'All Records' },
    { key: 'morning', label: 'ព្រឹក' },
    { key: 'noon',    label: 'ថ្ងៃ' },
    { key: 'evening', label: 'យប់' },
    { key: 'winning', label: 'Winning' },
];

const SESSION_BADGE = {
    morning: { label: 'ព្រឹក',  bg: '#FFF3CD', text: '#856404' },
    noon:    { label: 'ថ្ងៃ',   bg: '#D1ECF1', text: '#0C5460' },
    evening: { label: 'យប់',   bg: '#D6EAF8', text: '#154360' },
};

// ─── Helpers ─────────────────────────────────────────────────

function fmt(num) {
    if (!num && num !== 0) return '—';
    return Number(num).toLocaleString();
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ─── Sub-components ──────────────────────────────────────────

function SessionBadge({ session }) {
    const b = SESSION_BADGE[session];
    if (!b) return <span className="text-gray-400 text-xs">—</span>;
    return (
        <span className="text-xs font-semibold px-2 py-0.5 rounded-full"
            style={{ backgroundColor: b.bg, color: b.text }}>
            {b.label}
        </span>
    );
}

function StatusBadge({ status, winAmount }) {
    if (Number(winAmount) > 0) {
        return (
            <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                ឈ្នះ
            </span>
        );
    }
    const map = {
        pending:   { bg: '#FFF3CD', text: '#856404', label: 'Pending' },
        completed: { bg: '#D4EDDA', text: '#155724', label: 'Done' },
        cancelled: { bg: '#F8D7DA', text: '#721C24', label: 'Cancel' },
    };
    const b = map[status] ?? map.pending;
    return (
        <span className="text-xs font-semibold px-2 py-0.5 rounded-full"
            style={{ backgroundColor: b.bg, color: b.text }}>
            {b.label}
        </span>
    );
}

function EmptyState() {
    return (
        <tr>
            <td colSpan={8} className="py-16 text-center">
                <div className="flex flex-col items-center gap-3 text-gray-400">
                    {/* Watermark HT logo */}
                    <div className="w-20 h-20 rounded-full flex items-center justify-center opacity-10 text-5xl font-black"
                        style={{ backgroundColor: colors.primary, color: '#fff' }}>
                        HT
                    </div>
                    <p className="text-sm">No records found</p>
                </div>
            </td>
        </tr>
    );
}

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

// ─── Main Page ───────────────────────────────────────────────

export default function Record({ dates, tickets, totals, filters }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    const selectedDate = filters?.date ?? '';
    const activeTab    = filters?.tab  ?? 'all';

    const navigate = useCallback((params) => {
        router.get(route('record'), { ...filters, ...params }, {
            preserveState:  true,
            preserveScroll: true,
        });
    }, [filters]);

    const handleDateSelect = (date) => navigate({ date, tab: activeTab });
    const handleTabSelect  = (tab)  => navigate({ date: selectedDate, tab });

    const rows   = tickets?.data  ?? [];
    const links  = tickets?.links ?? [];

    return (
        <>
            <Head title="Record — HT ភ្នាក់" />
            <NavBar />

            {/* Page body below nav */}
            <div className="pt-12 md:pt-14 pb-16 md:pb-0 flex flex-col md:flex-row h-screen overflow-hidden" style={{ backgroundColor: colors.background }}>

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
                                Records
                            </h1>
                            <p className="text-xs text-gray-500 mt-0.5">
                                {selectedDate ? formatDate(selectedDate) : 'Select a date'}
                            </p>
                        </div>
                        <div className="hidden md:block text-xs text-gray-500">
                            User: <span className="font-semibold" style={{ color: colors.accent }}>{user?.name}</span>
                            &nbsp;({user?.role})
                        </div>
                    </div>

                    {/* Session tabs */}
                    <div className="flex gap-0 border-b border-gray-200 flex-shrink-0 overflow-x-auto"
                        style={{ backgroundColor: '#fff' }}>
                        {TABS.map((t) => {
                            const active = t.key === activeTab;
                            return (
                                <button
                                    key={t.key}
                                    onClick={() => handleTabSelect(t.key)}
                                    className="px-4 py-2.5 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors"
                                    style={{
                                        borderBottomColor: active ? colors.accent : 'transparent',
                                        color: active ? colors.accent : '#6B7280',
                                        backgroundColor: active ? '#FFF5F5' : 'transparent',
                                    }}
                                >
                                    {t.label}
                                </button>
                            );
                        })}
                    </div>

                    {/* Table */}
                    <div className="flex-1 overflow-auto">
                        <table className="w-full text-sm border-collapse">
                            <thead className="sticky top-0 z-10">
                                <tr style={{ backgroundColor: colors.accent }}>
                                    <th className="px-3 py-2.5 text-left text-white font-semibold w-10">ល.រ</th>
                                    <th className="px-3 py-2.5 text-left text-white font-semibold">ល.វិកយប័ត្រ</th>
                                    {user?.role !== 'staff' && (
                                        <th className="px-3 py-2.5 text-left text-white font-semibold">ជន</th>
                                    )}
                                    <th className="px-3 py-2.5 text-center text-white font-semibold">វេន</th>
                                    <th className="px-3 py-2.5 text-right text-white font-semibold">ការលុបរំខើន</th>
                                    <th className="px-3 py-2.5 text-right text-white font-semibold">វីកប្រាក់</th>
                                    <th className="px-3 py-2.5 text-center text-white font-semibold">កាន់វីកស្ដូ</th>
                                    <th className="px-3 py-2.5 text-right text-white font-semibold">ឈ្នះ</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length === 0 ? (
                                    <EmptyState />
                                ) : (
                                    rows.map((ticket, idx) => {
                                        const isEven = idx % 2 === 0;
                                        return (
                                            <tr
                                                key={ticket.id}
                                                className="border-b border-gray-100 hover:bg-yellow-50 transition-colors"
                                                style={{ backgroundColor: isEven ? '#fff' : colors.surface }}
                                            >
                                                <td className="px-3 py-2.5 text-gray-500 text-xs">{idx + 1}</td>
                                                <td className="px-3 py-2.5 font-mono text-xs font-semibold"
                                                    style={{ color: colors.primaryDark }}>
                                                    {ticket.invoice_number}
                                                </td>
                                                {user?.role !== 'staff' && (
                                                    <td className="px-3 py-2.5 text-gray-700 max-w-[120px] truncate">
                                                        {ticket.user?.name ?? '—'}
                                                    </td>
                                                )}
                                                <td className="px-3 py-2.5 text-center">
                                                    <SessionBadge session={ticket.session} />
                                                </td>
                                                <td className="px-3 py-2.5 text-right text-gray-600">
                                                    {ticket.bets_count ?? 0}
                                                </td>
                                                <td className="px-3 py-2.5 text-right font-semibold text-gray-800">
                                                    {fmt(ticket.total_amount)}
                                                </td>
                                                <td className="px-3 py-2.5 text-center">
                                                    <StatusBadge status={ticket.status} winAmount={ticket.win_amount} />
                                                </td>
                                                <td className="px-3 py-2.5 text-right font-semibold"
                                                    style={{ color: Number(ticket.win_amount) > 0 ? '#16A34A' : '#6B7280' }}>
                                                    {Number(ticket.win_amount) > 0 ? fmt(ticket.win_amount) : '—'}
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {links.length > 3 && (
                        <div className="flex-shrink-0 px-4 py-2 border-t border-gray-100">
                            <Pagination links={links} />
                        </div>
                    )}

                    {/* Footer totals */}
                    <footer className="flex-shrink-0 border-t-2 border-gray-200 px-3 md:px-4 py-2 md:py-3 flex flex-wrap items-center justify-between gap-2 md:gap-6"
                        style={{ backgroundColor: colors.surface }}>
                        <div className="flex flex-wrap items-center gap-3 md:gap-6">
                            <div className="text-xs md:text-sm">
                                <span className="text-gray-500">Record: </span>
                                <span className="font-bold" style={{ color: colors.accent }}>
                                    {totals?.total_count ?? 0}
                                </span>
                            </div>
                            <div className="text-xs md:text-sm">
                                <span className="text-gray-500">Amount: </span>
                                <span className="font-bold text-gray-800">
                                    {fmt(totals?.total_amount ?? 0)}
                                </span>
                            </div>
                            <div className="text-xs md:text-sm">
                                <span className="text-gray-500">Win: </span>
                                <span className="font-bold"
                                    style={{ color: Number(totals?.total_win) > 0 ? '#16A34A' : '#6B7280' }}>
                                    {fmt(totals?.total_win ?? 0)}
                                </span>
                            </div>
                        </div>
                        <div className="hidden md:block text-xs text-gray-400">
                            {formatDate(selectedDate)}
                        </div>
                    </footer>
                </main>
            </div>
        </>
    );
}
