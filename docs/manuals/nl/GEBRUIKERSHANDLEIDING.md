# 📘 Progress Platform - Gebruikershandleiding

**Versie:** 1.0 (08-11-2025)
**Platform:** Enterprise Project Management & Time Tracking
**URL:** https://progress.adcompro.app

---

## 📋 Inhoudsopgave

1. [Wat is Progress?](#wat-is-progress)
2. [Belangrijkste Functies](#belangrijkste-functies)
3. [Gebruikersrollen](#gebruikersrollen)
4. [Project Management](#project-management)
5. [Tijd Registratie](#tijd-registratie)
6. [Budget Tracking](#budget-tracking)
7. [Facturatie](#facturatie)
8. [Teamleader Integratie](#teamleader-integratie)
9. [Calendar Integratie](#calendar-integratie)
10. [Tips & Handigheden](#tips--handigheden)

---

## 🎯 Wat is Progress?

Progress is een **enterprise project management platform** speciaal ontwikkeld voor het beheren van:
- Retainer projecten met vaste maandbudgetten
- Urenregistratie en facturatie
- Budget tracking met rollover functionaliteit
- Multi-company samenwerking
- Teamleader CRM integratie

### Voor wie is het bedoeld?

**Bedrijven die:**
- Werken met vaste maandbudgetten (retainers)
- Tijd moeten bijhouden per project/klant
- Budget overschrijding willen voorkomen
- Transparantie willen in gefactureerde vs werkelijke uren
- Met meerdere bedrijven samenwerken (inter-company billing)

---

## ⚡ Belangrijkste Functies

### ✅ **1. Complete Project Hiërarchie**
Projecten zijn georganiseerd in 4 niveaus:

```
📁 Project (bijv. "Website Ontwikkeling")
  ├─ 📌 Milestone (bijv. "Design Fase")
  │   ├─ ✓ Task (bijv. "Homepage Ontwerp")
  │   │   └─ • Subtask (bijv. "Header Design")
```

**Waarom handig?**
- Overzichtelijk werk opdelen in behapbare stukken
- Tijd registreren op het juiste niveau
- Voortgang per fase bijhouden

---

### ✅ **2. Budget Tracking met Rollover**

**Hoe werkt het?**

**Maand 1 (Januari):**
- Budget: €5,000
- Gebruikt: €4,200
- **Rollover naar Februari: €800** ✅

**Maand 2 (Februari):**
- Budget: €5,000
- Rollover van Januari: €800
- **Totaal beschikbaar: €5,800**
- Gebruikt: €6,100
- **Tekort (Exceeded): €300** ⚠️

**Maand 3 (Maart):**
- Budget: €5,000
- Tekort van Februari: -€300
- **Totaal beschikbaar: €4,700**

**Voordelen:**
- 📊 **Transparantie** - Zie direct hoeveel budget er over is
- 🔄 **Flexibiliteit** - Ongebruikt budget rolt door naar volgende maand
- ⚠️ **Waarschuwingen** - Rode indicatoren bij overschrijding
- 📈 **Trend Analyse** - Zie of je consistent over/onder budget zit

**Waar zie je dit?**
- Project detail pagina → Budget Overview sectie
- Recurring Dashboard → Alle maanden in één overzicht
- Series Budget View → Jaar totalen per project serie

---

### ✅ **3. Tijd Registratie**

**Basis Flow:**
1. Klik **"Log Time Entry"**
2. Selecteer **Project**
3. Kies **Work Item** (Milestone/Task/Subtask)
4. Vul **Datum, Uren, Beschrijving** in
5. Kies **Billable** of **Non-billable**
6. **Submit** → Status: Pending

**Goedkeuring:**
- Admin/Manager ziet pending entries
- Kan **Approve** of **Reject** met reden
- Bulk approve voor meerdere entries tegelijk

**Auto-Approve:**
- Voor vertrouwde medewerkers kan auto-approve worden ingeschakeld
- Hun entries worden direct goedgekeurd (⚡ icon)
- Scheelt administratieve tijd

**Handige Feature: Modal Auto-Filter** 🆕
- Open "Log Time Entry" modal
- Selecteer een project
- **Achtergrond lijst filtert automatisch** op dat project
- Zie direct hoeveel uren er al geregistreerd zijn
- Modal blijft open zodat je context hebt

---

### ✅ **4. Doorschuiven naar Volgende Maand (Defer)**

**Situatie:**
Je hebt in augustus 10 uur gewerkt aan een klus, maar deze wordt pas in september gefactureerd.

**Hoe werkt het?**

**In Augustus overzicht:**
```
⚠️ NOT invoiced in Aug 2025
Moved to Sep 2025 (Invoice #INV-2025-0002)
```
→ **Blauwe warning box**: Deze uren tellen NIET mee voor augustus budget

**In September overzicht:**
```
→ Moved to: Sep 2025 (Invoice #INV-2025-0002)
```
→ **Oranje indicator**: Deze uren komen van augustus, tellen mee voor september

**Waarom handig?**
- ✅ Duidelijkheid welke uren in welke maand gefactureerd worden
- ✅ Voorkom verwarring over budget verbruik
- ✅ Transparantie richting klant

**Waar zie je dit?**
- Time Entries lijst
- Project time entries modal
- Invoice detail pagina

---

### ✅ **5. Additional Costs**

Naast uren kun je ook extra kosten toevoegen:

**Types:**
- **One-time**: Eenmalige kosten (bijv. hosting setup €150)
- **Monthly Recurring**: Maandelijkse kosten (bijv. licentie €50/maand)

**Fee Types:**
- **In Fee** (Groene badge): Komt uit het vaste maandbudget
- **Additional** (Rode badge): Extra kosten bovenop het budget

**Voorbeeld:**
```
Project: Website Hosting
├─ Hosting Server (In Fee): €45/maand
│  → Komt uit het €5,000 budget
└─ Extra Storage (Additional): €25/maand
   → Wordt extra gefactureerd (€5,025 totaal)
```

**Start/End Date:**
- Stel in vanaf wanneer tot wanneer de kosten lopen
- Automatisch berekend voor recurring costs

---

### ✅ **6. Project Templates**

**Wat zijn templates?**
Herbruikbare project structuren met voorgedefinieerde milestones, tasks en subtasks.

**Gebruik:**
1. Ga naar **Project Templates**
2. Kies een template (bijv. "E-commerce Website")
3. Zie de complete structuur met estimated hours
4. Bij nieuw project: Selecteer template
5. Klik **Import Structure**
6. Complete hierarchie wordt gekopieerd naar je project!

**Voordelen:**
- ⚡ **Tijdsbesparing** - Niet elke keer opnieuw opbouwen
- 📋 **Consistentie** - Zelfde structuur voor vergelijkbare projecten
- 📊 **Budget Estimates** - Uren inschatting per fase

**Voorbeelden:**
- E-commerce Website Template (45 uur)
- Mobile App Development (120 uur)
- Marketing Website (30 uur)

---

### ✅ **7. Service Catalog**

**Wat zijn services?**
Vaste "productjes" die je vaak verkoopt, zoals:
- Complete Webdesign Package (€4,500)
- Professional Logo Design (€850)
- SEO Optimalisatie Pakket (€2,200)

**Verschil met Templates:**
- **Templates**: Interne werkstructuur (hoe je het werk opbouwt)
- **Services**: Externe producten (wat je verkoopt aan klanten)

**Gebruik in Project:**
1. Open een project
2. Klik **"Import from Services"**
3. Selecteer een service
4. Kies kleur en custom naam
5. **Import** → Service verschijnt als milestone(s) in je project

**Visuele Identificatie:**
- 📦 Package icon (ipv 📋 clipboard)
- **SERVICE** badge in custom kleur
- Gekleurde linker border
- Lichtblauwe achtergrond

---

## 👥 Gebruikersrollen

### **Super Admin** 🔴
**Kan alles:**
- Alle bedrijven zien en beheren
- Alle projecten, klanten, gebruikers
- Systeeminstellingen wijzigen
- Inter-company billing beheren

**Gebruik voor:**
- Platform beheerder
- Hoofdadministratie

---

### **Admin** 🟠
**Kan alles binnen eigen bedrijf:**
- Projecten aanmaken en beheren
- Klanten beheren
- Gebruikers aanmaken (alleen eigen bedrijf)
- Budgets en facturatie inzien
- Time entries goedkeuren/afwijzen

**Gebruik voor:**
- Bedrijfs administrator
- Office manager

---

### **Project Manager** 🟡
**Kan projecten beheren:**
- Toegewezen projecten bekijken en bewerken
- Time entries goedkeuren
- Budget inzien (alleen toegewezen projecten)
- Team members toevoegen

**Gebruik voor:**
- Project leads
- Account managers

---

### **User** 🟢
**Kan tijd registreren:**
- Tijd registreren op toegewezen projecten
- Eigen entries bekijken
- Status van entries checken (pending/approved)

**Gebruik voor:**
- Developers
- Designers
- Content creators
- Alle medewerkers die uren schrijven

---

### **Reader** 🔵
**Kan alleen inzien:**
- Toegewezen projecten bekijken
- Geen wijzigingen mogelijk
- Geen tijd registreren

**Gebruik voor:**
- Klanten (read-only toegang)
- Management (rapportage)
- Stakeholders

---

## 📊 Project Management

### **Project Types**

**1. Regular Projects**
- Normale projecten met start/einde datum
- Vaste scope
- Éénmalig budget

**2. Retainer Projects**
- Doorlopende projecten met maandelijks budget
- Rollover van ongebruikt budget
- Vaak recurring (elke maand nieuw project)

**3. Recurring Project Series**
- Reeks van 12 projecten (één per maand)
- Budget rolt door over de serie
- Voorbeeld: "Retainer 2025" → 12 maandprojecten
- Elk project heet bijv. "Retainer januari 2025", "Retainer februari 2025"

---

### **Project Creation**

**Stap 1: Basis Informatie**
- Naam (bijv. "Website Redesign Klant X")
- Klant selecteren
- Status (Draft/Active/On Hold/Completed/Cancelled)
- Start/End Date

**Stap 2: Budget**
- **Monthly Fee**: Vast maandbudget (bijv. €5,000)
- **Fee Rollover Enabled**: ✓ = Ongebruikt budget rolt door
- **Default Hourly Rate**: Standaard uurtarief (bijv. €75)

**Stap 3: Billing**
- **Billing Frequency**:
  - Monthly (elke maand factureren)
  - Quarterly (per kwartaal)
  - Per Milestone (bij oplevering fase)
  - On Project Completion (aan het einde)
  - Custom (eigen interval)
- **Invoicing Company**: Welk bedrijf factureert dit project?

**Stap 4: Team**
- Voeg team members toe
- Stel permissions in:
  - Can edit fee (budget wijzigen)
  - Can view financials (budget inzien)
  - Can log time (tijd registreren)
  - Can approve time (entries goedkeuren)

**Stap 5: Structure (Optioneel)**
- Importeer vanaf Template
- Of importeer Service artikelen
- Of handmatig milestones/tasks toevoegen

---

### **Project Structure Beheer**

**Milestones Toevoegen:**
1. Open project
2. Scroll naar "Milestones" sectie
3. Klik **"Add Milestone"**
4. Vul in:
   - Naam (bijv. "Design Fase")
   - Beschrijving
   - Start/End Date
   - Fee Type (In Fee / Extended)
   - Pricing Type (Fixed Price / Hourly Rate)
   - Estimated Hours

**Tasks & Subtasks:**
- Zelfde proces, maar dan binnen een Milestone/Task
- Hierarchie: Milestone → Task → Subtask
- Drag & Drop om volgorde te wijzigen

**Status Management:**
- Pending (nog niet begonnen)
- In Progress (bezig)
- Completed (afgerond)
- On Hold (gepauzeerd)

---

## ⏱️ Tijd Registratie

### **Time Entry Maken**

**Methode 1: Via Time Entries Pagina**
1. Ga naar **Time** → **Time Entries**
2. Klik **"Log Time Entry"**
3. Modal opent
4. Selecteer **Project** → Achtergrond lijst filtert automatisch! 🆕
5. Selecteer **Work Item** (hierarchisch: Milestone → Task → Subtask)
6. Vul in:
   - **Date**: Wanneer gewerkt
   - **Hours/Minutes**: Hoeveel tijd
   - **Description**: Wat gedaan
   - **Billable**: Yes = factureerbaar, No = intern
7. **Submit**

**Methode 2: Via Calendar Event**
- Heb je een meeting in Outlook?
- Klik **"Convert to Time Entry"**
- Duration wordt automatisch berekend
- Project/task selecteren → Done!

**Methode 3: Via Import (Excel)**
- Upload Excel bestand met tijdregistraties
- Kolommen: Datum, Project, Bedrijfsnaam, Uren, Beschrijving
- Automatische matching met projecten
- Bulk import van honderden entries tegelijk

---

### **Time Entry Status Flow**

```
📝 Draft (Concept)
   ↓ (Submit)
⏳ Pending (Wacht op goedkeuring)
   ↓
   ├─ ✅ Approved (Goedgekeurd door manager)
   │     ↓
   │  📄 Ready for invoicing
   │
   └─ ❌ Rejected (Afgewezen met reden)
        ↓
     🔄 Kan opnieuw ingediend worden
```

---

### **Billable vs Non-Billable**

**Billable (Factureerbaar):**
- Groen ✓ icoon
- Wordt meegeteld in budget
- Komt op factuur
- Telt mee in "Hours Value"

**Non-Billable (Niet factureerbaar):**
- Grijs icoon
- Interne tijd / overhead
- NIET op factuur
- Telt NIET mee in budget berekening

**Voorbeelden Non-Billable:**
- Interne meetings
- Training/opleidingen
- Administratie
- Pre-sales gesprekken

**❗ BELANGRIJK:**
Alleen **Billable** entries tellen mee voor budget tracking!

---

### **Time Entry Filtering**

**Filters beschikbaar:**
- **Project**: Zie alleen entries van één project
- **User**: Zie entries van specifieke medewerker (admin only)
- **Status**: Draft/Pending/Approved/Rejected
- **Date Range**: Van/tot datum

**Auto-Submit:**
Filters worden automatisch toegepast bij wijziging (geen "Apply" knop nodig)

**Handige Tip:**
Bij **"Log Time Entry"** modal:
- Selecteer project → Achtergrond lijst filtert automatisch
- Zie direct hoeveel uren al geregistreerd zijn
- Voorkom dubbele entries
- Krijg context van het project

---

## 💰 Budget Tracking

### **Budget Overzicht Bekijken**

**Niveau 1: Project Detail Pagina**

Klik op een project → Budget Overview sectie toont:

```
┌─────────────────────────────────────────┐
│ 📊 Budget Overview (Current Month)      │
├─────────────────────────────────────────┤
│ Previous Month Remaining:  + €800       │  (Groen = positief)
│ Monthly Budget:             €5,000      │
│ Used This Month:           -€4,200      │
│ Rollover to Next:          + €600       │  (Dit blijft over)
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ 📋 Work & Services                      │
├─────────────────────────────────────────┤
│ Time Entries:     32.5h = €2,437.50     │
│ Service Items:    15h   = €1,125.00     │
│ Subtotal:         47.5h = €3,562.50     │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ 💸 Additional Costs                     │  (Gele achtergrond)
├─────────────────────────────────────────┤
│ In Fee:           €45.00 (Hosting)      │  (Groene badge)
│ Additional:       €25.00 (Extra)        │  (Rode badge)
└─────────────────────────────────────────┘

TOTAL USED: €3,632.50
REMAINING: €1,367.50 ✅
```

---

**Niveau 2: Recurring Dashboard**

Ga naar **Recurring Dashboard** → Overzicht van ALLE projecten:

```
┌────────────────────────────────────────────────────────────────┐
│ Project Serie       │ Jan    │ Feb    │ Mar    │ ... │ Total   │
├────────────────────────────────────────────────────────────────┤
│ Klant A Retainer    │ €5,000 │ €5,000 │ €5,000 │ ... │ €60,000 │
│                     │ €4,200 │ €5,800 │ €4,500 │ ... │ €58,000 │
│                     │ + €800 │ - €800 │ + €500 │ ... │ +€2,000 │
├────────────────────────────────────────────────────────────────┤
│ Klant B Retainer    │ ...    │ ...    │ ...    │ ... │ ...     │
└────────────────────────────────────────────────────────────────┘

Legenda per maand:
Regel 1: Budget (ZONDER rollover)
Regel 2: Spent (gebruikt)
Regel 3: Variance (verschil)

Total kolom:
- Budget: SOM van alle maandbudgets
- Spent: SOM van alle used amounts
- Variance: Totaal verschil (positief = under budget)
```

**Kleur Codering:**
- 🟢 **Groen**: Under budget (goed bezig!)
- 🟠 **Oranje**: Bijna op (75-90% gebruikt)
- 🔴 **Rood**: Over budget (overschrijding)

---

**Niveau 3: Series Budget View**

Voor één project serie → Klik **"View Totals"** knop:

```
┌──────────────────────────────────────────────────────────────────┐
│ Klant A Retainer 2025 - Complete Budget Breakdown                │
├──────────────────────────────────────────────────────────────────┤
│ Month  │ Base   │ Roll In │ Total   │ Hours │ Used    │ Roll Out│
├──────────────────────────────────────────────────────────────────┤
│ Jan    │ 5,000  │ 0       │ 5,000   │ 56h   │ 4,200   │ + 800   │
│ Feb    │ 5,000  │ 800     │ 5,800   │ 77h   │ 5,775   │ + 25    │
│ Mar    │ 5,000  │ 25      │ 5,025   │ 60h   │ 4,500   │ + 525   │
│ Apr    │ 5,000  │ 525     │ 5,525   │ 0h    │ 0       │ +5,525  │ (Geen project deze maand)
│ May    │ 5,000  │ 5,525   │ 10,525  │ 45h   │ 3,375   │ +7,150  │ (Rollover spring door!)
│ ...    │ ...    │ ...     │ ...     │ ...   │ ...     │ ...     │
├──────────────────────────────────────────────────────────────────┤
│ TOTAL  │ 60,000 │ -       │ 60,000  │ 540h  │ 58,000  │ +2,000  │
└──────────────────────────────────────────────────────────────────┘

Year Totals:
✅ Total Base Budget: €60,000
✅ Total Used: €58,000
✅ Total Remaining: €2,000
```

**Belangrijke Details:**
- **Roll In**: Budget dat overblijft van vorige maand
- **Roll Out**: Wat er overblijft voor volgende maand
- **Maanden zonder project**: Rollover blijft behouden (zie April)
- **OF Remaining OF Exceeded**: Niet beide tegelijk (voorkomt verwarring)

---

### **Budget Alerts**

**Automatische Waarschuwingen:**

**75% gebruikt** 🟠
```
⚠️ Budget Warning
You've used 75% of your monthly budget.
Remaining: €1,250 of €5,000
```

**90% gebruikt** 🔴
```
⚠️ Budget Alert!
You've used 90% of your monthly budget.
Remaining: €500 of €5,000
Consider reviewing upcoming work.
```

**100% overschreden** 🔴
```
❌ Budget Exceeded!
You've exceeded the monthly budget by €500.
Used: €5,500 of €5,000
This will be deducted from next month's rollover.
```

---

## 📄 Facturatie

### **Invoice Generation**

**Automatisch:**
1. Ga naar project
2. Klik **"Generate Invoice"**
3. Selecteer periode (deze maand / vorige maand / custom)
4. Systeem verzamelt automatisch:
   - Alle goedgekeurde time entries
   - Service items
   - Additional costs (alleen "Additional" type)
5. Budget overview wordt berekend
6. Preview → Aanpassen indien nodig → Finalize

**Handmatig:**
- Creëer lege invoice
- Voeg manual line items toe
- Vul bedragen in

---

### **Invoice Editing (Draft)**

**Wanneer invoice nog Draft status heeft:**

✅ **Kan je wijzigen:**
- Descriptions aanpassen
- Quantities en prices wijzigen
- Lines toevoegen/verwijderen
- VAT rate aanpassen (21% / 9% / 0%)
- **Defer to next month**: Vink aan om regel door te schuiven

✅ **Drag & Drop:**
- Sleep invoice lines om volgorde te wijzigen

✅ **Delete:**
- Draft invoices kunnen volledig verwijderd worden
- Time entries worden niet verwijderd, alleen ontkoppeld

❌ **Finalized invoices:**
- Kunnen NIET meer gewijzigd worden
- Kunnen NIET verwijderd worden
- Audit trail behouden

---

### **Invoice Template Builder**

**Custom Invoice Templates Maken:**

1. Ga naar **Invoice Templates**
2. Klik **"Create Template"**
3. **Drag & Drop Builder** opent:

**Available Blocks** (18 stuks):
- Header Block (logo + titel)
- Company Info (bedrijfsgegevens)
- Customer Info (klant gegevens)
- Invoice Details (nummer, datum)
- Project Info (project naam)
- Line Items (factuurregels)
- Time Entries (uren details)
- Budget Overview (budget status)
- Additional Costs (extra kosten)
- Subtotal
- Tax Section (BTW)
- Discount Section
- Total Amount (totaalbedrag)
- Payment Terms (betalingsvoorwaarden)
- Bank Details (bankgegevens)
- Notes (opmerkingen)
- Footer
- QR Code (betaallink)
- Signature Section

**Gebruik:**
1. Sleep blocks van **Available** naar **Canvas**
2. Herorden blocks met drag & drop
3. Klik op block om te **configureren**:
   - Welke velden tonen
   - Stijl (standard/minimal/modern)
   - Kleuren en formaten
4. **Preview** → **Save**

**Template Toewijzen:**
- Per Customer: Default template voor alle projecten van deze klant
- Per Project: Override customer template
- Per Invoice: Handmatige selectie

---

## 🔗 Teamleader Integratie

### **Wat wordt geïmporteerd?**

```
Teamleader CRM  →  Progress Platform
─────────────────────────────────────────
Companies       →  Customers
Contacts        →  Users
Projects        →  Projects
```

---

### **Import Workflow**

**Stap 1: Global Sync (Admin Only)**

1. Ga naar **Teamleader** menu
2. Klik **"Sync All Companies"** → Alle bedrijven worden naar database cache gehaald
3. Klik **"Sync All Contacts"** → Alle contacten naar cache
4. Klik **"Sync All Projects"** → Alle projecten naar cache

**Waarom cache?**
- ⚡ Sneller (geen API calls tijdens import)
- 🔍 Betere filtering en zoeken
- 📊 Statistics en previews
- 🔄 Offline gebruik mogelijk

---

**Stap 2: Select & Import**

**Companies → Customers:**
1. Klik **"Select & Import"** bij Companies
2. Zie overzicht van alle bedrijven met:
   - Naam, BTW nummer, adres, email
   - **Status** (Active/Inactive)
   - **Already Imported** badge (als al bestaat)
3. Selecteer bedrijven (Select All / handmatig)
4. Klik **"Import Selected"**
5. **Company_id blijft NULL** (handmatig toewijzen later)

**Contacts → Users:**
1. Klik **"Select & Import"** bij Users
2. **Filter**: "Hide customer contacts" ✓ (aanbevolen)
   - Standalone contacts = team members
   - Company contacts = klant contactpersonen
3. Selecteer contacts
4. Klik **"Import Selected"**
5. **GEEN emails verzonden** bij import
6. Random password gegenereerd
7. Email verified automatisch

**Projects → Projects:**
1. Klik **"Select & Import"** bij Projects
2. **Filter op Status**:
   - Active (3 projecten)
   - Done (108 projecten)
   - On Hold (5 projecten)
3. Selecteer projecten
4. Klik **"Import Selected"**
5. Budget amount wordt overgenomen
6. Status mapping: "done" → "completed"

---

**Stap 3: Contact Import voor Specifieke Klant**

1. Open een **Customer** detail pagina
2. Scroll naar **Contacts** sectie
3. Klik **"Import from Teamleader"**
4. Zie alle contacten die aan deze klant gekoppeld zijn
5. Selecteer relevante contactpersonen
6. Import → Contacten worden aan klant gekoppeld

---

### **Belangrijke Details**

**Company_ID:**
- Blijft **NULL** na import
- Handmatig toewijzen aan juiste company
- Voorkomt verkeerde koppelingen

**Budget Data:**
- `budget_amount` uit Teamleader
- Wordt `total_value` EN `monthly_fee` in Progress
- Check altijd of bedragen kloppen

**Email Notificaties:**
- **NOOIT** verzonden bij user import
- Users krijgen `email_verified_at = now()`
- Random password gegenereerd
- Admin moet wachtwoord handmatig resetten voor gebruiker

**Address Nesting:**
- Teamleader heeft extra nesting: `addresses[0]['address']['line_1']`
- Niet `addresses[0]['line_1']` (dit werkt NIET)
- System handelt dit automatisch af

---

## 📅 Calendar Integratie

### **Microsoft 365 / Outlook Sync**

**Setup (Admin):**
1. Ga naar **Settings**
2. Vul in:
   - **Client ID** (van Azure AD app)
   - **Client Secret**
   - **Tenant ID** (meestal "common")
3. Klik **"Connect Microsoft 365"**
4. Login met Microsoft account
5. Geef permissions voor Calendar.ReadWrite

**Sync Frequentie (Admin Settings):**
- **Cron Sync**: Elke X minuten (bijv. 15 min)
- **Page Load Sync**: Bij openen calendar pagina (bijv. 5 min)
- **JavaScript Interval**: Real-time updates (bijv. 30 sec)

---

### **Calendar Gebruiken**

**Week View:**
1. Ga naar **Calendar**
2. Zie je Outlook events
3. Automatische sync met Microsoft 365

**Event → Time Entry Conversie:**
1. Klik op event in lijst
2. Klik **"Convert to Time Entry"**
3. Duration automatisch berekend
4. Selecteer Project/Task
5. Kies Billable/Non-billable
6. **Convert** → Time entry aangemaakt!

**Event Aanmaken met Time Entry:**
1. Klik **"Create Event"**
2. Vul in:
   - Subject (onderwerp)
   - Location
   - Start/End tijd
   - **Project/Milestone/Task** selecteren
   - ✓ **Automatically create time entry**
3. Voeg attendees toe (colleagues + externe emails)
4. **Create** → Event in Outlook + Time Entry in Progress!

**Attendee Management:**
1. Klik **"Manage Attendees"** bij event
2. Search bar voor snel zoeken
3. Groepering per bedrijf
4. Select All / Clear All
5. Externe emails: Comma-separated (john@example.com, jane@example.com)
6. **Email Invitations** worden verzonden met:
   - ICS bestand (calendar import)
   - Accept/Decline buttons
   - Meeting details

**Event Annuleren:**
1. Klik **"Cancel Event"** (alleen voor eigen events)
2. Vul **Cancellation Reason** in (optioneel)
3. ✓ **Notify attendees**
4. **Confirm** → Emails naar alle attendees + Outlook event verwijderd

---

## 💡 Tips & Handigheden

### **🎯 Tijd Registratie Tips**

**1. Modal Auto-Filter gebruiken** 🆕
```
✅ Open "Log Time Entry" modal
✅ Selecteer project
✅ Achtergrond lijst filtert automatisch
→ Zie direct hoeveel uren al geregistreerd zijn
→ Voorkom dubbele entries
→ Krijg context
```

**2. Bulk Import via Excel**
```
Veel uren tegelijk registreren?
→ Download Excel template
→ Vul kolommen in: Datum, Project, Bedrijf, Uren, Beschrijving
→ Upload → Automatische matching
→ Honderden entries in één keer!
```

**3. Calendar Events Hergebruiken**
```
Meeting gehad?
→ Ga naar Calendar
→ Klik "Convert to Time Entry"
→ Duration al ingevuld
→ Selecteer project → Done!
```

**4. Weekly Time Review**
```
Elke vrijdag:
→ Filter op jezelf + deze week
→ Check of alles geregistreerd is
→ Submit alle draft entries
→ Voorkom administratieve achterstand
```

---

### **💰 Budget Monitoring Tips**

**1. Dashboard als Startpagina**
```
Recurring Dashboard → Pin in browser
→ Zie in één oogopslag alle projecten
→ Rode cijfers = actie vereist
→ Groene cijfers = onder controle
```

**2. Wekelijkse Budget Check**
```
Elke maandag:
→ Open recurring projects
→ Check Budget Overview
→ Zie percentage gebruikt deze maand
→ Plan werk voor rest van de maand
```

**3. Alert op 75%**
```
Budget op 75%?
→ Check welke taken nog open staan
→ Prioriteer belangrijkste werk
→ Overleg met klant over extra budget
→ Of schuif werk door naar volgende maand
```

**4. Rollover Strategie**
```
Maand eindigt met €800 rollover?
→ Plan grotere klus voor volgende maand
→ Of: Bewaar als buffer voor toekomstige overschrijdingen
```

---

### **📊 Project Management Tips**

**1. Templates Gebruiken**
```
Nieuwe website project?
→ Gebruik "E-commerce Website" template
→ Complete structuur al klaar
→ Alleen aanpassen wat nodig is
→ Tijdsbesparing: 80%!
```

**2. Service Catalog Opbouwen**
```
Verkoop je vaak dezelfde dingen?
→ Maak er een Service van
→ Custom kleur en naam per project
→ Snelle import in projecten
→ Consistente pricing
```

**3. Team Permissions Slim Instellen**
```
Developers:
✓ Can log time
✗ Can view financials (tenzij nodig)

Account Managers:
✓ Can log time
✓ Can view financials
✓ Can approve time
```

**4. Status Updates**
```
Wekelijks:
→ Update milestone/task statussen
→ Completed afvinken
→ In Progress starten
→ Geeft overzicht voortgang
```

---

### **🔍 Zoeken & Filteren Tips**

**1. Project Dropdown met Klantnaam**
```
Meerdere "Retainer maart 2025" projecten?
→ Klantnaam staat tussen haakjes!
→ "Retainer maart 2025 (Huttopia Nl)"
→ "Retainer maart 2025 (Idewe)"
→ Geen verwarring meer
```

**2. Auto-Submit Filters**
```
Filters worden direct toegepast
→ Geen "Apply" knop nodig
→ Selecteer dropdown → Direct resultaat
→ Snelle workflow
```

**3. Date Range Tricks**
```
Deze maand: 01-11-2025 t/m 30-11-2025
Vorige maand: 01-10-2025 t/m 31-10-2025
Dit kwartaal: 01-10-2025 t/m 31-12-2025
```

**4. Combined Filters**
```
Project + User + Status + Date Range
→ Zeer specifieke resultaten
→ Perfect voor rapportages
```

---

### **📧 Teamleader Import Tips**

**1. Sync Eerst, Import Daarna**
```
✅ Eerst: Global Sync (alle data naar cache)
✅ Daarna: Select & Import (selectief importeren)
→ Sneller en overzichtelijker
```

**2. Customer Contacts Filteren**
```
User import:
✓ "Hide customer contacts" aanvinken
→ Alleen team members importeren
→ Geen klant contactpersonen als users
```

**3. Status Filtering bij Projects**
```
Vaak alleen "Active" projecten importeren
→ Maar soms ook "Done" projecten nodig voor historie
→ Filter dropdown helpt hierbij
```

**4. Handmatige Company Toewijzing**
```
Na import:
→ Check dat company_id NULL is
→ Wijs handmatig juiste company toe
→ Voorkomt verkeerde koppelingen
```

---

### **🎨 Invoice Tips**

**1. Template Per Klant**
```
Klant A houdt van detailed invoices
→ Wijs "Detailed Template" toe aan klant

Klant B wil minimale info
→ Wijs "Minimal Template" toe

→ Automatisch juiste template bij nieuwe invoices
```

**2. Defer to Next Month**
```
Werk gedaan maar nog niet factureerbaar?
→ Vink "Defer to next month" aan bij invoice line
→ Komt automatisch op volgende factuur
→ Budget blijft correct
```

**3. Additional Costs Meenemen**
```
Check altijd:
→ In Fee costs (binnen budget)
→ Additional costs (extra factureren)
→ Beide types op factuur of alleen Additional?
```

**4. Preview Altijd Eerst**
```
Voor finalize:
→ Klik "Preview"
→ Check alle bedragen
→ Check BTW berekening
→ Check totalen
→ Dan pas "Finalize"
```

---

### **⚡ Performance Tips**

**1. Eager Loading**
```
Grote lijsten traag?
→ System gebruikt automatisch eager loading
→ Maar: Filters helpen ook
→ Filter op datum/project voor snellere resultaten
```

**2. Pagination**
```
Default: 20 items per pagina
→ Gebruik pagination voor grote datasets
→ Filter eerst, dan door pagina's bladeren
```

**3. Cache Refresh**
```
Teamleader data niet up-to-date?
→ Klik "Sync All" opnieuw
→ Database cache wordt ververst
→ Meestal 1x per dag voldoende
```

---

### **🔐 Security & Permissions Tips**

**1. Wachtwoord Reset**
```
Imported users hebben random password
→ Admin: Ga naar user edit
→ Klik "Generate New Password"
→ Deel veilig met gebruiker
→ User moet bij eerste login wijzigen
```

**2. Auto-Approve Verstandig Gebruiken**
```
Geef alleen aan:
✓ Senior developers
✓ Vertrouwde medewerkers
✓ Account managers

Niet aan:
✗ Junior developers
✗ Freelancers
✗ Nieuwe medewerkers
```

**3. Financial Permissions**
```
"Can view financials" alleen voor:
→ Management
→ Account managers
→ Senior project managers

Niet automatisch aan alle users geven
```

---

### **📊 Rapportage Tips**

**1. Recurring Dashboard voor Overzicht**
```
Maandelijks management meeting:
→ Open Recurring Dashboard
→ Exporteer naar PDF/Excel
→ Bespreek rode cijfers
→ Plan acties
```

**2. Series Budget View voor Detail**
```
Klant vraagt om jaaroverzicht:
→ Open project serie
→ Klik "View Totals"
→ Export complete breakdown
→ Transparant naar klant
```

**3. Time Entries Export**
```
Detaillering nodig?
→ Filter op project + maand
→ Export to Excel
→ Pivot tables maken
→ Grafieken toevoegen
```

---

## 🆘 Veelvoorkomende Vragen

### **Q: Waarom staat mijn time entry op "Pending"?**
**A:** Alle time entries moeten goedgekeurd worden door een admin/manager, tenzij je auto-approve hebt. Check met je manager of vraag om auto-approve als je ervaring hebt.

### **Q: Budget is overschreden, wat nu?**
**A:** Twee opties:
1. Werk doorschuiven naar volgende maand (defer functionaliteit)
2. Extra budget aanvragen bij klant
3. Rollover van volgende maand komt tekort automatisch compenseren

### **Q: Hoe weet ik of uren al gefactureerd zijn?**
**A:** Check de "defer details" bij time entry:
- Oranje tekst "→ Moved to:" = wordt nog gefactureerd
- Blauwe box "⚠️ NOT invoiced in [maand]" = al gefactureerd in andere maand
- Geen indicatie = nog niet gefactureerd

### **Q: Kan ik een finalized invoice nog wijzigen?**
**A:** Nee, finalized invoices zijn locked voor audit trail. Je kunt wel:
1. Een credit note maken
2. Nieuwe invoice met correctie
3. Of: Als echt nodig, admin kan status terugzetten naar draft (maar vermijd dit)

### **Q: Waarom zie ik niet al mijn projecten?**
**A:** Afhankelijk van je rol:
- **Super Admin**: Ziet alles
- **Admin**: Alleen projecten van eigen company
- **Project Manager**: Alleen toegewezen projecten
- **User**: Alleen projecten waar je time mag loggen

### **Q: Hoe voeg ik een nieuw team member toe aan project?**
**A:**
1. Open project
2. Scroll naar "Team" sectie
3. Klik "Add Team Member"
4. Selecteer company → dan user
5. Stel permissions in
6. Save

### **Q: Teamleader import lukt niet, wat nu?**
**A:** Check:
1. Is OAuth2 connectie nog geldig? (Herconnect indien nodig)
2. Heb je eerst Global Sync gedaan?
3. Check browser console voor errors
4. Contact admin voor API credentials check

### **Q: Budget rollover klopt niet, wat check ik?**
**A:**
1. Is "Fee Rollover Enabled" aangevinkt bij project?
2. Zijn alle time entries **Billable**? (Non-billable telt niet mee)
3. Zijn additional costs correct gecategoriseerd (In Fee vs Additional)?
4. Check of er geen handmatige correcties zijn geweest

---

## 📞 Support & Contact

**Bug gevonden of feature request?**
→ GitHub Issues: https://github.com/anthropics/claude-code/issues

**Technische vragen?**
→ Check eerst deze handleiding
→ Contact system admin

**Training nodig?**
→ Deze handleiding bevat alle basis informatie
→ Vraag demo aan bij admin voor hands-on training

---

**Laatste update:** 08-11-2025
**Versie:** 1.0
**Platform:** Progress Enterprise Project Management
