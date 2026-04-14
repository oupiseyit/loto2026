// Screen: account | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import NavBar from '@/Components/NavBar';
import colors from '@/theme/colors';

// ─── Helpers ─────────────────────────────────────────────────

function fmt(num) {
    return Number(num || 0).toLocaleString();
}

// ─── Eye-toggle password field ───────────────────────────────

function PasswordField({ label, id, value, onChange, error }) {
    const [show, setShow] = useState(false);
    return (
        <div>
            <label htmlFor={id} className="block text-sm font-medium text-gray-600 mb-1">
                {label}
            </label>
            <div className="relative">
                <input
                    id={id}
                    type={show ? 'text' : 'password'}
                    value={value}
                    onChange={onChange}
                    autoComplete="off"
                    className="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none pr-10 transition-colors focus:border-[#D4A017]"
                    style={{ borderColor: error ? '#DC143C' : '#E5E7EB' }}
                />
                <button
                    type="button"
                    onClick={() => setShow(s => !s)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                    {show ? (
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                        </svg>
                    ) : (
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    )}
                </button>
            </div>
            {error && <p className="text-xs text-red-500 mt-1">{error}</p>}
        </div>
    );
}

// ─── Create Staff Modal ──────────────────────────────────────

function CreateStaffModal({ open, onClose }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name:                  '',
        username:              '',
        password:              '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('account.users.store'), {
            preserveScroll: true,
            onSuccess: () => { reset(); onClose(); },
        });
    };

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div className="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
                <h3 className="text-base font-bold mb-4" style={{ color: colors.accent }}>
                    Create Staff Account
                </h3>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={e => setData('name', e.target.value)}
                            className="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017]"
                            style={{ borderColor: errors.name ? '#DC143C' : '#E5E7EB' }}
                        />
                        {errors.name && <p className="text-xs text-red-500 mt-1">{errors.name}</p>}
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Username</label>
                        <input
                            type="text"
                            value={data.username}
                            onChange={e => setData('username', e.target.value)}
                            className="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017]"
                            style={{ borderColor: errors.username ? '#DC143C' : '#E5E7EB' }}
                        />
                        {errors.username && <p className="text-xs text-red-500 mt-1">{errors.username}</p>}
                    </div>
                    <PasswordField
                        label="Password"
                        id="new-staff-password"
                        value={data.password}
                        onChange={e => setData('password', e.target.value)}
                        error={errors.password}
                    />
                    <PasswordField
                        label="Confirm Password"
                        id="new-staff-password-confirm"
                        value={data.password_confirmation}
                        onChange={e => setData('password_confirmation', e.target.value)}
                        error={errors.password_confirmation}
                    />
                    <div className="flex gap-3 pt-2">
                        <button
                            type="button"
                            onClick={onClose}
                            className="flex-1 py-2 rounded-xl text-sm font-semibold border-2 text-gray-600 transition-colors hover:bg-gray-50"
                            style={{ borderColor: '#E5E7EB' }}
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex-1 py-2 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                            style={{ backgroundColor: colors.accent }}
                        >
                            {processing ? 'Creating…' : 'Create'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

// ─── Staff Row ───────────────────────────────────────────────

function StaffRow({ staff, isAdmin }) {
    const handleToggle = () => {
        router.put(route('account.users.update', staff.id), {
            is_active: !staff.is_active,
        }, { preserveScroll: true });
    };

    const handleDelete = () => {
        if (!confirm(`Delete ${staff.name}? This cannot be undone.`)) return;
        router.delete(route('account.users.destroy', staff.id), { preserveScroll: true });
    };

    return (
        <tr className="border-b border-gray-100 hover:bg-yellow-50 transition-colors">
            <td className="px-3 py-2.5 font-medium text-gray-800 text-sm">{staff.name}</td>
            <td className="px-3 py-2.5 text-gray-500 text-sm font-mono">{staff.username}</td>
            <td className="px-3 py-2.5 text-right text-sm font-semibold text-gray-700">
                {fmt(staff.today_amount)}
            </td>
            <td className="px-3 py-2.5 text-center">
                <span className={`text-xs font-semibold px-2 py-0.5 rounded-full ${
                    staff.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                }`}>
                    {staff.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td className="px-3 py-2.5 text-center">
                <div className="flex items-center justify-center gap-2">
                    <button
                        onClick={handleToggle}
                        className="text-xs px-2 py-1 rounded border font-medium transition-colors"
                        style={{
                            borderColor: staff.is_active ? '#F59E0B' : '#10B981',
                            color:       staff.is_active ? '#B45309' : '#065F46',
                        }}
                    >
                        {staff.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                    {isAdmin && (
                        <button
                            onClick={handleDelete}
                            className="text-xs px-2 py-1 rounded border font-medium text-red-600 border-red-200 hover:bg-red-50 transition-colors"
                        >
                            Delete
                        </button>
                    )}
                </div>
            </td>
        </tr>
    );
}

// ─── Main Page ───────────────────────────────────────────────

export default function Account({ today_sales, breakdown, staff }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    const isAdmin  = user?.role === 'admin';
    const isMaster = user?.role === 'master';
    const canManageStaff = isAdmin || isMaster;

    const [showCreateModal, setShowCreateModal] = useState(false);

    // Password change form
    const { data, setData, post, processing, errors, reset } = useForm({
        current_password:      '',
        password:              '',
        password_confirmation: '',
    });

    const handlePasswordSubmit = (e) => {
        e.preventDefault();
        post(route('account.password'), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const handleLogout = () => {
        router.post(route('logout'));
    };

    return (
        <>
            <Head title="Account — HT ភ្នាក់" />
            <NavBar />

            <CreateStaffModal open={showCreateModal} onClose={() => setShowCreateModal(false)} />

            <div className="pt-14 min-h-screen" style={{ backgroundColor: colors.background }}>
                <div className="max-w-4xl mx-auto px-4 py-6">

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {/* ── Left panel: Sales ───────────────── */}
                        <div className="space-y-4">
                            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                                <p className="text-sm font-semibold mb-4" style={{ color: colors.accent }}>
                                    Sale Amount Today
                                    <span className="ml-1 text-xs font-normal text-gray-400">ⓘ</span>
                                </p>

                                {/* Big circle */}
                                <div className="flex justify-center mb-4">
                                    <div className="w-40 h-40 rounded-full flex flex-col items-center justify-center shadow-lg"
                                        style={{ backgroundColor: colors.accent }}>
                                        <span className="text-white text-3xl font-black leading-none">
                                            {fmt(today_sales)}
                                        </span>
                                        <span className="text-white/70 text-xs mt-1">KHR</span>
                                    </div>
                                </div>

                                {/* Session breakdown */}
                                {breakdown && (
                                    <div className="space-y-2 mt-2">
                                        {['morning', 'noon', 'evening'].map(session => {
                                            const b = breakdown[session];
                                            const label = { morning: 'ព្រឹក', noon: 'ថ្ងៃ', evening: 'ល្ងាច' }[session];
                                            return (
                                                <div key={session}
                                                    className="flex justify-between items-center text-sm px-3 py-1.5 rounded-lg"
                                                    style={{ backgroundColor: colors.surface }}>
                                                    <span className="text-gray-600">{label}</span>
                                                    <span className="font-semibold text-gray-800">
                                                        {fmt(b?.amount ?? 0)}
                                                    </span>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* ── Right panel: Change Password ──── */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                            <h2 className="text-base font-bold mb-4" style={{ color: colors.accent }}>
                                Change Password
                            </h2>

                            <form onSubmit={handlePasswordSubmit} className="space-y-4">
                                <PasswordField
                                    label="Current Password"
                                    id="current_password"
                                    value={data.current_password}
                                    onChange={e => setData('current_password', e.target.value)}
                                    error={errors.current_password}
                                />
                                <PasswordField
                                    label="New Password"
                                    id="password"
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                    error={errors.password}
                                />
                                <PasswordField
                                    label="Confirm Password"
                                    id="password_confirmation"
                                    value={data.password_confirmation}
                                    onChange={e => setData('password_confirmation', e.target.value)}
                                    error={errors.password_confirmation}
                                />

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full py-2.5 rounded-xl text-white font-bold text-sm shadow-sm transition-opacity hover:opacity-80 disabled:opacity-50"
                                    style={{ backgroundColor: colors.primary }}
                                >
                                    {processing ? 'Updating…' : 'Update Password'}
                                </button>
                            </form>

                            {/* Logout */}
                            <div className="mt-4 pt-4 border-t border-gray-100">
                                <button
                                    onClick={handleLogout}
                                    className="w-full py-2.5 rounded-xl text-white font-bold text-sm transition-opacity hover:opacity-80"
                                    style={{ backgroundColor: colors.accent }}
                                >
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* ── Staff Management — admin + master ─── */}
                    {canManageStaff && (
                        <div className="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                            <div className="flex items-center justify-between mb-4">
                                <h2 className="text-base font-bold" style={{ color: colors.accent }}>
                                    Staff Accounts
                                </h2>
                                <button
                                    onClick={() => setShowCreateModal(true)}
                                    className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white text-sm font-semibold"
                                    style={{ backgroundColor: colors.primary }}
                                >
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Staff
                                </button>
                            </div>

                            {staff && staff.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm border-collapse">
                                        <thead>
                                            <tr style={{ backgroundColor: colors.accent }}>
                                                <th className="px-3 py-2.5 text-left text-white font-semibold">Name</th>
                                                <th className="px-3 py-2.5 text-left text-white font-semibold">Username</th>
                                                <th className="px-3 py-2.5 text-right text-white font-semibold">Today Sales</th>
                                                <th className="px-3 py-2.5 text-center text-white font-semibold">Status</th>
                                                <th className="px-3 py-2.5 text-center text-white font-semibold">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {staff.map(s => (
                                                <StaffRow key={s.id} staff={s} isAdmin={isAdmin} />
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <p className="text-sm text-gray-400 text-center py-8">
                                    No staff accounts yet. Create one above.
                                </p>
                            )}
                        </div>
                    )}

                </div>
            </div>
        </>
    );
}
