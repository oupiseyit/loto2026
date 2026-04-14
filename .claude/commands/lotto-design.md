# Lotto — Design System

## Available Color Schemes

The theme is passed as the second argument to `/lotto-screen`. Default is `gold/crimson`.

| Theme Key | Primary (nav/buttons) | Primary Dark (pressed) | Accent (headers/danger) | Accent Light (secondary) | Surface (table rows) | Login BG |
|---|---|---|---|---|---|---|
| `gold/crimson` | `#D4A017` | `#B8860B` | `#DC143C` | `#C0392B` | `#FFF8DC` | `#DC143C` |
| `gold/scarlet` | `#D4A017` | `#B8860B` | `#FF2400` | `#E32000` | `#FFF8DC` | `#FF2400` |
| `gold/ruby` | `#D4A017` | `#B8860B` | `#9B111E` | `#7A0E18` | `#FFF8DC` | `#9B111E` |
| `gold/burgundy` | `#D4A017` | `#B8860B` | `#800020` | `#660019` | `#FFF8DC` | `#800020` |
| `gold/carmine` | `#D4A017` | `#B8860B` | `#960018` | `#780013` | `#FFF8DC` | `#960018` |
| `amber/scarlet` | `#FFBF00` | `#CC9900` | `#FF2400` | `#E32000` | `#FFFACD` | `#FF2400` |
| `bronze/crimson` | `#CD7F32` | `#A0522D` | `#DC143C` | `#C0392B` | `#FDF5E6` | `#DC143C` |
| `copper/scarlet` | `#B87333` | `#8B5A2B` | `#FF2400` | `#E32000` | `#FDF5E6` | `#FF2400` |
| `marigold/coral` | `#EFC050` | `#D4A017` | `#FF6B6B` | `#E85555` | `#FFFDE7` | `#FF6B6B` |
| `burnt-orange/crimson` | `#CC5500` | `#A34400` | `#DC143C` | `#C0392B` | `#FFF3E0` | `#DC143C` |

## Color Token Roles

| Token | Usage |
|---|---|
| `primary` | Top nav bar bg, active tab bg, primary action buttons, numpad buttons |
| `primary-dark` | Button pressed/hover state |
| `accent` | Table headers, active radio/checkbox, danger buttons (Logout), login screen bg |
| `accent-light` | Secondary buttons (Cancel, Clear outline) |
| `background` | `#FFFFFF` — screen background (always white) |
| `surface` | Card / table row background (warm tint, varies by theme) |
| `text-primary` | Body text, section labels — use `accent` color |
| `text-on-primary` | `#FFFFFF` — text on colored backgrounds |
| `border` | Table borders, dividers — use `primary` color |

## Typography
- **Headers / Nav labels:** Bold, white on primary background
- **Body / Labels:** `accent` color, regular weight
- **Khmer font:** Use system font or `Khmer OS` if available

## Navigation Bar (Top)
Present on all screens except Login. Fixed top bar with `primary` background:
- **Left:** HT logo circle + username + balance (coin icon + amount)
- **Right:** 5 icon tabs — `Home | Record | Result | Setting | Account`
- Active tab: slightly lighter background highlight

## Button Styles
| Type | Style |
|---|---|
| Primary action | `primary` bg, white text, `rounded-lg` |
| Danger / Logout | `accent` bg, white text, `rounded-lg` |
| Outline / Clear | transparent fill, `primary` border, `primary` text |
| Numpad key | square `w-12 h-12`, `primary` bg, white text |

## Table Styles
- Header row: `accent` bg, white text
- Data rows: `surface` bg
- Borders: `primary` color
- Selected/active date: `primary` bg highlight
