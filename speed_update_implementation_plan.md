# Speed Update — Implementation Plan

Goal: make reports fast when **"All locations"** is selected, today (7–8 locations) and at
100+ locations (a mix of separate OpenDental offices and multi-clinic offices).

All work follows [refractor-blueprint/09-architecture-rules.md](refractor-blueprint/09-architecture-rules.md):
metric SQL lives only in `app/Domain/…` services, filters travel as `MetricFilter`, and any
definition change is proven with `php artisan blueprint:parity` / `blueprint:production`.

---

## 1. Baseline (measured 2026-10-21, local MariaDB 10.4, 7 locations, Jan 1 – Oct 21)

| Endpoint | Time | Queries |
|---|---|---|
| `/kpis/office` | 82.0 s | 81 |
| `/kpis/doctor` | 73.2 s | 53 |
| `/dashboard/data` | 31.6 s | 134 |
| `/operations/data/offices` | 18.7 s | 98 |
| `/dashboard/financials-per-location` | 17.6 s | 74 |
| `/dashboard/patient-visits-per-location` | 14.5 s | 67 |
| `/kpis/hygiene` | 14.1 s | 81 |
| `/dashboard/providers` | 9.2 s | 18 |
| `/dashboard/location-stats` | 5.6 s | 25 |

### Root causes (proven, not assumed)

1. **Each query is slow; the number of queries is not the problem.** Round-trip cost is
   0.14 ms/query locally. Merging the per-office loop into one `office_id IN (…)` query on the
   raw tables gave **no gain** (1,594 ms looped vs 1,846 ms batched), because MySQL still
   reads the same rows.
2. **Money and code columns are `VARCHAR(255)`.** No index can cover a `SUM`, so every
   matching row is fetched from the table and converted from text:
   - `COUNT(*)` on 98k claim-proc rows (index only): **105 ms**
   - `SUM(WriteOff)` on the same rows: **1,636 ms** (about 15× slower)
   - It also causes float drift: totals like `28355.789999999957`.

   Affected columns: `od_procedure_logs.ProcFee`, `od_procedure_logs.CodeNum`,
   `od_claim_procs.WriteOff`, `od_claim_procs.InsPayAmt`, `od_adjustments.AdjAmt`,
   `od_pay_splits.SplitAmt`, `od_claim_payments.CheckAmt`.
3. **New, active and retained patient metrics scan each candidate patient's full history**
   using huge `PatNum IN (…)` lists plus a `CodeNum NOT IN` filter no index covers
   ([PatientVisitService.php:68-78](app/Domain/Patient/PatientVisitService.php#L68-L78),
   `OperationsAnalyticsService::patientRetentionMetrics` / `activePatientMetrics`).
   A single one of these took 8.6 s.
4. **Everything is recomputed from raw rows on every request.** Nothing is pre-aggregated or
   cached, even though the data only changes when a sync runs.
5. **Per-office loops run serially** in the controllers (Dashboard, Financial, KPIs,
   Operations, HygieneRecall, Calendar, DepositSlip). Cost grows linearly with location count.

### Profiling tool

`php artisan perf:profile` (read-only) dispatches the report endpoints as a super-admin
and prints wall time, SQL time, query count, the slowest queries and a **hash of the
response body**. Same filters + same hash = the change did not alter any number.
Options: `--locations=`, `--start=`, `--end=`, `--user=`, `--repeat=2` (shows warm-cache
timings), or pass paths: `php artisan perf:profile kpis/office dashboard/data`.

---

## Status (2026-10-27)

| Phase | Status |
|---|---|
| 1 | **Done** (1.1, 1.3, 1.6 in code; 1.4 and 1.5 are live-server actions) |
| 2 | **Deferred — merge into Phase 4** (see §4) |
| 3 | **Done** |
| 4 | **Done**: 4.A–4.D, 4.G; per-office caching + `reports:warm` serve every main page from cache (see §6, "4.Z"); `daily_financials` dropped (money sums no longer the cost) |
| 5 | **Done** (5.1 money DECIMAL, 5.2 covering indexes, plus key-type fixes and MyISAM → InnoDB) |
| 6 | Not started |

Results, 7 locations, Jan 1 – Oct 21, identical response hashes before and after:

| Endpoint | Before | Repeat view | After one office's sync |
|---|---|---|---|
| `/kpis/office` | 83.8 s | 9 ms | 0.5 s |
| `/kpis/doctor` | 69.1 s | 19 ms | 0.9 s |
| `/dashboard/data` | 19.5 s | 15 ms | — |
| `/dashboard/financials-per-location` | 16.8 s | 22 ms | — |
| `/dashboard/patient-visits-per-location` | 15.0 s | 14 ms | — |
| `/kpis/hygiene` | 14.9 s | 14 ms | — |
| `/operations/data/offices` | 11.2 s | 27 ms | — |
| `/dashboard/providers` | 9.1 s | 16 ms | — |
| `/dashboard/location-stats` | 4.9 s | 12 ms | — |

The first view after a sync still pays the full cost for every office that changed.
Phase 4 is what makes that first view fast at 100 locations.

---

## 2. Phase overview

| Phase | What | Schema change | Risk | Expected gain |
|---|---|---|---|---|
| 1 | Quick wins + accuracy bug | No | Low | 10–30% plus correct multi-office totals |
| 2 | Multi-location `MetricFilter` | No | Medium | Removes per-office loops; foundation for 4 |
| 3 | Cache results until next sync | No | Low | Repeat views < 100 ms |
| 4 | Summary tables | **Yes (new tables)** | Medium | First views < 1 s at 100 locations |
| 5 | Column types + covering indexes | **Yes (ALTER, ~2M rows)** | High | 5–15× on remaining raw-table queries |
| 6 | Cleanup | No | Low | Minor |

Phases 1–3 need no changes to existing tables. Phase 4 is what makes 100 locations fast.
Phase 5 requires a maintenance window (see §8).

---

## 3. Phase 1 — Quick wins

| # | Change | File(s) |
|---|---|---|
| 1.1 | `summary()` calls `adjustments()` (which sums `WriteOff`) and then `writeOffs()` (sums it again). Compute the writeoff sum once and reuse it. | [app/Domain/Production/ProductionService.php](app/Domain/Production/ProductionService.php) |
| 1.2 | ~~Bug: multi-office `fromRequest()` keeps only the first office.~~ **Not a live bug:** `MetricFilter::fromRequest()` has no callers. Must be fixed before anything starts using it (Phase 2/4). | [app/Domain/Support/MetricFilter.php:52-58](app/Domain/Support/MetricFilter.php#L52-L58) |
| 1.3 | Enable OPcache: uncomment `zend_extension=opcache`, set `opcache.enable=1`, `opcache.memory_consumption=256`, `opcache.max_accelerated_files=20000`; on live also `opcache.validate_timestamps=0` (reset on deploy). | `C:\xampp\php\php.ini`, live php-fpm ini |
| 1.4 | `APP_DEBUG=false` on live. Run `php artisan config:cache route:cache view:cache` on deploy. | live `.env`, deploy script |
| 1.5 | Check the RDS instance class: if `od_*` data + indexes (~3.5 GB today) exceed `innodb_buffer_pool_size`, queries read from disk. Size the instance accordingly. | AWS console / parameter group |
| 1.6 | Add the profiler to the repo and record the new baseline. An artisan command, not a script file, because the live docroot is the project root and a script would be web-reachable. | [app/Console/Commands/PerfProfileCommand.php](app/Console/Commands/PerfProfileCommand.php) |

**Done when:** profiler shows the dashboard writeoff query running once per office, and
multi-office totals equal the sum of each office viewed alone.

---

## 4. Phase 2 — Multi-location `MetricFilter`

> **Deferred (2026-10-27): do this as part of Phase 4, per domain.** Two findings changed
> the case for doing it on its own:
> 1. On raw tables it gives no speed: one batched query was no faster than the per-office
>    loop (root cause 1).
> 2. Most metric SQL is not in domain services yet. It sits in `KpisController` (68 `DB::`
>    calls), `DashboardController` (52), `OperationsController` (65) and
>    `OperationsAnalyticsService` (128). Phase 2 would mean moving all of it into services,
>    then rewriting it again for the summary tables in Phase 4.
>
> Doing it once, while each domain moves to its summary table, avoids that double rewrite.

Today `MetricFilter` holds one `officeId`, so every controller loops offices and every
service runs once per office. Extend the single source rather than adding loops.

### 2.1 Extend `MetricFilter`

- Add `scopes` (`array<int officeId, int[] clinics>`, empty array = whole office), built from
  `LocationSelection::scopes()`.
- Keep `officeId` / `clinics` working for single-office callers (derived from the first scope)
  so existing code keeps running during migration.
- New builders: `MetricFilter::forSelection(LocationSelection, start, end)` and `withScopes()`.
- Include `scopes` in `signature()`.
- `fromRequest()` builds the full scopes map, which permanently fixes bug 1.2.

### 2.2 One scope predicate, in one place

Add a single helper (e.g. `App\Domain\Support\ScopeQuery::apply(Builder $q, MetricFilter $f, string $alias)`)
that renders:

```sql
(  t.office_id IN (/* whole offices */)
OR (t.office_id = 3 AND t.ClinicNum IN (2, 5)) )   -- partial offices
```

Every domain service's `applyClinicProvider()` delegates to it. Results that need a
per-location breakdown `GROUP BY office_id, ClinicNum`.

### 2.3 Combining locations — business rule (OPEN QUESTION, see §9)

- **Money / counts of rows** (production, adjustments, collections, procedures): add up across all scopes.
- **Distinct patients across separate offices:** PatNum is only unique within one office, so
  add up the per-office distinct counts.
- **Distinct patients across clinics of the same office:** also add up the per-clinic counts.
  A patient seen at two clinics counts once in each (decided 2026-10-27; see §10.1).

### 2.4 Migrate callers (one controller per PR, parity-checked)

Order by baseline cost: `KpisController` → `DashboardController` → `OperationsController` /
`OperationsAnalyticsService` → `FinancialController` → `HygieneRecallController` →
`CalendarController` → `DepositSlipController`. Each `foreach ($locations->scopes() …)` loop
becomes one service call with a multi-scope filter.

> On raw tables this phase alone gives little speed (see root cause 1). Its value is that
> Phases 3 and 4 then apply to every page through one code path.

**Done when:** no controller loops over `scopes()` for metric values, and `blueprint:parity`
matches for single-office, multi-clinic and all-location selections.

---

## 5. Phase 3 — Cache results until the next sync

Data only changes when a sync runs, so results can be cached until then. Do **not** cache
"closed" months forever: claim writeoffs are dated by `ProcDate` but arrive when insurance
pays, months later, and adjustments and procedures get back-dated or edited.

### As built

**[MetricCache](app/Domain/Support/MetricCache.php)** is the single home for report caching.
- `remember($name, $officeIds, $params, $compute)` / `rememberFilter($name, MetricFilter, $compute)`.
- The key embeds a **version token per office**; `invalidateOffice($id)` replaces the token,
  so old entries are never read again. No tags, so it works on the `database` driver.
- Versions live in the cache itself (random tokens, not counters), so **no new table / no DDL**.
  If the cache is cleared, fresh tokens are issued and nothing stale can come back.
- Config: [config/metric_cache.php](config/metric_cache.php) — `METRIC_CACHE_ENABLED`
  (kill switch), `METRIC_CACHE_STORE`, `METRIC_CACHE_TTL` (default 24 h).

**Invalidation: every writer to report data calls `invalidateOffice()`:**

| Writer | Where |
|---|---|
| Sync upserts (all modules, incl. Data Explorer row repairs) | `BaseQuerySyncService::persistBatch()` |
| Patient balance rollups | `PatientBalanceSyncService::sync()` |
| Hard-delete pruning | `HardDeleteSyncService` |
| Data Explorer confirmed deletes | `RowRepairService::pruneConfirmedDeleted()` |
| Schedule snapshots | `ScheduleSnapshotService` (after commit) |
| Goals and office names (edited in the app) | `InvalidatesMetricCache` trait on `OfficeGoal`, `SpecialtyGoal`, `ProviderGoal`, `Office` |

> **Anything that writes report data some other way** (manual SQL, a new import, a seeder)
> must call `invalidateOffice()`, or run `php artisan cache:clear` afterwards.

**Where results are cached:**
- KPIs: `KpisController::cachedSet()`, **per location** — all KPI pages, provider tables,
  and drill-downs.
- Dashboard: **per office** in `data`, `locationStats`, `providerPerformance`,
  `financialsPerLocationData`, `patientVisitsPerLocationData`.
- Operations: **per selection** for every tab spec in `OperationsController::data()`, and
  (Phase 4) **per office** inside `OperationsAnalyticsService::rowsAcross()` for the
  offices, payors, cancellations, production-details, providers, services, claims and
  compliance tabs.
- Financials (`data`, `revenue`, `score-cards`) and Deposit Slip (`data`): per selection
  (Phase 4). `BasicSetting` invalidates on save (a global row: every office).
- Not cached: Dashboard `providerDetails`, Operations drill-downs, HygieneRecall and
  Calendar (Calendar is < 0.1 s). Results over `METRIC_CACHE_MAX_BYTES` (512 KB serialized,
  e.g. a year of deposit detail rows) are served uncached; a failed cache write never fails
  the request.

**Housekeeping:** `php artisan metrics:prune-cache`, scheduled hourly, deletes expired rows
from the database cache table, since superseded entries are never read and so never removed
on their own.

**Tests:** `tests/Feature/MetricCacheTest.php` (hits, per-office invalidation, multi-office
keys, filter signatures, kill switch, goal-save invalidation).

**Limitation:** core modules sync every 10 minutes, so during business hours most offices
change every cycle. The first view after a sync recomputes those offices at full cost;
repeat views within the cycle are instant. Phase 4 removes that cost.

---

## 6. Phase 4 — Summary tables

Reports read small pre-aggregated tables instead of millions of raw rows. A year across
100 locations becomes tens of thousands of summary rows, read in one grouped query.

**Scope (product owner, 2026-10-27):** every tab of the most-used modules, in this order of
importance: Dashboard, Calendar, Deposit Slip, Financials, Operations, Patient/Provider
portal, RCM, then the rest (KPIs, Front Office). Not a pilot on a few pages.

### 4.0 Module baseline (2026-10-27, cache off, 7 locations, Jan 1 – Oct 27)

| Module | Endpoint / tab | Time | Main cost |
|---|---|---|---|
| Operations | payors | **1,597 s** | 522,923 queries: per-row queries (N+1) |
| Operations | marketing / patient-analysis | 85 s | first-visit scan of all history (26 s query) |
| KPIs | office | 84.8 s → **14.8 s** | done: `patient_visit_days` (4.A); rest = claim-proc estimate sums |
| KPIs | doctor | 73 s | same-day TP acceptance join, appointment hours |
| Financials | data | 44.6 s | appointment first-visit list, writeoff by day |
| Front Office | stats | 42.5 s (SQL 4.5 s) | loads raw procedure rows into PHP |
| Operations | offices diff-last-year | 36.3 s | new-patient lookup ×2 periods, money sums |
| Operations | performance | 32.7 s | new-patient lookup, money sums |
| Operations | providers | 23.8 s | 423 queries, money sums |
| Financials | score-cards | 20.3 s | one query: `DATE(REPLACE(ProcDate))` defeats the index |
| Operations | offices / last-year | 18.7 s | new-patient lookup (2.3–2.9 s per office) |
| Dashboard | data | 17.1 s | new-patient lookup, writeoff sums |
| Dashboard | financials-per-location | 16.3 s | writeoff / adjustment sums |
| Operations | production-details | 15.6 s | new-patient lookup |
| Operations | services | 14.6 s | new-patient lookup |
| KPIs | hygiene | 14.1 s | visit-history scans |
| Dashboard | patient-visits-per-location | 11.4 s | new-patient lookup |
| Operations | marketing | 10.6 s | new-patient lookup |
| Operations | compliance | 9.4 s | writeoff sums |
| Dashboard | providers | 9.4 s | provider production sums |
| Calendar | monthly-summary / stats | 7.9 s / 7.0 s | `DATE(REPLACE())` writeoff sums |
| Front Office | collections-stats / -data | 7.9 s / 6.0 s | `InsPayAmt` sum, guarantor balances |
| Operations | trends | 7.3 s | monthly writeoff sums |
| Financials | revenue | 5.8 s | writeoff sums |
| Dashboard | location-stats | 4.8 s | writeoff sums |
| Deposit Slip | data | 4.4 s | pay-split provider lookup |
| Front Office | hygiene-recall-due | 4.6 s (SQL 0.2 s) | PHP processing |
| Operations | cancellations, claims; Calendar (other); RCM; Provider portal; Patients | < 2 s | — |

**Three shared causes cover almost all of it:**
1. **Money sums on `VARCHAR` columns** (`WriteOff`, `AdjAmt`, `SplitAmt`, `CheckAmt`, `PayAmt`,
   `InsPayAmt`, `ProcFee`) over 0.4–1.8M-row tables, repeated by every module.
2. **The new-patient lookup** (`PatientVisitService`, `SELECT DISTINCT PatNum … PatNum IN (…)`),
   2.3–2.9 s per office, run by nearly every Operations tab and the Dashboard.
3. **Per-row code paths**: N+1 queries (Payors) and raw rows processed in PHP (Front Office).

### 4.0.1 Work order

| Step | What | Fixes |
|---|---|---|
| 4.A | `patient_visit_days` + `PatientActivityService` | **Done**: Office KPIs |
| 4.B | Payors tab N+1 | Operations payors |
| 4.C | New-patient lookup reads `patient_visit_days` | Dashboard, Operations (≈10 tabs), Financials |
| 4.D | Remove `DATE(REPLACE(col))` on `DATE` columns (no value change) | Financials score-cards, Calendar |
| 4.E | `daily_financials` (office, clinic, provider, day): gross, procedures, adjustments, writeoffs, patient/insurance payments, estimates | Dashboard, Financials, Operations, Calendar, Front Office, KPIs |
| 4.F | Same-day TP acceptance + appointment summaries | KPIs doctor, Operations performance |
| 4.G | PHP-side processing pages (Front Office stats, recall due) | Front Office |
| 4.H | Remaining tabs re-profiled; anything > 1 s gets a step | all |

Every step: parity (`summaries:parity` / response hash identical to the raw path) before switching.

### 4.A `patient_visit_days` (as built)

- Table: one row per office × clinic × patient × provider × day with a completed procedure;
  flags `completed, coded, coded_not_broken, fmx, recare, np_any, np_not_broken`.
  1.42M completed procedures → 736k rows. Full build of all 7 offices: 76 s.
- [PatientActivityService](app/Domain/Patient/PatientActivityService.php): `fromSummary()` and the
  raw reference `fromRaw()`; `officeActivity()` picks the summary when the office is `ready`.
- Kept current by [PatientVisitDays](app/Domain/Patient/PatientVisitDays.php): each procedure-log
  sync batch rebuilds the touched patients (before + after the write); deletes go through
  `deletingRows()`; a procedure-code sync marks the office stale and queues
  `RebuildPatientVisitDays`; nightly full rebuild 04:00 ET.
- Commands: `summaries:rebuild {offices?*}`, `summaries:parity {offices?*}` (35/35 match).
- Definitions retained as they ran (product decision), except the recare sub-query, which now
  uses `ProcStatus::completed()` instead of `'C'` only (product decision 2026-10-27; Active in
  Recare was always 0%), and Reactivation List now counts every lapsed patient (it read one
  patient's count, so it showed 1 or 0).

### 4.Z Where Phase 4 stands (2026-10-27, cache off, warm buffer pool, 7 locations, YTD)

Every change was checked against a saved "before" response of all 34 endpoints (value by
value, `--save` + compare). Differences found and accepted: 1-cent rounding (exact DECIMAL
instead of float), an all-zero provider row that float drift had kept, and tie order of
equal rows. Two type regressions (numbers sent as strings; provider ids as numbers) were
fixed at the source.

| Module / endpoint | Baseline | Now | What did it |
|---|---|---|---|
| Operations payors | 1,597 s | 11.4 s | 4.B: payor-label map held in memory (was 522,923 cache queries) |
| Operations patient-analysis | 85 s | 19.7 s | 4.C cohort from `patient_visit_days`, indexes |
| KPIs office | 84.8 s | 7.9 s | 4.A `patient_visit_days` |
| KPIs doctor | 73 s | 14.5 s | same-day TP sub-query bounded to the period, sargable dates, cohort |
| Financials data | 44.6 s | 11.2 s | two-step new-patients-scheduled, Top Services pre-aggregated, ProcCat BIGINT |
| Front Office stats | 42.5 s | 1.7–5.3 s | 4.G: age buckets grouped by date in SQL; one count query |
| Operations offices diff-LY | 36.3 s | 15.6 s | 4.C + indexes |
| Operations performance | 32.7 s | 14.2 s | indexes |
| Financials score-cards | 20.3 s | 1.2 s | 4.D sargable date, `od_providers.ProvNum` BIGINT |
| Dashboard data | 17.1 s | 4.5 s | 4.C, indexes, new-patients-scheduled |
| Dashboard financials-per-location | 16.3 s | 3.4–4.0 s | DECIMAL + covering indexes |
| Operations offices | 18.7 s | 5.8 s | 4.C + indexes |
| KPIs hygiene | 14.1 s | 8.3 s | indexes |
| Dashboard patient-visits-per-location | 11.4 s | 3.4 s | 4.C |
| Dashboard providers | 9.4 s | 2.0 s | indexes, ProvNum BIGINT |
| Calendar stats / monthly summary | 7.0 / 7.9 s | 0.07 / 0.04 s | 4.D sargable dates |
| Financials revenue | 5.8 s | 0.95 s | indexes |
| Deposit slip | 4.4 s | 3.0 s | indexes |
| Operations trends / compliance | 7.3 / 9.4 s | 1.9 / 4.1 s | indexes |
| RCM, Provider portal, Calendar (others) | < 0.3 s | < 0.1 s | — |

**Why `daily_financials` was not built:** profiling every query of the slow pages after
Phase 5 (131 s of SQL in total) showed money sums are now ~1–2% each; the time is in
patient-list work (earlier-procedure checks, new-patient visit details, same-day TP
acceptance, case acceptance, payor-plan mapping) at 100–300 ms per office per query. A
money summary would not move the pages; the remaining cost is per office.

**How the target is met instead — every view is served from cache:**
1. **Per-office caching everywhere a page loops offices** (KPIs, Dashboard, and now
   Operations rows): a sync recomputes only the office it touched; the other offices'
   rows are reused. An all-locations view costs one office's work after a sync, not N.
2. **`php artisan reports:warm`**, scheduled 5 minutes after each 10-minute sync dispatch,
   requests every main page (Dashboard, Financials, Deposit Slip, KPIs, Operations default
   tabs) for all locations, month to date (the date picker default) and year to date. Pages
   whose offices did not change are cache hits; only changed offices are recomputed. The
   next person to open a page gets a cached result (< 100 ms).
3. Custom date ranges and drill-downs are still computed on demand (the times above), then
   cached until that office's next sync.

**Measured (7 offices, both ranges, 44 requests):** cold 64 s; nothing changed 4.4 s; one
office changed 13.7 s (smallest) to 20.2 s (largest). The domain services that every module
calls per office are cached per office too (`PatientVisitService::newPatientVisits`,
`NewPatientsScheduledService`, Operations per-office pieces via `officeCached()`), so one
office's change recomputes one office. Fixed per-run cost: Deposit Slip YTD (~2 s, result
over the cache size limit) and the parts of Compliance / Financials that still query all
selected offices at once (~2–3 s each).

**Scaling note:** a warm run costs ~4 s plus ~10–16 s per office that changed since the last
run. At 100 offices that all change every 10 minutes that is ~20–25 minutes of work per
cycle: run the warm-up on its own queue with several workers (or split it by office) before
going beyond ~30 frequently-changing offices.

**Fixed (2026-10-27):** Front Office → Collections (summary cards and daily table) summed
every office through `DB::table()` while the balances beside them were the active office
only; all its queries now use the same office rule (`activeOfficeTable()`).

**Fixed (2026-10-28, product decision):** that page computed net production as gross +
adjustments **+** writeoffs; it now uses the canonical definition every other module uses
(`ProductionService::netFrom()`, D3: gross + adjustments − writeoffs), on the summary card
and the daily Collections table. `od_providers` ids are BIGINT in the database but cast to
string by the model so pages comparing ids as text keep working.

### 4.1 `daily_production_summary` (superseded by 4.E `daily_financials`)

| Column | Notes |
|---|---|
| `office_id`, `clinic_num`, `prov_num`, `date` | Composite primary key |
| `gross` DECIMAL(14,2) | `SUM(ProcFee)`, completed procs (`ProcStatus::completed()`) |
| `procedures` INT | completed proc count |
| `hygiene_gross`, `hygiene_procedures` | for the `hygiene` filter dimension |
| `adjustments` DECIMAL(14,2) | `SUM(AdjAmt)` by `AdjDate` |
| `writeoffs` DECIMAL(14,2) | `SUM(WriteOff)` by `ProcDate` |
| `patient_collections` DECIMAL(14,2) | `SUM(SplitAmt)` by `DatePay` |
| `insurance_collections` DECIMAL(14,2) | `SUM(CheckAmt)` by `CheckDate` |
| `refreshed_at` | |

Sums are fully additive, so any date range / location set is a `SUM … GROUP BY`.

### 4.2 `patient_activity_summary` (distinct-patient metrics)

Distinct counts can't be added up from daily rows. Keep one row per patient per office:
`office_id, pat_num, clinic_num, first_visit_date, last_visit_date, last_hygiene_date, …`
(using the same broken-appointment code exclusions as today). New patients, active
patients, retention and recall metrics then become indexed range counts instead of
full-history scans (root cause 3). Patient-visit counts (patient × day) use a
`daily_patient_visits (office_id, clinic_num, prov_num, date, pat_num)` table.

### 4.3 Refresh strategy

1. **Touched dates:** each sync module records the distinct business dates of the rows it
   upserted (`ProcDate`, `AdjDate`, `DatePay`, `CheckDate`), and the patients it touched.
2. A queued `RefreshSummaries(officeId, dates[], patNums[])` job, dispatched after
   `SyncOfficeModule` finishes, deletes and rebuilds exactly those
   `(office, date)` / `(office, patient)` rows in one transaction, then bumps the office
   sync version (Phase 3).
3. **Safety nets:** nightly rebuild of the last 90 days per office; weekly full rebuild per
   office (chunked by month), scheduled in off-hours.
4. `php artisan summaries:rebuild {office?} {--from=} {--to=}` for backfill and repair.

### 4.4 Switch services over

`ProductionService` reads the summary table when available. The raw-table query stays as
the reference implementation used only by the parity check.

### 4.5 Parity

Extend `blueprint:parity` / `blueprint:production` to compare summary vs raw results for
every office, a set of date ranges (day, month, YTD, last year) and clinic / provider /
hygiene filters. **Must match to the cent before switching each metric.**

### 4.6 Roll out to the other domains

Pilot Production first, then Scheduling (appointments), Treatment Acceptance, Recall,
Insurance, using the same pattern.

> DDL: creating new tables does not touch existing data, but per project memory the local
> MariaDB is fragile on DDL. Take a dump first and confirm before running migrations.

**Done when:** all-location dashboard and KPI pages load in < 1 s on a cache miss, and
parity passes.

---

## 7. Redis / Valkey (optional)

- **Not required.** Phase 3 uses versioned keys, which work with the `database` driver.
- **Cost:** the software is free (Redis 8 under AGPL; Valkey, its fully open-source fork, is
  drop-in compatible with Laravel's `redis` driver).
- **AWS RDS cannot host it.** Choices are:
  - Valkey on the app server (free, uses RAM).
  - ElastiCache for Valkey (roughly $10–15/month for the smallest node; check current pricing).
- **Recommendation:** start with `database`; move to Valkey on the app server if cache reads
  become a bottleneck; use ElastiCache only once there are multiple app servers. Then set
  `CACHE_STORE=redis`, `SESSION_DRIVER=redis`.

---

## 8. Phase 5 — Column types and covering indexes

### 5.1 Money and code columns

| Table | Column | From | To |
|---|---|---|---|
| `od_procedure_logs` | `ProcFee` | VARCHAR(255) | DECIMAL(14,2) |
| `od_procedure_logs` | `CodeNum` | VARCHAR(255) | BIGINT |
| `od_claim_procs` | `WriteOff`, `InsPayAmt` | VARCHAR(255) | DECIMAL(14,2) |
| `od_adjustments` | `AdjAmt` | VARCHAR(255) | DECIMAL(14,2) |
| `od_pay_splits` | `SplitAmt` | VARCHAR(255) | DECIMAL(14,2) |
| `od_claim_payments` | `CheckAmt` | VARCHAR(255) | DECIMAL(14,2) |

Steps:
1. **Pre-check:** count values that are not clean numbers
   (`WHERE col <> '' AND col NOT REGEXP '^-?[0-9]+(\\.[0-9]+)?$'`). Decide how to handle each
   (empty → NULL or 0, matching what reports treat them as today).
2. Full dump of the database.
3. One migration per table, run in a maintenance window (each `ALTER` rebuilds a table of up
   to ~2.2M rows). On RDS, take a snapshot first; consider a blue/green deployment.
4. Update the sync mappers to cast to numeric / int before writing.
5. Remove string workarounds in queries (`ClinicNum + 0`, multi-format `IsHygiene` / `ProcStatus`
   lists where the type is now fixed).
6. Re-run parity. Totals will lose float drift (e.g. `28355.79` instead of `28355.789999999957`);
   that difference is expected and correct.

### 5.2 Covering indexes (for queries that still read raw tables, e.g. drill-downs)

- `od_claim_procs (office_id, ProcDate, ClinicNum, WriteOff)`
- `od_claim_procs (office_id, DateCP, Status, ClinicNum, InsPayAmt)` (replaces `idx_claim_procs_ops_datecp`)
- `od_procedure_logs (office_id, PatNum, ProcStatus, ProcDate, CodeNum)` (replaces `idx_proc_logs_ops_pat_status_date`; covers the prior-visit check)
- `od_adjustments (office_id, AdjDate, ClinicNum, AdjAmt)`
- `od_pay_splits (office_id, DatePay, ClinicNum, SplitAmt)`

Verify each with `EXPLAIN` ("Using index") and drop the indexes they supersede to keep sync
write speed. With Phase 4 in place, only add the ones raw-table queries still need.

---

## 9. Phase 6 — Cleanup

- Rewrite retention / active-patient queries with `EXISTS` instead of plucked `PatNum` lists,
  **keeping the existing rules** (completed status, broken-appointment code exclusions on the
  prior-visit check). Parity-check before and after.
- Remove the `route('operations.drilldown', …)` call inside the cell loop of
  [resources/views/components/analytics-table.blade.php:192](resources/views/components/analytics-table.blade.php#L192):
  render data attributes and build the URL once in `DDS` JS. (Small gain: tens of ms at 2,300 cells.)
- For tables with hundreds of rows at 100 locations, enable `DDS.dataTable` paging.

---

## 10. Open questions (needed before Phase 4)

1. ~~**"All locations" total for patient counts:** unique patients or sum of per-location counts?~~
   **Answered 2026-10-27:** sum of per-location counts. A patient counts once in each
   clinic/office they appear in; no cross-clinic de-duplication. Patient counts are therefore
   additive per location, so Phase 4 can pre-aggregate them per clinic like money.
   Business rule: a patient is treated only at the office/clinic they are registered with;
   seeing another requires registering there separately (a new patient record). The rule is office
   practice, not enforced by OpenDental. Attribution stays by procedure clinic (`ClinicNum` on the
   procedure), which under this rule equals the patient's home clinic.
2. Live server: is the app on a single EC2 instance (Valkey can run locally) or several?
3. RDS instance class / memory, to size the buffer pool (step 1.5).
4. Is a maintenance window available for Phase 5 (expected minutes per large table)?

---

## 11. Verification checklist (every PR)

- [ ] Profiler run before/after, numbers in the PR description
- [ ] `php artisan blueprint:parity` and `blueprint:production` pass
- [ ] Single office, multi-clinic office, mixed selection and "All locations" checked
- [ ] No metric SQL outside `app/Domain/…`; no new per-office loops in controllers
- [ ] DDL only after a dump/snapshot and explicit approval
