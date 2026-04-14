import { Link, router, usePage } from '@inertiajs/react';
import colors from '@/theme/colors';

// Tabs visible to all roles
const ALL_TABS = [
    { name: 'Home',    route: 'home',    roles: null, icon: (
        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
    )},
    { name: 'Record',  route: 'record',  roles: null, icon: (
        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
    )},
    { name: 'Result',  route: 'result',  roles: null, icon: (
        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
        </svg>
    )},
    // Setting — admin and master only (staff cannot access)
    { name: 'Setting', route: 'setting', roles: ['admin', 'master'], icon: (
        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    )},
    { name: 'Account', route: 'account', roles: null, icon: (
        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
    )},
];

function LogoutButton() {
    const handleLogout = () => {
        router.post(route('logout'));
    };

    return (
        <button
            onClick={handleLogout}
            className="flex flex-col items-center gap-0.5 px-2 py-1 rounded-lg transition-colors text-white hover:bg-white/20"
            title="Logout"
        >
            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span className="text-[10px] leading-none">Logout</span>
        </button>
    );
}

export default function NavBar() {
    const { auth } = usePage().props;
    const user = auth?.user;
    const role = user?.role;

    // Filter tabs by role — null means visible to everyone
    const tabs = ALL_TABS.filter(tab =>
        tab.roles === null || tab.roles.includes(role)
    );

    return (
        <>
            {/* ── Desktop top bar (hidden on mobile) ────────── */}
            <nav className="hidden md:flex fixed top-0 left-0 right-0 z-50 items-center justify-between px-4 h-14 shadow-md"
                style={{ backgroundColor: colors.primary }}>
                {/* Left — logo + user info */}
                <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-full bg-white flex items-center justify-center text-xs font-black"
                        style={{ color: colors.accent }}>
                        HT
                    </div>
                    <div className="text-white">
                        <div className="text-sm font-bold leading-none">{user?.name ?? 'HT ភ្នាក់'}</div>
                        <div className="text-xs opacity-75 leading-none mt-0.5">
                            🪙 {user?.role ?? '—'}
                        </div>
                    </div>
                </div>

                {/* Right — nav tabs + logout */}
                <div className="flex items-center gap-1">
                    {tabs.map(tab => {
                        const isActive = route().current(tab.route);
                        return (
                            <Link
                                key={tab.route}
                                href={route(tab.route)}
                                className="flex flex-col items-center gap-0.5 px-2 py-1 rounded-lg transition-colors text-white"
                                style={{ backgroundColor: isActive ? 'rgba(255,255,255,0.2)' : 'transparent' }}
                            >
                                {tab.icon}
                                <span className="text-[10px] leading-none">{tab.name}</span>
                            </Link>
                        );
                    })}
                    <div className="w-px h-6 bg-white/30 mx-1" />
                    <LogoutButton />
                </div>
            </nav>

            {/* ── Mobile top bar (minimal — only logo + user) ── */}
            <nav className="md:hidden fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-4 h-12 shadow-md"
                style={{ backgroundColor: colors.primary }}>
                <div className="flex items-center gap-2">
                    <div className="w-8 h-8 rounded-full bg-white flex items-center justify-center text-xs font-black"
                        style={{ color: colors.accent }}>
                        HT
                    </div>
                    <div className="text-white">
                        <div className="text-sm font-bold leading-none">{user?.name ?? 'HT ភ្នាក់'}</div>
                        <div className="text-[10px] opacity-75 leading-none mt-0.5">
                            🪙 {user?.role ?? '—'}
                        </div>
                    </div>
                </div>
                <LogoutButton />
            </nav>

            {/* ── Mobile bottom tab bar ─────────────────────── */}
            <nav className="md:hidden fixed bottom-0 left-0 right-0 z-50 flex items-center justify-around border-t shadow-lg"
                style={{ backgroundColor: colors.primary, borderColor: 'rgba(255,255,255,0.1)' }}>
                {tabs.map(tab => {
                    const isActive = route().current(tab.route);
                    return (
                        <Link
                            key={tab.route}
                            href={route(tab.route)}
                            className="flex flex-col items-center gap-0.5 py-2 px-3 transition-colors text-white"
                            style={{ opacity: isActive ? 1 : 0.6 }}
                        >
                            {tab.icon}
                            <span className="text-[10px] leading-none font-medium">{tab.name}</span>
                            {isActive && (
                                <div className="w-1 h-1 rounded-full bg-white mt-0.5" />
                            )}
                        </Link>
                    );
                })}
            </nav>
        </>
    );
}
