# Staff Role — Figma Screens

**Figma Page:** `Staff`  
**File:** https://www.figma.com/design/w1QlWIADCGWRd1m1ZcVgHU/LOTT0999

| Frame | Description |
|---|---|
| `Staff / 01-Login` | Same login screen |
| `Staff / 02-Home` | Bet entry — scoped to own bets (user_id filter) |
| `Staff / 03-Record` | Own records only — no other staff visible |
| `Staff / 04-Result` | Read-only — no input, no print button |
| `Staff / 05-Setting` | Printer settings only — Commission row **hidden** |
| `Staff / 06-Account` | Own sales only — no user management, no Add button |
| ~~`Staff / 07-Report`~~ | **403 Forbidden — screen not included** |

## Key Differences from Master
- **Record:** only own bets (`where user_id = auth()->id()`)
- **Result:** print button also hidden
- **Account:** no Add Staff / user management at all
- **Report:** completely inaccessible (middleware 403)
