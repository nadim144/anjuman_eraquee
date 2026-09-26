# Design System & UI Specifications

## Anjuman Eraquee INDIA — Visual Identity, Typography & Component Library

| Metadata | Details |
| :--- | :--- |
| **Document Version** | 1.0.0 |
| **Date** | September 20, 2026 |
| **Status** | Active / Design Standard |
| **Brand Identity** | Anjuman Eraquee India |
| **Target Viewports** | Mobile (320px - 480px), Tablet (768px - 1024px), Desktop (1200px+) |

---

## 1. Design Principles

- **Dignified & Community-Centered**: The aesthetic balances cultural prestige, Islamic community values, and modern digital clean-tech interfaces.
- **High Visual Contrast & Readability**: High-contrast text against crisp neutral backgrounds to accommodate all demographic ages, including senior community elders.
- **Mobile-First Accessibility**: Clean touch targets (minimum 44x44px), thumb-friendly button pills, and fluid form controls for smartphone simulators and handhelds.
- **Consistent Visual Hierarchy**: Cohesive color tokens, badge statuses, and button variants shared across both the Public Website, Member Dashboard, and Admin Portal.

---

## 2. Color Palette & Token System

### Brand Identity Colors
| Token Name | HEX Value | RGB | CSS Variable / Class | Purpose |
| :--- | :---: | :---: | :--- | :--- |
| **Primary Green** | `#009146` | `rgb(0, 145, 70)` | `--primary-color` | Brand identity, primary CTAs, active links, header borders |
| **Primary Dark** | `#006b33` | `rgb(0, 107, 51)` | `--primary-dark` | Hover states, prominent headers, card accents |
| **Secondary Gold** | `#e5ae49` | `rgb(229, 174, 73)` | `--secondary-color` | Highlights, sub-headers, certificates, star badges |
| **Highlight Blue** | `#38bdf8` | `rgb(56, 189, 248)` | `--accent-blue` | Slider caption accents, interactive info badges |

### Neutral & Surface Colors
| Token Name | HEX Value | RGB | Purpose |
| :--- | :---: | :---: | :--- |
| **Background Neutral** | `#f8fafc` | `rgb(248, 250, 252)` | Body background, dashboard content canvas |
| **Card Surface** | `#ffffff` | `rgb(255, 255, 255)` | Modals, table containers, form cards |
| **Border Neutral** | `#e2e8f0` | `rgb(226, 232, 240)` | Form input borders, table row dividers, card borders |
| **Dark Slate (Sidebar)** | `#0f172a` | `rgb(15, 23, 42)` | Admin sidebar background, footer backgrounds |
| **Text Primary** | `#1e293b` | `rgb(30, 41, 59)` | Main body copy, headings, table contents |
| **Text Muted** | `#64748b` | `rgb(100, 116, 139)` | Subtitles, input help text, metadata timestamps |

### Feedback & Status Tokens
| Token Name | Background | Text | Border | Usage |
| :--- | :---: | :---: | :---: | :--- |
| **Success** | `#dcfce7` | `#15803d` | `#86efac` | Active member badge, success alerts, confirmed actions |
| **Warning** | `#fef3c7` | `#92400e` | `#fcd34d` | Reset requested, temp password notice, pending review |
| **Danger / Error** | `#fee2e2` | `#b91c1c` | `#fca5a5` | Delete action, error notifications, suspended status |
| **Info / Role** | `#e0f2fe` | `#0369a1` | `#bae6fd` | Standard Admin badge, details button, informational alert |
| **Super Admin** | `#fef3c7` | `#b45309` | `#fde68a` | Exclusive 👑 Super Admin royal badge |

---

## 3. Typography Hierarchy

The typographic system relies on clean, widely-available sans-serif fonts: **'Open Sans'**, **'Roboto'**, and fallback `system-ui`.

| Element | Font Size | Line Height | Font Weight | Letter Spacing | Purpose |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Display H1** | `28px - 32px` | `1.25` | `700 (Bold)` | `-0.02em` | Main page titles, certificate headings |
| **Section H2** | `22px - 24px` | `1.3` | `600 (Semi-bold)`| `normal` | Card titles, dashboard module headers |
| **Subsection H3**| `18px - 20px` | `1.4` | `600 (Semi-bold)`| `normal` | Modal headers, table titles |
| **Body Regular** | `14px - 15px` | `1.5` | `400 (Regular)` | `normal` | Paragraphs, member details, descriptions |
| **Form Label** | `12px - 13px` | `1.2` | `700 (Bold)` | `0.02em` | Input field labels, table headers |
| **Micro / Caption**| `11px - 12px` | `1.2` | `500 (Medium)` | `0.04em` | Metadata timestamps, father name sub-tags |

---

## 4. UI Component Library

### A. Action Buttons
- **Primary CTA (`.btn-primary`)**:
  - Background: `#009146`, Text: `#ffffff`, Padding: `10px 24px`, Border-radius: `6px`.
  - Hover: `#006b33`, Box-shadow: `0 4px 6px -1px rgba(0, 145, 70, 0.2)`.
- **Secondary Action (`.btn-secondary`)**:
  - Background: `#f1f5f9`, Text: `#475569`, Padding: `8px 16px`, Border: `1px solid #cbd5e1`.
- **Mobile Header Pill Buttons (`.btn-mobile-pill`)**:
  - Unified inline-flex pill buttons (`Home`, `Member Dashboard`, `Logout`) with consistent padding `6px 14px`, rounded `20px` radius, preventing awkward line wrapping on 360px wide screens.
- **Admin Row Action Badges (`.btn-action`)**:
  - Details: `#e0f2fe` / `#0369a1`
  - Edit: `#fef3c7` / `#b45309`
  - Temp Pwd: `#dcfce7` / `#15803d`
  - Make Admin: `#ede9fe` / `#6d28d9`
  - Revoke Admin: `#fee2e2` / `#dc2626`
  - Delete: `#fee2e2` / `#b91c1c`

### B. Form Controls
- **Input Fields & Selects**:
  - Border: `1px solid #e2e8f0`, Radius: `6px`, Height: `42px`, Padding: `8px 12px`.
  - Focus state: Border `#009146`, Glow `0 0 0 3px rgba(0, 145, 70, 0.15)`.
- **Password Input with Eye Toggle**:
  - Interactive trailing icon (`.fa-eye` / `.fa-eye-slash`) for show/hide password visibility.
- **Avatar Upload Preview**:
  - Circular avatar container with border `3px solid #009146`, fallback vector silhouette (`images/dummy-avatar.svg`), and instant live JavaScript file preview.

### C. Modals & Overlays
- **Modal Overlay**: Dark slate backdrop blur (`rgba(15, 23, 42, 0.6)` with `backdrop-filter: blur(2px)`).
- **Modal Box**: Clean white card, border-radius `8px`, top accent bar `4px solid #009146`, responsive max-width (`650px` for Member Dossier, `450px` for Temp Password).

---

## 5. Responsive Design Breakpoints

```
[ Mobile Extra-Small ]  < 480px   -> Single-column stacked forms, mobile pill navigation
[ Mobile / Tablet ]     480-768px  -> Fluid grid, collapsible sidebar navigation
[ Tablet Landscape ]    768-1024px -> 2-column cards, full table with horizontal scrolling
[ Desktop Standard ]    1024-1440px-> Fixed 260px admin sidebar, multi-column dashboard grid
[ Wide Display ]        > 1440px   -> Centered max-width container (1200px / 1320px)
```

