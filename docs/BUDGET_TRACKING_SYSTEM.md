# 💰 Budget Tracking & Recurring Dashboard System - Complete Documentatie

**Versie**: 1.0
**Laatst bijgewerkt**: 03-11-2025
**Status**: Production Ready

---

## 📋 Inhoudsopgave

1. [Systeem Overzicht](#systeem-overzicht)
2. [Kern Concepten](#kern-concepten)
3. [Database Structuur](#database-structuur)
4. [Budget Berekeningen](#budget-berekeningen)
5. [Recurring Dashboard](#recurring-dashboard)
6. [Series Budget View](#series-budget-view)
7. [Projects Index Budget Display](#projects-index-budget-display)
8. [Code Locaties](#code-locaties)
9. [Business Rules](#business-rules)
10. [Gebruikers Handleiding](#gebruikers-handleiding)

---

## 📖 Systeem Overzicht

Het Budget Tracking systeem biedt complete financiële tracking voor projecten met:
- **Maandelijkse budgets** met rollover functionaliteit
- **Tijd tracking** (alleen billable uren tellen mee)
- **Additional costs** (in_fee vs additional)
- **Recurring project series** met geconsolideerde rapportage
- **Real-time budget status** met visual indicators

### Belangrijkste Features:
- ✅ Monthly fee tracking per project
- ✅ Budget rollover tussen maanden (optioneel)
- ✅ Non-billable uren worden uitgefilterd
- ✅ Recurring series consolidatie
- ✅ Year-over-year budget views
- ✅ Visual budget status (progress bars, kleur codes)

---

## 🧠 Kern Concepten

### 1. Monthly Fee (Maandelijks Budget)
Elk project kan een vast maandelijks budget hebben:
```php
projects.monthly_fee = €14,528.00
```

**Belangrijke regel**: Dit is het BASE budget ZONDER rollover.

### 2. Billable vs Non-Billable Uren
**KRITIEK**: Alleen `billable` uren tellen mee in budget berekeningen!

```sql
SELECT * FROM time_entries
WHERE is_billable = 'billable'  -- ALTIJD deze filter!
AND status = 'approved'
```

**Voorbeelden**:
- Client vergaderingen → Billable
- Interne meetings → Non-billable
- Development werk → Billable
- Training/opleiding → Non-billable

### 3. Budget Rollover
Ongebruikt budget kan doorgeschoven worden naar volgende maand:

**Voorbeeld**:
```
Januari:  Budget €10,000 - Used €8,000 = Rollover +€2,000
Februari: Budget €10,000 + Rollover €2,000 = €12,000 beschikbaar
```

**Belangrijke regel**: Rollover is **interne verschuiving**, NIET nieuw geld!
- Total year budget = Som van alle monthly fees (ZONDER rollover)
- Rollover verschuift beschikbaar budget tussen maanden

### 4. Recurring Project Series
Projecten die elke maand herhalen worden gegroepeerd:

```php
recurring_series_id = "Huawei retainer 2025"
↓
Projects:
- Huawei Retainer February 2025 (monthly_fee: €14,528)
- Huawei Retainer March 2025 (monthly_fee: €14,528)
- Huawei Retainer June 2025 (monthly_fee: €14,528)
- ...etc
```

**Budget consolidatie**: Alle projecten in een series worden samengevoegd voor totaal overzicht.

### 5. Budget Status Indicators

**Per Maand**:
- **Remaining** (groen): Budget - Used (als positief)
- **Exceeded** (rood): Used - Budget (als positief)
- **Altijd één van de twee**, nooit beide tegelijk!

**Percentage Kleuren**:
- < 75% gebruikt: Groen (on track)
- 75-90% gebruikt: Oranje (warning)
- > 90% gebruikt: Rood (overspent)

### 6. Rollover "Pending" Logica
Als er geen actief project is in een maand, blijft rollover behouden:

```
Maart:  Active project, rollover out: -€500 (overspent)
April:  Geen project → rollover blijft -€500
Mei:    Geen project → rollover blijft -€500
Juni:   Active project, rollover in: -€500 (wordt toegepast)
```

---

## 🗄️ Database Structuur

### Projects Table
```sql
projects
├── id (PK)
├── name
├── recurring_series_id (varchar) -- Groepeert recurring projecten
├── monthly_fee (decimal) -- BASE maandbudget
├── fee_rollover_enabled (boolean) -- 1 = rollover aan
├── default_hourly_rate (decimal) -- Voor cost berekeningen
├── start_date
├── end_date
└── ...
```

### Time Entries Table
```sql
time_entries
├── id (PK)
├── project_id (FK)
├── user_id (FK)
├── entry_date
├── hours (int)
├── minutes (int)
├── is_billable (enum: 'billable', 'non_billable') -- KRITIEK!
├── status (enum: 'draft', 'pending', 'approved', 'rejected')
├── hourly_rate_used (decimal) -- Override rate
└── ...
```

**BELANGRIJK**: Alleen entries met `is_billable = 'billable'` EN `status = 'approved'` tellen mee!

### Project Monthly Fees Table
```sql
project_monthly_fees
├── id (PK)
├── project_id (FK)
├── year (int)
├── month (int)
├── base_monthly_fee (decimal) -- BASE budget ZONDER rollover
├── rollover_from_previous (decimal) -- Kan positief of negatief zijn
├── total_available_fee (decimal) -- base + rollover
├── hours_worked (decimal)
├── hours_value (decimal) -- hours * rate (alleen billable!)
├── additional_costs_in_fee (decimal)
├── budget_used (decimal) -- hours_value + additional_costs
├── budget_remaining (decimal)
├── rollover_to_next (decimal) -- Voor volgende maand
└── ...
```

**Deze tabel wordt NIET gebruikt in de huidige implementatie!**
Berekeningen gebeuren real-time vanuit time_entries.

---

## 🧮 Budget Berekeningen

### 1. Billable Hours & Costs (KRITIEK!)

**Query Pattern**:
```php
$timeEntries = TimeEntry::where('project_id', $projectId)
    ->where('status', 'approved')
    ->where('is_billable', 'billable')  // ALTIJD DEZE FILTER!
    ->get();

$totalHours = 0;
$totalCosts = 0;

foreach ($timeEntries as $entry) {
    $entryHours = $entry->hours + ($entry->minutes / 60);
    $totalHours += $entryHours;

    $hourlyRate = $entry->hourly_rate_used
        ?? $project->default_hourly_rate
        ?? 165.00;

    $totalCosts += $entryHours * $hourlyRate;
}
```

**Resultaat**:
- `$totalHours` = Alleen billable uren
- `$totalCosts` = Alleen billable uren × rate

### 2. Monthly Budget Calculation

**Voor één maand**:
```php
$baseMonthlyBudget = $project->monthly_fee; // BASE budget
$rolloverIn = $previousMonthRollover; // Van vorige maand
$totalBudget = $baseMonthlyBudget + $rolloverIn;

$spent = $totalCosts; // Van billable hours
$remaining = max(0, $totalBudget - $spent);
$exceeded = max(0, $spent - $totalBudget);

$rolloverOut = $totalBudget - $spent; // Kan negatief zijn!
```

**Belangrijke punten**:
- `$remaining` en `$exceeded` zijn **mutually exclusive** (alleen één > 0)
- `$rolloverOut` kan positief (underspent) of negatief (overspent) zijn

### 3. Year Total Calculation

**KRITIEKE REGEL**: Gebruik BASE budgets, NIET budgets met rollover!

```php
$totalBaseBudget = 0;
$totalUsed = 0;

foreach ($months as $month) {
    $totalBaseBudget += $month['base_monthly_fee']; // ZONDER rollover!
    $totalUsed += $month['budget_used'];
}

$yearTotals = [
    'total_budget' => $totalBaseBudget,
    'total_used' => $totalUsed,
    'total_remaining' => max(0, $totalBaseBudget - $totalUsed),
    'total_exceeded' => max(0, $totalUsed - $totalBaseBudget),
];
```

**Waarom GEEN rollover in totalen?**
Rollover is interne verschuiving tussen maanden. Als je alle `rollover_in` bedragen optelt, tel je hetzelfde geld meerdere keren!

**Voorbeeld**:
```
Jan: Base €10k, Rollover Out €2k
Feb: Base €10k, Rollover In €2k (dit is hetzelfde geld!)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Year Total Budget = €20k (NIET €22k!)
```

### 4. Budget Percentage

```php
$budgetPercentage = 0;

if ($originalBudget > 0 && $budgetUsed > 0) {
    // GEEN min(100, ...) cap! Overspent moet >100% kunnen zijn
    $budgetPercentage = round(($budgetUsed / $originalBudget) * 100);
} else if ($budgetUsed > 0) {
    $budgetPercentage = 100; // Geen budget maar wel costs
}

// Kleur bepalen
if ($budgetPercentage > 90) {
    $color = '#ef4444'; // Rood
} elseif ($budgetPercentage > 75) {
    $color = '#f59e0b'; // Oranje
} else {
    $color = '#10b981'; // Groen
}
```

**Belangrijke punten**:
- Gebruik ORIGINEEL monthly_fee (niet total_available_fee met rollover)
- Geen percentage cap bij overspent (kan 175%, 231%, etc. zijn)
- Hardcoded hex kleuren (geen CSS variables vanwege caching issues)

---

## 📊 Recurring Dashboard

**URL**: `/recurring-dashboard`
**Controller**: `RecurringDashboardController.php`
**View**: `resources/views/recurring-dashboard/index.blade.php`

### Functionaliteit

Toont alle recurring project series in één overzicht met:
- **Maandelijkse cellen**: Budget / Spent / Variance (3 regels)
- **Totalen kolom**: Year totals met labels
- **Groepering**: Overspent vs Underspent vs No Budget

### Maand Cell Layout

```blade
{{-- 3-regels layout --}}
<div class="text-xs font-medium">
    €{{ number_format($monthData['base_budget'], 0) }}  <!-- ZONDER rollover -->
</div>
<div class="text-xs">
    €{{ number_format($monthData['spent'], 0) }}
</div>
<div class="text-sm font-bold">
    €{{ number_format($monthData['month_variance'], 0) }}  <!-- ZONDER rollover -->
</div>
```

### Totalen Kolom

```blade
<div class="text-xs font-medium">
    <span style="opacity: 0.6;">Budget:</span> €{{ number_format($yearTotals['budget'], 0) }}
</div>
<div class="text-xs">
    <span style="opacity: 0.6;">Used:</span> €{{ number_format($yearTotals['spent'], 0) }}
</div>
<div class="text-sm font-bold">
    <span style="opacity: 0.7;">Variance:</span> €{{ number_format($yearTotals['variance'], 0) }}
</div>
```

### Kleuren Code

- **Underspent** (groen): Variance > 10%
- **On Budget** (blauw): Variance tussen -10% en +10%
- **Overspent** (rood): Variance < -10%
- **No Data** (grijs): Geen tijdregistraties

---

## 📈 Series Budget View

**URL**: `/projects/{project}/series-budget?year=2025`
**Controller**: `ProjectController::seriesBudget()`
**View**: `resources/views/projects/series-budget.blade.php`

### Functionaliteit

Gedetailleerd jaar-overzicht voor één recurring series met:
- **Alle 12 maanden** (ook maanden zonder actief project)
- **Per-maand budgets** (respecteert verschillen per maand)
- **Rollover tracking** (toont in/out per maand)
- **Year totals** (zonder rollover dubbel tellen)

### Rollover "Pending" Logica

```php
if ($activeProjectThisMonth) {
    // Er is een actief project deze maand
    $monthlyBudget = $activeProjectThisMonth->monthly_fee ?? 0;
    $rolloverIn = $activeProjectThisMonth->fee_rollover_enabled ? $previousRollover : 0;
    $totalBudget = $monthlyBudget + $rolloverIn;

    // Bereken nieuwe rollover
    $rolloverOut = $activeProjectThisMonth->fee_rollover_enabled
        ? ($totalBudget - $totalCosts)
        : 0;
    $previousRollover = $rolloverOut;
} else {
    // Geen actief project deze maand
    $monthlyBudget = 0;
    $rolloverIn = 0; // Toon niet in deze maand
    $totalBudget = 0;

    // BELANGRIJK: Rollover blijft behouden voor volgende maand!
    $rolloverOut = $previousRollover;
    // $previousRollover blijft ongewijzigd
}
```

### Visual Consistency

**Statistics Cards**:
- Remaining card: Groen als > 0, grijs als 0
- Exceeded card: Rood als > 0, grijs als 0
- Altijd één prominent, de andere grijs

**Table Row**:
- Remaining kolom: Bedrag als > 0, anders "-"
- Exceeded kolom: Bedrag als > 0, anders "-"

---

## 💻 Projects Index Budget Display

**URL**: `/projects`
**Controller**: `ProjectController::index()`
**View**: `resources/views/projects/index.blade.php`

### Budget Used Column

Toont budget gebruik met progress bar en percentage:

```blade
@php
    if ($budgetPercentage > 90) {
        $progressColor = '#ef4444'; // red
        $textColor = '#ef4444';
    } elseif ($budgetPercentage > 75) {
        $progressColor = '#f59e0b'; // orange
        $textColor = '#f59e0b';
    } else {
        $progressColor = '#10b981'; // green
        $textColor = '#10b981';
    }
@endphp

<div class="w-24 rounded-full h-1.5 bg-gray-200">
    <div class="h-1.5 rounded-full"
         style="width: {{ min(100, $budgetPercentage) }}%;
                background-color: {{ $progressColor }};"></div>
</div>
<span style="color: {{ $textColor }};">{{ $budgetPercentage }}%</span>
```

### Year Budget Column (Recurring Series)

```blade
@if($project->recurring_series_id)
    <a href="{{ route('projects.series-budget', $project->id) }}"
       class="inline-flex items-center px-3 py-1.5 rounded-lg">
        <i class="fas fa-chart-line"></i>
        View Totals
    </a>
@else
    <span>-</span>
@endif
```

### Sorteerbare Kolommen

Alle kolommen klikbaar voor sortering:
- name, status, start_date, end_date
- monthly_fee, billing_frequency, created_at
- **budget_used** (in-memory sorting na berekeningen)

**Helper Function**:
```php
function sortableHeader($label, $field, $currentSort, $currentDirection) {
    $newDirection = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $field, 'direction' => $newDirection]);
    $icon = $currentSort === $field ? ($currentDirection === 'asc' ? '↑' : '↓') : '';
    return '<a href="' . $url . '">' . $label . ' ' . $icon . '</a>';
}
```

---

## 📁 Code Locaties

### Controllers

**ProjectController.php** (`app/Http/Controllers/ProjectController.php`)
- `index()` - Lines 94-275: Projects lijst met budget berekeningen
- `seriesBudget()` - Lines 3700-3890: Series budget year view

**RecurringDashboardController.php** (`app/Http/Controllers/RecurringDashboardController.php`)
- `index()` - Lines 18-283: Recurring dashboard hoofdpagina

### Models

**Project.php** (`app/Models/Project.php`)
- Relationships: customer, users, companies, milestones, tasks
- Attributes: monthly_fee, fee_rollover_enabled, default_hourly_rate

**TimeEntry.php** (`app/Models/TimeEntry.php`)
- Relationships: user, project, approver
- Critical fields: is_billable, status, hourly_rate_used

### Views

**Recurring Dashboard**:
- `resources/views/recurring-dashboard/index.blade.php` (428 lines)

**Series Budget**:
- `resources/views/projects/series-budget.blade.php` (378 lines)

**Projects Index**:
- `resources/views/projects/index.blade.php` (480+ lines)

### Routes

```php
// routes/web.php
Route::get('/recurring-dashboard', [RecurringDashboardController::class, 'index'])
    ->name('recurring-dashboard');

Route::get('/projects/{project}/series-budget', [ProjectController::class, 'seriesBudget'])
    ->name('projects.series-budget');

Route::get('/projects', [ProjectController::class, 'index'])
    ->name('projects.index');
```

---

## 📜 Business Rules

### 1. Billable Hours Filter (KRITIEK!)

**REGEL**: Alleen `is_billable = 'billable'` EN `status = 'approved'` tellen mee in budget berekeningen.

**Implementatie locaties**:
- ProjectController::index() - Line 176
- ProjectController::seriesBudget() - Line 3785

**Waarom**: Non-billable uren zijn interne tijd die niet aan klant gefactureerd kan worden.

### 2. Rollover in Year Totals

**REGEL**: Rollover mag NIET opgeteld worden in year totals.

**Rationale**: Rollover verschuift budget tussen maanden maar voegt geen nieuw geld toe aan het totale jaarbudget.

**Implementatie**: ProjectController::seriesBudget() - Lines 3865-3880

### 3. Remaining vs Exceeded Exclusivity

**REGEL**: Altijd OF remaining OF exceeded, nooit beide tegelijk prominent.

**Implementatie**:
```php
$remaining = max(0, $totalBudget - $totalCosts);
$exceeded = max(0, $totalCosts - $totalBudget);
```

Als `$remaining > 0` dan `$exceeded = 0` en vice versa.

### 4. Per-Month Budget Variatie

**REGEL**: Respecteer verschillende monthly_fee per maand in recurring series.

**Voorbeeld**:
```
Feb-Aug 2025: €14,528/maand
Sep-Oct 2025: €9,775.50/maand (nieuw contract)
```

**Implementatie**: Gebruik budget van specifiek actief project per maand.

### 5. Percentage Display

**REGEL**: Geen percentage cap bij overspent. Toon echte waarden (175%, 231%).

**Waarom**: Overspent projecten moeten exact zichtbaar zijn voor management.

**Implementatie**: Verwijder alle `min(100, $percentage)` caps.

### 6. Authorization

**REGEL**: Super_admin en admin zien alles, anderen alleen eigen company.

```php
if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
    $query->where('company_id', Auth::user()->company_id);
}
```

---

## 👥 Gebruikers Handleiding

### Voor Project Managers

#### Recurring Dashboard Bekijken

1. Navigeer naar **Dashboard** → **Recurring Dashboard**
2. Selecteer jaar (dropdown rechtsboven)
3. Bekijk overzicht:
   - **Groene sectie**: Underspent projecten (gaat goed)
   - **Rode sectie**: Overspent projecten (aandacht nodig!)
   - **Grijze sectie**: Tracking only (geen budget)

**Maand cells lezen**:
```
€14,528  ← Budget deze maand (zonder rollover)
€23,401  ← Werkelijk gebruikt
-€8,873  ← Overspent (rood)
```

**Totalen kolom**:
```
Budget:   €92,191  ← Totaal jaarbudget
Used:     €106,088 ← Totaal gebruikt
Variance: -€13,897 ← Totaal overspent
```

#### Series Budget Detail Bekijken

1. In Projects lijst, klik **"View Totals"** bij recurring project
2. Of: Recurring Dashboard → klik op project naam
3. Bekijk jaar overzicht met:
   - Alle 12 maanden (ook lege maanden)
   - Rollover tracking
   - Statistics cards bovenaan

**Rollover lezen**:
- **Groen (+€2,500)**: Budget over, gaat naar volgende maand
- **Rood (-€3,000)**: Overspent, tekort gaat naar volgende maand

#### Budget Status Actie Nemen

**Groen (< 75%)**:
- ✅ On track, geen actie nodig

**Oranje (75-90%)**:
- ⚠️ Let op, houd in de gaten
- Check of remaining werk binnen budget past

**Rood (> 90%)**:
- 🚨 Urgent: Budget bijna op of overschreden
- Actie opties:
  1. Scope verkleinen
  2. Extra budget aanvragen bij klant
  3. Non-billable werk beperken

### Voor Finance/Admin

#### Budget Refresh

Recurring Dashboard heeft een **"Refresh Data"** knop die:
- `recurring:update-monthly-fees` command draait
- Budget tracking data herberekent
- Kan lang duren (5+ minuten voor grote datasets)

#### Non-Billable Uren Controleren

```sql
-- Check non-billable uren per project
SELECT
    p.name,
    SUM(CASE WHEN te.is_billable = 'billable' THEN te.hours + te.minutes/60 ELSE 0 END) as billable_h,
    SUM(CASE WHEN te.is_billable = 'non_billable' THEN te.hours + te.minutes/60 ELSE 0 END) as non_billable_h
FROM projects p
LEFT JOIN time_entries te ON te.project_id = p.id AND te.status = 'approved'
WHERE p.recurring_series_id IS NOT NULL
GROUP BY p.id, p.name
HAVING non_billable_h > 0;
```

#### Budget Reports Exporteren

1. Recurring Dashboard → Screenshot (browser)
2. Of Series Budget → Print page → Save as PDF
3. Of: Custom export feature (toekomstig)

### Voor Developers

#### Time Entry Registreren

**BELANGRIJK**: Altijd juiste `is_billable` status kiezen!

**Billable uren**:
- Client meetings
- Development werk
- Code reviews voor client
- Bug fixes (client issues)

**Non-billable uren**:
- Internal meetings
- Training/learning
- Bug fixes (internal issues)
- Admin/management tijd

**Waarom**: Non-billable uren tellen NIET mee in budget, dus foutieve marking geeft verkeerd budget beeld!

---

## 🔍 Troubleshooting

### Probleem: Budget percentages kloppen niet

**Check**:
1. Zijn er non-billable uren die per ongeluk billable gemarkeerd zijn?
2. Is `default_hourly_rate` correct ingesteld op project?
3. Zijn alle time entries `approved`?

**SQL Check**:
```sql
SELECT
    p.name,
    p.monthly_fee,
    COUNT(te.id) as entries,
    SUM(te.hours + te.minutes/60) as total_hours,
    SUM(CASE WHEN te.is_billable = 'billable' THEN 1 ELSE 0 END) as billable_count
FROM projects p
LEFT JOIN time_entries te ON te.project_id = p.id AND te.status = 'approved'
WHERE p.id = 372
GROUP BY p.id;
```

### Probleem: Year totals kloppen niet met maandtotalen

**Oorzaak**: Waarschijnlijk rollover dubbel geteld.

**Check**: Year total moet zijn:
```
Sum of base monthly fees (ZONDER rollover)
```

**Niet**:
```
Sum of total_budget (MET rollover) ← FOUT!
```

### Probleem: Series budget toont niet alle maanden

**Check**:
1. Is `$startMonth = 1` en `$endMonth = 12`?
2. Loop gaat door alle 12 maanden ongeacht actieve projecten?

**Code verificatie**: ProjectController::seriesBudget() lines 3730-3732

### Probleem: Progress bars hebben geen kleur

**Oorzaak**: CSS variables laden niet correct.

**Oplossing**: Gebruik hardcoded hex kleuren:
```php
$progressColor = '#ef4444'; // NIET var(--theme-danger)
```

---

## 🚀 Toekomstige Uitbreidingen

### Geplande Features

1. **Excel Export**
   - Export recurring dashboard naar Excel
   - Series budget detail export
   - Custom date ranges

2. **Email Alerts**
   - Automatische alerts bij > 90% budget gebruikt
   - Weekly budget status reports
   - Overspent notifications

3. **Budget Forecasting**
   - Predictive analytics op basis van historical data
   - "Budget depleted by" date voorspelling
   - Velocity tracking (budget burn rate)

4. **Multi-Currency Support**
   - USD, EUR, GBP rates
   - Automatic conversion
   - Historical exchange rates

5. **Mobile App**
   - Budget dashboard in mobile app
   - Quick time entry met budget feedback
   - Push notifications voor budget alerts

---

## 📞 Support & Contact

**Technical Owner**: Development Team
**Business Owner**: Finance Department
**Documentation**: `/docs/BUDGET_TRACKING_SYSTEM.md`
**Code Location**: `app/Http/Controllers/ProjectController.php`, `RecurringDashboardController.php`

**Bij vragen of issues**:
1. Check deze documentatie
2. Check CLAUDE.md voor recent updates
3. Contact development team

---

**Laatste update**: 03-11-2025
**Versie**: 1.0 (Production Ready)
**Status**: ✅ Volledig getest en gevalideerd
