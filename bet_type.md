# Bet Type Specification

## Overview

5 bet types, each targeting a specific digit position of a 2- or 3-digit number.

---

## Type 1 — Normal Bet

**Display:** `Normal`

Single specific number, 2 or 3 digits.

**Input:** one number field

**Example:**
```
number = 123   →  bet on 123
number = 79    →  bet on 79
```

---

## Type 2 — Head Bet

**Display:** `<`

The **first digit** is swept from the input value up to **9**; all remaining digits stay fixed.

**Input:** one number field (start); end is auto-calculated but **editable** — user can set a custom end ≤ auto-max

**Auto-end formula:**
- 2-digit `XY` → end = `9Y`  (`90 + (start % 10)`)
- 3-digit `XYZ` → end = `9YZ`  (`900 + (start % 100)`)

**Step:** 10 for 2-digit, 100 for 3-digit

**Example:**
```
input: 11   →  end: 91 (default)   covers 11, 21, 31, 41, 51, 61, 71, 81, 91
         →  end: 51 (custom)    covers 11, 21, 31, 41, 51
input: 44   →  end: 94 (default)
         →  end: 84 (custom)    covers 44, 54, 64, 74, 84
input: 123  →  end: 923 (default)  covers 123, 223, 323, 423, 523, 623, 723, 823, 923
```

---

## Type 3 — Middle Bet

**Display:** `=`

The **middle digit** is swept from the input value up to **9**; first and last digits stay fixed. 3-digit numbers only.

**Input:** one number field (start); end is auto-calculated but **editable** — user can set a custom end ≤ auto-max

**Auto-end formula:**
- 3-digit `XYZ` → end = `X9Z`  (`floor(start/100)*100 + 90 + (start%10)`)

**Step:** 10

**Example:**
```
input: 123  →  end: 193  covers 123, 133, 143, 153, 163, 173, 183, 193
```

---

## Type 4 — Tail Bet

**Display:** `>`

The **last digit** is swept from the input value up to **9**; all preceding digits stay fixed.

**Input:** one number field (start); end is auto-calculated but **editable** — user can set a custom end ≤ auto-max

**Auto-end formula:**
- 2-digit `XY` → end = `X9`  (`floor(start/10)*10 + 9`)
- 3-digit `XYZ` → end = `XY9`  (`floor(start/10)*10 + 9`)

**Step:** 1

**Example:**
```
input: 11   →  end: 19   covers 11, 12, 13, 14, 15, 16, 17, 18, 19
input: 123  →  end: 129  covers 123, 124, 125, 126, 127, 128, 129
```

---

## Type 5 — Multiple Bet

**Display:** `X`

All **unique permutations** of the 3 input digits. The multiplier badge shows the count.

**Input:** one 3-digit number field; multiplier badge is auto-calculated (readonly)

**Count formula (unique permutations of 3 digits):**
```
count = 6 / (freq_d1! × freq_d2! × freq_d3!)
```

| Input | Digit frequencies | Count | Numbers |
|-------|-------------------|-------|---------|
| `112` | {1:2, 2:1}       | 3     | 112, 121, 211 |
| `123` | {1:1, 2:1, 3:1}  | 6     | 123, 132, 213, 231, 312, 321 |
| `111` | {1:3}            | 1     | 111 |

**Example:**
```
input: 112  →  X 3   (bets on 112, 121, 211)
input: 123  →  X 6   (bets on 123, 132, 213, 231, 312, 321)
```

---

## Summary

| Type | Display | Digit changed | Step | Input fields |
|------|---------|--------------|------|--------------|
| 1 — Normal | `Normal` | — | — | 1 (number) |
| 2 — Head   | `<`      | First → 9   | 10 / 100 | 1 + auto-end |
| 3 — Middle | `=`      | Middle → 9  | 10       | 1 + auto-end |
| 4 — Tail   | `>`      | Last → 9    | 1        | 1 + auto-end |
| 5 — Multiple | `X`   | All perms   | —        | 1 + count badge |
