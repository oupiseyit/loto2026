// Screen: login | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL
import { useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import colors from '@/theme/colors';

// Floating coin/money decoration component
function FloatingDeco() {
    const items = ['💰', '🪙', '💵', '🏆', '🎰', '💎', '⭐', '🌟'];
    return (
        <div className="pointer-events-none absolute inset-0 overflow-hidden">
            {items.map((icon, i) => (
                <span
                    key={i}
                    className="absolute text-2xl opacity-20 animate-bounce"
                    style={{
                        top:  `${10 + (i * 11) % 80}%`,
                        left: `${5  + (i * 13) % 88}%`,
                        animationDelay: `${i * 0.4}s`,
                        animationDuration: `${2 + (i % 3)}s`,
                    }}
                >
                    {icon}
                </span>
            ))}
        </div>
    );
}

export default function Login({ status }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        username: '',
        password: '',
    });

    useEffect(() => {
        return () => reset('password');
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <>
            <Head title="Login — HT ភ្នាក់" />

            {/* Full-screen accent background */}
            <div
                className="min-h-screen flex items-center justify-center relative"
                style={{ backgroundColor: colors.accent }}
            >
                <FloatingDeco />

                {/* Login card */}
                <div className="relative z-10 w-full max-w-sm mx-4">
                    {/* Logo */}
                    <div className="flex flex-col items-center mb-8">
                        {/* HT circle logo */}
                        <div
                            className="w-24 h-24 rounded-full flex items-center justify-center mb-3 shadow-xl border-4"
                            style={{
                                backgroundColor: '#FFFFFF',
                                borderColor: colors.primary,
                            }}
                        >
                            <div
                                className="w-16 h-16 rounded-full flex flex-col items-center justify-center"
                                style={{ backgroundColor: colors.primary }}
                            >
                                <span className="text-white font-black text-lg leading-none">HT</span>
                                <span className="text-white text-xs leading-none mt-0.5">ភ្នាក់</span>
                            </div>
                        </div>
                        <h1 className="text-white text-2xl font-bold tracking-wide">HT ភ្នាក់</h1>
                        <p className="text-white/70 text-sm mt-1">Lottery Agent System</p>
                    </div>

                    {/* Card */}
                    <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20">
                        {status && (
                            <div className="mb-4 text-sm text-green-200 text-center">{status}</div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            {/* Username */}
                            <div>
                                <div
                                    className="flex items-center rounded-lg border overflow-hidden"
                                    style={{ borderColor: 'rgba(255,255,255,0.6)' }}
                                >
                                    <div className="px-3 py-3" style={{ backgroundColor: 'rgba(255,255,255,0.15)' }}>
                                        <svg className="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input
                                        type="text"
                                        value={data.username}
                                        onChange={e => setData('username', e.target.value)}
                                        placeholder="Username"
                                        autoComplete="username"
                                        className="flex-1 bg-transparent text-white placeholder-white/60 px-3 py-3 outline-none text-sm"
                                        required
                                    />
                                </div>
                                {errors.username && (
                                    <p className="text-red-200 text-xs mt-1">{errors.username}</p>
                                )}
                            </div>

                            {/* Password */}
                            <div>
                                <div
                                    className="flex items-center rounded-lg border overflow-hidden"
                                    style={{ borderColor: 'rgba(255,255,255,0.6)' }}
                                >
                                    <div className="px-3 py-3" style={{ backgroundColor: 'rgba(255,255,255,0.15)' }}>
                                        <svg className="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input
                                        type="password"
                                        value={data.password}
                                        onChange={e => setData('password', e.target.value)}
                                        placeholder="Password"
                                        autoComplete="current-password"
                                        className="flex-1 bg-transparent text-white placeholder-white/60 px-3 py-3 outline-none text-sm"
                                        required
                                    />
                                </div>
                                {errors.password && (
                                    <p className="text-red-200 text-xs mt-1">{errors.password}</p>
                                )}
                            </div>

                            {/* Login button */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-3 rounded-lg font-bold text-sm tracking-wide transition-opacity disabled:opacity-70 flex items-center justify-center gap-2 mt-2"
                                style={{ backgroundColor: '#FFFFFF', color: colors.accent }}
                            >
                                {processing ? (
                                    <>
                                        <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                            <path className="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        Logging in...
                                    </>
                                ) : (
                                    'LOGIN'
                                )}
                            </button>
                        </form>
                    </div>

                    <p className="text-center text-white/40 text-xs mt-6">
                        © 2026 HT ភ្នាក់ Lottery System
                    </p>
                </div>
            </div>
        </>
    );
}
