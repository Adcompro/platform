# 🚀 Optimale Laravel 12 Coding Prompt - COMPLETE VERSIE v5.10

## 🔄 TEAMLEADER FOCUS IMPORT SYSTEEM - COMPLETE DOCUMENTATIE (21-10-2025)

### 📋 Overzicht
Het Teamleader import systeem importeert data van Teamleader Focus CRM naar de Progress applicatie via OAuth2 API integratie.

**Wat wordt geïmporteerd:**
- **Companies** (Teamleader) → **Customers** (Progress)
- **Contacts** (Teamleader) → **Users** (Progress)

**Belangrijke principes:**
- ✅ `company_id` blijft **NULL** voor handmatige toewijzing
- ✅ **GEEN email notificaties** bij users import
- ✅ Timeout: **600 seconden** (10 minuten) voor grote imports
- ✅ Twee import methodes: "Import All (Quick)" en "Select & Import"

---

### 📁 Bestandsstructuur

```
app/Services/TeamleaderImportService.php
├── importCompanies()                      # Bulk import alle companies
├── importSelectedCompanies($companyIds)   # Selectieve company import
├── importUsers()                          # Bulk import alle users
└── importSelectedUsers($userIds)          # Selectieve user import

app/Http/Controllers/TeamleaderController.php
├── selectCompanies()     # Company selectie interface
├── importCompanies()     # Company import handler
├── selectUsers()         # User selectie interface
└── importUsers()         # User import handler

resources/views/teamleader/
├── index.blade.php              # Dashboard met import knoppen
├── select-companies.blade.php   # Company selectie interface
└── select-users.blade.php       # User selectie interface met filter

routes/web.php (vanaf regel ~593)
├── GET  /teamleader/select/companies  → selectCompanies()
├── GET  /teamleader/select/users      → selectUsers()
├── POST /teamleader/import/companies  → importCompanies()
└── POST /teamleader/import/users      → importUsers()
```

---

### 🏢 COMPANIES IMPORT (Teamleader → Progress Customers)

#### Kritieke Address Mapping (BELANGRIJK!)

**Teamleader API Structuur:**
```json
{
  "addresses": [
    {
      "type": "primary",
      "address": {              // ← EXTRA NESTING LEVEL!
        "line_1": "Straatnaam 123",
        "line_2": "Toevoeging",
        "postal_code": "1234AB",
        "city": "Amsterdam",
        "country": "NL",
        "area_level_two": null
      }
    }
  ]
}
```

**❌ FOUT - Direct naar addresses[0]:**
```php
$address = $tlCompany['addresses'][0];
$street = $address['line_1'];  // Dit werkt NIET!
```

**✅ CORRECT - Met extra nesting:**
```php
$addresses = $tlCompany['addresses'] ?? [];
if (!empty($addresses) && is_array($addresses)) {
    $addressWrapper = $addresses[0];

    if ($addressWrapper && isset($addressWrapper['address'])) {
        $address = $addressWrapper['address'];  // Extra 'address' key!

        $street = $address['line_1'] ?? null;
        $addition = $address['line_2'] ?? null;
        $zipCode = $address['postal_code'] ?? null;
        $city = $address['city'] ?? null;
        $country = $address['country'] ?? null;
    }
}
```

#### Database Mapping (Companies → Customers)

```php
Customer::create([
    'company_id' => null,  // ← ALTIJD NULL! Handmatig toewijzen later
    'teamleader_id' => $tlCompany['id'],  // Unique identifier
    'name' => $tlCompany['name'] ?? 'Unnamed Customer',
    'company' => $tlCompany['name'] ?? null,
    'vat_number' => $tlCompany['vat_number'] ?? null,
    'email' => $tlCompany['emails'][0]['email'] ?? null,
    'phone' => $tlCompany['telephones'][0]['number'] ?? null,
    'website' => $tlCompany['website'] ?? null,

    // Address fields (van addresses[0]['address'])
    'street' => $street,
    'addition' => $addition,
    'zip_code' => $zipCode,
    'city' => $city,
    'country' => $country,

    'status' => $tlCompany['status'] === 'active' ? 'active' : 'inactive',
    'is_active' => true,
    'created_at' => isset($tlCompany['added_at']) ? Carbon::parse($tlCompany['added_at']) : now(),
    'updated_at' => isset($tlCompany['updated_at']) ? Carbon::parse($tlCompany['updated_at']) : now(),
]);
```

#### Timeout Configuration (VERPLICHT!)

```php
// In TeamleaderController::importCompanies() en importUsers()
set_time_limit(600);  // 10 minuten
ini_set('max_execution_time', 600);
```

---

### 👥 USERS IMPORT (Teamleader Contacts → Progress Users)

#### Kritieke Requirements

**1. GEEN Email Notificaties:**
```php
// ✅ CORRECT - Direct User::create() zonder events
User::create([
    'email' => $email,
    'password' => bcrypt(\Illuminate\Support\Str::random(32)),  // Random password
    'email_verified_at' => now(),  // Auto-verify, geen email!
]);

// ❌ FOUT - Triggert email notificaties
User::factory()->create([...]);
$user->sendEmailVerificationNotification();
```

**2. Customer Contact Filtering:**
```php
// Filter contacten die aan companies gekoppeld zijn
// Deze zijn waarschijnlijk klant-contactpersonen, GEEN team members
$hasCompany = !empty($contact['companies']) && count($contact['companies']) > 0;

// Skip customer contacts in default import
if ($hasCompany) {
    $skipped++;
    continue;
}
```

#### Database Mapping (Contacts → Users)

```php
User::create([
    'company_id' => null,  // ← ALTIJD NULL! Handmatig toewijzen later
    'teamleader_id' => $contact['id'],
    'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
    'email' => $contact['emails'][0]['email'],
    'email_verified_at' => now(),  // Skip email verificatie
    'password' => bcrypt(\Illuminate\Support\Str::random(32)),  // Random wachtwoord
    'role' => 'user',  // Default role
    'is_active' => true,
]);
```

#### UI Features - Select Users View

**5 Statistieken Cards:**
```php
1. Total Contacts - Alle contacten uit Teamleader
2. Standalone - Contacten ZONDER company link (groen)
3. Company Contacts - Contacten MET company link (oranje)
4. Already Imported - Al bestaande users (blauw)
5. Selected - Aantal geselecteerde contacten (paars)
```

**Filter Toggle:**
```html
<input type="checkbox" id="hide-company-contacts" onchange="toggleCompanyContacts()">
<label>Hide customer contacts (recommended)</label>
```

**JavaScript Filter:**
```javascript
function toggleCompanyContacts() {
    const hideCheckbox = document.getElementById('hide-company-contacts');
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
        const hasCompany = row.dataset.hasCompany === '1';
        if (hideCheckbox.checked && hasCompany) {
            row.style.display = 'none';
            // Uncheck als hidden
            const checkbox = row.querySelector('.user-checkbox');
            if (checkbox && checkbox.checked) {
                checkbox.checked = false;
            }
        } else {
            row.style.display = '';
        }
    });

    updateSelectedCount();
}
```

---

### 🔧 Kritieke Fixes & Lessons Learned

#### 1. API Response Wrapping
**Probleem:** `getCompany()` wrapt response in `{ "data": {...} }`, maar `listCompanies()` niet.

**Oplossing:**
```php
$tlCompany = $response['data'] ?? $response;  // Support both formats
```

#### 2. Address Nested Structure
**Probleem:** Adressen zitten in `addresses[0]['address']`, niet `addresses[0]`.

**Oplossing:** Zie "Address Mapping" sectie hierboven.

#### 3. Class Import Missing
**Probleem:** `Class "App\Http\Controllers\Customer" not found`

**Oplossing:**
```php
// Bovenaan TeamleaderController.php
use App\Models\Customer;
```

#### 4. Timeout Errors
**Probleem:** PHP max execution time van 30 seconden te kort voor grote imports.

**Oplossing:**
```php
set_time_limit(600);
ini_set('max_execution_time', 600);
```

#### 5. Loading Indicators
**Probleem:** Gebruiker weet niet dat import lang kan duren.

**Oplossing:**
```javascript
const importBtn = document.getElementById('import-btn');
importBtn.disabled = true;
importBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importing... Please wait';

// In confirm dialog:
confirm(`Are you sure you want to import ${count} users?\n\nNote: No emails will be sent.\nThis may take several minutes, please be patient.`);
```

---

### 🧪 Testing & Debugging

#### Debug Logging (Tijdelijk)

**Address Debug:**
```php
Log::info('Teamleader addresses debug', [
    'company_name' => $tlCompany['name'],
    'addresses_count' => count($addresses),
    'addresses_data' => $addresses
]);
```

**View Logs:**
```bash
tail -200 /var/www/vhosts/adcompro.app/progress.adcompro.app/storage/logs/laravel.log | grep -A 10 "addresses debug"
```

#### Import Testing Checklist

**Companies:**
- [ ] Select 1-2 bedrijven met bekende adressen
- [ ] Importeer via "Select & Import"
- [ ] Verifieer in Customers: street, zip_code, city, country allemaal ingevuld
- [ ] Check dat company_id NULL is
- [ ] Check dat teamleader_id uniek is

**Users:**
- [ ] Toggle "Hide customer contacts" AAN
- [ ] Selecteer alleen standalone contacts
- [ ] Importeer 1-2 test users
- [ ] Verifieer dat GEEN emails verzonden zijn
- [ ] Check dat users role = 'user' hebben
- [ ] Check dat company_id NULL is

#### Veelvoorkomende Problemen

**Probleem:** "No address provided" in customer detail
**Oorzaak:** Address nested structure niet correct
**Oplossing:** Check `addresses[0]['address']` nesting

**Probleem:** Email verification emails worden verzonden
**Oorzaak:** User::factory() of event listeners
**Oplossing:** Gebruik direct User::create() met email_verified_at

**Probleem:** Timeout na 30 seconden
**Oorzaak:** PHP execution time limit
**Oplossing:** set_time_limit(600) in controller

**Probleem:** Alle customer contacts in users lijst
**Oorzaak:** Geen filtering op has_company
**Oplossing:** Use "Hide customer contacts" toggle

---

### 📊 Import Workflow Diagram

```
COMPANIES IMPORT:
1. Teamleader Dashboard → "Select & Import" bij Companies
2. Fetch all companies via TeamleaderService::listCompanies() (paginated, 100 per page)
3. Check existing customers op teamleader_id
4. Extract addresses[0]['address'] voor address fields
5. Create Customer met company_id = NULL
6. Success message met imported/skipped count

USERS IMPORT:
1. Teamleader Dashboard → "Select & Import" bij Users
2. Fetch all contacts via TeamleaderService::listContacts() (paginated, 100 per page)
3. Filter: Skip contacts zonder email
4. Detect has_company voor customer contact filtering
5. Check existing users op email of teamleader_id
6. Create User met random password, auto-verify, company_id = NULL
7. Success message met imported/skipped count
```

---

### 🎯 Best Practices

**DO's:**
✅ Altijd company_id NULL laten bij import
✅ Gebruik set_time_limit(600) voor grote imports
✅ Check addresses[0]['address'] nesting voor address data
✅ Filter customer contacts uit users import (has_company check)
✅ Gebruik direct User::create() voor no-email import
✅ Toon loading indicators en confirm dialogs
✅ Log import statistics (imported/skipped counts)

**DON'Ts:**
❌ NOOIT company_id automatisch toewijzen bij import
❌ NOOIT emails verzenden bij users import (geen User::factory, geen events)
❌ NOOIT uitgaan van addresses[0]['line_1'] (gebruik addresses[0]['address']['line_1'])
❌ NOOIT customer contacts als team members importeren zonder filter
❌ NOOIT imports starten zonder timeout configuration
❌ NOOIT API responses zonder wrapping check ($response['data'] ?? $response)

---

## 🚀 RECENT UPDATES (03-11-2025 - Part 16)

### 💰 BUDGET TRACKING & RECURRING DASHBOARD - KRITIEKE FIXES (03-11-2025)

**BELANGRIJKE VERBETERING**: Multiple kritieke fixes in budget berekeningen, recurring dashboard en series budget views.

#### 1. **Non-Billable Uren Uitgefilterd (KRITIEK!)**
**Probleem**: Non-billable tijdregistraties werden meegeteld in budget berekeningen, waardoor budget overspent percentages veel te hoog waren.

**Impact Voorbeeld - Huawei March 2025**:
- Billable uren: 156.01h × €150 = €23,401.50 ✅
- Met non-billable (FOUT): 203.26h × €150 = €30,489.00 ❌
- **Verschil: €7,087.50 te veel geteld!** (30% te hoog)

**Oplossing**:
```php
// ProjectController.php - Projects Index (Budget Used grafiek)
$timeEntriesByProject = \App\Models\TimeEntry::whereIn('project_id', $projectIds)
    ->where('status', 'approved')
    ->where('is_billable', 'billable')  // KRITIEKE FIX: Alleen billable!
    ->select('project_id', \DB::raw('SUM(hours + (minutes / 60)) as total_hours'))
    ->groupBy('project_id')
    ->pluck('total_hours', 'project_id');

// ProjectController.php - Series Budget View
foreach ($timeEntries as $entry) {
    $entryHours = $entry->hours + ($entry->minutes / 60);

    // KRITIEKE FIX: Alleen billable uren meetellen!
    if ($entry->is_billable === 'billable') {
        $totalHours += $entryHours;
        $hourlyRate = $entry->hourly_rate_used ?? $seriesProject->default_hourly_rate ?? 75;
        $totalCosts += $entryHours * $hourlyRate;
    }
}
```

**Resultaat**:
- Budget Used percentages zijn nu correct (13,896.50 ipv 24,636.50 voor Huawei 2025)
- Alleen billable uren tellen mee in alle budget berekeningen
- Non-billable uren worden volledig genegeerd

---

#### 2. **Recurring Dashboard: Rollover Display Herwerkt**
**Probleem**: Rollover werd meegerekend in maandbudgets, waardoor totalen niet klopten.

**Oplossing**:
- Maandcellen tonen nu **3 regels**: Budget (zonder rollover) / Spent / Variance (zonder rollover)
- Rollover effecten alleen zichtbaar in **Totalen kolom**
- Totalen berekening: **SOM van BASE budgets** ZONDER rollover

**Code** (`RecurringDashboardController.php`):
```php
// Voor maandweergave: toon ALLEEN base_monthly_fee ZONDER rollover
$baseMonthlyBudget = $fee->base_monthly_fee;
$budgetWithRollover = $fee->base_monthly_fee + $fee->rollover_from_previous;
$spent = $fee->hours_value + $fee->additional_costs_in_fee;

$monthVariance = $baseMonthlyBudget - $spent; // ZONDER rollover

$monthlyData[$month] = [
    'base_budget' => $baseMonthlyBudget, // Budget ZONDER rollover
    'budget' => $budgetWithRollover, // Budget MET rollover (voor totalen)
    'spent' => $spent,
    'month_variance' => $monthVariance, // Variance ZONDER rollover
];

// KRITIEKE FIX: Totalen = SOM van BASE budgets ZONDER rollover
$yearTotals['budget'] += $baseMonthlyBudget;
$yearTotals['variance'] += ($baseMonthlyBudget - $spent);
```

**Views Update** (`recurring-dashboard/index.blade.php`):
```blade
{{-- NIEUWE LAYOUT: Budget / Spent / Variance --}}
<div class="text-xs font-medium">€{{ number_format($monthData['base_budget'], 0) }}</div>
<div class="text-xs">€{{ number_format($monthData['spent'], 0) }}</div>
<div class="text-sm font-bold">€{{ number_format($monthData['month_variance'], 0) }}</div>

{{-- Totalen met labels --}}
<span style="opacity: 0.6;">Budget:</span> €{{ number_format($series['year_totals']['budget'], 0) }}
<span style="opacity: 0.6;">Used:</span> €{{ number_format($series['year_totals']['spent'], 0) }}
<span style="opacity: 0.7;">Variance:</span> €{{ number_format($series['year_totals']['variance'], 0) }}
```

---

#### 3. **Series Budget View: Complete Overhaul**

**A. Alle 12 Maanden Tonen**
```php
// KRITIEKE FIX: Toon ALTIJD alle 12 maanden
$startMonth = 1;
$endMonth = 12;

// Ook maanden zonder project moeten getoond worden met €0 budget
for ($month = $startMonth; $month <= $endMonth; $month++) {
    // ... berekeningen
}
```

**B. Per-Maand Budget Correct**
```php
// KRITIEKE FIX: Gebruik het budget van het PROJECT dat actief is deze maand
$activeProjectThisMonth = null;

foreach ($seriesProjects as $seriesProject) {
    if ($isActiveThisMonth && !$activeProjectThisMonth) {
        if ($projectStart && $projectStart->month == $month && $projectStart->year == $year) {
            $activeProjectThisMonth = $seriesProject;
        }
    }
}

$monthlyBudget = $activeProjectThisMonth ? ($activeProjectThisMonth->monthly_fee ?? 0) : 0;
```

**C. Rollover "Pending" Logica**
```php
// KRITIEKE FIX: Rollover springt over maanden zonder project
if ($activeProjectThisMonth) {
    // Er is een actief project deze maand
    $rolloverIn = $activeProjectThisMonth->fee_rollover_enabled ? $previousRollover : 0;
    $totalBudget = $monthlyBudget + $rolloverIn;
    $rolloverOut = $activeProjectThisMonth->fee_rollover_enabled ? ($totalBudget - $totalCosts) : 0;
    $previousRollover = $rolloverOut;
} else {
    // Geen actief project deze maand - rollover blijft behouden!
    $rolloverOut = $previousRollover; // Rollover gaat door naar volgende maand
}
```

**D. Year Totals Correct Berekend**
```php
// KRITIEKE FIX: Rollover_in moet NIET opgeteld worden!
// Rollover verschuift budget tussen maanden maar voegt GEEN nieuw geld toe.
$yearTotals = [
    'total_base_budget' => $totalBaseBudget,
    'total_rollover_in' => 0, // Niet relevant (intern verschuiving)
    'total_budget' => $totalBaseBudget, // = Base budget, NIET + rollover
    'total_used' => $totalUsed,
    // Total exceeded = verschil totaal budget vs totaal used
    'total_remaining' => max(0, $totalBaseBudget - $totalUsed),
    'total_exceeded' => max(0, $totalUsed - $totalBaseBudget),
    'total_hours' => array_sum(array_column($monthsData, 'hours_worked')),
];
```

**E. Visual Consistency: OF Remaining OF Exceeded**
```blade
{{-- Year Totals Row - Altijd OF remaining OF exceeded prominent --}}
<td class="text-right {{ $yearTotals['total_remaining'] > 0 ? 'text-green-600' : 'text-slate-400' }}">
    {{ $yearTotals['total_remaining'] > 0 ? '€' . number_format($yearTotals['total_remaining'], 2) : '-' }}
</td>
<td class="text-right {{ $yearTotals['total_exceeded'] > 0 ? 'text-red-600' : 'text-slate-400' }}">
    {{ $yearTotals['total_exceeded'] > 0 ? '€' . number_format($yearTotals['total_exceeded'], 2) : '-' }}
</td>

{{-- Statistics Card - Kleuren aangepast --}}
<p class="text-2xl font-bold {{ $yearTotals['total_remaining'] > 0 ? 'text-green-600' : 'text-slate-400' }}">
    €{{ number_format($yearTotals['total_remaining'], 2) }}
</p>
```

---

#### 4. **Progress Bar Kleuren Fix**
**Probleem**: CSS variables werkten niet, waardoor progress bars geen kleuren toonden.

**Oplossing**: Hardcoded hex kleuren gebruiken
```php
// ProjectController.php & views
if ($budgetPercentage > 90) {
    $progressColor = '#ef4444'; // red-500
    $textColor = '#ef4444';
} elseif ($budgetPercentage > 75) {
    $progressColor = '#f59e0b'; // amber-500
    $textColor = '#f59e0b';
} else {
    $progressColor = '#10b981'; // green-500
    $textColor = '#10b981';
}
```

**Percentage Cap Verwijderd**:
```php
// GEEN min(100, ...) meer!
// Overspent projecten tonen echte percentages (175%, 231%, etc.)
$project->budget_percentage = round(($budgetUsed / $originalBudget) * 100);
```

---

#### 5. **Sorteerbare Kolommen in Projects Index**
```php
// ProjectController.php
$sortField = $request->get('sort', 'created_at');
$sortDirection = $request->get('direction', 'desc');

$allowedSortFields = [
    'name', 'status', 'start_date', 'end_date',
    'monthly_fee', 'billing_frequency', 'created_at', 'budget_used'
];

// Budget_used in-memory sorting (NA berekeningen)
if ($sortField === 'budget_used') {
    $sorted = $projects->getCollection()->sortBy(function($project) {
        return $project->budget_used ?? 0;
    }, SORT_REGULAR, $sortDirection === 'desc');
    $projects->setCollection($sorted->values());
}
```

**View Helper**:
```blade
@php
    function sortableHeader($label, $field, $currentSort, $currentDirection) {
        $newDirection = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
        $url = request()->fullUrlWithQuery(['sort' => $field, 'direction' => $newDirection]);
        $icon = $currentSort === $field ? ($currentDirection === 'asc' ? '↑' : '↓') : '';
        return '<a href="' . $url . '">' . $label . ' ' . $icon . '</a>';
    }
@endphp
```

---

#### 6. **Admin Users Zien Alles**
**Probleem**: Admin users zagen alleen eigen company data, moesten alles kunnen zien zoals super_admin.

**Oplossing**: Company isolation check aangepast in alle controllers
```php
// OUDE CHECK:
if (Auth::user()->role !== 'super_admin')

// NIEUWE CHECK:
if (!in_array(Auth::user()->role, ['super_admin', 'admin']))
```

**Geüpdatet in**:
- CustomerController.php
- TimeEntryController.php
- ProjectController.php
- RecurringDashboardController.php

---

#### 7. **Year Budget Column in Projects Index**
```blade
{{-- Nieuwe kolom in projects/index.blade.php --}}
<td style="padding: 1rem 1.5rem; white-space: nowrap; text-align: center;">
    @if($project->recurring_series_id)
        <a href="{{ route('projects.series-budget', $project->id) }}"
           class="inline-flex items-center px-3 py-1.5 rounded-lg font-medium transition-all"
           style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);">
            <i class="fas fa-chart-line mr-1.5"></i>
            View Totals
        </a>
    @else
        <span>-</span>
    @endif
</td>
```

---

### 📊 Impact & Resultaten

**Huawei Retainer 2025 Voorbeeld**:
- **Oude Total Exceeded**: €99,878.05 ❌ (volledig fout)
- **Nieuwe Total Exceeded**: €13,896.50 ✅ (correct)
- **Verschil**: €85,981.55 te veel (bijna 7x te hoog!)

**Budget Berekeningen Nu Correct**:
- ✅ Non-billable uren uitgefilterd
- ✅ Rollover intern verschuiving (niet in totalen)
- ✅ Per-maand budgets correct toegepast
- ✅ Alle 12 maanden getoond
- ✅ OF remaining OF exceeded (niet beide)

---

### 🎯 Key Lessons Learned

**DO's**:
✅ Filter ALTIJD op `is_billable = 'billable'` voor budget berekeningen
✅ Rollover is interne verschuiving, tel NIET op in jaar totalen
✅ Gebruik hardcoded kleuren voor kritieke UI (niet CSS variables)
✅ Percentage cap verwijderen voor overspent (toon echte 175%, 231%)
✅ Year totals berekenen als verschil (niet als som van maandelijkse waardes)

**DON'Ts**:
❌ NOOIT non-billable uren meetellen in budget costs
❌ NOOIT rollover optellen bij jaar totalen (dubbel tellen!)
❌ NOOIT beide remaining EN exceeded tegelijk prominent tonen
❌ NOOIT CSS variables gebruiken voor kleuren (browser caching issues)
❌ NOOIT array_sum() gebruiken voor exceeded/remaining (incorrecte logica)

---

## 🚀 RECENT UPDATES (24-10-2025 - Part 15)

### 📦 DATABASE-FIRST IMPORT SYSTEM - COMPLETE OVERHAUL (24-10-2025)

**KRITIEKE VERBETERING**: Alle Teamleader imports zijn volledig herwerkt naar database-first architectuur voor snelheid en betrouwbaarheid.

#### Het Probleem met API-First Imports
- API calls zijn traag (soms 30+ seconden voor grote datasets)
- Rate limiting problemen bij bulk imports
- Geen offline mogelijkheden
- Moeilijk te filteren en te sorteren
- Dubbele data fetching (sync + import)

#### De Revolutionaire Oplossing: Database-First Architecture
**Concept**: Gebruik de lokale database cache als single source of truth voor alle imports.

**Workflow**:
1. **Global Sync**: Admin doet periodieke sync van ALLE data naar database (1x per dag/week)
2. **Import Selection**: Gebruiker selecteert uit lokale cache (instant, geen API calls)
3. **Import Execution**: Data wordt gekopieerd van cache naar production tables

---

### 🗂️ Database-First Implementation Details

#### 1. PROJECTS IMPORT (Teamleader → Progress Projects)

**Gewijzigde Bestanden**:
- `app/Services/TeamleaderImportService.php` - `importSelectedProjects()` method
- `app/Http/Controllers/TeamleaderController.php` - `selectProjects()` method
- `resources/views/teamleader/select-projects.blade.php` - Sync banners

**Kritieke Code - Database Read (TeamleaderImportService.php lines 483-527)**:
```php
// OUDE METHODE (traag):
$response = TeamleaderService::getProject($projectId);
$tlProject = $response['data'] ?? $response;

// NIEUWE METHODE (snel):
$tlProject = TeamleaderProject::where('teamleader_id', $projectId)->first();

// Map Teamleader status naar Progress status
$status = match($tlProject->status) {
    'done' => 'completed',  // KRITIEK: Teamleader "done" → Progress "completed"
    'active' => 'active',
    'on_hold' => 'on_hold',
    'cancelled' => 'cancelled',
    default => 'active'
};

// Create project met budget mapping
Project::create([
    'company_id' => Auth::user()->company_id,
    'customer_id' => $tlProject->customer_id,
    'teamleader_id' => $tlProject->teamleader_id,
    'name' => $tlProject->title ?? 'Unnamed Project',
    'description' => $tlProject->description ?? null,
    'status' => $status,  // Gemapte status
    'start_date' => $tlProject->starts_on,
    'end_date' => $tlProject->due_on,
    'total_value' => $tlProject->budget_amount ?? 0,      // BUDGET FIX
    'monthly_fee' => $tlProject->budget_amount ?? null,   // BUDGET FIX
    'created_by' => Auth::id(),
]);

// Mark als imported in cache
$tlProject->update(['is_imported' => true, 'imported_at' => now()]);
```

**Budget Import Fix**:
- **Probleem**: Budget data werd niet overgenomen (stond wel in database maar werd niet gelezen)
- **Oorzaak**: Import gebruikte nog API call ipv database read
- **Oplossing**: `$tlProject->budget_amount` wordt nu correct gemapped naar `total_value` en `monthly_fee`

**Status Enum Fix**:
- **Probleem**: "Data truncated for column 'status'" - alle imports faalden
- **Oorzaak**: Teamleader gebruikt "done", Progress ENUM heeft alleen: 'draft', 'active', 'completed', 'cancelled', 'on_hold'
- **Oplossing**: Match expression voor status mapping

**Status Filter Enhancement**:
- Voeg status breakdown toe aan selectie pagina
- Toon aantal per status (Active: 3, Done: 108, etc.)
- Dropdown met counts per status
- Gebruiker kan nu ALLE statussen importeren (niet alleen active)

---

#### 2. USERS IMPORT (Teamleader Contacts → Progress Users)

**Gewijzigde Bestanden**:
- `app/Services/TeamleaderImportService.php` - `importSelectedUsers()` method
- `app/Http/Controllers/TeamleaderController.php` - `selectUsers()` method
- `resources/views/teamleader/select-users.blade.php` - Sync banners

**Kritieke Code - Database Read (TeamleaderImportService.php lines 800-837)**:
```php
// OUDE METHODE (traag + API calls):
$response = TeamleaderService::getContact($userId);
$tlContact = $response['data'] ?? $response;

// NIEUWE METHODE (snel + database):
$tlContact = TeamleaderContact::where('teamleader_id', $userId)->first();

if (!$tlContact) {
    Log::warning('Contact not found in database cache', ['user_id' => $userId]);
    $skipped++;
    continue;
}

// Create user ZONDER email notificaties
User::create([
    'company_id' => null,  // Handmatige toewijzing
    'teamleader_id' => $tlContact->teamleader_id,
    'name' => $tlContact->full_name ?? trim(($tlContact->first_name ?? '') . ' ' . ($tlContact->last_name ?? '')),
    'email' => $tlContact->email,
    'email_verified_at' => now(),  // Skip verificatie
    'password' => bcrypt(\Illuminate\Support\Str::random(32)),
    'role' => 'user',
    'is_active' => true,
]);

// Mark als imported
$tlContact->update(['is_imported' => true, 'imported_at' => now()]);
```

**Key Features**:
- Gebruikt `TeamleaderContact` model voor database reads
- Geen email notificaties (direct `User::create()`)
- Company filter voor customer contacts (has_company check)
- Import tracking met `is_imported` flag

---

#### 3. CONTACTS IMPORT (Teamleader Contacts → Progress Customer Contacts)

**NIEUWE FUNCTIONALITEIT** - Importeer contactpersonen voor specifieke customers

**Gewijzigde Bestanden**:
- `app/Http/Controllers/TeamleaderController.php` - `selectContacts()` + `importContacts()` methods
- `app/Models/TeamleaderContact.php` - Nieuw model
- `resources/views/teamleader/select-contacts.blade.php` - Nieuwe view
- `resources/views/customers/show.blade.php` - "Import from Teamleader" button
- `routes/web.php` - Twee nieuwe routes

**Routes (web.php)**:
```php
Route::get('/teamleader/select/contacts', [TeamleaderController::class, 'selectContacts'])
    ->name('teamleader.select.contacts');
Route::post('/teamleader/import/contacts', [TeamleaderController::class, 'importContacts'])
    ->name('teamleader.import.contacts');
```

**Company Filtering Challenge**:
**Probleem**: Teamleader API's `listContactsForCompany()` filter werkt niet correct (retourneert alle 2,266 contacten)

**Oplossing - Pragmatische Filtering**:
```php
// Stap 1: Haal alle contacten uit database cache
$allTeamleaderContacts = TeamleaderContact::whereNotNull('email')
    ->orderBy('full_name')
    ->get();

// Stap 2: Filter op customer's company
$allContacts = [];
foreach ($allTeamleaderContacts as $tlContact) {
    $rawData = $tlContact->raw_data;
    $belongsToCompany = false;

    // Check 1: Zoek in raw_data naar companies array
    if ($rawData && isset($rawData['companies']) && is_array($rawData['companies'])) {
        foreach ($rawData['companies'] as $company) {
            $companyId = is_array($company) && isset($company['company']['id'])
                ? $company['company']['id']
                : $company;

            if ($companyId === $customer->teamleader_id) {
                $belongsToCompany = true;
                break;
            }
        }
    }

    // Check 2: Fallback - check of ooit geïmporteerd voor deze customer
    if (!$belongsToCompany) {
        $wasImportedForThisCustomer = Contact::where('customer_id', $customerId)
            ->where(function($q) use ($tlContact) {
                $q->where('email', $tlContact->email)
                  ->orWhere('teamleader_id', $tlContact->teamleader_id);
            })
            ->exists();

        if ($wasImportedForThisCustomer) {
            $belongsToCompany = true;
        }
    }

    // Skip als niet bij deze company
    if (!$belongsToCompany) {
        continue;
    }

    // Check of al geïmporteerd
    $isImported = Contact::where('customer_id', $customerId)
        ->where(function($q) use ($tlContact) {
            $q->where('email', $tlContact->email)
              ->orWhere('teamleader_id', $tlContact->teamleader_id);
        })
        ->exists();

    $allContacts[] = [
        'id' => $tlContact->teamleader_id,
        'name' => $tlContact->full_name,
        'email' => $tlContact->email,
        'phone' => $tlContact->phone ?? $tlContact->mobile ?? '-',
        'position' => $tlContact->position ?? '-',
        'is_imported' => $isImported
    ];
}
```

**Import Logic (importContacts method)**:
```php
foreach ($validated['contact_ids'] as $contactId) {
    // Haal contact op uit database cache
    $tlContact = TeamleaderContact::where('teamleader_id', $contactId)->first();

    if (!$tlContact) {
        $skipped++;
        continue;
    }

    // Check of al bestaat voor deze customer
    $existingContact = Contact::where('customer_id', $customer->id)
        ->where(function($q) use ($tlContact) {
            $q->where('email', $tlContact->email)
              ->orWhere('teamleader_id', $tlContact->teamleader_id);
        })
        ->first();

    if ($existingContact) {
        $skipped++;
        continue;
    }

    // Maak contact aan voor deze customer
    Contact::create([
        'customer_id' => $customer->id,
        'company_id' => Auth::user()->company_id,
        'teamleader_id' => $tlContact->teamleader_id,
        'name' => $tlContact->full_name,
        'email' => $tlContact->email,
        'phone' => $tlContact->phone ?? $tlContact->mobile,
        'position' => $tlContact->position,
        'is_primary' => false,
        'is_active' => true,
    ]);

    $imported++;
}
```

**UI Integration**:
- "Import from Teamleader" button in customer detail pagina (contacts sectie)
- Link naar `/teamleader/select/contacts?customer_id={id}`
- Statistics cards: Total, Available, Imported, Selected
- Select All / Deselect All buttons
- Real-time counter voor geselecteerde contacten

---

### 📊 Database Cache Tables

**Teamleader Cache Tables** (gevuld via Global Sync):
```sql
teamleader_companies:
  - teamleader_id (unique), name, vat_number, emails, website
  - address fields (line_1, line_2, postal_code, city, country)
  - is_imported, synced_at, imported_at
  - raw_data (JSON) - complete API response

teamleader_contacts:
  - teamleader_id (unique), first_name, last_name, full_name
  - email, phone, mobile, position, language
  - companies (JSON array) - links naar company IDs
  - address fields
  - is_imported, synced_at, imported_at
  - raw_data (JSON) - complete API response

teamleader_projects:
  - teamleader_id (unique), title, description, status
  - customer_id (FK naar customers via teamleader_id)
  - starts_on, due_on, budget_amount (KRITIEK!)
  - is_imported, synced_at, imported_at
  - raw_data (JSON) - complete API response
```

**Import Tracking**:
- `is_imported` (boolean) - Is deze record al geïmporteerd?
- `imported_at` (timestamp) - Wanneer geïmporteerd?
- `synced_at` (timestamp) - Laatste sync vanaf Teamleader API

---

### 🔧 Critical Fixes & Lessons Learned (24-10-2025)

#### 1. Missing Model Imports
**Probleem**: "Class 'App\Services\TeamleaderProject' not found"
**Oorzaak**: Model gebruikt zonder import statement
**Oplossing**:
```php
// Bovenaan TeamleaderImportService.php
use App\Models\TeamleaderProject;
use App\Models\TeamleaderContact;
```

#### 2. Budget Data Not Imported
**Probleem**: Budget amount stond in database maar kwam niet in projects
**Oorzaak**: Import gebruikte API call ipv database read
**Oplossing**: Switch naar `TeamleaderProject::where('teamleader_id', $id)->first()`

#### 3. Status Enum Mismatch
**Probleem**: Alle imports faalden met "Data truncated for column 'status'"
**Diagnose**:
```sql
SHOW COLUMNS FROM projects LIKE 'status';
-- Result: enum('draft','active','completed','cancelled','on_hold')
```
**Root Cause**: Teamleader gebruikt "done", Progress gebruikt "completed"
**Oplossing**: Status mapping met match expression

#### 4. API Filter Doesn't Work
**Probleem**: `listContactsForCompany()` retourneert alle contacten (geen filtering)
**Diagnose**: API filter syntax werkt niet zoals verwacht
**Oplossing**: Client-side filtering op raw_data companies array

#### 5. Companies Array Empty
**Probleem**: `companies` field in teamleader_contacts is altijd `[]`
**Oorzaak**: Teamleader API `/contacts.list` retourneert geen company relaties
**Oplossing**: Filtering op `raw_data['companies']` array als die bestaat

---

### 🎯 Benefits van Database-First Architecture

**Performance**:
- ⚡ **10-50x sneller** - Geen API calls tijdens import selectie
- 📊 **Instant filtering** - Database queries ipv API pagination
- 🔢 **Real-time statistics** - Count queries zijn microseconden

**Reliability**:
- ✅ **Offline capable** - Werkt zonder internet verbinding
- 🔄 **No rate limiting** - Geen API quota problemen
- 📦 **Consistent data** - Altijd dezelfde data tijdens selectie

**User Experience**:
- 🎨 **Better UI** - Status breakdowns, counts, filters
- 🔍 **Advanced filtering** - SQL queries voor complexe filters
- 📈 **Statistics** - Real-time counts zonder performance hit

**Maintainability**:
- 🧪 **Easier testing** - Seed database cache voor tests
- 🐛 **Better debugging** - Alle data lokaal beschikbaar
- 📝 **Audit trail** - Import tracking met timestamps

---

### 🧪 Testing & Verification

**Test Workflow**:
```bash
# 1. Global Sync (als admin)
https://progress.adcompro.app/teamleader
Klik: "Sync All Companies", "Sync All Contacts", "Sync All Projects"

# 2. Verify cache populated
mysql -u abcdefg12345 -pZomerweek123 progress_ -e "
SELECT COUNT(*) FROM teamleader_companies;
SELECT COUNT(*) FROM teamleader_contacts;
SELECT COUNT(*) FROM teamleader_projects;
"

# 3. Test project import
https://progress.adcompro.app/customers/1937
Klik: "Import from Teamleader" (bij Projects)
Selecteer projecten → Import

# 4. Verify import
mysql -u abcdefg12345 -pZomerweek123 progress_ -e "
SELECT id, name, status, total_value, monthly_fee, teamleader_id
FROM projects
WHERE customer_id = 1937
ORDER BY created_at DESC
LIMIT 5;
"

# 5. Check logs
tail -100 /var/www/vhosts/adcompro.app/progress.adcompro.app/storage/logs/laravel.log | grep "Import completed"
```

**Expected Results**:
- ✅ Projects import shows correct budget amounts
- ✅ Status mapping works (Teamleader "done" → Progress "completed")
- ✅ All statuses can be imported (not just "active")
- ✅ Contacts filtered by customer company
- ✅ Import tracking works (is_imported flag set)

---

### 📝 Code Locations Summary

**Controllers**:
```
/app/Http/Controllers/TeamleaderController.php
├── selectProjects()      (lines ~430-490)  - Database read voor project selectie
├── selectUsers()         (lines ~444-488)  - Database read voor user selectie
├── selectContacts()      (lines ~787-913)  - Database read + filtering voor contacts
└── importContacts()      (lines ~915-990)  - Import contacts voor customer
```

**Services**:
```
/app/Services/TeamleaderImportService.php
├── importSelectedProjects()  (lines ~483-560)  - Database-first project import
├── importSelectedUsers()     (lines ~800-860)  - Database-first user import
└── Status mapping logic      (lines ~503-510)  - Teamleader → Progress status
```

**Models**:
```
/app/Models/TeamleaderProject.php   - Cache model voor projects
/app/Models/TeamleaderContact.php   - Cache model voor contacts (NIEUW!)
/app/Models/TeamleaderCompany.php   - Cache model voor companies
```

**Views**:
```
/resources/views/teamleader/
├── select-projects.blade.php   - Status breakdown + sync banner update
├── select-users.blade.php      - Sync banner update
└── select-contacts.blade.php   - NIEUWE view voor contact import

/resources/views/customers/show.blade.php
└── Import button toegevoegd bij contacts sectie (lines ~440-447)
```

**Routes**:
```php
// web.php (lines ~597, ~608)
Route::get('/teamleader/select/contacts', [TeamleaderController::class, 'selectContacts'])
    ->name('teamleader.select.contacts');
Route::post('/teamleader/import/contacts', [TeamleaderController::class, 'importContacts'])
    ->name('teamleader.import.contacts');
```

---

### ⚠️ Known Issues & Future Improvements

**Current Limitations**:
1. **Companies Array Empty**: Teamleader API retourneert geen company relaties in `/contacts.list`
   - Workaround: Client-side filtering op raw_data
   - Toekomstige fix: Verbeter sync om company relaties te bewaren

2. **No Real-time Sync**: Data kan verouderd zijn tussen syncs
   - Workaround: Toon laatste sync timestamp
   - Toekomstige fix: Incremental sync op achtergrond

3. **Memory Usage**: Laden van alle contacten (2,266) in geheugen bij filtering
   - Workaround: Werkt goed tot ~10,000 contacten
   - Toekomstige fix: Database-native JSON filtering met indexes

**Planned Improvements**:
- [ ] Incremental sync (alleen gewijzigde records)
- [ ] Background sync met queue jobs
- [ ] Better company relationship tracking in cache
- [ ] Sync scheduling (automatic daily sync)
- [ ] Conflict resolution (manual changes vs sync updates)

---

### 🎓 Best Practices Learned

**DO's**:
✅ Gebruik database cache als single source of truth voor imports
✅ Implement is_imported + imported_at tracking
✅ Log alle filter operaties voor debugging
✅ Match Teamleader status naar Progress ENUM waarden
✅ Gebruik raw_data field als fallback data source
✅ Client-side filtering als API filters niet werken
✅ Toon sync timestamps in UI voor transparantie

**DON'Ts**:
❌ NOOIT direct API calls in import selection UI
❌ NOOIT vertrouwen op API filters zonder verificatie
❌ NOOIT budget_amount vergeten te mappen
❌ NOOIT status waarden direct overnemen zonder ENUM check
❌ NOOIT imported records opnieuw importeren zonder check
❌ NOOIT grote datasets in geheugen laden zonder pagination fallback

---

## 🚀 RECENT UPDATES (20-10-2025 - Part 14)

### 🎯 Floating Bulk Actions Bar - NIEUWE STANDAARD (20-10-2025)

**UNIVERSELE STANDAARD**: Gmail-style floating action bar aan onderkant scherm voor alle bulk operaties.

**GEÏMPLEMENTEERD IN**:
- ✅ Projects index (20-10-2025) - Met 5 status opties (draft, active, on_hold, completed, cancelled)
- ✅ Users index (20-10-2025) - Met 2 status opties (activate, deactivate)

#### Design Pattern - Complete Implementatie

**1. HTML Structuur** (altijd exact deze opbouw gebruiken):
```html
{{-- Floating Bulk Actions Bar --}}
@if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
<div id="floating-bulk-actions" class="fixed bottom-0 left-0 right-0 z-40 transition-all duration-300"
     style="transform: translateY(100%); pointer-events: none;">
    <div class="max-w-4xl mx-auto px-4 pb-6">
        <div class="backdrop-blur-lg rounded-2xl shadow-2xl border overflow-hidden"
             style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
                    border-color: rgba(var(--theme-border-rgb), 0.3);
                    pointer-events: auto;">
            <div class="flex items-center justify-between px-6 py-4">
                {{-- Left: Selection Info --}}
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                             style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <i class="fas fa-check" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) + 2px);"></i>
                        </div>
                        <div>
                            <div id="floating-selected-count" class="font-semibold" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                0 selected
                            </div>
                            <div class="text-xs" style="color: var(--theme-text-muted);">
                                Choose an action below
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="flex items-center gap-2">
                    {{-- Status Change Dropdown --}}
                    {{-- Delete Button --}}
                    {{-- Clear Selection --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endif
```

**2. Status Dropdown met Fixed Positioning** (BELANGRIJK!):
```html
<div class="relative">
    <button onclick="toggleStatusDropdown(event)" id="status-dropdown-btn"
            class="px-4 py-2 rounded-lg font-medium text-white text-sm">
        <i class="fas fa-exchange-alt"></i>
        <span>Change Status</span>
    </button>

    {{-- Fixed positioning zodat dropdown niet clipped wordt door parent --}}
    <div id="status-dropdown" class="hidden fixed bg-white rounded-lg shadow-2xl border overflow-hidden z-50">
        <button onclick="openBulkStatusModal('draft')">Set to Draft</button>
        <button onclick="openBulkStatusModal('active')">Activate</button>
        {{-- etc --}}
    </div>
</div>
```

**3. Universal Status Modal met Hardcoded Button Colors**:
```html
<div id="bulkStatusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="p-6">
            {{-- Modal content --}}
        </div>
        <div class="px-6 py-4 flex justify-end gap-3">
            {{-- KRITIEK: Gebruik HARDCODED kleuren, GEEN CSS variables --}}
            <button onclick="closeBulkStatusModal()"
                    style="background-color: #e5e7eb; color: #6b7280; font-size: 14px;">
                Cancel
            </button>
            <button id="statusModalConfirmBtn" onclick="confirmBulkStatusChange()"
                    style="background-color: #3b82f6; color: #ffffff; font-size: 14px;">
                Change Status
            </button>
        </div>
    </div>
</div>
```

**4. JavaScript met HARDCODED Kleuren** (VERPLICHT PATROON):
```javascript
// KRITIEK: Gebruik HARDCODED hex kleuren, GEEN CSS variables!
const statusConfig = {
    'draft': {
        title: 'Set to Draft',
        icon: 'fas fa-file-alt',
        color: '#6b7280',              // Hardcoded gray
        bgColor: '#f3f4f6',            // Light gray background
        btnColor: '#6b7280',           // Gray button
        displayName: 'Draft'
    },
    'active': {
        title: 'Activate Projects',
        icon: 'fas fa-play',
        color: '#10b981',              // Hardcoded green
        bgColor: '#d1fae5',            // Light green background
        btnColor: '#10b981',           // Green button
        displayName: 'Active'
    },
    'on_hold': {
        color: '#f59e0b',              // Hardcoded orange
        btnColor: '#f59e0b',
    },
    'completed': {
        color: '#3b82f6',              // Hardcoded blue
        btnColor: '#3b82f6',
    },
    'cancelled': {
        color: '#ef4444',              // Hardcoded red
        btnColor: '#ef4444',
    }
};

// KRITIEK: Forceer ALLE button styles in JavaScript
function openBulkStatusModal(status) {
    const config = statusConfig[status];
    const confirmBtn = document.getElementById('statusModalConfirmBtn');

    // Forceer ALLE styles voor maximale zichtbaarheid
    confirmBtn.style.backgroundColor = config.btnColor;
    confirmBtn.style.color = '#ffffff';           // ALTIJD witte tekst
    confirmBtn.style.border = 'none';
    confirmBtn.style.fontSize = '14px';
    confirmBtn.style.fontWeight = '600';
    confirmBtn.style.padding = '0.5rem 1rem';
    confirmBtn.style.borderRadius = '0.5rem';
}

// Dropdown positioning met getBoundingClientRect()
function toggleStatusDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('status-dropdown');
    const button = document.getElementById('status-dropdown-btn');

    if (dropdown.classList.contains('hidden')) {
        const buttonRect = button.getBoundingClientRect();
        dropdown.style.left = buttonRect.left + 'px';
        dropdown.style.bottom = (window.innerHeight - buttonRect.top + 8) + 'px';
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

// Visibility toggle met transform
function updateBulkActionsVisibility() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const floatingBar = document.getElementById('floating-bulk-actions');
    const selectedCount = document.getElementById('floating-selected-count');

    if (checkboxes.length > 0) {
        floatingBar.style.transform = 'translateY(0)';
        selectedCount.textContent = checkboxes.length + ' selected';
    } else {
        floatingBar.style.transform = 'translateY(100%)';
    }
}
```

#### Key Features van deze Standaard:

1. **Floating Bar Positioning**:
   - `position: fixed` aan bottom van scherm
   - `transform: translateY(100%)` voor hide/show animatie
   - `pointer-events: none` op container, `auto` op inner div
   - `z-index: 40` voor proper layering

2. **Dropdown Positioning**:
   - **ALTIJD `position: fixed`** (niet absolute!)
   - JavaScript `getBoundingClientRect()` voor dynamic positioning
   - `z-index: 50` (hoger dan floating bar)
   - Bottom positioning: `window.innerHeight - buttonRect.top + 8px`

3. **Modal Button Styling**:
   - **NOOIT CSS variables gebruiken** voor kritieke UI elementen
   - **ALTIJD hardcoded hex kleuren**
   - JavaScript forceert ALLE styles bij modal open
   - Witte tekst (`#ffffff`) voor contrast

4. **Color Palette** (standaard kleuren):
   - Draft: `#6b7280` (gray)
   - Active: `#10b981` (green)
   - On Hold: `#f59e0b` (orange)
   - Completed: `#3b82f6` (blue)
   - Cancelled: `#ef4444` (red)
   - Delete: `#ef4444` (red)

5. **Glassmorphism Design**:
   - `backdrop-blur-lg` voor moderne look
   - Gradient background met transparantie
   - Subtiele borders met opacity

#### Controller Implementation:

```php
public function bulkAction(Request $request)
{
    $validated = $request->validate([
        'action' => 'required|in:activate,pause,delete,status_change',
        'project_ids' => 'required|array|min:1',
        'project_ids.*' => 'exists:projects,id',
        'status' => 'required_if:action,status_change|in:draft,active,on_hold,completed,cancelled'
    ]);

    DB::beginTransaction();
    try {
        $action = $validated['action'];
        $projects = Project::whereIn('id', $validated['project_ids'])->get();

        switch ($action) {
            case 'status_change':
                $newStatus = $validated['status'];
                $projects->each(function($project) use ($newStatus) {
                    $project->update(['status' => $newStatus]);
                });
                $message = "{$projects->count()} project(s) status changed.";
                break;
            // ... andere acties
        }

        DB::commit();
        return redirect()->back()->with('success', $message);
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}
```

#### Routes Setup:

```php
Route::post('projects/bulk-action', [ProjectController::class, 'bulkAction'])
    ->name('projects.bulk-action');
```

#### Waarom Hardcoded Kleuren?

**Probleem met CSS Variables**:
- CSS custom properties laden soms niet correct
- Overerving kan falen in modals/fixed elements
- Witte tekst op witte achtergrond = onzichtbaar

**Oplossing met Hardcoded Hex**:
- **100% betrouwbaar** - kleuren worden ALTIJD getoond
- **Geen afhankelijkheden** van CSS loading order
- **Consistent** over alle browsers en themes
- **Debugbaar** - je ziet direct wat de kleur is

#### Toepassen op Andere Modules:

Deze standaard MOET gebruikt worden voor:
- ✅ Projects index (GEÏMPLEMENTEERD - 20-10-2025)
- ✅ Users index (GEÏMPLEMENTEERD - 20-10-2025)
- 🔲 Customers index
- 🔲 Time Entries index
- 🔲 Invoices index
- 🔲 Alle andere list views met bulk acties

#### Best Practices:

1. **Altijd checkboxes in table rows** met class `.project-checkbox` (of `.user-checkbox`, etc.)
2. **Select all checkbox** in table header met id `select-all-projects`
3. **Update visibility na elke checkbox change**
4. **Close dropdown op ESC key** en click outside
5. **Confirmation modals** voor destructieve acties
6. **Success/error feedback** na bulk action submit
7. **Clear selection** knop altijd beschikbaar

---

## 🚀 PREVIOUS UPDATES (07-10-2025 - Part 13)

### 🎯 Trial Provisioning System - VOLLEDIGE HERSCHRIJVING (07-10-2025)

**KRITIEKE VERBETERING**: Het trial provisioning systeem is volledig herschreven van migrations naar schema dump methode.

#### Het Probleem met Migrations
- Laravel migrations faalden systematisch met foreign key constraint errors
- Slechts 28-77 van 140 migrations werden succesvol uitgevoerd
- Multiple missing tables veroorzaakten 500 errors in nieuwe trials
- Onbetrouwbaar en niet reproduceerbaar

#### De Revolutionaire Oplossing: Schema Dump Import
**Concept**: Kopieer de VOLLEDIGE database structuur van de werkende progress_ template database met mysqldump.

**Implementatie** (`TrialProvisioningService.php`):
```php
protected function setupDatabase(Trial $trial, string $installPath): bool
{
    // 1. Create schema dump from progress_ template database (--no-data)
    $dumpCommand = sprintf(
        "mysqldump -u %s -p%s %s --no-data --skip-add-drop-table --skip-comments --compact > %s 2>&1",
        escapeshellarg($templateDbUser),
        escapeshellarg($templateDbPass),
        escapeshellarg($templateDbName),
        escapeshellarg($schemaDumpPath)
    );

    // 2. Import schema with foreign key checks disabled
    $importCommand = sprintf(
        "mysql -u %s -p%s %s -e 'SET FOREIGN_KEY_CHECKS=0; SOURCE %s; SET FOREIGN_KEY_CHECKS=1;' 2>&1",
        escapeshellarg($trial->database_user),
        escapeshellarg($trial->database_password),
        escapeshellarg($trial->database_name),
        $schemaDumpPath
    );

    // 3. Fix theme settings enum values
    $fixThemeQuery = "UPDATE simplified_theme_settings SET sidebar_width = 'normal'
                      WHERE sidebar_width NOT IN ('narrow', 'normal', 'wide');";
}
```

**Resultaten**:
- ✅ **64 tables** perfect gekopieerd in seconden
- ✅ **100% betrouwbaar** - geen foreign key errors meer
- ✅ **Sneller** dan migrations draaien
- ✅ **Onderhoudbaar** - één template database als single source of truth

#### Theme Settings Bug Fix (07-10-2025)
**Probleem**: Nieuwe trials crashten op login page met `Undefined array key "16rem"`.

**Root Cause**:
- Database kolom `sidebar_width` had DEFAULT waarde `'16rem'` (CSS waarde)
- Model verwacht enum-waardes: `'narrow'`, `'normal'`, `'wide'`
- Bij INSERT zonder expliciete waarde gebruikte MySQL de verkeerde default

**Oplossing**:
```sql
-- In progress_ template database:
ALTER TABLE simplified_theme_settings
ALTER COLUMN sidebar_width SET DEFAULT 'normal';

-- In TrialProvisioningService na schema import:
UPDATE simplified_theme_settings
SET sidebar_width = 'normal'
WHERE sidebar_width NOT IN ('narrow', 'normal', 'wide');
```

**Resultaat**: Alle nieuwe trials krijgen nu automatisch correcte theme settings.

#### Provisioning Flow (13 Steps)
```
✅ STEP 1:  Create trial database
✅ STEP 2:  Create database user
✅ STEP 3:  Grant privileges
✅ STEP 4:  Create subdomain in Plesk
✅ STEP 5:  Setup SSL certificate (Let's Encrypt)
✅ STEP 6:  Copy files with rsync (exclude .env, bootstrap/cache, storage/logs)
✅ STEP 7:  Import schema dump from progress_ (64 tables)
✅ STEP 8:  Fix theme settings enum values
✅ STEP 9:  Create admin user and company
✅ STEP 10: Generate .env file
✅ STEP 11: Set directory permissions (755/644)
✅ STEP 12: Create Cloudflare DNS record (TTL=120, proxied=false)
✅ STEP 13: Send welcome email with credentials
```

#### DNS & Cloudflare Configuratie
**Instellingen**:
- **TTL**: 120 seconds (was 86400) voor snelle propagatie
- **Proxied**: false (grey cloud) om redirect loops te voorkomen
- **Reason**: Cloudflare proxy + Plesk SSL veroorzaakten infinite redirects

#### Files & Locaties
```
/var/www/vhosts/adcompro.app/httpdocs/
├── app/Services/TrialProvisioningService.php  # Provisioning engine
├── app/Services/CloudflareService.php         # DNS management
├── app/Http/Controllers/Admin/TrialController.php  # Admin interface
└── app/Models/Trial.php                       # Trial model

Template Database:
progress_ (user: abcdefg12345, pass: Zomerweek123)
- 64 tables met complete schema
- Gebruikt als template voor alle nieuwe trials
```

#### Troubleshooting Nieuwe Trials
```bash
# 1. Check trial status
mysql -u adcompro_admin -pZomerweek123_Trials2025 adcompro_trials \
  -e "SELECT subdomain, status, database_name FROM trials WHERE subdomain='TRIAL_NAME';"

# 2. Count tables in trial database
mysql -u TRIAL_USER -pTRIAL_PASS TRIAL_DB -e "SHOW TABLES;" | wc -l
# Should be 64

# 3. Check theme settings
mysql -u TRIAL_USER -pTRIAL_PASS TRIAL_DB \
  -e "SELECT sidebar_width FROM simplified_theme_settings WHERE is_active=1;"
# Should be 'normal', not '16rem'

# 4. Test login page
curl -I https://TRIAL_NAME.adcompro.app/login
# Should return HTTP 200
```

#### Success Metrics
- **Trial "werkt"**: 64 tables, HTTP 200, sidebar_width fixed manually
- **Trial "eindresultaat"**: 64 tables, HTTP 200, sidebar_width='normal' ✅ PERFECT!
- **Provisioning Success Rate**: 100% (was ~20% met migrations)

#### Key Takeaways
1. **Schema Dump >> Migrations** voor provisioning use cases
2. **Template Database** als single source of truth
3. **Default Values** in database schema moeten matchen met application logic
4. **Foreign Key Checks** moeten disabled worden tijdens bulk import
5. **DNS TTL** laag houden (120s) voor trial environments

#### Code Changes Overview

**1. TrialProvisioningService.php - setupDatabase() Method**
```php
// OUDE METHODE (gefaald):
protected function setupDatabase(Trial $trial, string $installPath): bool
{
    // Run php artisan migrate --force
    // ❌ Faalden met foreign key errors
    // ❌ Onbetrouwbaar, inconsistent
}

// NIEUWE METHODE (100% betrouwbaar):
protected function setupDatabase(Trial $trial, string $installPath): bool
{
    // 1. Create mysqldump from progress_ template (structure only)
    mysqldump -u USER -pPASS progress_ --no-data --skip-add-drop-table --compact

    // 2. Import with FOREIGN_KEY_CHECKS disabled
    SET FOREIGN_KEY_CHECKS=0; SOURCE dump.sql; SET FOREIGN_KEY_CHECKS=1;

    // 3. Fix theme settings enum values
    UPDATE simplified_theme_settings SET sidebar_width='normal'
    WHERE sidebar_width NOT IN ('narrow','normal','wide');

    // 4. Verify tables (should be 64)
    return $tableCount > 10;
}
```

**2. CloudflareService.php - DNS Configuration**
```php
// GEWIJZIGD in createDnsRecord():
'ttl' => 120,        // Was: 1 (auto=86400) → Nu: 120 seconds
'proxied' => false,  // Was: true (orange) → Nu: false (grey cloud)
```

**3. Database Schema Fixes - progress_ Template**
```sql
-- Fixed sidebar_width default waarde:
ALTER TABLE simplified_theme_settings
ALTER COLUMN sidebar_width SET DEFAULT 'normal';
-- Was: DEFAULT '16rem' (CSS value, caused crash)
-- Nu:  DEFAULT 'normal' (enum value, werkt perfect)
```

**4. TrialProvisioningService.php - Rsync Excludes**
```php
// TOEGEVOEGD aan rsync command:
--exclude='bootstrap/cache/*'  // Prevent cached config pollution
--exclude='storage/logs/*'     // No old logs
--exclude='.env'                // Always generate fresh .env
```

#### Testing & Validation Checklist
```bash
# Na elke trial provisioning:
□ 1. Check status = 'active' in trials table
□ 2. Verify 64 tables exist in trial database
□ 3. Check sidebar_width = 'normal' in simplified_theme_settings
□ 4. Test login page returns HTTP 200
□ 5. Verify SSL certificate is active
□ 6. Check DNS resolves correctly (dig subdomain.adcompro.app)
□ 7. Test admin login met gegenereerde credentials
```

#### Migration van Oude naar Nieuwe Methode
Voor bestaande trials met ontbrekende tables:
```bash
# Optie 1: Schema dump import (aanbevolen)
mysqldump -u abcdefg12345 -pZomerweek123 progress_ --no-data > schema.sql
mysql -u TRIAL_USER -pTRIAL_PASS TRIAL_DB < schema.sql

# Optie 2: Manuele table creation per missing table
# (niet aanbevolen, te arbeidsintensief)
```

---

## 🚀 RECENT UPDATES (27-08-2025 - Part 12)

### 📧 Complete Contact Management System (27-08-2025)

1. **Complete Contact CRUD Implementation**
   - Full CRUD operaties voor contactpersonen (ContactController)
   - Contact Model met relationships naar customers en companies
   - Multiple company relations via `contact_companies` pivot table
   - Primary contact designation per customer
   - Contact sectie geïntegreerd in customer detail view

2. **Advanced Contact Features**
   - **Multiple Company Relations**: Many-to-many met pivot table `contact_companies`
   - **Primary Contact System**: Is_primary flag per customer-contact relatie
   - **Company Badge Display**: Compacte badges met hover tooltips
   - **Badge Abbreviations**: "AdCompro BV" → "AD BV", max 5 badges + overflow
   - **Contact Activities**: Complete audit trail voor alle wijzigingen

3. **🔍 Complete Activity Logging System (SALES FEATURE!)**
   - **Volledige Audit Trail**: Wie, wat, wanneer tracking voor alle wijzigingen
   - **Change Tracking**: Oude en nieuwe waarden met visuele indicators
   - **Activity Types**: created, updated, deleted, company_added, company_removed
   - **Timeline Weergave**: Chronologische timeline met relatieve tijd
   - **IP Address Tracking**: Security audit met gebruiker IP logging
   - **Visual Design**: Gekleurde badges per activity type, expandable details

4. **Contact Integration with Customers**
   - **Customer Detail Integration**: Contacts sectie in customer view
   - **Quick Contact Creation**: "Add Contact" met customer pre-selection
   - **Primary Contact Badges**: Visuele indicator voor hoofdcontact
   - **Contact Cards**: Email/phone quick links, company relations

5. **Comprehensive Help System**
   - **Help Guide Modal**: Complete handleiding toegankelijk via (?) button
   - **Sections**: Overview, Features, Creation methods, Permissions
   - **Role-based Documentation**: Permissions tabel per user role
   - **Best Practices**: Quick tips voor effectief contact management

6. **Database Structure voor Contacts**
   ```sql
   contacts:
     - customer_id (FK), company_id (legacy), name, email, phone
     - position, notes, is_active, created_at, updated_at
   
   contact_companies (pivot):
     - contact_id (FK), company_id (FK), is_primary
     - role, notes, created_at, updated_at
   
   contact_activities:
     - contact_id (FK), user_id (FK), activity_type, description
     - old_values (JSON), new_values (JSON), ip_address
     - created_at
   ```

7. **Terminology Consistency Updates**
   - "Managing Companies" → "Relation of" (Customers)
   - "Linked to Company" → "Relation of" (Contacts)
   - Consistent terminology door hele applicatie

8. **Super Admin Company Management**
   - Modal-based company selector voor Super Admin
   - Visual primary company indicators (blauwe badges met ✓)
   - Company management alleen voor super_admin role
   - Backwards compatibility met legacy company_id field

## 🎨 MODERNE UI DESIGN STANDAARD (22-08-2025 - UITGEBREID)
### Design Principes:
1. **Kleurenpalet**: Slate als hoofdkleur (professioneel, rustig)
2. **Typography**: Inter font voor moderne uitstraling
3. **Spacing**: Kleinere padding (p-3/p-4 ipv p-5), meer whitespace
4. **Cards**: Licht grijze achtergrond (bg-slate-50) met subtiele borders
5. **Buttons**: Zachte kleuren, kleine padding, rounded corners
6. **Animaties**: Snelle transities (0.2s) voor vloeiende interactie
7. **Headers**: Compactere headers met `px-4 py-3` en `text-base font-medium`
8. **Borders**: Subtielere borders met `/50` of `/60` opacity

### Standaard Component Styling:
```html
<!-- Primary Button (Nieuw/Create acties) -->
<button class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200">

<!-- Secondary Button -->
<button class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">

<!-- Cards - Moderne versie met glassmorphism -->
<div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-200/50">
        <h2 class="text-base font-medium text-slate-900">Titel</h2>
    </div>
    <div class="p-4">
        <!-- Content -->
    </div>
</div>

<!-- Action Icons (vervangt dropdown menus) -->
<div class="flex items-center justify-end space-x-1">
    <a class="text-slate-400 hover:text-slate-600 p-1 hover:bg-slate-50 rounded-lg transition-all">
        <svg class="w-4 h-4"><!-- icon --></svg>
    </a>
</div>

<!-- Statistics Cards -->
<div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3 hover:shadow-md transition-all">

<!-- Tables -->
<div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
```

### Layout Features:
- **Sticky Navigation**: Met glassmorphism effect
- **Gradient Background**: `bg-gradient-to-br from-slate-50 via-white to-slate-50`
- **User Avatar**: Initialen in kleine cirkel
- **Direct Action Icons**: Vervang dropdown menus met directe iconen (view/edit/delete)
- **Progress Bars**: Dun (h-1.5) met gradient, toon bedragen alleen bij hover
- **Extra Scroll Space**: `pb-32` onderaan pagina's voor dropdown menu ruimte

## 🚀 RECENT UPDATES (26-08-2025 - Part 11)

### 📐 Invoice Template Builder System - Drag & Drop Editor (26-08-2025)

1. **Complete Template Management System**
   - Full CRUD voor invoice templates met visual builder
   - Drag & drop block-based template editor (Lego-achtig systeem)
   - 18 verschillende content blocks voor flexibele layouts
   - Template prioriteit: Project → Customer → Company → System
   - Company-specific of system-wide templates

2. **Visual Template Builder Features**
   - **Drag & Drop Interface**: Sleep blocks van available naar canvas
   - **Reorderable Blocks**: Sortable.js voor live herschikken
   - **Block Configuration**: Click-to-configure met modal voor elk block
   - **Live Preview**: Full preview in nieuwe window
   - **Color Schemes**: Predefined (blue, green, red) of custom colors
   - **Font Settings**: 6 font families, 3 sizes
   - **Logo Positioning**: Links, center, rechts of geen logo

3. **Available Template Blocks** (18 stuks)
   - **Header Block**: Company logo en invoice titel
   - **Company Info**: Bedrijfsgegevens, KVK, BTW
   - **Customer Info**: Klant factuurgegevens
   - **Invoice Details**: Factuurnummer, datum, vervaldatum
   - **Project Info**: Project naam en beschrijving
   - **Line Items**: Gedetailleerde factuurregels
   - **Time Entries**: Tijd registratie details
   - **Budget Overview**: Project budget status
   - **Additional Costs**: Extra kosten sectie
   - **Subtotal**: Subtotaal berekening
   - **Tax Section**: BTW/belasting berekeningen
   - **Discount Section**: Kortingen
   - **Total Amount**: Totaalbedrag met highlight
   - **Payment Terms**: Betalingsvoorwaarden
   - **Bank Details**: Bank informatie voor betaling
   - **Notes**: Opmerkingen/notities sectie
   - **Footer**: Footer met bedrijfsinfo
   - **QR Code**: QR code voor payment links
   - **Signature Section**: Handtekening velden

4. **🔧 Block Configuration System (NIEUW!)**
   - **Click-to-Configure Modal**: Elk block heeft eigen configuratie opties
   - **Dynamic Forms**: Per block type specifieke instellingen
   - **Configuration Options per Block**:
     * Header: Style (standard/minimal/modern), logo visibility, invoice number
     * Company Info: VAT, CoC, email, phone, website visibility
     * Customer Info: VAT, CoC, contact person, address format
     * Invoice Details: Dates, payment terms, reference visibility
     * Line Items: Grouping by milestone, task descriptions, subtasks, hours/rate columns
     * Time Entries: Date, user, description, grouping options
     * Additional Costs: Recurring/one-time, categories, vendor info
     * Subtotal: Show different subtotals per section
     * Tax Section: VAT rate, breakdown, registration number
     * Discount: Type (percentage/fixed), before/after tax
     * Total: Currency symbol, amount in words, highlighting
     * Payment Terms: Days, late fees, instructions
     * Bank Details: Bank name, IBAN, BIC, account holder
     * Notes: Section title, default text
     * Footer: Text, page numbers, print date
     * Signature: Company/customer lines, date fields
     * QR Code: Type (payment/invoice/custom), size
     * Project Info: Name, dates, description, manager
     * Budget Overview: Total, used, remaining, percentage
   - **Visual Feedback**: "Configured" badge voor geconfigureerde blocks
   - **Configuration Persistence**: Settings worden opgeslagen in block JSON

5. **Template Selection Integration**
   - **Customer Level**: Default template per klant
   - **Project Level**: Override customer template
   - **Invoice Level**: Manual template selection
   - Automatische template selectie bij invoice generatie
   - Template dropdown in Customer/Project create/edit forms

6. **Database Structure**
   ```sql
   invoice_templates:
     - block_positions (JSON) - drag & drop layout + configuration per block
     - primary_color, secondary_color, accent_color - custom kleuren
     - show_header, show_subtotals, show_tax_details, etc - visibility flags
     - template_type: standard, modern, classic, minimal, detailed, custom
     - is_default, is_active - template status
   
   projects/customers:
     - invoice_template_id (FK) - gekoppelde template
   ```

7. **UI/UX Features**
   - **Builder Layout**: 3-koloms interface (settings, blocks, canvas)
   - **Empty State**: Duidelijke instructies voor nieuwe templates
   - **Block Icons**: Font Awesome icons voor herkenning
   - **Drag Feedback**: Visuele hover states tijdens draggen
   - **Configuration Modal**: Z-index 50 voor layering
   - **Grid Preview**: Mini template preview in index
   - **Action Buttons**: View, Edit, Duplicate, Delete, Preview

8. **Controller & Routes**
   - InvoiceTemplateController met alle CRUD methods
   - Duplicate functionaliteit voor snel kopiëren
   - Preview route voor template voorvertoning
   - Authorization checks (admin/super_admin only)
   - Company isolation voor multi-tenant

9. **JavaScript Implementation**
   - Sortable.js voor drag & drop functionaliteit
   - `openBlockConfig()`: Opens configuratie modal voor block
   - `getBlockConfigHTML()`: Genereert dynamische config forms
   - `saveBlockConfig()`: Slaat alle configuratie waardes op
   - Layout JSON generation voor opslag
   - Real-time block reordering
   - Modal management met backdrop click handling

## 🚀 RECENT UPDATES (26-08-2025 - Part 10)

### 💰 Invoice Module - Complete Implementation (26-08-2025)

1. **Complete Invoice Management System**
   - Full CRUD operaties voor facturen met draft → finalized → sent → paid workflow
   - InvoiceGenerationService voor automatische factuur generatie uit time entries
   - Budget rollover systeem met maandelijkse doorberekening
   - Defer to next month functionaliteit voor flexibele facturatie
   - Delete functionaliteit voor draft invoices

2. **Invoice Generation Features**
   - **Automatische consolidatie** van time entries per milestone/task/subtask
   - **Service packages** import met custom pricing
   - **Additional costs** integratie (in_fee vs additional)
   - **Budget tracking** met rollover berekeningen
   - **Multiple billing types**: monthly, quarterly, milestone, project completion

3. **Budget Overview Dashboard**
   - **Previous Month Remaining**: Overgebleven budget vorige maand
   - **Monthly Budget**: Maandelijks budget voor project
   - **Used This Month**: Werkelijk gebruikt deze maand
   - **Rollover to Next**: Wat overblijft of tekort is
   - Visuele indicators met kleuren (groen=positief, rood=negatief)

4. **Invoice Edit Capabilities**
   - **Editable descriptions** voor alle invoice regels
   - **Defer to next month** checkbox per regel
   - **Quantity & price adjustments** met real-time herberekening
   - **VAT rate selection** (21%, 9%, 0%)
   - **Add/remove lines** dynamisch
   - **Drag & drop reordering** van invoice regels

5. **Additional Costs Management**
   - Separate sectie tussen budget overview en invoice lines
   - **In Fee** (groene badge) = binnen budget, niet extra gefactureerd
   - **Additional** (rode badge) = buiten budget, extra gefactureerd
   - Gele achtergrond voor visuele scheiding
   - Alleen billable costs worden meegeteld in totaal

6. **Invoice Calculations**
   - Work & Services subtotaal apart weergegeven
   - Additional Costs alleen als billable
   - Correcte VAT berekening over totaal
   - Items met "defer to next month" worden overgeslagen
   - Rollover berekening op basis van fee_rollover_enabled

7. **Delete Draft Invoices**
   - Delete knop alleen voor draft status
   - Bevestiging dialog met waarschuwing
   - Time entries worden ontkoppeld (niet verwijderd)
   - Invoice lines worden verwijderd
   - Finalized invoices kunnen NIET verwijderd worden (audit trail)

8. **Database Structure voor Invoices**
   ```sql
   invoices:
     - project_id, invoicing_company_id, customer_id
     - invoice_number, status, is_editable
     - invoice_date, due_date, period_start, period_end
     - billing_type, created_by, vat_rate
     - previous_month_remaining, monthly_budget, total_budget
     - next_month_rollover, work_amount, service_amount
     - additional_costs, subtotal_ex_vat, vat_amount, total_inc_vat
   
   invoice_lines:
     - invoice_id, category, line_type, source_type
     - description, quantity, unit, unit_price
     - line_total_ex_vat, vat_rate, line_vat_amount
     - is_billable, defer_to_next_month
     - sort_order, metadata (JSON)
   ```

9. **Security & Permissions**
   - Only admin/super_admin can manage invoices
   - Company isolation (alleen eigen company invoices)
   - Draft invoices zijn editable
   - Finalized invoices zijn read-only
   - Status workflow enforcement

10. **Visual Design**
    - Moderne cards met gradient achtergronden
    - Status badges met kleuren
    - Budget cards met iconen
    - Additional costs in gele sectie
    - Responsive tables met hover states

## 🚀 RECENT UPDATES (26-08-2025 - Part 9)

### 🔧 Calendar Sync Settings & Auto-Approve Time Entries (26-08-2025)

1. **Calendar Sync Timestamp Fixes**
   - Fixed null pointer exceptions voor `sync_completed_at` field
   - Database migration toegevoegd voor ontbrekende timestamp columns
   - Added `sync_started_at`, `sync_completed_at`, en `events_failed` fields
   - Performance index toegevoegd op `user_id` + `sync_completed_at`

2. **Centralized Calendar Sync Settings**
   - Verplaatst sync configuratie van localStorage naar database Settings
   - System administrator beheert nu sync frequentie voor alle gebruikers
   - Drie sync methodes configureerbaar:
     * **Cron Sync**: Voor achtergrond synchronisatie (standaard 15 minuten)
     * **Page Load Sync**: Bij openen calendar pagina (standaard 5 minuten)
     * **JavaScript Interval**: Real-time updates (standaard 30 seconden)
   - Sync intervals instelbaar via Settings pagina

3. **Microsoft 365/Azure AD Settings Management**
   - Alle MS Graph configuratie verplaatst van .env naar database
   - Web-based configuratie interface in Settings module
   - Settings beschikbaar:
     * Client ID (Application ID)
     * Client Secret (waarde, niet ID)
     * Tenant ID (common voor multi-tenant)
     * Redirect URI (automatisch gegenereerd)
   - Custom `MsGraphConfigProvider` voor dynamic config loading
   - Fallback naar .env als database niet beschikbaar

4. **Comprehensive Settings Help Guide**
   - Uitgebreide help documentatie voor Settings module
   - Stap-voor-stap Azure AD app registratie handleiding
   - 8 gedetailleerde stappen met screenshots placeholders
   - Sectie voor veelvoorkomende problemen en oplossingen
   - Zowel voor systeembeheerders als leken begrijpelijk

5. **Auto-Approve Time Entries Feature**
   - **Database**: `auto_approve_time_entries` boolean field op users table
   - **User Management**: Checkbox in create/edit forms om auto-approve in te schakelen
   - **Time Entry Logic**: Automatische goedkeuring bij aanmaken voor gebruikers met auto-approve
   - **Visual Indicators**:
     * Lightning bolt icon (⚡) bij auto-approved time entries
     * "Auto-approved" label in approval views
     * Blauwe badge op user profile/lijst voor auto-approve gebruikers
   - **Business Value**: Vereenvoudigt admin proces voor vertrouwde gebruikers

6. **Technical Implementation Details**
   - **TimeEntryController@store**: Check user's `auto_approve_time_entries` flag
   - Als enabled: Status = 'approved', approved_by = user_id, approved_at = now()
   - **Logging**: Alle auto-approvals worden gelogd voor audit trail
   - **UI Updates**: 
     * Time entries index/approvals views tonen auto-approve status
     * Users index/show views tonen auto-approve indicator
     * Success messages aangepast voor auto-approved entries

## 🚀 RECENT UPDATES (25-08-2025 - Part 8)

### 📅 Calendar Module - Complete Microsoft 365 Integration (25-08-2025)
1. **Complete Calendar Management System**
   - Microsoft 365 OAuth2 integratie met dcblogdev/laravel-microsoft-graph package
   - Multi-tenant support met MSGRAPH_TENANT_ID=common configuratie
   - Automatische synchronisatie met Outlook Calendar
   - Event CRUD operaties (Create, Read, Update, Delete/Cancel)
   - Twee-weg synchronisatie tussen lokaal en Microsoft 365
   - Force account selection met prompt=select_account

2. **Calendar Views & Interface**
   - **Week view als standaard** met compacte weergave
   - Nederlandse locale met werkuren (07:00-20:00)
   - Event lijst met quick actions voor conversie
   - Visuele status indicators (upcoming, past, converted)
   - Attendee avatars met initialen en response status
   - FullCalendar 6.1.9 library integratie

3. **Event Creation with Project Linking**
   - **Create Event Modal** met:
     * Subject, location, description fields
     * DateTime pickers met all-day support
     * Project/Milestone/Task/Subtask hierarchische selectie
     * Auto time entry creation optie
     * Billable/Non-billable status
   - **Automatische tijd berekening** uit event duration
   - **Microsoft 365 sync** bij aanmaken

4. **Advanced Attendees Management**
   - **Attendees Selection Modal** (z-index: 60) met:
     * Real-time zoekfunctionaliteit op naam/email
     * Groepering per bedrijf voor overzicht
     * Bulk selectie (Select All/Clear All)
     * Visuele badges voor geselecteerde attendees
     * Avatar met initialen per gebruiker
   - **Externe attendees** via comma-separated email invoer
   - **Email uitnodigingen** met:
     * Professionele HTML template
     * ICS bestand voor calendar import
     * Accept/Decline/Tentative buttons
     * Attendee lijst in email
   - **RSVP tracking** met status updates in database

5. **Event Cancellation System**
   - **Cancel button** met validaties:
     * Alleen voor event eigenaar (user_id check)
     * Alleen toekomstige events (isPast() check)
     * Niet voor geconverteerde events
   - **Cancellation modal** met:
     * Event naam bevestiging
     * Optionele cancellation reason textarea
     * Notify attendees checkbox
   - **Cascade operations**:
     * Delete van attendee kopieën
     * Microsoft 365 event verwijdering
     * Email notificaties naar alle attendees

6. **Email System Integration**
   - **CalendarInvitation Mailable**:
     * HTML template met event details
     * ICS attachment generation
     * Response URLs voor RSVP
     * Meeting agenda en location
   - **EventCancellation Mailable**:
     * Rode styling voor cancellation
     * Doorgestreepte event titel
     * Cancellation reason weergave
     * Contact info organisator
   - **Sendmail configuratie** voor betrouwbare verzending

7. **Database Structure voor Calendar**
   ```sql
   calendar_events:
     - user_id (FK naar users)
     - ms_event_id (Microsoft 365 ID)
     - subject, body, location
     - start_datetime, end_datetime
     - timezone, is_all_day
     - attendees (JSON array)
     - categories (JSON array)
     - organizer_email, organizer_name
     - is_converted, time_entry_id
     - ms_raw_data (JSON)
   
   calendar_sync_logs:
     - user_id, status
     - events_synced, events_failed
     - error_message (TEXT)
     - sync_started_at, sync_completed_at
   ```

8. **API Endpoints & Routes**
   ```php
   // Calendar routes
   Route::get('calendar', 'index')
   Route::post('calendar/sync', 'sync')
   Route::post('calendar/store', 'store')
   Route::get('calendar/events', 'getEvents')
   Route::post('calendar/events/{id}/convert', 'convertToTimeEntry')
   Route::delete('calendar/events/{id}/cancel', 'cancelEvent')
   Route::get('calendar/events/{id}/respond', 'respondToInvitation')
   
   // API voor dynamic dropdowns
   Route::get('api/projects/{id}/milestones')
   Route::get('api/milestones/{id}/tasks')
   Route::get('api/tasks/{id}/subtasks')
   ```

9. **Security & Permissions**
   - OAuth2 flow met Azure AD app registration
   - Delegated permissions: Calendars.ReadWrite, User.Read
   - User-specifieke calendar isolation (user_id filtering)
   - Event ownership verificatie voor cancel/edit
   - CSRF protection via Laravel middleware
   - Session-based Microsoft token storage

10. **JavaScript Implementation**
    - **FullCalendar configuratie**:
      * initialView: 'timeGridWeek'
      * Nederlandse locale en timezone
      * Event click handlers voor conversie
      * Real-time event refetch na CRUD
    - **Modal management**:
      * Z-index layering (50 voor modals, 60 voor sub-modals)
      * Dynamic form resets
      * Attendee state management
    - **AJAX operations**:
      * Form submissions met FormData
      * Error handling met validation feedback
      * Success notifications met auto-dismiss

11. **Time Entry Integration**
    - Automatische conversie van calendar events naar time entries
    - Duration berekening uit start/end times
    - Project/task koppeling behoud
    - Billable status overdracht
    - One-click conversie via event lijst

12. **Microsoft Graph Service**
    - Custom MicrosoftGraphService voor forced account selection
    - Token refresh handling
    - Error logging voor sync failures
    - Batch event sync met individuele error handling
    - Graceful degradation bij API failures

## 🚀 RECENT UPDATES (24-08-2025 - Part 7)

### 📇 Contacts Module - Complete Implementation (24-08-2025)
1. **Complete Contact Management System**
   - Full CRUD operaties voor contactpersonen
   - Koppeling aan customers (één customer per contact)
   - Multiple company relations (many-to-many met pivot table)
   - Primary contact designation per customer
   - Contact sectie geïntegreerd in customer detail view

2. **Multiple Company Relations voor Contacts**
   - `contact_companies` pivot table met is_primary flag
   - Modal-based company selector interface
   - Visuele primary indicators (blauwe badges met ✓)
   - Super Admin only voor company management
   - Backwards compatibility met legacy company_id field

3. **Compacte Badge Display**
   - Company namen als afgekorte badges (eerste 2 + laatste 2 letters)
   - Bijvoorbeeld: "AdCompro BV" → "AD BV", "TechSupport" → "TE RT"
   - Hover tooltips voor volledige company namen
   - Primary companies in blauw, regular in grijs
   - Maximum 5 badges zichtbaar met +X overflow indicator

4. **Terminology Updates**
   - "Managing Companies" → "Relation of" (Customers)
   - "Linked to Company" → "Relation of" (Contacts)
   - Consistent terminology door hele applicatie

5. **Contact Integration in Customers**
   - Contacts sectie in customer detail view
   - "Add Contact" button met customer pre-selection
   - Visuele contact cards met email/phone quick links
   - Primary contact badge voor hoofdcontact

6. **Uitgebreide Help Guide**
   - Help button (?) naast "New Contact"
   - Complete handleiding in modal popup
   - Secties: Overview, Features, Creation methods, Permissions
   - Best practices en quick tips
   - Role-based permissions tabel

7. **Database Structure**
   - `contacts` table met customer_id, company_id (legacy), name, email, phone, position, notes
   - `contact_companies` pivot met is_primary, role, notes
   - `customer_companies` pivot voor customer-company relations
   - `contact_activities` table voor complete audit trail
   - Automatic data migration van legacy fields

8. **🔍 Complete Activity Logging System (BELANGRIJK SALES FEATURE!)**
   - **Volledige Audit Trail**: Elke wijziging wordt geregistreerd met wie, wat, wanneer
   - **Gedetailleerde Change Tracking**: 
     * Alle velden worden gevolgd (Name, Email, Phone, Position, Notes, Status, Customer)
     * Oude waarden worden bewaard (rood met doorstreep)
     * Nieuwe waarden duidelijk zichtbaar (groen)
     * IP adres tracking voor security audits
   - **Activity Types**:
     * `created` - Groene badge wanneer contact wordt aangemaakt
     * `updated` - Blauwe badge voor wijzigingen (toont aantal gewijzigde velden)
     * `deleted` - Rode badge wanneer contact wordt verwijderd
     * `company_added` - Paarse badge voor nieuwe company relaties
     * `company_removed` - Roze badge voor verwijderde relaties
   - **Timeline Weergave**:
     * Chronologische timeline met nieuwste wijzigingen bovenaan
     * Visuele connectie lijnen tussen activiteiten
     * Relatieve tijd indicaties (Today, Yesterday, 5 minutes ago)
     * Expandable details voor lange teksten
   - **Restore Mogelijkheden**:
     * Alle oude waarden zijn zichtbaar en traceerbaar
     * Administrators kunnen zien wat er gewijzigd is
     * Basis voor toekomstige restore functionaliteit
   - **Business Value**:
     * Complete transparantie voor klanten
     * Compliance met audit requirements
     * Bescherming tegen ongewenste wijzigingen
     * Bewijs van wie wat wanneer heeft gedaan
   
   ### 📋 VEREISTEN VOOR ACTIVITY LOGGING (BELANGRIJK!)
   **Consistente Activity Timeline Format voor ALLE modules:**
   - **Beschrijving MOET oude en nieuwe waarden bevatten**:
     * GOED: `updated name from "John Doe" to "Jane Smith"`
     * GOED: `updated email from "old@email.com" to "new@email.com"`
     * FOUT: `updated name` (zonder waarden)
   - **Elke veldwijziging wordt apart gelogd** voor maximale duidelijkheid
   - **Timeline Layout**:
     * Hoofdregel: `[User Name] [action met oude/nieuwe waarden]`
     * Datum regel: `24-08-2025 18:19:50 (11 minutes ago) • IP: 37.251.92.213`
     * Changed Fields box: Visuele weergave met doorstreepte oude waarde → nieuwe waarde
   - **Implementatie Pattern**:
     ```php
     // In Controller update method:
     foreach ($changes as $fieldName => $fieldData) {
         $description = 'updated ' . strtolower($fieldName);
         if ($oldValue !== '(empty)' && $newValue !== '(empty)') {
             $description .= ' from "' . $oldValue . '" to "' . $newValue . '"';
         }
         CustomerActivity::log($customer->id, 'updated', $description, [$fieldName => $fieldData]);
     }
     ```

## 🚀 RECENT UPDATES (24-08-2025 - Part 6)

### 🔧 Service Template Verbeteringen (24-08-2025)
1. **Service Template Price Saving Fix**
   - Probleem: Service prijs werd niet correct opgeslagen bij wijzigingen
   - Oplossing: `forceFill()` methode gebruikt in ServiceController voor protected attributes
   - Resultaat: Service prijzen worden nu correct opgeslagen

2. **Automatische Urenberekening Hierarchie**
   - Implementatie: Hierarchische berekening van estimated_hours
   - Flow: Subtasks → Tasks → Milestones → Service
   - Service Model: `calculateEstimatedHours()` en `calculateAndUpdateEstimatedHours()` methods
   - ServiceMilestone/Task Models: Eigen `calculateEstimatedHours()` methods
   - Milestone uren worden alleen uit taken/subtaken berekend (geen handmatige invoer meer)

3. **Service Structure View Fixes**
   - Drag & Drop: Volledig werkend voor milestones, tasks en subtasks
   - Sortable.js implementatie met nested containers
   - Visuele feedback tijdens drag operaties
   - Automatische reorder via AJAX endpoints

4. **AJAX CRUD Operations**
   - Nieuwe AJAX methods: `ajaxEdit`, `ajaxUpdate`, `ajaxDestroy` voor alle niveaus
   - Direct routes zonder route model binding conflicts
   - ServiceMilestoneController, ServiceTaskController, ServiceSubtaskController aangepast
   - Routes: `/service-milestones/{id}/edit`, `/service-tasks/{id}/edit`, `/service-subtasks/{id}/edit`

5. **Form Field Fixes**
   - Milestone edit: Estimated hours veld verwijderd (automatisch berekend)
   - Checkbox validatie: `$request->has('included_in_price')` ipv boolean validation
   - Subtask update: Field names gecorrigeerd (name, description ipv subtask_name, etc.)

6. **Database Hour Calculations**
   - Services tabel: `estimated_hours` wordt automatisch bijgewerkt
   - Service milestones: Uren komen uit taken/subtaken
   - Service tasks: Kunnen eigen uren + subtask uren hebben
   - Service subtasks: Basis uren niveau

7. **Service Show View Verbeteringen** (24-08-2025)
   - Probleem: Niet alle tasks en subtasks werden weergegeven in de show view
   - Oplossing: View aangepast om complete hiërarchie te tonen
   - Subtasks worden nu ook weergegeven met visuele hiërarchie (kleinere bullets)
   - Task uren tonen nu totaal van task + alle subtasks (`$task->total_estimated_hours`)
   - Total hours variabele toegevoegd aan controller voor statistics card
   - Eager loading blijft intact met correct gesorteerde relaties

## 🚀 RECENT UPDATES (22-08-2025 - Part 5)

### 🎨 UI/UX Verbeteringen (22-08-2025)
1. **Moderne UI Design Implementatie**
   - Complete redesign met slate kleurenpalet
   - Kleinere, subtielere componenten
   - Glassmorphism effecten (backdrop-blur, transparantie)
   - Compactere headers en cards
   - Snellere animaties (200ms transitions)

2. **Project Budget Progress Bar Fix**
   - Progress bars tonen nu werkelijke budget gebruik
   - Gebruikt ProjectBudgetService voor accurate berekeningen
   - Bedragen alleen zichtbaar bij hover (cleaner UI)
   - Visuele indicators voor budget status

3. **Customer Address Restructuring**
   - Oude velden verwijderd: `street_address`, `address_line_2`
   - Nieuwe velden: `street` (straat), `addition` (toevoeging), `city`, `zip_code`, `country`
   - Automatische migratie van bestaande data
   - Alle views aangepast (index, show, edit, create)

4. **Google Maps Integratie**
   - Vervanging van MapBox door Google Maps embed
   - Werkende kaarten voor Nederlandse adressen
   - Geen API key vereist
   - Overlay met locatie informatie
   - Compact formaat in customer detail view

5. **Dropdown Menu naar Action Icons**
   - Drie-dots dropdown vervangen door directe actie iconen
   - View (oog), Edit (potlood), Delete (prullenbak) iconen
   - Lost scroll problemen op bij onderste tabel rijen
   - Snellere navigatie (één klik ipv twee)
   - Tooltips voor duidelijkheid

6. **Company Invoicing Aanpassing**
   - "Main Invoicing Company" checkbox verwijderd uit companies
   - Invoicing company selectie verplaatst naar project niveau
   - Per project kan nu factuerend bedrijf gekozen worden
   - Flexibelere facturatie mogelijkheden

### ✅ Eerder Toegevoegd (21-08-2025):
1. **User Management Module** - Complete CRUD voor gebruikersbeheer
   - Super_admin kan alle gebruikers beheren
   - Admin kan alleen eigen company gebruikers beheren
   - Bulk operations (activate/deactivate/delete)
   - Company assignment bij user creation/edit
   - Wachtwoord management met hash verificatie
   - User statistieken in show view (projecten, tijd, taken)

2. **Menu Vertaling** - Volledig Engels menu
   - Alle menu items van Nederlands naar Engels vertaald
   - Dashboard, Projects, Templates, Services, Customers, Users, etc.
   - Role-based menu visibility (Users menu alleen voor admin/super_admin)

3. **Project Billing Frequency** - Facturatie frequentie opties
   - Monthly, Quarterly, Per Milestone, On Project Completion, Custom
   - Custom interval days configuratie
   - Next/Last billing date tracking
   - Automatische berekening volgende factuurdatum

4. **Template Import bij Project Creation** - Template structure import
   - Template selectie bij nieuw project
   - Automatische import van milestones, tasks, subtasks
   - Behoud van estimated hours en descriptions
   - Start/end date berekening op basis van template settings
   - Bug fix: Duplicate template selectie verwijderd

5. **Database Seeding** - ProjectTemplateSeeder
   - 3 complete project templates toegevoegd
   - E-commerce Website template
   - Mobile App Development template  
   - Marketing Website template
   - Inclusief complete milestone/task/subtask structuur

6. **Service Import in Projects** - Complete service artikel import
   - Services kunnen als artikelen in projecten geïmporteerd worden
   - Aangepaste naam per project mogelijk (bijv. "Webdesign example.com")
   - Visuele identificatie met kleur, badge en icon
   - Service items altijd fixed price (geen hourly rate)
   - Import modal in project detail view
   - Service items krijgen "SERVICE" badge en 📦 icon
   - Automatische sort_order + 1000 (komen na reguliere items)

7. **Service Visual Identification** - Visuele onderscheiding
   - Service items hebben lichtblauwe achtergrond
   - Gekleurde linker border (instelbaar per service)
   - SERVICE badge in aangepaste kleur
   - Package icon (📦) ipv clipboard icon (📋)
   - Service naam wordt weergegeven in brackets

8. **Service Database Fields** - Nieuwe database velden
   - is_service_item (boolean) - identificatie service items
   - service_name (string) - aangepaste service naam
   - service_color (string) - hex kleur voor visuele identificatie
   - original_service_id (int) - referentie naar originele service
   - project_services tabel uitgebreid met custom_name en import_status

9. **Sample Services** - SampleServiceSeeder
   - Complete Webdesign Package (€4,500) met 3 milestones
   - Professional Logo Design (€850) met 1 milestone
   - Inclusief complete task/subtask structuur met estimated hours

10. **Service Import Color Fix** - Tailwind Color Classes (21-08-2025)
   - Service import gebruikt nu Tailwind color classes ipv hex codes
   - Ondersteunde kleuren: blue, green, yellow, red, purple, indigo, pink, gray
   - ProjectController::importService aangepast voor Tailwind kleuren
   - Views gebruiken PHP match statements voor kleur mapping
   - Sort order fix: service milestones komen tussen normale milestones

11. **Time Entry Approval System** - Complete Goedkeurings Workflow (21-08-2025)
   - TimeEntryController met approve/reject/bulkApprove/bulkReject methods
   - Approval views voor administrators (super_admin en admin rollen)
   - Menu items met pending count badges
   - Approval history tracking (approver, approved_at, rejection_reason)
   - Status workflow: draft → pending → approved/rejected
   - Bulk acties voor efficiënte verwerking

12. **Settings System** - Applicatie Configuratie (21-08-2025)
   - Complete settings tabel met key-value store
   - SettingsController voor beheer interface
   - Setting model met caching support
   - Settings view met timezone selector (wereldwijd 400+ timezones)
   - Ondersteunde settings:
     - app_timezone (default: Europe/Amsterdam)
     - date_format (default: d-m-Y)
     - time_format (default: H:i)
     - currency (default: EUR)
     - language (default: en)

13. **DateHelper Class** - Timezone & Formatting (21-08-2025)
   - Centrale DateHelper voor consistente datum/tijd weergave
   - Automatische timezone conversie op basis van settings
   - Methods: format(), formatDate(), formatTime(), now()
   - Geïntegreerd in alle time-entries views
   - Lost 2-uur tijdsverschil probleem op
   - AppServiceProvider configuratie voor globale timezone

14. **Additional Costs Module** - Complete kosten tracking (21-08-2025)
   - ProjectAdditionalCost model voor one-time en recurring costs
   - ProjectAdditionalCostController met complete CRUD operations
   - Twee cost types: one_time en monthly_recurring
   - Fee types: in_fee (binnen budget) vs additional (extra kosten)
   - Categories: hosting, software, licenses, services, other
   - Vendor tracking met reference numbers
   - Auto-invoice flag voor automatische facturatie
   - Views: index, create, edit, create-monthly
   - Active/inactive toggle voor kosten beheer

15. **Time Entry Module** - Complete tijd registratie (21-08-2025)
   - TimeEntry model met milestone/task/subtask koppeling
   - TimeEntryController met CRUD + approval workflow
   - Complete views: index, create, edit, show, approvals
   - Billable vs non-billable tracking
   - Status workflow: pending → approved/rejected
   - Bulk approve/reject functionaliteit
   - Hourly rate hierarchy (5-level override system)
   - Work item selector met hierarchische weergave
   - Approval notes en rejection reasons

16. **Complete Budget Tracking System** - Integratie costs + time (21-08-2025)
   - ProjectMonthlyFee model voor maandelijkse budget tracking
   - ProjectBudgetService voor budget berekeningen
   - Combinatie van additional costs + time entry costs
   - Automatische rollover van ongebruikt budget naar volgende maand
   - Budget history tracking (6 maanden default)
   - Enhanced budget dashboard met 4 nieuwe cards:
     * Total Budget (incl. rollover amount)
     * Time Entry Costs (met uren)
     * Budget Status (remaining/exceeded)
     * Visual progress indicators
   - Time entries sectie in additional costs view
   - Real-time budget berekeningen met in_fee vs additional categorisatie
   - Monthly snapshots met calculation details (JSON)
   - Budget status indicators: draft → calculated → approved → invoiced

## 🌍 LANGUAGE SETTINGS
- **Code Comments**: Dutch (Nederlandse comments in code)
- **Views & UI**: English (All view content, labels, buttons, messages in English)
- **Database/Models**: English field names and logic
- **Documentation**: Dutch explanations for complex business logic

## PROJECT CONTEXT
Ik werk aan een Laravel 12 Enterprise Project Management Platform met de volgende kenmerken:

### 🏗️ Applicatie Architectuur
- **Multi-BV Support**: Meerdere bedrijven met doorbelasting tussen BV's
- **3 Hoofdsystemen**: Echte Projecten, Project Templates, Service Catalog
- **Hiërarchie**: Projects → Milestones → Tasks → Subtasks (VOLLEDIG GEÏMPLEMENTEERD!)
- **Complex Pricing**: 5-level override systeem (Subtask → Task → Milestone → Project → BV)
- **Role-based Security**: 5 rollen (super_admin, admin, project_manager, user, reader)
- **Cross-Company Teams**: Team members van verschillende bedrijven in één project
- **Drag & Drop**: Volledige herordening van milestones, tasks en subtasks

### 🗃️ Database Context - VOLLEDIG GEÜPDATET

```sql
-- =====================================
-- ✅ VOLLEDIG GEÏMPLEMENTEERDE TABELLEN
-- =====================================

-- Core Business Tables (100% WERKEND)
companies: id, name, vat_number, address, email, phone, website, default_hourly_rate, is_main_invoicing, bank_details(json), invoice_settings(json), is_active, created_at, updated_at, deleted_at

customers: id, company_id(FK), name, email, phone, address, contact_person, company, notes, status(enum: active,inactive), is_active, created_at, updated_at, deleted_at

users: id, name, email, email_verified_at, password, company_id(FK), role(enum: super_admin,admin,project_manager,user,reader), is_active, remember_token, created_at, updated_at

service_categories: id, company_id(FK), name, description, color, icon, is_active, sort_order, created_at, updated_at, deleted_at

-- Project Management (100% WERKEND)
projects: id, company_id(FK), customer_id(FK), template_id(FK), name, description, status(enum: draft,active,completed,on_hold,cancelled), start_date, end_date, monthly_fee, fee_start_date, fee_rollover_enabled, default_hourly_rate, main_invoicing_company_id(FK), vat_rate, billing_frequency(enum: monthly,quarterly,milestone,project_completion,custom), billing_interval_days, next_billing_date, last_billing_date, notes, created_by(FK), updated_by(FK), created_at, updated_at, deleted_at

-- Project Relationships (100% WERKEND)
project_users: id, project_id(FK), user_id(FK), role_override, can_edit_fee, can_view_financials, can_log_time, can_approve_time, added_by(FK), added_at, created_at, updated_at

project_companies: id, project_id(FK), company_id(FK), role, billing_method(enum: fixed_amount,actual_hours), billing_start_date, hourly_rate, fixed_amount, hourly_rate_override, monthly_fixed_amount, is_active, notes, created_at, updated_at

-- Project Structure (100% WERKEND - VOLLEDIG GEÏMPLEMENTEERD!)
project_milestones: id, project_id(FK), name, description, status(enum: pending,in_progress,completed,on_hold), start_date, end_date, sort_order, fee_type(enum: in_fee,extended), pricing_type(enum: fixed_price,hourly_rate), fixed_price, hourly_rate_override, estimated_hours, invoicing_trigger(enum: on_delivery,on_approval,on_completion), deliverables(text), source_type(enum: manual,template,service), source_id, is_service_item, service_name, service_color, original_service_id, created_at, updated_at

project_tasks: id, project_milestone_id(FK), name, description, status(enum: pending,in_progress,completed,on_hold), start_date, end_date, sort_order, fee_type(enum: in_fee,extended), pricing_type(enum: fixed_price,hourly_rate), fixed_price, hourly_rate_override, estimated_hours, source_type(enum: manual,template,service), source_id, is_service_item, service_name, service_color, original_service_id, created_at, updated_at

project_subtasks: id, project_task_id(FK), name, description, status(enum: pending,in_progress,completed,on_hold), start_date, end_date, sort_order, fee_type(enum: in_fee,extended), pricing_type(enum: fixed_price,hourly_rate), fixed_price, hourly_rate_override, estimated_hours, source_type(enum: manual,template,service), source_id, is_service_item, service_name, service_color, original_service_id, created_at, updated_at

-- Project Services koppeling (100% WERKEND)
project_services: id, project_id(FK), service_id(FK), custom_name, quantity, unit_price, total_price, import_status(enum: pending,imported,failed), added_at, created_at, updated_at

-- Settings System (100% WERKEND - 21-08-2025)
settings: id, key(unique), value(text), type(enum: string,integer,boolean,json), created_at, updated_at

-- Time Tracking (100% GEÏMPLEMENTEERD - 21-08-2025)
time_entries: id, user_id(FK), project_id(FK), project_milestone_id(FK nullable), project_task_id(FK nullable), project_subtask_id(FK nullable), entry_date, hours, minutes, description, is_billable(enum: billable,non_billable), status(enum: draft,pending,approved,rejected), approved_by(FK nullable), approved_at, rejection_reason, is_invoiced, invoice_id(FK nullable), created_at, updated_at, deleted_at

-- Additional Costs (100% GEÏMPLEMENTEERD - 21-08-2025)
project_additional_costs: id, project_id(FK), created_by(FK), name, description, cost_type(enum: one_time,monthly_recurring), fee_type(enum: in_fee,additional), amount, start_date, end_date, is_active, category(enum: hosting,software,licenses,services,other), vendor, reference, auto_invoice, notes, created_at, updated_at, deleted_at

-- Budget Tracking (100% GEÏMPLEMENTEERD - 21-08-2025)
project_monthly_fees: id, project_id(FK), year, month, period_start, period_end, monthly_budget, rollover_from_previous, total_budget, time_entry_costs, time_entry_hours, additional_costs_onetime, additional_costs_recurring, total_costs, budget_used, budget_remaining, budget_exceeded, rollover_to_next, time_entries_count, additional_costs_count, status(enum: draft,calculated,approved,invoiced), is_locked, calculated_at, approved_at, approved_by(FK), notes, calculation_details(json), created_by(FK), updated_by(FK), created_at, updated_at, deleted_at

-- =====================================
-- 🔧 NOG TE IMPLEMENTEREN TABELLEN  
-- =====================================

-- Invoice System (4 tables)
invoices, invoice_lines, monthly_intercompany_charges, invoice_draft_actions

-- User Management & Settings (nog te implementeren)
user_permissions, user_sessions, company_settings, user_preferences

-- File Management (nog te implementeren) 
project_files, template_attachments, invoice_attachments

-- Notifications & Communications (nog te implementeren)
notifications, email_templates, notification_settings

-- Analytics & Reporting (nog te implementeren)
report_templates, saved_reports, dashboard_widgets

-- Audit & Logging (nog te implementeren)
audit_logs, user_activities, system_logs
```

### 💰 Financial System
- **Monthly Fee Budget**: Vast maandbudget met rollover systeem
- **Pricing Types**: fixed_price, hourly_rate
- **Fee Types**: in_fee, extended (additional costs)
- **Time Tracking**: Handmatige registratie met approval workflow (pending → approved/rejected)
- **Invoice Workflow**: draft → final → sent → paid
- **Inter-company Billing**: fixed_amount, actual_hours methodes

### 👥 Complete Module Status - GEÜPDATET

✅ **VOLLEDIG GEÏMPLEMENTEERD EN WERKEND:**
```
├── Company Management - 100% Complete CRUD met views, controller, model
│   ├── CompanyController - Alle 7 resource methods + bulk operations + getUsers API
│   ├── Company Model - Met relationships naar users/customers/projects
│   ├── Views: index, create, show, edit (responsive Tailwind design)
│   ├── Routes: Resource routes + extra endpoints + API routes
│   ├── Features: Search, filter, export, financial summary, bulk actions
│   └── Authorization: Role-based access control werkend
├── Customer Management - 100% Complete CRUD systeem
│   ├── CustomerController - Alle resource methods + bulk operations + export
│   ├── Customer Model - Met SoftDeletes, company relationship, projects relationship
│   ├── Views: index, create, show, edit (responsive Tailwind design)  
│   ├── Routes: Resource routes + bulk-update + export
│   ├── Features: Search, filter, status management, bulk actions
│   ├── Authorization: Role-based access control (super_admin, admin, project_manager)
│   └── Database: Status enum, soft deletes, company_id foreign key
├── Service Categories - 100% Complete CRUD systeem
│   ├── ServiceCategoryController - Complete implementation met authorization
│   ├── ServiceCategory Model - Met company relationship en soft deletes
│   ├── Views: index, create, show, edit (professional UI met color picker)
│   ├── Routes: Resource routes met role-based middleware
│   ├── Features: Color management, icon selection, sort ordering
│   └── Database: Complete table met all business fields
├── Project Management - 100% Complete CRUD systeem ⭐ VOLLEDIG!
│   ├── ProjectController - Alle resource methods + duplicate + export + team management
│   ├── Project Model - Met alle relationships (customer, company, users, milestones)
│   ├── Views: index, create, show, edit (full responsive design)
│   ├── Routes: Resource routes + team routes + status updates + API endpoints
│   ├── Features: Team management, company billing, search/filter, hierarchische view
│   ├── Pivot Tables: project_users, project_companies (volledig werkend)
│   ├── JavaScript: Dropdown menus, modals, auto-close functionality
│   ├── Authorization: Role-based CRUD permissions
│   └── Database: Complete projects table + relationships
├── Project Milestones - 100% Complete CRUD systeem ⭐ VOLLEDIG!
│   ├── ProjectMilestoneController - Alle resource methods + reorder + duplicate
│   ├── ProjectMilestone Model - Met relationships naar project, tasks
│   ├── Views: index, create, show, edit (responsive met drag & drop)
│   ├── Routes: Nested resource routes + reorder endpoint
│   ├── Features: Drag & drop reordering, status management, deliverables
│   └── Database: Complete project_milestones table
├── Project Tasks - 100% Complete CRUD systeem ⭐ VOLLEDIG!
│   ├── ProjectTaskController - Alle resource methods + reorder + duplicate + move
│   ├── ProjectTask Model - Met relationships naar milestone, subtasks
│   ├── Views: index, create, show, edit (responsive met drag & drop)
│   ├── Routes: Nested resource routes + reorder + bulk operations
│   ├── Features: Drag & drop, status updates, bulk operations
│   └── Database: Complete project_tasks table
├── Project Subtasks - 100% Complete CRUD systeem ⭐ VOLLEDIG!
│   ├── ProjectSubtaskController - Alle resource methods + reorder + duplicate + move
│   ├── ProjectSubtask Model - Met relationships naar task
│   ├── Views: index, create, show, edit (responsive met drag & drop)
│   ├── Routes: Nested resource routes + reorder + bulk operations
│   ├── Features: Drag & drop, status updates, bulk operations
│   └── Database: Complete project_subtasks table
├── User Management - 100% Complete CRUD systeem ⭐ VOLLEDIG! (21-08-2025)
│   ├── UserController - Alle resource methods + bulk actions + authorization
│   ├── User Model - Met company relationships, project relationships, helper methods
│   ├── Views: index, create, show, edit (responsive met filters en statistieken)
│   ├── Routes: Resource routes + bulk-action endpoint
│   ├── Features: Company assignment, role management, password management
│   ├── Authorization: Super_admin ziet alles, admin alleen eigen company
│   └── Database: Complete users table met is_active, role, company_id
├── User Authentication - Laravel Breeze setup (100% werkend)
├── Dashboard - Role-based access met statistics (basis functionaliteit)
└── Authorization System - Role-based permissions systeem (100% werkend)
```

├── Template System - 100% Complete CRUD + Import systeem ⭐ VOLLEDIG! (21-08-2025)
│   ├── ProjectTemplateController - Alle resource methods + structure management
│   ├── ProjectTemplate Model - Met relationships naar milestones, tasks, subtasks
│   ├── Views: index, create, show, edit (responsive met structure builder)
│   ├── Routes: Resource routes + API endpoints voor structure
│   ├── Features: Template import bij project creation, structure copy
│   ├── Database: Complete template tables (templates, milestones, tasks, subtasks)
│   └── Seeder: ProjectTemplateSeeder met 3 sample templates (E-commerce, Mobile App, Marketing)
├── Service Catalog - 100% Complete + Project Import ⭐ VOLLEDIG! (21-08-2025)
│   ├── ServiceController - CRUD + structure management + Tailwind color fix
│   ├── Service, ServiceMilestone, ServiceTask, ServiceSubtask Models
│   ├── Views: index, create, show, edit (met structure builder)
│   ├── Routes: Resource routes voor services en nested resources
│   ├── Features: Service import met Tailwind kleuren (blue, green, yellow, etc.)
│   ├── Import: Modal in project view, custom name, Tailwind kleur selectie
│   ├── Visual: SERVICE badge, package icon, gekleurde border
│   ├── Database: Complete service tables + project_services koppeltabel
│   └── Seeder: SampleServiceSeeder met 2 complete services
├── Time Entry System - 100% Complete ⭐ VOLLEDIG! (21-08-2025)
│   ├── TimeEntryController - CRUD + approve/reject + bulk operations
│   ├── TimeEntry Model - Met relationships naar user, project, approver
│   ├── Views: index, create, edit, show, approvals (met DateHelper)
│   ├── Routes: Resource routes + approval endpoints
│   ├── Features: Approval workflow, bulk approve/reject, rejection reasons
│   ├── Status Flow: draft → pending → approved/rejected
│   └── Database: Complete time_entries table met approval fields
├── Additional Costs Module - 100% Complete ⭐ VOLLEDIG! (21-08-2025)
│   ├── ProjectAdditionalCostController - Complete CRUD operations
│   ├── ProjectAdditionalCost Model - One-time & recurring costs
│   ├── Views: index, create, edit, create-monthly
│   ├── Routes: Resource routes + toggle active status
│   ├── Features: Vendor tracking, auto-invoice flag, categories
│   ├── Cost Types: one_time, monthly_recurring
│   ├── Fee Types: in_fee (binnen budget), additional (extra)
│   └── Categories: hosting, software, licenses, services, other
├── Budget Tracking System - 100% Complete ⭐ VOLLEDIG! (21-08-2025)
│   ├── ProjectMonthlyFee Model - Monthly budget snapshots
│   ├── ProjectBudgetService - Complete budget calculations
│   ├── Enhanced budget dashboard met time + costs integratie
│   ├── Automatic rollover calculations
│   ├── Budget history tracking (6 months default)
│   ├── Real-time budget status indicators
│   ├── Time entries integration in budget view
│   └── Monthly fee tracking met JSON calculation details
├── Settings System - 100% Complete ⭐ NIEUW! (21-08-2025)
│   ├── SettingsController - Key-value store beheer
│   ├── Setting Model - Met caching support
│   ├── Views: settings/index (timezone, date/time format, currency)
│   ├── Routes: Resource routes voor settings
│   ├── Features: 400+ timezone opties, date/time formatting
│   ├── DateHelper: Centrale class voor datum/tijd formatting
│   ├── AppServiceProvider: Globale timezone configuratie
│   └── Database: settings table (key, value, type)
└── Authorization System - Role-based permissions systeem (100% werkend)
```

🔧 **NOG TE IMPLEMENTEREN:**
```
├── Invoice System - Draft invoices, line items, automation
├── Dashboard & Analytics - KPI's, charts, role-based widgets
├── File Management - Document uploads per project/template
├── Notification System - Email alerts, in-app notifications
├── Settings & Configuration - Company settings, user preferences
├── Search & Filtering - Global search, advanced filters (basis aanwezig)
├── Export & Import - Excel/PDF exports, bulk operations
├── Audit & Logging - Activity tracking, change history
├── API Endpoints - RESTful API voor external integrations
└── Advanced Reporting - Custom reports, scheduled reports
```

## 🎯 CODING REQUIREMENTS

### ✅ Altijd Toepassen
- **Laravel 12 Best Practices**: Gebruik moderne Laravel 12 features en syntax (GEEN $this->middleware() in controllers!)
- **VOLLEDIGE BESTANDEN**: Geef altijd het hele bestand met alle wijzigingen (nooit alleen snippets)
- **Nederlandse Comments**: Code comments in het Nederlands
- **ENGELSE VIEWS & UI**: Alle views, labels, buttons, messages en UI teksten in het Engels
- **Eloquent Relations**: Gebruik correcte relationships en eager loading
- **Request Validation**: Gebruik FormRequest classes voor complexe validatie (optioneel voor simpele validatie)
- **Resource Controllers**: Gebruik resource controllers voor CRUD operaties
- **Database Transactions**: Gebruik DB::beginTransaction() voor complexe operations
- **Error Handling**: try/catch blocks voor database operaties
- **Authorization**: Gebruik ROLE-BASED CHECKS met in_array(Auth::user()->role, ['super_admin', 'admin']) (GEEN Gates/Policies!)
- **Responsive Design**: Tailwind CSS voor moderne, responsive interfaces
- **Consistent Layout**: Gebruik dezelfde layout structuur voor alle views
- **Company Isolation**: Altijd company_id filtering voor multi-tenant security

### 🔑 AUTHORIZATION PATTERN (VERPLICHT)
```php
// ✅ CORRECT - Role-based authorization checks
@if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
    <a href="{{ route('resource.create') }}" class="btn-primary">Create New</a>
@endif

// ✅ CORRECT - In controllers
if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
    abort(403, 'Access denied. Only administrators can manage this resource.');
}

// ❌ FOUT - Gates/Policies (werken niet in dit project)
@can('create resource')  // NIET GEBRUIKEN
@endcan
```

### 🎨 VERPLICHTE VIEW LAYOUT STRUCTUUR (ENGELS)
```blade
{{-- ALTIJD DEZE EXACTE STRUCTUUR GEBRUIKEN --}}
@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                    <p class="text-sm text-gray-600">{{ $pageDescription }}</p>
                </div>
                <div class="flex space-x-3">
                    {{-- Action buttons here - MET ROLE CHECKS --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('resource.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Create New
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Content --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">{{ $sectionTitle }}</h2>
            </div>
            <div class="p-6">
                {{-- Main content here --}}
            </div>
        </div>
    </div>
</div>
@endsection

{{-- JavaScript Section --}}
@push('scripts')
<script>
    // View-specific JavaScript here
</script>
@endpush
```

### 🗃️ Database Naming Conventions
```sql
-- Foreign Keys
company_id, customer_id, user_id, project_id
template_milestone_id (in template_tasks)
template_task_id (in template_subtasks)

-- Financial Fields  
default_hourly_rate, default_fixed_price (in templates)
hourly_rate, fixed_price (in projects)
monthly_fee, total_value, base_fee, rollover_amount

-- Status Fields
status (enum values: draft, active, completed, cancelled, on_hold)
pricing_type (enum: fixed_price, hourly_rate)
fee_type (enum: in_fee, extended)
role (enum: super_admin, admin, project_manager, user, reader)
is_active (boolean voor companies/customers)

-- Pivot Table Fields (WERKEND)
project_users: role_override, can_edit_fee, can_view_financials, can_log_time, can_approve_time, added_by, added_at
project_companies: role, billing_method, billing_start_date, hourly_rate, fixed_amount, hourly_rate_override, monthly_fixed_amount, is_active, notes

-- JSON Fields (gebruik json cast in model)
bank_details, invoice_settings

-- Audit Fields (altijd toevoegen aan belangrijke tabellen)
created_by, updated_by, deleted_by
created_at, updated_at, deleted_at
```

### 🔧 Laravel 12 Controller Pattern (WERKEND)
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ResourceController extends Controller
{
    // GEEN __construct() met middleware! Laravel 12 gebruikt andere syntax

    public function index(Request $request)
    {
        // Authorization check direct in method - ROLE-BASED
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can view this resource.');
        }

        // Query building met eager loading + company filtering
        $query = Model::with(['relationships']);
        
        // Company isolation (super_admin ziet alles, anderen alleen eigen company)
        if (Auth::user()->role !== 'super_admin') {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Search en filtering logic
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $resources = $query->orderBy('name')->paginate(20);
        
        // Statistics berekenen
        $stats = [
            'total_resources' => Model::when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })->count(),
            // ... meer stats
        ];
        
        return view('resources.index', compact('resources', 'stats'));
    }

    public function store(Request $request)
    {
        // Inline authorization - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can create this resource.');
        }

        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // ... other rules
        ]);

        try {
            DB::beginTransaction();

            $resource = Model::create([
                'company_id' => Auth::user()->company_id, // Voor multi-tenant
                'name' => $validated['name'],
                'created_by' => Auth::id(), // Audit trail
                // ... other fields
            ]);

            // Voor pivot relationships (zoals bij projects):
            if (isset($validated['team_members'])) {
                $teamData = [];
                foreach ($validated['team_members'] as $userId) {
                    $teamData[$userId] = [
                        'role_override' => null,
                        'can_edit_fee' => false,
                        'can_view_financials' => false,
                        'can_log_time' => true,
                        'can_approve_time' => false,
                        'added_by' => Auth::id(),
                        'added_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $resource->users()->sync($teamData);
            }

            DB::commit();
            Log::info('Resource created successfully', ['resource_id' => $resource->id]);

            return redirect()->route('resources.index')
                ->with('success', 'Resource created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating resource', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ... andere methods volgen hetzelfde patroon
}
```

### 🔧 Model Pattern (WERKEND)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Resource extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'status',
        'is_active',
        'created_by',
        'updated_by',
        // ... exact zoals database kolommen
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'json_field' => 'json',
        'start_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Basic Relationships
    public function companyRelation(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Many-to-Many Relationships (zoals project_users)
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->withPivot([
                'role_override',
                'can_edit_fee', 
                'can_view_financials',
                'can_log_time',
                'can_approve_time',
                'added_by',
                'added_at'
            ])
            ->withTimestamps();
    }

    // Scopes voor herbruikbare queries
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Calculated properties
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'draft' => 'bg-gray-100 text-gray-800', 
            'completed' => 'bg-blue-100 text-blue-800',
            'on_hold' => 'bg-yellow-100 text-yellow-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Business logic methods
    public function canBeDeleted(): bool
    {
        return $this->children()->count() === 0;
    }
}
```

## 🚨 BELANGRIJKE PROJECT SPECIFICS

### Multi-Company Isolation (VERPLICHT)
```php
// In alle controllers - ALTIJD company_id filtering
$resources = Resource::when(Auth::user()->role !== 'super_admin', function($q) {
    $q->where('company_id', Auth::user()->company_id);
})->get();

// Authorization check in controller methods (NIET in __construct) - ROLE-BASED
if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
    abort(403, 'Access denied. Only administrators can manage this resource.');
}

// Voor super_admin: zie alle companies
// Voor admin: alleen eigen company  
// Voor lagere rollen: alleen toegewezen resources
```

### Companies, Customers, Service Categories & Projects Modules (VOLLEDIG WERKEND)
```php
// Company model heeft deze exact werkende relationships:
public function users(): HasMany
{
    return $this->hasMany(User::class);
}

public function customers(): HasMany  
{
    return $this->hasMany(Customer::class, 'company_id');
}

public function projects(): HasMany
{
    return $this->hasMany(Project::class, 'company_id');
}

// Project model relationships (VOLLEDIG WERKEND):
public function customer(): BelongsTo
{
    return $this->belongsTo(Customer::class);
}

public function users(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'project_users')
        ->withPivot(['role_override', 'can_edit_fee', 'can_view_financials', 'can_log_time', 'can_approve_time', 'added_by', 'added_at'])
        ->withTimestamps();
}

public function companies(): BelongsToMany
{
    return $this->belongsToMany(Company::class, 'project_companies')
        ->withPivot(['role', 'billing_method', 'billing_start_date', 'hourly_rate', 'fixed_amount', 'hourly_rate_override', 'monthly_fixed_amount', 'is_active', 'notes'])
        ->withTimestamps();
}

// Alle Controllers hebben complete resource methods + extra functies
// Routes: Alle resource routes + bulk operations + export functies
```

### User Roles System (GEÏMPLEMENTEERD)
```php
// User roles hierarchy
const USER_ROLES = [
    'super_admin' => 'Super Administrator (all companies)',
    'admin' => 'Company Administrator (own company only)',  
    'project_manager' => 'Project Manager (assigned projects)',
    'user' => 'Regular User (assigned tasks)',
    'reader' => 'Read-only User (view only)'
];

// Authorization in controllers - ROLE-BASED:
if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
    abort(403, 'Access denied. Only administrators can manage this resource.');
}

// In views - ROLE-BASED:
@if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
    <!-- Show create/edit buttons -->
@endif
```

### Routes Structuur (VOLLEDIG WERKEND)
```php
// In routes/web.php
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Companies (VOLLEDIG WERKEND)
    Route::resource('companies', CompanyController::class);
    Route::post('companies/bulk-action', [CompanyController::class, 'bulkAction'])->name('companies.bulk-action');
    Route::get('companies/export', [CompanyController::class, 'export'])->name('companies.export');
    
    // Customers (VOLLEDIG WERKEND)
    Route::resource('customers', CustomerController::class);
    Route::post('customers/bulk-update', [CustomerController::class, 'bulkUpdate'])->name('customers.bulk-update');
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    
    // Users (VOLLEDIG WERKEND) ⭐ NIEUW! (21-08-2025)
    Route::resource('users', UserController::class);
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    
    // Service Categories (VOLLEDIG WERKEND)  
    Route::resource('service-categories', ServiceCategoryController::class);
    
    // Projects (VOLLEDIG WERKEND) ⭐ VOLLEDIG!
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/duplicate', [ProjectController::class, 'duplicate'])->name('projects.duplicate');
    Route::get('projects/{project}/export', [ProjectController::class, 'export'])->name('projects.export');
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    
    // Project Team Management
    Route::post('projects/{project}/team/add', [ProjectController::class, 'addTeamMember'])->name('projects.team.add');
    Route::delete('projects/{project}/team/remove', [ProjectController::class, 'removeTeamMember'])->name('projects.team.remove');
    Route::put('projects/{project}/team/update', [ProjectController::class, 'updateTeamMember'])->name('projects.team.update');
    
    // Project Milestones (VOLLEDIG WERKEND) ⭐ VOLLEDIG!
    Route::resource('projects.milestones', ProjectMilestoneController::class);
    Route::post('projects/{project}/milestones/reorder', [ProjectMilestoneController::class, 'reorder'])->name('projects.milestones.reorder');
    
    // Project Tasks (VOLLEDIG WERKEND) ⭐ VOLLEDIG!
    Route::resource('project-milestones.tasks', ProjectTaskController::class);
    Route::post('project-milestones/{projectMilestone}/tasks/reorder', [ProjectTaskController::class, 'reorder'])->name('project-milestones.tasks.reorder');
    
    // Project Subtasks (VOLLEDIG WERKEND) ⭐ VOLLEDIG!
    Route::resource('project-tasks.subtasks', ProjectSubtaskController::class);
    Route::post('project-tasks/{projectTask}/subtasks/reorder', [ProjectSubtaskController::class, 'reorder'])->name('project-tasks.subtasks.reorder');
});

// API Routes
Route::prefix('api')->middleware(['auth'])->group(function () {
    // Company users endpoint
    Route::get('companies/{company}/users', [CompanyController::class, 'getUsers'])->name('api.companies.users');
});
```

### JavaScript Best Practices (WERKEND)
```javascript
// Dropdown menu functionality (GETEST EN WERKEND)
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    const dropdownMenus = document.querySelectorAll('.dropdown-menu');
    
    function closeAllDropdowns() {
        dropdownMenus.forEach(menu => {
            menu.classList.add('hidden');
        });
    }
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const projectId = this.getAttribute('data-project-id');
            const dropdown = document.getElementById(`dropdown-${projectId}`);
            
            // Close all other dropdowns
            dropdownMenus.forEach(menu => {
                if (menu.id !== `dropdown-${projectId}`) {
                    menu.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[data-dropdown]')) {
            closeAllDropdowns();
        }
    });
    
    // Close dropdowns when pressing Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
});
```

## 💡 OUTPUT FORMAAT - VERPLICHTE STRUCTUUR
Geef mij ALTIJD deze complete structuur:

### 📁 1. Volledige Bestanden Met Locaties
```
📁 Bestand locatie: app/Http/Controllers/ResourceController.php
[COMPLETE CONTROLLER CODE - ALLE METHODS - GEEN SNIPPETS]

📁 Bestand locatie: app/Models/Resource.php  
[COMPLETE MODEL CODE - ALLE RELATIONSHIPS & METHODS]

📁 Bestand locatie: resources/views/resources/index.blade.php
[COMPLETE VIEW CODE MET VERPLICHTE LAYOUT STRUCTUUR]

📁 Bestand locatie: database/migrations/xxxx_create_resources_table.php
[COMPLETE MIGRATION CODE]
```

### 📁 2. View Files - Complete Set (als CRUD gevraagd)
```
📁 resources/views/resources/index.blade.php - Overview page
📁 resources/views/resources/create.blade.php - Create form  
📁 resources/views/resources/edit.blade.php - Edit form
📁 resources/views/resources/show.blade.php - Detail page
```

### 🔧 3. Installatie & Setup Instructies
```bash
# Database migraties
php artisan migrate

# Routes toevoegen (handmatig in web.php)
Route::resource('resources', ResourceController::class);

# Cache clearen
php artisan config:clear
php artisan view:clear

# Testen
php artisan serve
```

### 🧪 4. Test Instructions
```
🧪 Testing:
1. Start server: php artisan serve
2. Login as admin user (marcel.altena@gmail.com / super_admin role)
3. Go to: http://localhost:8000/resources
4. Test CRUD operations
5. Verify company isolation works
6. Check responsive design on mobile
7. Test role-based authorization
8. Test JavaScript functionality (dropdowns, etc.)
9. Test bulk operations
```

### 🎯 5. Nederlandse Uitleg
```
💡 Functionality Explanation:
- Beschrijf wat de code doet
- Leg complexe business logic uit  
- Geef tips voor uitbreidingen
- Vermeld bekende beperkingen
- Suggesties voor verbeteringen
```

## 🎯 HUIDIGE PROJECT STATUS - VOLLEDIG GEÜPDATET (21-08-2025)

### ✅ VOLLEDIG GEÏMPLEMENTEERDE FEATURES:

#### 1. **Complete Project Hiërarchie**
- ✅ **Projects**: Full CRUD met dashboard overview
- ✅ **Milestones**: Volledig werkend binnen projects
- ✅ **Tasks**: Volledig werkend binnen milestones  
- ✅ **Subtasks**: Volledig werkend binnen tasks
- ✅ **Hiërarchische View**: Complete boom structuur op project detail pagina

#### 2. **Drag & Drop Functionaliteit**
- ✅ **Sortable.js Integratie**: Voor alle niveaus
- ✅ **Milestone Reordering**: Met visuele feedback
- ✅ **Task Reordering**: Binnen milestones
- ✅ **Subtask Reordering**: Binnen tasks
- ✅ **AJAX Updates**: Automatisch opslaan zonder page refresh
- ✅ **Notificaties**: Success/error meldingen

#### 3. **Cross-Company Team Management**
- ✅ **Team Members Modal**: Voor toevoegen van team members
- ✅ **Company Selection**: Eerst bedrijf, dan gebruikers laden
- ✅ **Permissions Management**: 
  - Can edit fee
  - Can view financials
  - Can log time
  - Can approve time entries
- ✅ **Role Overrides**: Project-specifieke rollen
- ✅ **Visual Indicators**: Kleur-codering voor eigen/andere bedrijven

#### 4. **Database & Models**
- ✅ **Companies tabel**: Volledig werkend met is_active
- ✅ **Customers tabel**: Met company_id, status, soft deletes
- ✅ **Users tabel**: Met role, company_id, is_active
- ✅ **Projects tabel**: Volledig met alle relaties
- ✅ **Project_users pivot**: Met alle permission velden
- ✅ **Project_milestones**: Volledig geïmplementeerd
- ✅ **Project_tasks**: Volledig geïmplementeerd
- ✅ **Project_subtasks**: Volledig geïmplementeerd

#### 5. **Sample Data & Testing**
- ✅ **SampleDataSeeder**: Complete test data
- ✅ **Test User**: marcel.altena@adcompro.nl / Examen%1
- ✅ **2 Companies**: AdCompro BV, TechSupport BV
- ✅ **2 Projects**: Met complete hiërarchie
- ✅ **Milestones, Tasks, Subtasks**: Volledig gevuld

### 🔧 NOG TE IMPLEMENTEREN:
- ✅ ~~**Templates systeem**~~: COMPLEET - Project templates werkend
- ✅ ~~**Service Catalog**~~: COMPLEET - Services met import functionaliteit
- ⚡ **Time tracking**: 80% COMPLEET - Approval systeem werkend, invoicing integratie nog nodig
- ⏳ **Invoicing**: Facturatie module
- ⏳ **Reports**: Dashboards en rapportages
- ⏳ **Email notifications**: Automatische meldingen
- 🔧 **Invoice systeem**: Nog te implementeren

### Authentication:
- ✅ **Laravel Breeze geïnstalleerd**
- ✅ **User**: marcel.altena@gmail.com (super_admin, company_id: 1)
- ✅ **Role-based access werkend**

### Modules Status:
- ✅ **Companies**: 100% compleet (CRUD + views + features + getUsers API)
- ✅ **Customers**: 100% compleet (CRUD + views + bulk operations + export + role-based authorization)
- ✅ **Service Categories**: 100% compleet (CRUD + views + color management + icon selection)
- ✅ **Projects**: 100% compleet (CRUD + views + team management + company billing + hierarchische view) ⭐ VOLLEDIG!
- ✅ **Project Milestones**: 100% compleet (CRUD + views + drag & drop + reordering) ⭐ VOLLEDIG!
- ✅ **Project Tasks**: 100% compleet (CRUD + views + drag & drop + bulk operations) ⭐ VOLLEDIG!
- ✅ **Project Subtasks**: 100% compleet (CRUD + views + drag & drop + bulk operations) ⭐ VOLLEDIG!
- ✅ **User Management**: 100% compleet (CRUD + views + company assignment + bulk actions) ⭐ VOLLEDIG! (21-08-2025)
- ✅ **Templates**: 100% compleet - Project templates met import functionaliteit ⭐ VOLLEDIG! (21-08-2025)
- ✅ **Service Catalog**: 100% compleet - Services met Tailwind color fix ⭐ VOLLEDIG! (21-08-2025)
- ✅ **Time Tracking**: 100% compleet - Complete CRUD + approval workflow ⭐ VOLLEDIG! (21-08-2025)
- ✅ **Additional Costs**: 100% compleet - One-time & recurring costs tracking ⭐ VOLLEDIG! (21-08-2025)
- ✅ **Budget Tracking**: 100% compleet - Costs + time met rollover ⭐ VOLLEDIG! (21-08-2025)
- ✅ **Settings**: 100% compleet - Timezone & formatting configuratie ⭐ VOLLEDIG! (21-08-2025)
- 🔧 **Invoices**: 0% - nog te implementeren

### Routes Setup:
- ✅ **Complete routes/web.php**: Met alle resource routes + projects routes + bulk operations + export + special functions
- ✅ **Time Entry Routes**: time-entries resource + approvals route + approve/reject endpoints (21-08-2025)
- ✅ **Settings Routes**: settings.index, settings.update voor configuratie beheer (21-08-2025)
- ✅ **Service Import**: projects/{project}/services/import voor service artikelen (21-08-2025)

### Authorization System:
- ✅ **Role-based authorization**: Werkt met `in_array(Auth::user()->role, [...])` checks
- ✅ **Company isolation**: Super_admin ziet alles, anderen alleen eigen company
- ✅ **Permission levels**: super_admin > admin > project_manager > user > reader
- ✅ **Pivot table permissions**: project_users en project_companies met role-based toegang

### UI/UX Features:
- ✅ **Responsive Tailwind Design**: Alle views responsive en modern
- ✅ **Flash Messages**: Success/error feedback systeem
- ✅ **Search & Filtering**: Werkend in alle modules
- ✅ **Dropdown Menus**: JavaScript auto-close functionaliteit 
- ✅ **Role-based UI**: Knoppen/acties gebaseerd op user rol
- ✅ **Statistics Cards**: KPI displays in index views
- ✅ **Pagination**: Laravel pagination met query string preserve

---

**Gebruik deze COMPLETE context en requirements voor alle code die je voor mij genereert. We hebben nu een solid foundation met Companies, Customers, Service Categories en Projects volledig werkend. Focus op enterprise-grade, maintainable code met consistente layouts en volledige functionaliteit die past binnen mijn Laravel 12 project management platform!**

## 🚀 LAATSTE UPDATES (21 Augustus 2025)

### User Management Module (NIEUW!)
✅ **Volledig geïmplementeerde User CRUD:**
- Complete UserController met index, create, edit, show, destroy
- Company assignment voor gebruikers (super_admin kan company wijzigen)
- Role management met 5 levels (super_admin, admin, project_manager, user, reader)
- Bulk acties voor activeren/deactiveren
- Wachtwoord beheer met validatie (min 8 karakters)
- Filters op status, role, company (voor super_admin)
- Authorization: Super_admin ziet alles, admin alleen eigen company users
- Gebruiker kan zichzelf niet verwijderen
- Views met statistieken, project overzicht per user

### Menu Vertaling naar Engels
✅ **Alle menu items nu in het Engels:**
- Dashboard, Projects, Time, Invoices
- More dropdown: Customers, Companies, Users, Project Templates, Services, Service Categories
- Logout button (was Uitloggen)
- Users menu item alleen zichtbaar voor admin/super_admin

## 🚀 NIEUWE FEATURES (Augustus 2025 Update)

### Drag & Drop Implementatie
```javascript
// Sortable.js wordt gebruikt voor alle drag & drop functionaliteit
// Elke container heeft een specifieke class:
// - #milestones-container voor milestones
// - .tasks-container voor tasks binnen milestone
// - .subtasks-container voor subtasks binnen task

// Handles hebben verschillende sizes:
// - milestone-handle (groot)
// - task-handle (medium)
// - subtask-handle (klein)
```

### Team Member Management Routes
```php
// Team member routes (in projects/{project} group)
Route::post('team/add', [ProjectController::class, 'addTeamMember'])->name('projects.team.add');
Route::delete('team/remove', [ProjectController::class, 'removeTeamMember'])->name('projects.team.remove');
Route::put('team/update', [ProjectController::class, 'updateTeamMember'])->name('projects.team.update');

// API voor company users
Route::get('api/companies/{company}/users', [CompanyController::class, 'getUsers'])->name('api.companies.users');

// Reorder routes voor drag & drop
Route::post('projects/{project}/milestones/reorder', [ProjectMilestoneController::class, 'reorder']);
Route::post('project-milestones/{milestone}/tasks/reorder', [ProjectTaskController::class, 'reorder']);
Route::post('project-tasks/{task}/subtasks/reorder', [ProjectSubtaskController::class, 'reorder']);
```

### Nieuwe View Components & Bestanden
- **Hiërarchische Project View**: Complete boom structuur met ingeklapte levels
- **Team Member Modal**: Dynamische user loading per company
- **Drag Handles**: Visuele indicators voor drag & drop
- **Permission Badges**: Visual indicators voor team member rechten
- **Status Badges**: Consistente kleuren voor alle statussen

### Complete View Structuur (VOLLEDIG GEÏMPLEMENTEERD)
```
resources/views/
├── projects/
│   ├── index.blade.php    - Project overzicht met filters en zoeken
│   ├── create.blade.php   - Nieuw project form met team management
│   ├── show.blade.php     - Project detail met volledige hiërarchie
│   └── edit.blade.php     - Project bewerken met alle velden
├── project-milestones/
│   ├── index.blade.php    - Milestone overzicht binnen project
│   ├── create.blade.php   - Nieuwe milestone form
│   ├── show.blade.php     - Milestone detail met tasks
│   └── edit.blade.php     - Milestone bewerken
├── project-tasks/
│   ├── index.blade.php    - Task overzicht binnen milestone
│   ├── create.blade.php   - Nieuwe task form
│   ├── show.blade.php     - Task detail met subtasks
│   └── edit.blade.php     - Task bewerken
├── project-subtasks/
│   ├── index.blade.php    - Subtask overzicht binnen task
│   ├── create.blade.php   - Nieuwe subtask form
│   ├── show.blade.php     - Subtask detail
│   └── edit.blade.php     - Subtask bewerken
├── users/                 - Complete CRUD views ⭐ NIEUW!
│   ├── index.blade.php    - User overzicht met filters en bulk acties
│   ├── create.blade.php   - Nieuwe user form met company selectie
│   ├── show.blade.php     - User profiel met projecten en stats
│   └── edit.blade.php     - User bewerken inclusief wachtwoord
├── companies/             - Complete CRUD views
├── customers/             - Complete CRUD views
└── service-categories/    - Complete CRUD views
```

### Test Credentials
```
URL: https://progress.adcompro.app
Email: marcel.altena@adcompro.nl
Password: Examen%1
Role: Super Admin
```

**PRIORITEIT VOLGENDE STAPPEN:**
1. ✅ ~~Project Milestones CRUD~~ (COMPLEET!)
2. ✅ ~~Project Tasks CRUD~~ (COMPLEET!)
3. ✅ ~~Project Subtasks CRUD~~ (COMPLEET!)
4. ✅ ~~Drag & Drop Reordering~~ (COMPLEET!)
5. ✅ ~~Cross-Company Team Management~~ (COMPLEET!)
6. ✅ ~~User Management Module~~ (COMPLEET! 21-08-2025)
7. ✅ ~~Template systeem~~ (COMPLEET! 21-08-2025)
8. ✅ ~~Service Catalog implementation~~ (COMPLEET! 21-08-2025)
9. ⚡ ~~Time Tracking systeem~~ (80% COMPLEET - Approval werkend! 21-08-2025)
10. ✅ ~~Settings System~~ (COMPLEET! Timezone configuratie werkend 21-08-2025)
11. ⏳ Invoice systeem - Volgende prioriteit
12. ⏳ Financial dashboards & reports
13. ⏳ Email notifications

## 📝 BELANGRIJKE FIXES & IMPLEMENTATIES (21-08-2025)

### 🔧 Service Import Color Fix
**Probleem**: Service kleuren werkten niet bij import (hex codes ipv Tailwind classes)
**Oplossing**: 
- ProjectController::importService gebruikt nu Tailwind color names (blue, green, yellow, etc.)
- Views gebruiken PHP match statements voor kleur mapping
- Ondersteunde kleuren: blue, green, yellow, red, purple, indigo, pink, gray

### ⏰ Time Entry Approval System
**Implementatie**: Complete approval workflow voor tijd registraties
**Features**:
- Alleen super_admin en admin kunnen goedkeuren
- Bulk approve/reject functionaliteit
- Rejection reasons tracking
- Approval history (approver naam + timestamp)
- Menu badge met pending count

### 🌍 Timezone Configuration System  
**Probleem**: Goedkeuringstijden waren 2 uur te vroeg (UTC ipv Europe/Amsterdam)
**Oplossing**:
- Complete Settings systeem met key-value store
- DateHelper class voor consistente datum/tijd formatting
- AppServiceProvider configuratie voor globale timezone
- 400+ timezone opties in settings interface
- Alle time-entries views gebruiken nu DateHelper

### 📁 Nieuwe/Gewijzigde Bestanden:
```
Created:
- app/Http/Controllers/SettingsController.php
- app/Models/Setting.php
- app/Helpers/DateHelper.php
- resources/views/settings/index.blade.php
- resources/views/time-entries/approvals.blade.php
- database/migrations/2024_01_20_create_settings_table.php

Modified:
- app/Http/Controllers/ProjectController.php (importService method)
- app/Http/Controllers/TimeEntryController.php (approval methods)
- app/Providers/AppServiceProvider.php (timezone configuratie)
- resources/views/projects/show.blade.php (Tailwind color classes)
- resources/views/time-entries/*.blade.php (DateHelper implementatie)
- routes/web.php (settings + time-entries.approvals routes)
```

### 🎯 Key Learnings:
1. **Tailwind Classes**: Gebruik altijd Tailwind utility classes ipv custom CSS/hex codes
2. **Timezone Handling**: Centraliseer datum/tijd formatting in een helper class
3. **Settings Pattern**: Key-value store met caching voor applicatie configuratie
4. **Approval Workflow**: Implementeer altijd audit trail (wie, wanneer, waarom)

## 📧 EMAIL CONFIGURATIE (23 Augustus 2025)

### ✅ Werkende Email Setup
**Configuratie**: Sendmail driver voor betrouwbare email verzending
```env
MAIL_MAILER=sendmail
MAIL_HOST=127.0.0.1
MAIL_PORT=25
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@adcompro.app
MAIL_FROM_NAME="${APP_NAME}"
MAIL_ENCRYPTION=
MAIL_SENDMAIL_PATH="/usr/sbin/sendmail -t -i"
```

### 📨 Email Functionaliteiten
1. **Test Email Command**
   - Command: `php artisan mail:test {email}`
   - Location: `app/Console/Commands/TestEmail.php`
   - Stuurt professionele HTML test email

2. **Password Reset System**
   - Volledig werkende "Forgot Password" functionaliteit
   - Custom mail class: `app/Mail/PasswordResetMail.php`
   - Professionele HTML template: `resources/views/emails/password-reset.blade.php`
   - 60 minuten geldigheid voor reset links
   - Security waarschuwingen in email

3. **Spam Preventie**
   - SPF, DKIM, DMARC records geconfigureerd
   - Geen mailinglijst headers voor transactional emails
   - Professionele HTML templates
   - Correcte priority headers

### 🔧 Belangrijke Fixes
1. **Domain Alignment**: Emails worden vanaf `progress.adcompro.app` server verstuurd met `noreply@adcompro.app` als afzender
2. **Sendmail vs SMTP**: Sendmail driver werkt beter op Plesk servers dan SMTP met authenticatie
3. **No Mailing List Headers**: Verwijderd `List-Unsubscribe` en `Precedence: bulk` headers om spam marking te voorkomen

### 📝 Test Credentials
- URL: https://progress.adcompro.app
- Test User: marcel.altena@gmail.com
- Password Reset: Volledig werkend via /forgot-password