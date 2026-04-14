// Screen: setting | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

import { Head, useForm, usePage } from '@inertiajs/react';
import NavBar from '@/Components/NavBar';
import colors from '@/theme/colors';

// ─── Helpers ─────────────────────────────────────────────────

function SectionHeader({ title }) {
    return (
        <div className="flex items-center gap-3 mb-4">
            <h2 className="text-base font-bold" style={{ color: colors.accent }}>{title}</h2>
            <div className="flex-1 h-px" style={{ backgroundColor: colors.accent + '33' }} />
        </div>
    );
}

function SettingRow({ label, children }) {
    return (
        <div className="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <span className="text-sm font-medium text-gray-700">{label}</span>
            <div className="flex items-center gap-3">{children}</div>
        </div>
    );
}

function RadioGroup({ name, value, onChange, options }) {
    return (
        <div className="flex gap-4">
            {options.map(opt => (
                <label key={opt.value} className="flex items-center gap-1.5 cursor-pointer text-sm">
                    <input
                        type="radio"
                        name={name}
                        value={opt.value}
                        checked={value === opt.value}
                        onChange={() => onChange(opt.value)}
                        className="accent-[#DC143C]"
                    />
                    <span className="text-gray-700">{opt.label}</span>
                </label>
            ))}
        </div>
    );
}

// ─── Main Page ───────────────────────────────────────────────

export default function Setting({ setting }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    const isAdmin = user?.role === 'admin';

    const { data, setData, post, processing, errors } = useForm({
        bluetooth_enabled: setting?.bluetooth_enabled ?? false,
        printer_size:      setting?.printer_size      ?? '58mm',
        logo_mode:         setting?.logo_mode         ?? 'logo',
        commission_mode:   setting?.commission_mode   ?? 'default',
        commission_value:  setting?.commission_value  ?? '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('setting.update'), { preserveScroll: true });
    };

    return (
        <>
            <Head title="Setting — HT ភ្នាក់" />
            <NavBar />

            <div className="pt-14 min-h-screen" style={{ backgroundColor: colors.background }}>
                <div className="max-w-lg mx-auto px-4 py-6">

                    <form onSubmit={handleSubmit} className="space-y-6">

                        {/* ── Printer Section ─────────────────── */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                            <SectionHeader title="Printer" />

                            {/* Bluetooth */}
                            <SettingRow label="Bluetooth">
                                <label className="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.bluetooth_enabled}
                                        onChange={e => setData('bluetooth_enabled', e.target.checked)}
                                        className="sr-only peer"
                                    />
                                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                        peer-checked:after:translate-x-full peer-checked:after:border-white
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                        after:bg-white after:border-gray-300 after:border after:rounded-full
                                        after:h-5 after:w-5 after:transition-all peer-checked:bg-[#DC143C]"
                                    />
                                </label>
                            </SettingRow>

                            {/* Printer size */}
                            <SettingRow label="Selected Device">
                                <RadioGroup
                                    name="printer_size"
                                    value={data.printer_size}
                                    onChange={v => setData('printer_size', v)}
                                    options={[
                                        { value: '58mm', label: '58MM (printer)' },
                                        { value: '80mm', label: '80MM (printer)' },
                                    ]}
                                />
                            </SettingRow>
                            {errors.printer_size && (
                                <p className="text-xs text-red-500 mt-1">{errors.printer_size}</p>
                            )}

                            {/* Logo mode */}
                            <SettingRow label="Logo">
                                <RadioGroup
                                    name="logo_mode"
                                    value={data.logo_mode}
                                    onChange={v => setData('logo_mode', v)}
                                    options={[
                                        { value: 'logo', label: 'Print with Logo' },
                                        { value: 'text', label: 'Print with Text' },
                                    ]}
                                />
                            </SettingRow>

                            {/* Commission — admin only */}
                            {isAdmin && (
                                <>
                                    <SettingRow label="Commission">
                                        <RadioGroup
                                            name="commission_mode"
                                            value={data.commission_mode}
                                            onChange={v => setData('commission_mode', v)}
                                            options={[
                                                { value: 'default', label: 'Default' },
                                                { value: 'custom',  label: 'Custom' },
                                            ]}
                                        />
                                    </SettingRow>

                                    {data.commission_mode === 'custom' && (
                                        <div className="pt-2 pb-1 flex items-center gap-3">
                                            <label className="text-sm text-gray-600 w-32">
                                                Commission %
                                            </label>
                                            <div className="flex items-center gap-1">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    step="0.01"
                                                    value={data.commission_value}
                                                    onChange={e => setData('commission_value', e.target.value)}
                                                    placeholder="e.g. 5.00"
                                                    className="w-28 border-2 rounded-lg px-3 py-1.5 text-sm outline-none transition-colors focus:border-[#D4A017]"
                                                    style={{ borderColor: errors.commission_value ? '#DC143C' : '#E5E7EB' }}
                                                />
                                                <span className="text-sm text-gray-500">%</span>
                                            </div>
                                        </div>
                                    )}
                                    {errors.commission_value && (
                                        <p className="text-xs text-red-500">{errors.commission_value}</p>
                                    )}
                                </>
                            )}
                        </div>

                        {/* ── Save button ─────────────────────── */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3 rounded-xl text-white font-bold text-base shadow-md transition-opacity hover:opacity-80 disabled:opacity-50"
                            style={{ backgroundColor: colors.primary }}
                        >
                            {processing ? 'Saving…' : 'Save Settings'}
                        </button>

                        {/* ── About Section ───────────────────── */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                            <SectionHeader title="About" />
                            <div className="space-y-2 text-sm text-gray-600">
                                <div className="flex justify-between">
                                    <span>App Name</span>
                                    <span className="font-semibold text-gray-800">HT ភ្នាក់</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Version</span>
                                    <span className="font-semibold text-gray-800">1.0.0</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Stack</span>
                                    <span className="font-semibold text-gray-800">Laravel 13 + Inertia + React</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Role</span>
                                    <span className="font-semibold capitalize" style={{ color: colors.accent }}>
                                        {user?.role}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </>
    );
}
