Replicate Jarvis Analytics Calendar - Day View
I need to build a day calendar view that matches Jarvis Analytics exactly. Here are the key requirements:

1. Top Provider Header
Scrollable horizontal list of providers with:

Avatar circle with initials (e.g., "El" for Elias, "Ha" for Haddow)

Provider name: Last, First (e.g., "Elias, Kathy")

Specialty & count: e.g., "Invis - (81)"

Each provider has a unique color for their avatar

2. Operatory Columns
Each operatory is a separate column

Column headers show: DR-1, DR-2, DR-3, DR-4, Unassigned 6, etc.

Multiple operatories can belong to the same provider

Show all 10 operatories as separate columns

3. Appointment Cards
Each appointment displays:

Patient name: First Last (bold)

Procedure code: (e.g., "PstTrtStb")

Phone number: (if available)

Notes: Truncated to 2-3 lines

NP badge for New Patients

Color coding: Each provider has a unique event color

4. Data Structure
javascript
{
  provider: {
    id, name: "Last, First", initials, specialty, count, color,
    operatories: ["DR-1", "DR-2"]
  },
  operatory: {
    id, name: "DR-1", providerId
  },
  event: {
    patientName, patientId, time, amount, procedureCode,
    phone, notes, isNewPatient, status, color
  }
}
5. Sidebar
When clicking an appointment, show:

Patient name & ID

Provider, Time, Operatory, Procedure, Status

Full notes

"View Patient" and "Close" buttons

6. Statistics Bar
Production: Today's total production

Scheduled Production: Today's scheduled production

Active Columns Toggle: Show/hide empty operatories

7. Time Grid
Hours: 6:00 AM – 8:00 PM

Increments: 30-minute intervals

Current time: Red line indicator

Database Tables
Table	Use
provider	Provider names, specialty
operatory	Operatory names, provider assignment
appointment	Appointments, time, status, notes
patient	Patient names, phone
procedurelog	Procedure codes, fees
Current Issues to Fix
❌ Provider header missing avatars & specialty info

❌ Operatory columns are op-1 instead of DR-1, DR-2, etc.

❌ Only 5 columns visible instead of 10

❌ Events are truncated/overlapping

❌ No color coding per provider

❌ No NP badges for new patients

SQL Query Example
sql
SELECT 
    a.AptNum,
    CONCAT(p.FName, ' ', p.LName) AS PatientName,
    CONCAT(pr.FName, ' ', pr.LName) AS ProviderName,
    o.OperatoryNum,
    o.Abbreviation AS OperatoryName,
    a.AptDateTime,
    a.AptLength,
    a.AptStatus,
    a.Note,
    pl.ProcCode,
    pl.ProcFee
FROM appointment a
JOIN patient p ON a.PatNum = p.PatNum
JOIN provider pr ON a.ProvNum = pr.ProvNum
JOIN operatory o ON a.OperatoryNum = o.OperatoryNum
LEFT JOIN procedurelog pl ON a.AptNum = pl.AptNum
WHERE CAST(a.AptDateTime AS DATE) = @SelectedDate
ORDER BY a.AptDateTime, o.OperatoryNum
Quick Reference: The 10 Operatories
text
DR-2, DR-3, DR-4, Unassigned 6, Unassigned 7, 
DR-1, DR-5, Unassigned 8, Unassigned 9, Unassigned 10
Provider mapping:

Elias, Kathy → DR-2, DR-3, DR-4, DR-1

Haddow, Mason → DR-5

Unassigned → Unassigned 6, 7, 8, 9, 10

Color Scheme
Provider	Color
each provider each color	#6DE5C1 (Turquoise/Green), #996BE5 (Purple)
Timeline etc
Phase 1: Fix provider header with avatars

Phase 2: Fix operatory columns (DR-1, DR-2, etc.) and show all 10

Phase 3: Fix event display (names, notes, colors)

Phase 4: Add sidebar with appointment details

Phase 5: Add NP badges and polish UI

