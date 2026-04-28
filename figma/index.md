# Figma Design Files — HT ភ្នាក់

**Figma File:** https://www.figma.com/design/w1QlWIADCGWRd1m1ZcVgHU/LOTT0999?node-id=0-1&p=f&t=1yszvxOrw8x4RqDU-0

---

## Folder Structure

```
figma/
├── admin/    ← Admin role screens (full access — all 7 screens)
├── master/   ← Master role screens (7 screens, restricted views)
└── staff/    ← Staff role screens (6 screens, no Report)
```

## Figma Page Organization (by Role)

| Figma Page | Role | Screens Included |
|---|---|---|
| `Admin` | admin | Login, Home, Record, Result, Setting, Account, Report |
| `Master` | master | Login, Home, Record, Result, Setting, Account, Report (own staff only) |
| `Staff` | staff | Login, Home, Record, Result, Setting, Account (no Report — 403) |

---

## Screen Reference per Role

### Admin — Full Access
| Screen | Route | Notes |
|---|---|---|
| Login | `/login` | Standard login |
| Home | `/home` | All bet sessions |
| Record | `/record` | All staff records |
| Result | `/result` | Can **enter & edit** results |
| Setting | `/setting` | Commission setting **visible** |
| Account | `/account` | Total sales across all staff + user management |
| Report | `/report` | All staff, no filter restriction |

### Master — Own Staff Scope
| Screen | Route | Notes |
|---|---|---|
| Login | `/login` | Standard login |
| Home | `/home` | All bet sessions |
| Record | `/record` | Own staff records only |
| Result | `/result` | Read-only (cannot edit results) |
| Setting | `/setting` | Commission setting **hidden** |
| Account | `/account` | Own staff sales + can create staff |
| Report | `/report` | Own staff only in dropdown |

### Staff — Personal Scope Only
| Screen | Route | Notes |
|---|---|---|
| Login | `/login` | Standard login |
| Home | `/home` | All bet sessions |
| Record | `/record` | Own records only |
| Result | `/result` | Read-only |
| Setting | `/setting` | Commission setting **hidden** |
| Account | `/account` | Own sales only, no user management |
| Report | — | **403 Forbidden** — not accessible |

---

## Color Theme

| Token | Color | Hex |
|---|---|---|
| Primary (nav/buttons) | Gold | `#D4A017` |
| Accent (headers/danger) | Crimson | `#DC143C` |
| Surface (table rows) | Warm Cream | `#FFF8DC` |
| Background | White | `#FFFFFF` |
