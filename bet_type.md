# Bet Type Analysis — Sample Image

Source: `Sample/sample_bet_type.jpg` (handwritten notebook)

---

## Overview

The system has **4 bet types**, each with a different number selection method and payout structure.

---

## Type 1 — Normal Bet (មូលគ្រាល)

**Description:** Staff bets on a single specific number.

### Input

| Field    | Type   | Description                      |
|----------|--------|----------------------------------|
| `number` | string | The number to bet on (2 or 3 digits) |

### Rules

- 2-digit numbers (e.g. `79`) and 3-digit numbers (e.g. `086`) are both valid
- Amount is entered per session
- Up to 10 entries per ticket
- Number range: `011` – `911`

### Example

```
number = 086   →  bet on 086
number = 79    →  bet on 79
```

---

## Type 2 — Head Bet (ជំហានៗ)

**Description:** Staff bets on a **consecutive run of 10 numbers** by entering a start and end number.

### Inputs

| Field          | Type   | Description                     |
|----------------|--------|---------------------------------|
| `start_number` | string | First number in the run         |
| `end_number`   | string | Last number in the run          |

### Rules

- Block size is always **10 consecutive numbers**
- `end_number` = `start_number` + 9
- Amount formula: `100 ពាក់ × 500`

### Example

```
start_number = 110,  end_number = 119   →  covers 110, 111 … 119
start_number = 780,  end_number = 789   →  covers 780, 781 … 789
```

---

## Type 3 — Tail Bet (ចំនួន)

**Description:** Staff bets on a **block of 100 consecutive numbers** (standard range: 0 – 10,000).

### Inputs

| Field          | Type   | Description                          |
|----------------|--------|--------------------------------------|
| `start_number` | string | First number in the block (e.g. `000`) |
| `end_number`   | string | Last number in the block (e.g. `099`)  |

### Rules

- Block size is always **100 consecutive numbers**
- `end_number` = `start_number` + 99
- Bet amount range: **0 – 10,000**
- Payout rate: `100 × 800`

### Example

```
start_number = 000,  end_number = 099   →  covers 000 … 099  (rate: 100 × 800)
```

---

## Type 4 — Tail Bet 2 (ចំនួន / ប្រការ2)

**Description:** Staff bets on a **block of 100 consecutive numbers** (premium range: 5 – 10,000+).

### Inputs

| Field          | Type   | Description                          |
|----------------|--------|--------------------------------------|
| `start_number` | string | First number in the block (e.g. `500`) |
| `end_number`   | string | Last number in the block (e.g. `599`)  |

### Rules

- Block size is always **100 consecutive numbers**
- `end_number` = `start_number` + 99
- Bet amount range: **5 – 10,000+**
- Payout rate: `100 × 800`

### Example

```
start_number = 500,  end_number = 599   →  covers 500 … 599  (rate: 100 × 800)
```

---

## Summary Comparison

| Property         | Type 1 — Normal Bet   | Type 2 — Head Bet      | Type 3 — Tail Bet       | Type 4 — Tail Bet 2     |
|------------------|-----------------------|------------------------|-------------------------|-------------------------|
| Khmer name       | មូលគ្រាល              | ជំហានៗ                 | ចំនួន                   | ចំនួន / ប្រការ2          |
| Inputs           | `number`              | `start_number` `end_number` | `start_number` `end_number` | `start_number` `end_number` |
| Numbers per bet  | 1                     | 10 (consecutive)       | 100 (consecutive)       | 100 (consecutive)       |
| Digit length     | 2 or 3 digits         | 3 digits               | 3 digits (000–999)      | 3 digits (000–999)      |
| Amount range     | Per session           | 100 × 500              | 0 – 10,000              | 5 – 10,000+             |
| Payout rate      | —                     | 100 × 500              | 100 × 800               | 100 × 800               |

---

## Open Questions

- The exact Khmer labels for the session columns in Type 1 are partially illegible — likely **ព្រឹក** (morning) and **ល្ងាច** (evening).
- The `× [multiplier]` factor in Type 2 is cut off in the image — may refer to a win multiplier configured per category.
- Whether Type 3 and Type 4 require new category definitions needs confirmation.
