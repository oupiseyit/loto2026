// Screen: home | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL
import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import NavBar from '@/Components/NavBar';
import colors from '@/theme/colors';

// ── constants ──────────────────────────────────────────────────────────────
const SESSIONS = [
    { value: 'morning', label: 'ព្រឹក' },
    { value: 'noon',    label: 'ថ្ងៃ' },
    { value: 'evening', label: 'ល្ងាច' },
];

const LETTERS_ABCD = ['A', 'B', 'C', 'D'];
const LETTERS_NOON = ['A', 'B', 'C', 'D', 'F', 'I', 'N'];
const POSITIONS    = ['X', 'W', 'H', 'W*'];
const NUMPAD       = ['1','2','3','4','5','6','7','8','9','0','00','X'];

const SESSION_LABELS = { morning: 'ព្រឹក', noon: 'ថ្ងៃ', evening: 'ល្ងាច' };

// ── sub-components ─────────────────────────────────────────────────────────
function RadioGroup({ options, value, onChange, accent }) {
    return (
        <div className="flex gap-1">
            {options.map(opt => (
                <button
                    key={opt.value}
                    type="button"
                    onClick={() => onChange(opt.value)}
                    className="flex-1 py-1.5 text-xs font-bold rounded border transition-all"
                    style={value === opt.value
                        ? { backgroundColor: accent, color: '#fff', borderColor: accent }
                        : { backgroundColor: '#fff', color: accent, borderColor: accent }}
                >
                    {opt.label}
                </button>
            ))}
        </div>
    );
}

function LetterButton({ letter, active, onClick }) {
    return (
        <button
            type="button"
            onClick={() => onClick(letter)}
            className="px-3 py-1.5 text-xs font-bold rounded border transition-all"
            style={active
                ? { backgroundColor: colors.primary, color: '#fff', borderColor: colors.primary }
                : { backgroundColor: '#fff', color: colors.primary, borderColor: colors.primary }}
        >
            {letter}
        </button>
    );
}

function NumpadButton({ label, onClick }) {
    if (label === 'X') {
        return (
            <button
                type="button"
                onClick={() => onClick(label)}
                className="h-10 rounded font-bold text-sm flex items-center justify-center"
                style={{ backgroundColor: colors.accentLight ?? colors.accent, color: '#fff' }}
            >
                ✕
            </button>
        );
    }
    return (
        <button
            type="button"
            onClick={() => onClick(label)}
            className="h-10 rounded font-bold text-sm flex items-center justify-center"
            style={{ backgroundColor: colors.primary, color: '#fff' }}
        >
            {label}
        </button>
    );
}

// ── shared sub-sections ────────────────────────────────────────────────────

function BetListRows({ bets, removeBet }) {
    if (bets.length === 0) {
        return (
            <div className="flex items-center justify-center h-full min-h-[80px] opacity-10">
                <div className="text-center">
                    <div className="text-6xl font-black" style={{ color: colors.primary }}>HT</div>
                    <div className="text-sm" style={{ color: colors.accent }}>ភ្នាក់</div>
                </div>
            </div>
        );
    }
    return bets.map((bet, idx) => (
        <div
            key={bet.id}
            className="grid grid-cols-5 text-center text-xs py-1.5 border-b cursor-pointer hover:opacity-70"
            style={{ backgroundColor: idx % 2 === 0 ? colors.surface : '#fff', borderColor: '#e5e7eb' }}
            onClick={() => removeBet(bet.id)}
        >
            <span className="font-medium">{bet.letter}</span>
            <span>{bet.number}</span>
            <span className="uppercase text-xs" style={{ color: colors.accent }}>{bet.bet_type}</span>
            <span>{bet.amount.toLocaleString()}</span>
            <span style={{ color: colors.primary }}>{bet.position}</span>
        </div>
    ));
}

function InputControls({ betType, setBetType, letters, letter, setLetter, position, setPosition,
    number, amount, activeInput, setActiveInput, handleClear, handleAdd, handleNumpad, compact, hideNumpad }) {
    return (
        <>
            <RadioGroup
                options={[{ value: 'ABCD', label: 'ABCD' }, { value: 'LO', label: 'LO' }]}
                value={betType} onChange={setBetType} accent={colors.accent} />
            <div className="flex flex-wrap gap-1">
                {letters.map(l => (
                    <LetterButton key={l} letter={l} active={letter === l} onClick={setLetter} />
                ))}
            </div>
            <div className="flex gap-1">
                {POSITIONS.map(p => (
                    <button key={p} type="button" onClick={() => setPosition(p)}
                        className="flex-1 py-1.5 text-xs font-bold rounded border"
                        style={position === p
                            ? { backgroundColor: colors.primary, color: '#fff', borderColor: colors.primary }
                            : { backgroundColor: '#fff', color: colors.primary, borderColor: colors.primary }}>
                        {p}
                    </button>
                ))}
            </div>

            {compact ? (
                <>
                    <input type="text" readOnly value={number}
                        onFocus={() => setActiveInput('number')} onClick={() => setActiveInput('number')}
                        placeholder="Number"
                        className="w-full text-center py-2 text-base font-bold rounded border-2 outline-none cursor-pointer"
                        style={{ borderColor: activeInput === 'number' ? colors.accent : colors.primary, color: colors.accent }} />
                    <input type="text" readOnly value={amount}
                        onFocus={() => setActiveInput('amount')} onClick={() => setActiveInput('amount')}
                        placeholder="Amount"
                        className="w-full text-center py-2 text-base font-bold rounded border-2 outline-none cursor-pointer"
                        style={{ borderColor: activeInput === 'amount' ? colors.accent : colors.primary, color: colors.primary }} />
                </>
            ) : (
                <>
                    <input type="text" readOnly value={number}
                        onFocus={() => setActiveInput('number')} onClick={() => setActiveInput('number')}
                        placeholder="Number"
                        className="w-full text-center py-2 text-lg font-bold rounded border-2 outline-none cursor-pointer"
                        style={{ borderColor: activeInput === 'number' ? colors.accent : colors.primary, color: colors.accent }} />
                    <input type="text" readOnly value={amount}
                        onFocus={() => setActiveInput('amount')} onClick={() => setActiveInput('amount')}
                        placeholder="Amount"
                        className="w-full text-center py-2 text-lg font-bold rounded border-2 outline-none cursor-pointer"
                        style={{ borderColor: activeInput === 'amount' ? colors.accent : colors.primary, color: colors.primary }} />
                </>
            )}

            <div className="flex gap-2">
                <button type="button" onClick={handleClear}
                    className="w-full py-2 rounded-lg font-bold text-sm border-2"
                    style={{ borderColor: colors.primary, color: colors.primary, backgroundColor: '#fff' }}>CLEAR</button>
                <button type="button" onClick={handleAdd}
                    className="w-full py-2 rounded-lg font-bold text-sm text-white"
                    style={{ backgroundColor: colors.primary }}>ADD</button>
            </div>

            {!hideNumpad && (
                <div className={`grid grid-cols-3 ${compact ? 'gap-1' : 'gap-1.5'}`}>
                    {NUMPAD.map(key => (
                        <NumpadButton key={key} label={key} onClick={handleNumpad} />
                    ))}
                </div>
            )}
        </>
    );
}

// ── main page ──────────────────────────────────────────────────────────────
export default function Home({ today }) {
    const { flash } = usePage().props;

    const [session, setSession]     = useState('morning');
    const [betType, setBetType]     = useState('ABCD');
    const [letter, setLetter]       = useState('A');
    const [position, setPosition]   = useState('X');
    const [number, setNumber]       = useState('');
    const [amount, setAmount]       = useState('');
    const [activeInput, setActiveInput] = useState('number');
    const [showBetList, setShowBetList] = useState(true);

    const [bets, setBets]           = useState([]);
    const [submitting, setSubmitting] = useState(false);

    const letters = session === 'noon' ? LETTERS_NOON : LETTERS_ABCD;

    const handleSessionChange = (s) => {
        setSession(s);
        if (s !== 'noon' && !LETTERS_ABCD.includes(letter)) setLetter('A');
    };

    const handleNumpad = (key) => {
        if (key === 'X') {
            if (activeInput === 'number') setNumber('');
            else setAmount('');
            return;
        }
        if (activeInput === 'number') {
            setNumber(prev => (prev + key).slice(0, 10));
        } else {
            setAmount(prev => (prev + key).slice(0, 10));
        }
    };

    const handleAdd = () => {
        if (!number || !amount || parseFloat(amount) <= 0) return;
        setBets(prev => [
            ...prev,
            { id: Date.now(), bet_type: betType, letter, position, number, amount: parseFloat(amount) },
        ]);
        setNumber('');
        setAmount('');
    };

    const handleClear = () => { setNumber(''); setAmount(''); };
    const removeBet = (id) => setBets(prev => prev.filter(b => b.id !== id));
    const totalAmount = bets.reduce((sum, b) => sum + b.amount, 0);

    const handleSubmit = () => {
        if (bets.length === 0) return;
        setSubmitting(true);
        router.post(route('home.bet'), {
            session,
            bet_date: new Date().toISOString().split('T')[0],
            bets: bets.map(({ id, ...rest }) => rest),
        }, {
            onSuccess: () => { setBets([]); setSubmitting(false); },
            onError:   () => setSubmitting(false),
        });
    };

    const controlProps = {
        betType, setBetType, letters, letter, setLetter, position, setPosition,
        number, amount, activeInput, setActiveInput, handleClear, handleAdd, handleNumpad,
    };

    return (
        <>
            <Head title="Home — HT ភ្នាក់" />
            <NavBar />

            <div className="pt-12 md:pt-14 pb-16 md:pb-0 min-h-screen flex flex-col" style={{ backgroundColor: '#f5f5f5' }}>

                {flash?.success && (
                    <div className="mx-3 md:mx-4 mt-2 px-4 py-2 rounded text-sm text-white" style={{ backgroundColor: '#22c55e' }}>
                        {flash.success}
                    </div>
                )}

                {/* Top bar — date + session */}
                <div className="flex items-center gap-2 md:gap-4 px-3 md:px-4 py-2 bg-white border-b"
                    style={{ borderColor: colors.primary }}>
                    <span className="text-xs md:text-sm font-semibold" style={{ color: colors.accent }}>
                        {today}
                    </span>
                    <div className="flex gap-1 ml-auto">
                        {SESSIONS.map(s => (
                            <button key={s.value} type="button"
                                onClick={() => handleSessionChange(s.value)}
                                className="px-2 md:px-3 py-1 text-xs font-bold rounded border"
                                style={session === s.value
                                    ? { backgroundColor: colors.accent, color: '#fff', borderColor: colors.accent }
                                    : { backgroundColor: '#fff', color: colors.accent, borderColor: colors.accent }}>
                                {s.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* ═══ Desktop: Two-column layout ═══════════════ */}
                <div className="hidden md:flex flex-1 overflow-hidden">
                    <div className="flex flex-col w-1/2 border-r" style={{ borderColor: colors.primary }}>
                        <div className="grid grid-cols-5 text-center text-xs font-bold text-white py-1.5"
                            style={{ backgroundColor: colors.accent }}>
                            <span>បុំស្ដំ</span><span>លេខ</span><span>ឥវ៉ាន់</span><span>សរុប</span><span>ល្មមប</span>
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            <BetListRows bets={bets} removeBet={removeBet} />
                        </div>
                        <div className="border-t p-2 bg-white" style={{ borderColor: colors.primary }}>
                            <div className="flex items-center gap-2 mb-2 text-xs">
                                <span style={{ color: colors.accent }}>សរុប:</span>
                                <span className="font-bold" style={{ color: colors.accent }}>{totalAmount.toLocaleString()}</span>
                                <span className="ml-auto px-2 py-0.5 rounded text-white text-xs"
                                    style={{ backgroundColor: colors.primary }}>{bets.length}R</span>
                            </div>
                            <button type="button" onClick={handleSubmit}
                                disabled={bets.length === 0 || submitting}
                                className="w-full py-2 rounded-lg text-white font-bold text-sm disabled:opacity-50"
                                style={{ backgroundColor: colors.accent }}>
                                {submitting ? 'Submitting...' : 'បញ្ជូន (Submit)'}
                            </button>
                        </div>
                    </div>
                    <div className="flex flex-col w-1/2 p-3 gap-2 bg-white overflow-y-auto">
                        <InputControls {...controlProps} compact={false} />
                    </div>
                </div>

                {/* ═══ Mobile: Stacked layout ═══════════════════ */}
                <div className="flex md:hidden flex-col flex-1 overflow-auto pb-2">
                    <div className="border-t" style={{ borderColor: colors.primary }}>
                        <button type="button" onClick={() => setShowBetList(!showBetList)}
                            className="w-full flex items-center justify-between px-3 py-2 bg-white"
                            style={{ color: colors.accent }}>
                            <div className="flex items-center gap-2 text-xs font-bold">
                                <span>សរុប: {totalAmount.toLocaleString()}</span>
                                <span className="px-2 py-0.5 rounded text-white text-xs"
                                    style={{ backgroundColor: colors.primary }}>{bets.length}R</span>
                            </div>
                            <svg className={`w-4 h-4 transition-transform ${showBetList ? 'rotate-180' : ''}`}
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {showBetList && (
                            <div className="max-h-40 overflow-y-auto">
                                <div className="grid grid-cols-5 text-center text-[10px] font-bold text-white py-1"
                                    style={{ backgroundColor: colors.accent }}>
                                    <span>បុំស្ដំ</span><span>លេខ</span><span>ឥវ៉ាន់</span><span>សរុប</span><span>ល្មមប</span>
                                </div>
                                {bets.length === 0 ? (
                                    <p className="text-center text-xs text-gray-400 py-4">No bets yet</p>
                                ) : (
                                    bets.map((bet, idx) => (
                                        <div key={bet.id}
                                            className="grid grid-cols-5 text-center text-[11px] py-1.5 border-b cursor-pointer hover:opacity-70"
                                            style={{ backgroundColor: idx % 2 === 0 ? colors.surface : '#fff', borderColor: '#e5e7eb' }}
                                            onClick={() => removeBet(bet.id)}>
                                            <span className="font-medium">{bet.letter}</span>
                                            <span>{bet.number}</span>
                                            <span className="uppercase" style={{ color: colors.accent }}>{bet.bet_type}</span>
                                            <span>{bet.amount.toLocaleString()}</span>
                                            <span style={{ color: colors.primary }}>{bet.position}</span>
                                        </div>
                                    ))
                                )}
                            </div>
                        )}


                        <div className="p-2 bg-white">
                            <button type="button" onClick={handleSubmit}
                                disabled={bets.length === 0 || submitting}
                                className="w-full py-2.5 rounded-lg text-white font-bold text-sm disabled:opacity-50"
                                style={{ backgroundColor: colors.accent }}>
                                {submitting ? 'Submitting...' : 'បញ្ជូន (Submit)'}
                            </button>
                        </div>
                    </div>

                    <div className="fixed bottom-[57px] left-0 right-0 border-t border-gray-200">
                       <div className="flex flex-col p-3 pb-[200px] gap-2 bg-white">
                            <InputControls {...controlProps} compact={true} hideNumpad={true} />
                        </div>
                    </div>

                    {/* Fixed numpad at bottom — sits above the mobile tab bar (~57px) */}
                    <div className="fixed bottom-[57px] left-0 right-0 bg-white border-t p-2 grid grid-cols-3 gap-1 md:hidden"
                        style={{ borderColor: colors.primary }}>
                        {NUMPAD.map(key => (
                            <NumpadButton key={key} label={key} onClick={handleNumpad} />
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}
