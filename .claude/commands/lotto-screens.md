# Lotto — Screen Specifications

All screens share the top Navigation Bar except Login. See `lotto-design.md` for colors.

---

## 1. Login
**Route:** `/login`  
**Layout:** Single — centered card on full-screen `accent` background with floating money/coin decorations

| Component | Details |
|---|---|
| Logo | HT circle — white inner, gold outer, Khmer text |
| Username field | Person icon prefix, white border, `accent` bg |
| Password field | Lock icon prefix, white border, `accent` bg |
| Login button | White bg, `accent` text, wide, `rounded-lg` |

**Behavior:** Validate non-empty fields → show spinner on button while authenticating.

---

## 2. Home / Betting
**Route:** `/home`  
**Layout:** Two-column — left panel (bet list) + right panel (input controls)

**Top bar (below nav):**
- `Date Bet: DD-MMM-YYYY` label
- Session radio: `ព្រឹក` (Morning) | `ថ្ងៃ` (Noon) | `ល្ងាច` (Evening)

**Right panel — Input controls:**
| Element | Details |
|---|---|
| Bet type radio | `ABCD` \| `LO` |
| Letter buttons (ABCD) | `A B C D` — 4 buttons |
| Letter buttons (Noon) | `A B C D F I N` — 7 buttons |
| Position toggles | `X \| W \| H \| W*` |
| Number input | Centered, bordered |
| Amount input | Bordered, below number |
| Action buttons | `CLEAR` (outline) \| `ADD` (filled) |
| Numpad | `1 2 3 / 4 5 6 / 7 8 9 / 0 00 X` — 4×3 grid |

**Left panel — Bet List:**
| Element | Details |
|---|---|
| Table columns | `បុំស្ដំ \| លេខ \| ឥវ៉ាន់ \| សរុប \| ល្មមប` |
| Empty state | Watermark logo centered |
| Footer | `បញ្ជូន (Submit)` button \| `សរុប (Total)` \| amount \| `R` count |

---

## 3. Record
**Route:** `/record`  
**Layout:** Two-column — date list (left) + records table (right)

**Left panel:**
- Scrollable date list (YYYY-MM-DD)
- Selected date: `primary` bg highlight

**Right panel:**
| Element | Details |
|---|---|
| Tabs | `All Records \| ព្រឹក \| ថ្ងៃ \| យប់ \| Winning Records` |
| Table columns | `ល.រ \| ល.វិកយប័ត្រ \| ជន \| ការលុបរំខើន \| វីកប្រាក់ \| កាន់វីកស្ដូ \| ឈ្នះ` |
| Footer | `Total Record: XX` \| `Total Amount: X` \| `Win/Lose: X` |

**Role difference:** admin sees all records; master sees own staff records; staff sees only their own.

---

## 4. Result
**Route:** `/result`  
**Layout:** Two-column — date list (left) + result table (right)

**Left panel:** Same scrollable date list as Record screen.

**Right panel:**
| Element | Details |
|---|---|
| Session radio | `ព្រឹក \| ថ្ងៃ \| ល្ងាច` |
| Print button | Top-right, `primary` bg, printer icon |
| Table columns | `បុំស្ដំ \| លេខ២` |
| Table header | `accent` bg, white text |

**Role difference:** staff + master = read-only; admin = can enter and edit results.

---

## 5. Setting
**Route:** `/setting`  
**Layout:** Single column — section list

**Printer section** (header: `Printer` in `accent` color, bold, with divider):

| Setting | Control | Role restriction |
|---|---|---|
| Bluetooth | Checkbox | Both |
| Selected Device | Radio: `58MM (printer)` \| `80MM (printer)` | Both |
| Logo | Radio: `Print with Logo` \| `Print with Text` | Both |
| Commission | Radio: `Default` \| `Custom` | **Admin only** |

Below Printer section: `About` section (app version info).

---

## 6. Account
**Route:** `/account`  
**Layout:** Two-column

**Left panel:**
| Element | Details |
|---|---|
| Label | `Sale Amount Today (i)` in `accent` color |
| Amount circle | Large `accent` circle, white amount number (e.g. `21000`) |
| Detail button | `Sale Amount Detail` — `primary` bg, `rounded-lg` |

**Right panel — Change Password:**
| Element | Details |
|---|---|
| Header | `Change Password` in `accent` color |
| Current Password | Text field + eye-toggle icon |
| New Password | Text field + eye-toggle icon |
| Confirm Password | Text field + eye-toggle icon |
| Update button | `primary` bg, white text |
| Logout button | `accent` bg, white text |

**Role difference:** admin sees total sales across all staff + user management; master sees own staff sales + can create staff; staff sees own sales only.

---

## 7. Report _(admin + master only — staff: 403)_
**Route:** `/report`  
**Layout:** Single column with filter bar + summary cards + data table

**Filter bar (top):**
| Element | Details |
|---|---|
| Date range | From / To date pickers |
| Staff filter | Dropdown — admin: all staff; master: own staff only |
| Session filter | All \| ព្រឹក \| ថ្ងៃ \| ល្ងាច |
| Export button | `primary` bg, right-aligned — PDF / CSV |

**Summary cards (row of 4):**
| Card | Value |
|---|---|
| Total Bets | count |
| Total Amount | sum in KHR |
| Total Win | payout sum |
| Net | Total Amount − Total Win |

**Data table:**
| Column | Description |
|---|---|
| Staff name | Who placed the bets |
| Session | Morning / Noon / Evening |
| Total bets | Count |
| Amount | Sum |
| Win | Payout |
| Net | Profit |

**Role difference:**
- **admin** — sees all staff, no filter restriction
- **master** — dropdown only shows their own staff; cannot see other masters' staff

---

## Pagination
Record, Result, and Report tables use **server-side pagination**:
- Default page size: `20` rows
- Laravel: `->paginate(20)` — pass `links` prop to Inertia
- React: render `<Pagination links={links} />` component below each table
- API: return `meta.current_page`, `meta.last_page`, `meta.total` in response
