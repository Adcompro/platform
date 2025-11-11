# Theme Consistency Guide voor Progress Applicatie

## Overzicht
Dit document beschrijft de correcte styling patterns die gebruikt moeten worden in alle views.

## ✅ CORRECT - Gebruik Theme Variabelen

### Layout & Spacing
```blade
{{-- Background --}}
<div style="background-color: var(--theme-bg);">

{{-- Card Padding --}}
<div style="padding: var(--theme-card-padding);">

{{-- Border Radius --}}
<div style="border-radius: var(--theme-border-radius);">

{{-- Header Height --}}
<div style="height: var(--theme-header-height);">
```

### Typography
```blade
{{-- Base Font Size --}}
<span style="font-size: var(--theme-font-size);">

{{-- Larger Text --}}
<h2 style="font-size: calc(var(--theme-font-size) + 4px);">

{{-- Smaller Text --}}
<small style="font-size: calc(var(--theme-font-size) - 2px);">
```

### Colors
```blade
{{-- Primary Color --}}
<div style="color: var(--theme-primary);">
<div style="background-color: rgba(var(--theme-primary-rgb), 0.1);">

{{-- Text Colors --}}
<span style="color: var(--theme-text);">
<span style="color: var(--theme-text-muted);">

{{-- Success/Warning/Danger --}}
<span style="color: var(--theme-success);">
<span style="color: var(--theme-warning);">
<span style="color: var(--theme-danger);">
```

### Cards
```blade
{{-- Standard Card --}}
<div class="bg-white/60 backdrop-blur-sm border border-slate-200/60"
     style="border-radius: var(--theme-border-radius); overflow: hidden;">
    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text);">
            Card Title
        </h2>
    </div>
    <div style="padding: var(--theme-card-padding);">
        {{-- Content --}}
    </div>
</div>
```

### Buttons
```blade
{{-- Primary Button --}}
<button style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding);
               font-size: var(--theme-view-header-button-size);
               background-color: rgba(var(--theme-primary-rgb), 0.1);
               color: var(--theme-primary);
               border: none;
               border-radius: var(--theme-border-radius);
               cursor: pointer;">
    Button Text
</button>
```

## ❌ INCORRECT - Hardcoded Tailwind Classes

### Vermijd Deze Patterns
```blade
{{-- FOUT: Hardcoded padding --}}
<div class="p-3">  ❌

{{-- CORRECT: Theme variable --}}
<div style="padding: var(--theme-card-padding);">  ✅

{{-- FOUT: Hardcoded border radius --}}
<div class="rounded-lg">  ❌

{{-- CORRECT: Theme variable --}}
<div style="border-radius: var(--theme-border-radius);">  ✅

{{-- FOUT: Hardcoded font size --}}
<span class="text-sm">  ❌

{{-- CORRECT: Theme variable --}}
<span style="font-size: var(--theme-font-size);">  ✅
```

## 📋 Beschikbare Theme Variabelen

### Layout
- `--theme-bg` - Main background color
- `--theme-card-padding` - Standard card padding
- `--theme-border-radius` - Border radius voor cards/buttons
- `--theme-card-shadow` - Box shadow voor cards
- `--theme-header-height` - Height van sticky headers

### Typography
- `--theme-font-size` - Base font size (14px default)
- `--theme-view-header-title-size` - Page title size
- `--theme-view-header-description-size` - Page description size
- `--theme-view-header-button-size` - Header button font size

### Colors
- `--theme-primary` - Primary brand color
- `--theme-primary-rgb` - Primary color as RGB values
- `--theme-accent` - Accent color
- `--theme-accent-rgb` - Accent color as RGB
- `--theme-success` - Success color (green)
- `--theme-success-rgb` - Success as RGB
- `--theme-warning` - Warning color (orange)
- `--theme-warning-rgb` - Warning as RGB
- `--theme-danger` - Danger color (red)
- `--theme-danger-rgb` - Danger as RGB
- `--theme-text` - Primary text color
- `--theme-text-muted` - Muted/secondary text color

### Spacing
- `--theme-view-header-padding` - Padding voor view headers

## 🎯 Best Practices

1. **Gebruik ALTIJD theme variabelen** voor kleuren, spacing, en typography
2. **Tailwind classes alleen** voor layout utilities (flex, grid, gap)
3. **Inline styles** voor alle theme-gerelateerde styling
4. **Consistent gebruik** van backdrop-blur en transparantie voor moderne look
5. **Responsive design** via CSS Grid met theme variabelen

## 📝 Voorbeeld: Complete Card Component

```blade
<div class="bg-white/60 backdrop-blur-sm border border-slate-200/60"
     style="border-radius: var(--theme-border-radius); overflow: hidden;">
    {{-- Header --}}
    <div class="border-b"
         style="border-color: rgba(203, 213, 225, 0.3);
                padding: var(--theme-card-padding);
                display: flex;
                align-items: center;
                justify-content: space-between;">
        <h2 style="font-size: calc(var(--theme-font-size) + 4px);
                   font-weight: 600;
                   color: var(--theme-text);
                   margin: 0;">
            Card Title
        </h2>
        <button style="padding: 0.25rem 0.75rem;
                       font-size: calc(var(--theme-font-size) - 1px);
                       color: var(--theme-primary);
                       background: rgba(var(--theme-primary-rgb), 0.1);
                       border: none;
                       border-radius: var(--theme-border-radius);
                       cursor: pointer;">
            Action
        </button>
    </div>

    {{-- Body --}}
    <div style="padding: var(--theme-card-padding);">
        <p style="font-size: var(--theme-font-size);
                  color: var(--theme-text);
                  margin: 0;">
            Card content here
        </p>
    </div>
</div>
```

## 🔄 Migratie Checklist

Bij het updaten van views:

- [ ] Vervang `p-3`, `p-4`, `p-5` door `padding: var(--theme-card-padding)`
- [ ] Vervang `rounded-lg`, `rounded-xl` door `border-radius: var(--theme-border-radius)`
- [ ] Vervang `text-sm`, `text-xs` door `font-size: var(--theme-font-size)` of calculated
- [ ] Vervang hardcoded kleuren door theme color variabelen
- [ ] Gebruik RGB variabelen voor transparante backgrounds
- [ ] Behoud Tailwind voor layout (flex, grid, gap, etc.)
- [ ] Test in verschillende theme configuraties

## 📌 Belangrijk

De applicatie ondersteunt **dynamische theme configuratie**. Alle styling MOET theme variabelen gebruiken zodat gebruikers het thema kunnen aanpassen via de Settings zonder code wijzigingen.

**Admin kan aanpassen:**
- Font family (Inter, Roboto, Poppins, Open Sans)
- Font size (Small, Medium, Large)
- Primary/Accent colors
- Card padding
- Border radius
- Header height

**Daarom is het CRUCIAAL dat ALLE styling theme variabelen gebruikt!**
