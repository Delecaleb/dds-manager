<?php

$svcOrigFile = 'app/Services/OpenDental/OperationsAnalyticsService.php.orig';
$svcFile = 'app/Services/OpenDental/OperationsAnalyticsService.php';

$code = file_get_contents($svcOrigFile);

// Helper replacements in OperationsAnalyticsService.php:

// 1. Class property and constructor
$oldConstruct = <<<'PHP'
    /** ClinicNum => display name, sourced from the multi-office ClinicRegistry. */
    private array $clinicNames = [];

    public function __construct(
        private readonly TreatmentAcceptanceService $treatmentAcceptance,
        private readonly ProductionService $production,
        private readonly PatientService $patients,
        private readonly ClinicRegistry $clinics,
        private readonly PayorService $payors,
        private readonly PatientVisitService $patientVisits,
        private readonly ScheduleSnapshotService $scheduleSnapshots,
    ) {
        $this->clinicNames = $this->clinics->all();
    }
PHP;

$newConstruct = <<<'PHP'
    /** ClinicNum => display name, sourced from the multi-office ClinicRegistry. */
    private array $clinicNames = [];

    public function __construct(
        private readonly TreatmentAcceptanceService $treatmentAcceptance,
        private readonly ProductionService $production,
        private readonly PatientService $patients,
        private readonly ClinicRegistry $clinics,
        private readonly PayorService $payors,
        private readonly PatientVisitService $patientVisits,
        private readonly ScheduleSnapshotService $scheduleSnapshots,
    ) {
        $this->clinicNames = $this->clinics->all();
    }

    private function resolveOfficeId(?int $officeId = null): int
    {
        return $officeId ?? (Office::getActiveOfficeId() ?? 1);
    }
PHP;

$code = str_replace($oldConstruct, $newConstruct, $code);

// 2. offices() signature and officeRows() call
$code = str_replace(
    "public function offices(string \$start, string \$end, string \$subtab = 'default', array \$clinics = []): array\n    {\n        \$columns = \$this->officeColumns();\n        \$percentDiff = \$subtab === 'percent-diff-last-year';",
    "public function offices(string \$start, string \$end, string \$subtab = 'default', array \$clinics = [], ?int \$officeId = null): array\n    {\n        \$officeId = \$this->resolveOfficeId(\$officeId);\n        \$this->clinicNames = \$this->clinics->all(\$officeId);\n        \$columns = \$this->officeColumns();\n        \$percentDiff = \$subtab === 'percent-diff-last-year';",
    $code
);
$code = str_replace('$this->officeRows($start, $end, $clinics)', '$this->officeRows($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->officeRows($lyStart, $lyEnd, $clinics)', '$this->officeRows($lyStart, $lyEnd, $clinics, $officeId)', $code);

// officeRows() signature and calls
$code = str_replace(
    'private function officeRows(string $start, string $end, array $clinics): array',
    'private function officeRows(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace('$this->productionMetrics($start, $end, $clinics)', '$this->productionMetrics($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->newPatientMetrics($start, $end, $clinics)', '$this->newPatientMetrics($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->patientRetentionMetrics($end, $clinics)', '$this->patientRetentionMetrics($end, $clinics, $officeId)', $code);
$code = str_replace('$this->activePatientMetrics($end, $clinics)', '$this->activePatientMetrics($end, $clinics, $officeId)', $code);
$code = str_replace('$this->sumByClinic(\'od_adjustments\', \'AdjAmt\', \'AdjDate\', $start, $end, $clinics)', '$this->sumByClinic(\'od_adjustments\', \'AdjAmt\', \'AdjDate\', $start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->collectionsByClinic($start, $end, $clinics)', '$this->collectionsByClinic($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->sumByClinic(\'od_claim_procs\', \'WriteOff\', \'ProcDate\', $start, $end, $clinics)', '$this->sumByClinic(\'od_claim_procs\', \'WriteOff\', \'ProcDate\', $start, $end, $clinics, $officeId)', $code);

// productionMetrics()
$code = str_replace(
    'private function productionMetrics(string $start, string $end, array $clinics): array',
    'private function productionMetrics(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$q = DB::table(\'od_procedure_logs\')
            ->selectRaw(\'ClinicNum, SUM(ProcFee) as gross, COUNT(*) as procedures\')
            ->whereIn(\'ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'ProcDate\', [$start, $end]);',
    '$q = DB::table(\'od_procedure_logs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, SUM(ProcFee) as gross, COUNT(*) as procedures\')
            ->whereIn(\'ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'ProcDate\', [$start, $end]);',
    $code
);

// newPatientMetrics()
$code = str_replace(
    'private function newPatientMetrics(string $start, string $end, array $clinics): array',
    'private function newPatientMetrics(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace('$firstVisit = $this->patients->firstVisitCohort();', '$firstVisit = $this->patients->firstVisitCohort($officeId);', $code);
$code = str_replace(
    '$q = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstVisit, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')',
    '$q = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstVisit, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
            ->where(\'pl.office_id\', $officeId)',
    $code
);

// patientRetentionMetrics()
$code = str_replace(
    'private function patientRetentionMetrics(string $end, array $clinics): array',
    'private function patientRetentionMetrics(string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
            ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->groupBy(\'pl.PatNum\');',
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->groupBy(\'pl.PatNum\');',
    $code
);
$code = str_replace(
    '$qCur = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
            ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$curStart.\' 00:00:00\', $end.\' 23:59:59\'])',
    '$qCur = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
            ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$curStart.\' 00:00:00\', $end.\' 23:59:59\'])',
    $code
);
$code = str_replace(
    '$qPrior = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
            ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$priorStart.\' 00:00:00\', $priorEnd.\' 23:59:59\'])',
    '$qPrior = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
            ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$priorStart.\' 00:00:00\', $priorEnd.\' 23:59:59\'])',
    $code
);

// activePatientMetrics()
$code = str_replace(
    'private function activePatientMetrics(string $end, array $clinics): array',
    'private function activePatientMetrics(string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$totalBase = DB::table(\'od_procedure_logs\')
            ->selectRaw(\'ClinicNum, COUNT(DISTINCT PatNum) as count\')
            ->whereIn(\'ProcStatus\', ProcStatus::completed());',
    '$totalBase = DB::table(\'od_procedure_logs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, COUNT(DISTINCT PatNum) as count\')
            ->whereIn(\'ProcStatus\', ProcStatus::completed());',
    $code
);
$code = str_replace(
    '$activeBase = DB::table(\'od_procedure_logs as pl\')
            ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$startWindow.\' 00:00:00\', $end.\' 23:59:59\']);',
    '$activeBase = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$startWindow.\' 00:00:00\', $end.\' 23:59:59\']);',
    $code
);

// sumByClinic()
$code = str_replace(
    'private function sumByClinic(string $table, string $amountCol, string $dateCol, string $start, string $end, array $clinics): array',
    'private function sumByClinic(string $table, string $amountCol, string $dateCol, string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$q = DB::table($table)
            ->selectRaw("ClinicNum, SUM({$amountCol}) as total")
            ->whereBetween($dateCol, [$start, $end]);',
    '$q = DB::table($table)
            ->where(\'office_id\', $officeId)
            ->selectRaw("ClinicNum, SUM({$amountCol}) as total")
            ->whereBetween($dateCol, [$start, $end]);',
    $code
);

// collectionsByClinic()
$code = str_replace(
    'private function collectionsByClinic(string $start, string $end, array $clinics): array',
    'private function collectionsByClinic(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$splits = $this->sumByClinic(\'od_pay_splits\', \'SplitAmt\', \'DatePay\', $start, $end, $clinics);',
    '$splits = $this->sumByClinic(\'od_pay_splits\', \'SplitAmt\', \'DatePay\', $start, $end, $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$qIns = DB::table(\'od_claim_procs\')
            ->selectRaw(\'ClinicNum, SUM(InsPayAmt) as total\')
            ->whereBetween(\'DateCP\', [$start, $end])
            ->where(\'Status\', \'!=\', 0);',
    '$qIns = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, SUM(InsPayAmt) as total\')
            ->whereBetween(\'DateCP\', [$start, $end])
            ->where(\'Status\', \'!=\', 0);',
    $code
);

// 3. payors() & payorRows()
$code = str_replace(
    "public function payors(string \$start, string \$end, string \$subtab = 'default', array \$clinics = []): array\n    {\n        \$columns = \$this->payorColumns();\n        \$percentDiff = \$subtab === 'percent-diff-last-year';",
    "public function payors(string \$start, string \$end, string \$subtab = 'default', array \$clinics = [], ?int \$officeId = null): array\n    {\n        \$officeId = \$this->resolveOfficeId(\$officeId);\n        \$this->clinicNames = \$this->clinics->all(\$officeId);\n        \$columns = \$this->payorColumns();\n        \$percentDiff = \$subtab === 'percent-diff-last-year';",
    $code
);
$code = str_replace('$this->payorRows($start, $end, $clinics)', '$this->payorRows($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->payorRows($lyStart, $lyEnd, $clinics)', '$this->payorRows($lyStart, $lyEnd, $clinics, $officeId)', $code);

// payorRows()
$code = str_replace(
    'private function payorRows(string $start, string $end, array $clinics): array',
    'private function payorRows(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$latestClaim = $this->payors->planForPatientSubquery();',
    '$latestClaim = $this->payors->planForPatientSubquery($officeId);',
    $code
);
$code = str_replace(
    '$prodQ = DB::table(\'od_procedure_logs as pl\')
            ->leftJoinSub($latestClaim, \'cp\', \'pl.PatNum\', \'=\', \'cp.PatNum\')',
    '$prodQ = DB::table(\'od_procedure_logs as pl\')
            ->leftJoinSub($latestClaim, \'cp\', \'pl.PatNum\', \'=\', \'cp.PatNum\')
            ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$adjQ = DB::table(\'od_adjustments as a\')
            ->leftJoinSub($latestClaim, \'cp\', \'a.PatNum\', \'=\', \'cp.PatNum\')',
    '$adjQ = DB::table(\'od_adjustments as a\')
            ->leftJoinSub($latestClaim, \'cp\', \'a.PatNum\', \'=\', \'cp.PatNum\')
            ->where(\'a.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$colQ = DB::table(\'od_pay_splits as p\')
            ->leftJoinSub($latestClaim, \'cp\', \'p.PatNum\', \'=\', \'cp.PatNum\')',
    '$colQ = DB::table(\'od_pay_splits as p\')
            ->leftJoinSub($latestClaim, \'cp\', \'p.PatNum\', \'=\', \'cp.PatNum\')
            ->where(\'p.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$insColQ = DB::table(\'od_claim_procs\')
            ->selectRaw(\'COALESCE(PlanNum, 0) AS PlanNum, ClinicNum, InsPayAmt\')',
    '$insColQ = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'COALESCE(PlanNum, 0) AS PlanNum, ClinicNum, InsPayAmt\')',
    $code
);
$code = str_replace(
    '$woQ = DB::table(\'od_claim_procs\')
            ->selectRaw(\'COALESCE(PlanNum, 0) AS PlanNum, ClinicNum, WriteOff\')',
    '$woQ = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'COALESCE(PlanNum, 0) AS PlanNum, ClinicNum, WriteOff\')',
    $code
);
$code = str_replace(
    '$caQ = DB::table(\'od_procedure_logs as pl\')
            ->leftJoinSub($latestClaim, \'cp\', \'pl.PatNum\', \'=\', \'cp.PatNum\')',
    '$caQ = DB::table(\'od_procedure_logs as pl\')
            ->leftJoinSub($latestClaim, \'cp\', \'pl.PatNum\', \'=\', \'cp.PatNum\')
            ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace('$this->newPatientsByPayor($start, $end, $clinics)', '$this->newPatientsByPayor($start, $end, $clinics, $officeId)', $code);

// newPatientsByPayor()
$code = str_replace(
    'private function newPatientsByPayor(string $start, string $end, array $clinics): array',
    'private function newPatientsByPayor(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$newVisits = $this->patientVisits->newPatientVisits($start, $end, $clinics);',
    '$newVisits = $this->patientVisits->newPatientVisits($start, $end, $clinics, [], $officeId);',
    $code
);
$code = str_replace(
    '$q = DB::table(\'od_procedure_logs as pl\')
            ->leftJoinSub($latestClaim, \'cp\', \'pl.PatNum\', \'=\', \'cp.PatNum\')',
    '$q = DB::table(\'od_procedure_logs as pl\')
            ->leftJoinSub($latestClaim, \'cp\', \'pl.PatNum\', \'=\', \'cp.PatNum\')
            ->where(\'pl.office_id\', $officeId)',
    $code
);

// payorLabel()
$code = str_replace(
    'private function payorLabel($planNum): string
    {
        return $this->payors->payorLabel($planNum);
    }',
    'private function payorLabel($planNum, ?int $officeId = null): string
    {
        return $this->payors->payorLabel($planNum, $officeId);
    }',
    $code
);

// 4. cancellations() & cancellationRows()
$code = str_replace(
    "public function cancellations(string \$start, string \$end, string \$subtab = 'default', array \$clinics = []): array\n    {\n        \$columns = \$this->cancellationColumns();\n        \$percentDiff = \$subtab === 'percent-diff-last-year';",
    "public function cancellations(string \$start, string \$end, string \$subtab = 'default', array \$clinics = [], ?int \$officeId = null): array\n    {\n        \$officeId = \$this->resolveOfficeId(\$officeId);\n        \$this->clinicNames = \$this->clinics->all(\$officeId);\n        \$columns = \$this->cancellationColumns();\n        \$percentDiff = \$subtab === 'percent-diff-last-year';",
    $code
);
$code = str_replace('$this->cancellationRows($start, $end, $clinics)', '$this->cancellationRows($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->cancellationRows($lyStart, $lyEnd, $clinics)', '$this->cancellationRows($lyStart, $lyEnd, $clinics, $officeId)', $code);

// cancellationRows()
$code = str_replace(
    'private function cancellationRows(string $start, string $end, array $clinics): array',
    'private function cancellationRows(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$brokenApptsQ = DB::table(\'od_appointments as a\')
            ->selectRaw(\'a.ClinicNum, a.AptNum, a.PatNum\')
            ->where(\'a.AptStatus\', \'5\')
            ->whereBetween(\'a.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',
    '$brokenApptsQ = DB::table(\'od_appointments as a\')
            ->where(\'a.office_id\', $officeId)
            ->selectRaw(\'a.ClinicNum, a.AptNum, a.PatNum\')
            ->where(\'a.AptStatus\', \'5\')
            ->whereBetween(\'a.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',
    $code
);
$code = str_replace(
    '$feeRows = DB::table(\'od_procedure_logs\')
                ->selectRaw(\'AptNum, SUM(ProcFee) as total_fee\')
                ->whereIn(\'AptNum\', $brokenAptNums)
                ->groupBy(\'AptNum\')
                ->pluck(\'total_fee\', \'AptNum\')
                ->all();',
    '$feeRows = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->selectRaw(\'AptNum, SUM(ProcFee) as total_fee\')
                ->whereIn(\'AptNum\', $brokenAptNums)
                ->groupBy(\'AptNum\')
                ->pluck(\'total_fee\', \'AptNum\')
                ->all();',
    $code
);
$code = str_replace('$this->countAppointments($start, $end, $clinics, null)', '$this->countAppointments($start, $end, $clinics, null, $officeId)', $code);
$code = str_replace('$this->countAppointments($start, $end, $clinics, \'1\')', '$this->countAppointments($start, $end, $clinics, \'1\', $officeId)', $code);

// countAppointments()
$code = str_replace(
    'private function countAppointments(string $start, string $end, array $clinics, ?string $status): array',
    'private function countAppointments(string $start, string $end, array $clinics, ?string $status, int $officeId): array',
    $code
);
$code = str_replace(
    '$q = DB::table(\'od_appointments\')
            ->selectRaw(\'ClinicNum, COUNT(*) as count\')
            ->whereBetween(\'AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',
    '$q = DB::table(\'od_appointments\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, COUNT(*) as count\')
            ->whereBetween(\'AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',
    $code
);

// 5. productionDetails() & helpers
$code = str_replace(
    "public function productionDetails(string \$start, string \$end, array \$group = [], array \$clinics = []): array\n    {\n        \$allowed = ['provider', 'date'];",
    "public function productionDetails(string \$start, string \$end, array \$group = [], array \$clinics = [], ?int \$officeId = null): array\n    {\n        \$officeId = \$this->resolveOfficeId(\$officeId);\n        \$this->clinicNames = \$this->clinics->all(\$officeId);\n        \$allowed = ['provider', 'date'];",
    $code
);
$code = str_replace('$this->productionDetailRows($start, $end, $dims, $clinics)', '$this->productionDetailRows($start, $end, $dims, $clinics, $officeId)', $code);

// productionDetailRows()
$code = str_replace(
    'private function productionDetailRows(string $start, string $end, array $dims, array $clinics): array',
    'private function productionDetailRows(string $start, string $end, array $dims, array $clinics, int $officeId): array',
    $code
);
$code = str_replace('$this->pdGroupedProduction($start, $end, $dims, $clinics)', '$this->pdGroupedProduction($start, $end, $dims, $clinics, $officeId)', $code);
$code = str_replace('$this->pdGroupedSum(\'od_adjustments\', \'AdjAmt\', \'AdjDate\', $dims, $start, $end, $clinics)', '$this->pdGroupedSum(\'od_adjustments\', \'AdjAmt\', \'AdjDate\', $dims, $start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->pdGroupedCollections($dims, $start, $end, $clinics)', '$this->pdGroupedCollections($dims, $start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->pdGroupedSum(\'od_claim_procs\', \'WriteOff\', \'ProcDate\', $dims, $start, $end, $clinics)', '$this->pdGroupedSum(\'od_claim_procs\', \'WriteOff\', \'ProcDate\', $dims, $start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->pdGroupedNewPatients($start, $end, $dims, $clinics)', '$this->pdGroupedNewPatients($start, $end, $dims, $clinics, $officeId)', $code);
$code = str_replace(
    '$providers = $withProvider ? DB::table(\'od_providers\')->get()->keyBy(\'ProvNum\') : collect();',
    '$providers = $withProvider ? DB::table(\'od_providers\')->where(\'office_id\', $officeId)->get()->keyBy(\'ProvNum\') : collect();',
    $code
);

// pdGroupedProduction()
$code = str_replace(
    'private function pdGroupedProduction(string $start, string $end, array $dims, array $clinics): array',
    'private function pdGroupedProduction(string $start, string $end, array $dims, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$q = DB::table(\'od_procedure_logs\')
            ->selectRaw("ClinicNum,',
    '$q = DB::table(\'od_procedure_logs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw("ClinicNum,',
    $code
);

// pdGroupedSum()
$code = str_replace(
    'private function pdGroupedSum(string $table, string $amountCol, string $dateCol, array $dims, string $start, string $end, array $clinics): array',
    'private function pdGroupedSum(string $table, string $amountCol, string $dateCol, array $dims, string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$q = DB::table($table)
            ->selectRaw("ClinicNum,',
    '$q = DB::table($table)
            ->where(\'office_id\', $officeId)
            ->selectRaw("ClinicNum,',
    $code
);

// pdGroupedCollections()
$code = str_replace(
    'private function pdGroupedCollections(array $dims, string $start, string $end, array $clinics): array',
    'private function pdGroupedCollections(array $dims, string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$splits = $this->pdGroupedSum(\'od_pay_splits\', \'SplitAmt\', \'DatePay\', $dims, $start, $end, $clinics);',
    '$splits = $this->pdGroupedSum(\'od_pay_splits\', \'SplitAmt\', \'DatePay\', $dims, $start, $end, $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$q = DB::table(\'od_claim_procs\')
            ->selectRaw("ClinicNum,',
    '$q = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw("ClinicNum,',
    $code
);

// pdGroupedNewPatients()
$code = str_replace(
    'private function pdGroupedNewPatients(string $start, string $end, array $dims, array $clinics): array',
    'private function pdGroupedNewPatients(string $start, string $end, array $dims, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    'private function pdGroupedNewPatients(string $start, string $end, array $dims, array $clinics, int $officeId): array
    {
        $firstVisit = $this->patients->firstVisitCohort();',
    'private function pdGroupedNewPatients(string $start, string $end, array $dims, array $clinics, int $officeId): array
    {
        $firstVisit = $this->patients->firstVisitCohort($officeId);',
    $code
);

// 6. performance()
$code = str_replace(
    'public function performance(string $start, string $end, string $subtab = \'default\', array $clinics = []): array',
    'public function performance(string $start, string $end, string $subtab = \'default\', array $clinics = [], ?int $officeId = null): array',
    $code
);
$code = str_replace(
    '$actualProdQuery = DB::table(\'od_procedure_logs\')
            ->selectRaw(\'ProcDate as d, SUM(ProcFee) as gross, COUNT(DISTINCT PatNum) as pts_visits\')',
    '$officeId = $this->resolveOfficeId($officeId);
        $this->clinicNames = $this->clinics->all($officeId);
        $actualProdQuery = DB::table(\'od_procedure_logs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ProcDate as d, SUM(ProcFee) as gross, COUNT(DISTINCT PatNum) as pts_visits\')',
    $code
);
$code = str_replace(
    '$adjQuery = DB::table(\'od_adjustments\')
            ->selectRaw(\'AdjDate as d, SUM(AdjAmt) as total\')',
    '$adjQuery = DB::table(\'od_adjustments\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'AdjDate as d, SUM(AdjAmt) as total\')',
    $code
);
$code = str_replace(
    '$woQuery = DB::table(\'od_claim_procs\')
            ->selectRaw(\'ProcDate as d, SUM(WriteOff) as total\')',
    '$woQuery = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ProcDate as d, SUM(WriteOff) as total\')',
    $code
);
$code = str_replace(
    '$colPatQuery = DB::table(\'od_pay_splits\')
            ->selectRaw(\'DatePay as d, SUM(SplitAmt) as total\')',
    '$colPatQuery = DB::table(\'od_pay_splits\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'DatePay as d, SUM(SplitAmt) as total\')',
    $code
);
$code = str_replace(
    '$colInsQuery = DB::table(\'od_claim_procs\')
            ->selectRaw(\'DateCP as d, SUM(InsPayAmt) as total\')',
    '$colInsQuery = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'DateCP as d, SUM(InsPayAmt) as total\')',
    $code
);
$code = str_replace(
    '$newVisits = $this->patientVisits->newPatientVisits($start, $end, $clinics);',
    '$newVisits = $this->patientVisits->newPatientVisits($start, $end, $clinics, [], $officeId);',
    $code
);
$code = str_replace(
    '$officeId = Office::getActiveOfficeId() ?? 1;
        $snapshots = $this->scheduleSnapshots->getSnapshotSummary($officeId, $start, $end, $clinics);',
    '$snapshots = $this->scheduleSnapshots->getSnapshotSummary($officeId, $start, $end, $clinics);',
    $code
);
$code = str_replace(
    '$schedProdQuery = DB::table(\'od_appointments as a\')
            ->join(\'od_procedure_logs as pl\', \'a.AptNum\', \'=\', \'pl.AptNum\')',
    '$schedProdQuery = DB::table(\'od_appointments as a\')
            ->join(\'od_procedure_logs as pl\', function ($j) use ($officeId) { $j->on(\'a.AptNum\', \'=\', \'pl.AptNum\')->where(\'pl.office_id\', \'=\', $officeId); })
            ->where(\'a.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$schedApptsQuery = DB::table(\'od_appointments\')
            ->selectRaw(\'DATE(AptDateTime) as d, COUNT(DISTINCT PatNum) as total\')',
    '$schedApptsQuery = DB::table(\'od_appointments\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'DATE(AptDateTime) as d, COUNT(DISTINCT PatNum) as total\')',
    $code
);
$code = str_replace(
    '$schedNptQuery = DB::table(\'od_appointments\')
            ->selectRaw(\'DATE(AptDateTime) as d, COUNT(DISTINCT PatNum) as total\')',
    '$schedNptQuery = DB::table(\'od_appointments\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'DATE(AptDateTime) as d, COUNT(DISTINCT PatNum) as total\')',
    $code
);
$code = str_replace(
    '$schedQuery = DB::table(\'od_schedules\')
            ->select(\'SchedDate\', \'StartTime\', \'StopTime\')',
    '$schedQuery = DB::table(\'od_schedules\')
            ->where(\'office_id\', $officeId)
            ->select(\'SchedDate\', \'StartTime\', \'StopTime\')',
    $code
);
$code = str_replace(
    '$apptList = DB::table(\'od_appointments\')
            ->select(\'AptDateTime\', \'Pattern\')',
    '$apptList = DB::table(\'od_appointments\')
            ->where(\'office_id\', $officeId)
            ->select(\'AptDateTime\', \'Pattern\')',
    $code
);
$code = str_replace(
    '$apptsWithPat = DB::table(\'od_appointments\')
            ->select(\'PatNum\', DB::raw(\'DATE(AptDateTime) as d\'))',
    '$apptsWithPat = DB::table(\'od_appointments\')
            ->where(\'office_id\', $officeId)
            ->select(\'PatNum\', DB::raw(\'DATE(AptDateTime) as d\'))',
    $code
);
$code = str_replace(
    '$unschedProcsByPat = DB::table(\'od_procedure_logs\')
            ->whereIn(\'PatNum\', $allPatsInPeriod)',
    '$unschedProcsByPat = DB::table(\'od_procedure_logs\')
            ->where(\'office_id\', $officeId)
            ->whereIn(\'PatNum\', $allPatsInPeriod)',
    $code
);

// 7. providers() & providerRows()
$code = str_replace(
    "public function providers(string \$start, string \$end, string \$subtab = 'default', array \$clinics = []): array\n    {\n        \$columns = \$this->providerColumns();\n        \$percentDiff = \$subtab === 'percent-diff-last-year';",
    "public function providers(string \$start, string \$end, string \$subtab = 'default', array \$clinics = [], ?int \$officeId = null): array\n    {\n        \$officeId = \$this->resolveOfficeId(\$officeId);\n        \$this->clinicNames = \$this->clinics->all(\$officeId);\n        \$columns = \$this->providerColumns();\n        \$percentDiff = \$subtab === 'percent-diff-last-year';",
    $code
);
$code = str_replace('$this->providerRows($start, $end, $clinics)', '$this->providerRows($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->providerRows($lyStart, $lyEnd, $clinics)', '$this->providerRows($lyStart, $lyEnd, $clinics, $officeId)', $code);

// providerRows()
$code = str_replace(
    'private function providerRows(string $start, string $end, array $clinics): array',
    'private function providerRows(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$prodQ = DB::table(\'od_procedure_logs\')
            ->selectRaw("ClinicNum, ProvNum,',
    '$prodQ = DB::table(\'od_procedure_logs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw("ClinicNum, ProvNum,',
    $code
);
$code = str_replace(
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
            ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->groupBy(\'pl.PatNum\');',
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->groupBy(\'pl.PatNum\');',
    $code
);
$code = str_replace(
    '$patsCur = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
            ->selectRaw(\'pl.ClinicNum, pl.ProvNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$curStart.\' 00:00:00\', $end.\' 23:59:59\'])',
    '$patsCur = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
            ->selectRaw(\'pl.ClinicNum, pl.ProvNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$curStart.\' 00:00:00\', $end.\' 23:59:59\'])',
    $code
);
$code = str_replace(
    '$patsPrior = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
            ->selectRaw(\'pl.ClinicNum, pl.ProvNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$priorStart.\' 00:00:00\', $priorEnd.\' 23:59:59\'])',
    '$patsPrior = DB::table(\'od_procedure_logs as pl\')
            ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
            ->selectRaw(\'pl.ClinicNum, pl.ProvNum, COUNT(DISTINCT pl.PatNum) as count\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$priorStart.\' 00:00:00\', $priorEnd.\' 23:59:59\'])',
    $code
);
$code = str_replace('$this->sumByClinicProvider(\'od_adjustments\', \'AdjAmt\', \'AdjDate\', $start, $end, $clinics)', '$this->sumByClinicProvider(\'od_adjustments\', \'AdjAmt\', \'AdjDate\', $start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->sumByClinicProvider(\'od_claim_procs\', \'WriteOff\', \'ProcDate\', $start, $end, $clinics)', '$this->sumByClinicProvider(\'od_claim_procs\', \'WriteOff\', \'ProcDate\', $start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->collectionsByClinicProvider($start, $end, $clinics)', '$this->collectionsByClinicProvider($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->scheduledHoursByClinicProvider($start, $end, $clinics)', '$this->scheduledHoursByClinicProvider($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->newPatientsByClinicProvider($start, $end, $clinics)', '$this->newPatientsByClinicProvider($start, $end, $clinics, $officeId)', $code);
$code = str_replace(
    '$providers = DB::table(\'od_providers\')->get()->keyBy(\'ProvNum\');',
    '$providers = DB::table(\'od_providers\')->where(\'office_id\', $officeId)->get()->keyBy(\'ProvNum\');',
    $code
);
$code = str_replace(
    '$specialtyDefs = DB::table(\'od_definitions\')->where(\'Category\', 35)->get()->keyBy(\'DefNum\');',
    '$specialtyDefs = DB::table(\'od_definitions\')->where(\'office_id\', $officeId)->where(\'Category\', 35)->get()->keyBy(\'DefNum\');',
    $code
);

// sumByClinicProvider()
$code = str_replace(
    'private function sumByClinicProvider(string $table, string $amountCol, string $dateCol, string $start, string $end, array $clinics): array',
    'private function sumByClinicProvider(string $table, string $amountCol, string $dateCol, string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$q = DB::table($table)
            ->selectRaw("ClinicNum, ProvNum, SUM({$amountCol}) as total")
            ->whereBetween($dateCol, [$start, $end]);',
    '$q = DB::table($table)
            ->where(\'office_id\', $officeId)
            ->selectRaw("ClinicNum, ProvNum, SUM({$amountCol}) as total")
            ->whereBetween($dateCol, [$start, $end]);',
    $code
);

// collectionsByClinicProvider()
$code = str_replace(
    'private function collectionsByClinicProvider(string $start, string $end, array $clinics): array',
    'private function collectionsByClinicProvider(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$splits = $this->sumByClinicProvider(\'od_pay_splits\', \'SplitAmt\', \'DatePay\', $start, $end, $clinics);',
    '$splits = $this->sumByClinicProvider(\'od_pay_splits\', \'SplitAmt\', \'DatePay\', $start, $end, $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$qIns = DB::table(\'od_claim_procs\')
            ->selectRaw(\'ClinicNum, ProvNum, SUM(InsPayAmt) as total\')
            ->whereBetween(\'DateCP\', [$start, $end])
            ->where(\'Status\', \'!=\', 0);',
    '$qIns = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, ProvNum, SUM(InsPayAmt) as total\')
            ->whereBetween(\'DateCP\', [$start, $end])
            ->where(\'Status\', \'!=\', 0);',
    $code
);

// scheduledHoursByClinicProvider()
$code = str_replace(
    'private function scheduledHoursByClinicProvider(string $start, string $end, array $clinics): array',
    'private function scheduledHoursByClinicProvider(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$q = DB::table(\'od_schedules\')
            ->select(\'ClinicNum\', \'ProvNum\', \'StartTime\', \'StopTime\')
            ->where(\'SchedType\', 1)
            ->whereBetween(\'SchedDate\', [$start, $end]);',
    '$q = DB::table(\'od_schedules\')
            ->where(\'office_id\', $officeId)
            ->select(\'ClinicNum\', \'ProvNum\', \'StartTime\', \'StopTime\')
            ->where(\'SchedType\', 1)
            ->whereBetween(\'SchedDate\', [$start, $end]);',
    $code
);

// newPatientsByClinicProvider()
$code = str_replace(
    'private function newPatientsByClinicProvider(string $start, string $end, array $clinics): array',
    'private function newPatientsByClinicProvider(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    'private function newPatientsByClinicProvider(string $start, string $end, array $clinics, int $officeId): array
    {
        $firstVisit = $this->patients->firstVisitCohort();',
    'private function newPatientsByClinicProvider(string $start, string $end, array $clinics, int $officeId): array
    {
        $firstVisit = $this->patients->firstVisitCohort($officeId);',
    $code
);

// 8. services() & serviceRows()
$code = str_replace(
    'public function services(string $start, string $end, string $subtab = \'default\', array $clinics = []): array',
    'public function services(string $start, string $end, string $subtab = \'default\', array $clinics = [], ?int $officeId = null): array',
    $code
);
$code = str_replace(
    '$qSrv = DB::table(\'od_procedure_logs as pl\')
            ->join(\'od_procedures as pc\', \'pl.CodeNum\', \'=\', \'pc.CodeNum\')',
    '$officeId = $this->resolveOfficeId($officeId);
        $this->clinicNames = $this->clinics->all($officeId);
        $qSrv = DB::table(\'od_procedure_logs as pl\')
            ->join(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pl.CodeNum\', \'=\', \'pc.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
            ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$metrics = $this->newPatientMetrics($start, $end, $clinics);',
    '$metrics = $this->newPatientMetrics($start, $end, $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$metricsYtd = $this->newPatientMetrics($ytdStart, $end, $clinics);',
    '$metricsYtd = $this->newPatientMetrics($ytdStart, $end, $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$qAct = DB::table(\'od_patients as pt\')
            ->join(\'od_procedure_logs as pl\', \'pt.PatNum\', \'=\', \'pl.PatNum\')',
    '$qAct = DB::table(\'od_patients as pt\')
            ->join(\'od_procedure_logs as pl\', function ($j) use ($officeId) { $j->on(\'pt.PatNum\', \'=\', \'pl.PatNum\')->where(\'pl.office_id\', \'=\', $officeId); })
            ->where(\'pt.office_id\', $officeId)',
    $code
);
$code = str_replace('$this->serviceRows($start, $end, $clinics)', '$this->serviceRows($start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->serviceRows($lyStart, $lyEnd, $clinics)', '$this->serviceRows($lyStart, $lyEnd, $clinics, $officeId)', $code);

// serviceRows()
$code = str_replace(
    'private function serviceRows(string $start, string $end, array $clinics): array',
    'private function serviceRows(string $start, string $end, array $clinics, int $officeId): array',
    $code
);
$code = str_replace(
    '$q = DB::table(\'od_procedure_logs as pl\')
            ->join(\'od_procedures as pc\', \'pl.CodeNum\', \'=\', \'pc.CodeNum\')',
    '$q = DB::table(\'od_procedure_logs as pl\')
            ->join(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pl.CodeNum\', \'=\', \'pc.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
            ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    'private function serviceRows(string $start, string $end, array $clinics, int $officeId): array
    {
        $q = DB::table(\'od_procedure_logs as pl\')
            ->join(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pl.CodeNum\', \'=\', \'pc.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
            ->where(\'pl.office_id\', $officeId)
            ->selectRaw(\'pl.ClinicNum, pl.ProvNum, pc.ProcCode, pc.Descript, pc.ProcCat, COUNT(*) as cnt, SUM(pl.ProcFee) as fee\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$start, $end]);
        if ($clinics) {
            $q->whereIn(\'pl.ClinicNum\', $clinics);
        }
        $data = $q->groupBy(\'pl.ClinicNum\', \'pl.ProvNum\', \'pc.ProcCode\', \'pc.Descript\', \'pc.ProcCat\')->get();

        $totalFee = $data->sum(\'fee\');
        $providers = DB::table(\'od_providers\')->get()->keyBy(\'ProvNum\');
        $cats = DB::table(\'od_definitions\')->where(\'Category\', 5)->get()->keyBy(\'DefNum\');',
    'private function serviceRows(string $start, string $end, array $clinics, int $officeId): array
    {
        $q = DB::table(\'od_procedure_logs as pl\')
            ->join(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pl.CodeNum\', \'=\', \'pc.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
            ->where(\'pl.office_id\', $officeId)
            ->selectRaw(\'pl.ClinicNum, pl.ProvNum, pc.ProcCode, pc.Descript, pc.ProcCat, COUNT(*) as cnt, SUM(pl.ProcFee) as fee\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'pl.ProcDate\', [$start, $end]);
        if ($clinics) {
            $q->whereIn(\'pl.ClinicNum\', $clinics);
        }
        $data = $q->groupBy(\'pl.ClinicNum\', \'pl.ProvNum\', \'pc.ProcCode\', \'pc.Descript\', \'pc.ProcCat\')->get();

        $totalFee = $data->sum(\'fee\');
        $providers = DB::table(\'od_providers\')->where(\'office_id\', $officeId)->get()->keyBy(\'ProvNum\');
        $cats = DB::table(\'od_definitions\')->where(\'office_id\', $officeId)->where(\'Category\', 5)->get()->keyBy(\'DefNum\');',
    $code
);

// 9. trends() & calculateTrendMetricBuckets()
$code = str_replace(
    'public function trends(string $start, string $end, string $subtab = \'default\', array $clinics = [], string $metric = \'BYO Production\', string $lob = \'\'): array',
    'public function trends(string $start, string $end, string $subtab = \'default\', array $clinics = [], string $metric = \'BYO Production\', string $lob = \'\', ?int $officeId = null): array',
    $code
);
$code = str_replace(
    '$res = $this->calculateTrendMetricBuckets($currentStart, $end, $clinics, $metric, $monthKeys);',
    '$officeId = $this->resolveOfficeId($officeId);
        $this->clinicNames = $this->clinics->all($officeId);
        $res = $this->calculateTrendMetricBuckets($currentStart, $end, $clinics, $metric, $monthKeys, $officeId);',
    $code
);

// calculateTrendMetricBuckets()
$code = str_replace(
    'private function calculateTrendMetricBuckets(string $start, string $end, array $clinics, string $metric, array $monthKeys): array',
    'private function calculateTrendMetricBuckets(string $start, string $end, array $clinics, string $metric, array $monthKeys, int $officeId): array',
    $code
);
$code = str_replace(
    '$docProvs = DB::table(\'od_providers\')
            ->where(\'Specialty\', \'!=\', 8)
            ->whereIn(\'IsSecondary\', [\'false\', \'0\', 0, false])
            ->pluck(\'ProvNum\')->toArray();',
    '$docProvs = DB::table(\'od_providers\')
            ->where(\'office_id\', $officeId)
            ->where(\'Specialty\', \'!=\', 8)
            ->whereIn(\'IsSecondary\', [\'false\', \'0\', 0, false])
            ->pluck(\'ProvNum\')->toArray();',
    $code
);
$code = str_replace(
    '$hygProvs = DB::table(\'od_providers\')
            ->where(function ($q) {
                $q->where(\'Specialty\', 8)
                    ->orWhereIn(\'IsSecondary\', [\'true\', \'1\', 1, true]);
            })
            ->pluck(\'ProvNum\')->toArray();',
    '$hygProvs = DB::table(\'od_providers\')
            ->where(\'office_id\', $officeId)
            ->where(function ($q) {
                $q->where(\'Specialty\', 8)
                    ->orWhereIn(\'IsSecondary\', [\'true\', \'1\', 1, true]);
            })
            ->pluck(\'ProvNum\')->toArray();',
    $code
);

// In calculateTrendMetricBuckets, scope all queries
$trendReplacements = [
    // 1. Pending Treatment
    '$q = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', [\'TP\', \'1\', 1])
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', [\'TP\', \'1\', 1])
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    // 1b. Tx Plans Presented
    '$qTx = DB::table(\'od_procedure_logs as pl\')
                ->whereNotNull(\'pl.DateTP\')
                ->where(\'pl.DateTP\', \'!=\', \'0001-01-01\')
                ->whereBetween(\'pl.DateTP\', [$start, $end]);' => '$qTx = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->whereNotNull(\'pl.DateTP\')
                ->where(\'pl.DateTP\', \'!=\', \'0001-01-01\')
                ->whereBetween(\'pl.DateTP\', [$start, $end]);',

    '$sub->select(\'CodeNum\')->from(\'od_procedures\')->where(\'ProcCode\', \'LIKE\', \'D8%\');' => '$sub->select(\'CodeNum\')->from(\'od_procedures\')->where(\'office_id\', $officeId)->where(\'ProcCode\', \'LIKE\', \'D8%\');',

    '$qWd = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, COUNT(DISTINCT DATE(pl.ProcDate)) as wd")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$qWd = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, COUNT(DISTINCT DATE(pl.ProcDate)) as wd")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    // 2. Doctor Production
    '$q = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereIn(\'pl.ProvNum\', $docProvs)
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereIn(\'pl.ProvNum\', $docProvs)
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    // 3. Hygiene Production
    '$q = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereIn(\'pl.ProvNum\', $hygProvs)
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereIn(\'pl.ProvNum\', $hygProvs)
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    // 4. Doctor Collections
    '$q = DB::table(\'od_pay_splits as ps\')
                ->selectRaw("ps.ClinicNum, {$mDatePay} as month, SUM(ps.SplitAmt) as val")
                ->whereIn(\'ps.ProvNum\', $docProvs)
                ->whereBetween(\'ps.DatePay\', [$start, $end]);' => '$q = DB::table(\'od_pay_splits as ps\')
                ->where(\'ps.office_id\', $officeId)
                ->selectRaw("ps.ClinicNum, {$mDatePay} as month, SUM(ps.SplitAmt) as val")
                ->whereIn(\'ps.ProvNum\', $docProvs)
                ->whereBetween(\'ps.DatePay\', [$start, $end]);',

    '$qIns = DB::table(\'od_claim_procs as cp\')
                ->selectRaw("cp.ClinicNum, {$mDateCP} as month, SUM(cp.InsPayAmt) as val")
                ->whereIn(\'cp.ProvNum\', $docProvs)
                ->whereBetween(\'cp.DateCP\', [$start, $end])
                ->where(\'cp.Status\', \'!=\', 0);' => '$qIns = DB::table(\'od_claim_procs as cp\')
                ->where(\'cp.office_id\', $officeId)
                ->selectRaw("cp.ClinicNum, {$mDateCP} as month, SUM(cp.InsPayAmt) as val")
                ->whereIn(\'cp.ProvNum\', $docProvs)
                ->whereBetween(\'cp.DateCP\', [$start, $end])
                ->where(\'cp.Status\', \'!=\', 0);',

    // 5. Hygiene Collections
    '$q = DB::table(\'od_pay_splits as ps\')
                ->selectRaw("ps.ClinicNum, {$mDatePay} as month, SUM(ps.SplitAmt) as val")
                ->whereIn(\'ps.ProvNum\', $hygProvs)
                ->whereBetween(\'ps.DatePay\', [$start, $end]);' => '$q = DB::table(\'od_pay_splits as ps\')
                ->where(\'ps.office_id\', $officeId)
                ->selectRaw("ps.ClinicNum, {$mDatePay} as month, SUM(ps.SplitAmt) as val")
                ->whereIn(\'ps.ProvNum\', $hygProvs)
                ->whereBetween(\'ps.DatePay\', [$start, $end]);',

    '$qIns = DB::table(\'od_claim_procs as cp\')
                ->selectRaw("cp.ClinicNum, {$mDateCP} as month, SUM(cp.InsPayAmt) as val")
                ->whereIn(\'cp.ProvNum\', $hygProvs)
                ->whereBetween(\'cp.DateCP\', [$start, $end])
                ->where(\'cp.Status\', \'!=\', 0);' => '$qIns = DB::table(\'od_claim_procs as cp\')
                ->where(\'cp.office_id\', $officeId)
                ->selectRaw("cp.ClinicNum, {$mDateCP} as month, SUM(cp.InsPayAmt) as val")
                ->whereIn(\'cp.ProvNum\', $hygProvs)
                ->whereBetween(\'cp.DateCP\', [$start, $end])
                ->where(\'cp.Status\', \'!=\', 0);',

    // 6. OTC % Collections
    '$gross = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as total")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$start.\' 00:00:00\', $end.\' 23:59:59\'])' => '$gross = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as total")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$start.\' 00:00:00\', $end.\' 23:59:59\'])',

    '$otcDefNums = DB::table(\'od_definitions\')
                ->where(\'Category\', 10)
                ->where(\'ItemName\', \'LIKE\', \'OTC%\')
                ->pluck(\'DefNum\')
                ->toArray();' => '$otcDefNums = DB::table(\'od_definitions\')
                ->where(\'office_id\', $officeId)
                ->where(\'Category\', 10)
                ->where(\'ItemName\', \'LIKE\', \'OTC%\')
                ->pluck(\'DefNum\')
                ->toArray();',

    '$otcColls = DB::table(\'od_pay_splits as ps\')
                ->join(\'od_payments as p\', \'ps.PayNum\', \'=\', \'p.PayNum\')
                ->selectRaw("ps.ClinicNum, {$mDatePay} as month, SUM(ps.SplitAmt) as total")
                ->whereBetween(\'ps.DatePay\', [$start, $end])
                ->whereIn(\'p.PayType\', ! empty($otcDefNums) ? $otcDefNums : [400, 401, 402, 403, 404])' => '$otcColls = DB::table(\'od_pay_splits as ps\')
                ->join(\'od_payments as p\', function ($j) use ($officeId) { $j->on(\'ps.PayNum\', \'=\', \'p.PayNum\')->where(\'p.office_id\', \'=\', $officeId); })
                ->selectRaw("ps.ClinicNum, {$mDatePay} as month, SUM(ps.SplitAmt) as total")
                ->where(\'ps.office_id\', $officeId)
                ->whereBetween(\'ps.DatePay\', [$start, $end])
                ->whereIn(\'p.PayType\', ! empty($otcDefNums) ? $otcDefNums : [400, 401, 402, 403, 404])',

    // 7. Broken Appts
    '$q = DB::table(\'od_appointments as apt\')
                ->selectRaw("apt.ClinicNum, {$mAptDate} as month, COUNT(*) as val")
                ->where(\'apt.AptStatus\', \'5\')
                ->whereBetween(\'apt.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);' => '$q = DB::table(\'od_appointments as apt\')
                ->where(\'apt.office_id\', $officeId)
                ->selectRaw("apt.ClinicNum, {$mAptDate} as month, COUNT(*) as val")
                ->where(\'apt.AptStatus\', \'5\')
                ->whereBetween(\'apt.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',

    // 8. Total Appts
    '$q = DB::table(\'od_appointments as apt\')
                ->selectRaw("apt.ClinicNum, {$mAptDate} as month, COUNT(*) as val")
                ->where(\'apt.AptStatus\', \'!=\', 6) // Exclude deleted
                ->whereBetween(\'apt.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);' => '$q = DB::table(\'od_appointments as apt\')
                ->where(\'apt.office_id\', $officeId)
                ->selectRaw("apt.ClinicNum, {$mAptDate} as month, COUNT(*) as val")
                ->where(\'apt.AptStatus\', \'!=\', 6) // Exclude deleted
                ->whereBetween(\'apt.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',

    // 9. Scheduled Appts
    '$q = DB::table(\'od_appointments as apt\')
                ->selectRaw("apt.ClinicNum, {$mAptDate} as month, COUNT(*) as val")
                ->whereIn(\'apt.AptStatus\', [1, 2])
                ->whereBetween(\'apt.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);' => '$q = DB::table(\'od_appointments as apt\')
                ->where(\'apt.office_id\', $officeId)
                ->selectRaw("apt.ClinicNum, {$mAptDate} as month, COUNT(*) as val")
                ->whereIn(\'apt.AptStatus\', [1, 2])
                ->whereBetween(\'apt.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',

    // 10. New Patient Dollars
    '$firstProcSub = DB::table(\'od_procedure_logs\')
                ->selectRaw(\'PatNum, MIN(ProcDate) as first_date\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->groupBy(\'PatNum\');' => '$firstProcSub = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->selectRaw(\'PatNum, MIN(ProcDate) as first_date\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->groupBy(\'PatNum\');',

    '$q = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstProcSub, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereRaw(\'DATE(pl.ProcDate) = DATE(fp.first_date)\')
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstProcSub, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereRaw(\'DATE(pl.ProcDate) = DATE(fp.first_date)\')
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    // 11. Patient Retention
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->groupBy(\'pl.PatNum\');' => '$firstProcs = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->groupBy(\'pl.PatNum\');',

    '$qProcs = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
                ->selectRaw(\'pl.ClinicNum, pl.PatNum, pl.ProcDate, fp.first_date\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$earliest36.\' 00:00:00\', $latestEnd.\' 23:59:59\']);' => '$qProcs = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstProcs, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
                ->selectRaw(\'pl.ClinicNum, pl.PatNum, pl.ProcDate, fp.first_date\')
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$earliest36.\' 00:00:00\', $latestEnd.\' 23:59:59\']);',

    // 12. Active Patients
    '$qTotal = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) as count\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed());' => '$qTotal = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) as count\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed());',

    '$qProcs = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw(\'pl.ClinicNum, pl.PatNum, pl.ProcDate\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$earliest24.\' 00:00:00\', $latestEnd.\' 23:59:59\']);' => '$qProcs = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw(\'pl.ClinicNum, pl.PatNum, pl.ProcDate\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$earliest24.\' 00:00:00\', $latestEnd.\' 23:59:59\']);',

    // 13. LOB code filter queries
    '$q = DB::table(\'od_procedure_logs as pl\')
                ->join(\'od_procedures as pc\', \'pc.CodeNum\', \'=\', \'pl.CodeNum\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereIn(\'pc.ProcCode\', $codeFilter)
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->join(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pc.CodeNum\', \'=\', \'pl.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereIn(\'pc.ProcCode\', $codeFilter)
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    // 14. Non-ortho
    '$q = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->where(\'pl.CodeNum\', \'!=\', 626)
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->where(\'pl.CodeNum\', \'!=\', 626)
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    // 15. Collections
    '$q = DB::table(\'od_pay_splits as ps\')
                ->selectRaw("ps.ClinicNum, {$mDatePay} as month, SUM(ps.SplitAmt) as val")
                ->whereBetween(\'ps.DatePay\', [$start, $end]);' => '$q = DB::table(\'od_pay_splits as ps\')
                ->where(\'ps.office_id\', $officeId)
                ->selectRaw("ps.ClinicNum, {$mDatePay} as month, SUM(ps.SplitAmt) as val")
                ->whereBetween(\'ps.DatePay\', [$start, $end]);',

    '$qIns = DB::table(\'od_claim_procs as cp\')
                ->selectRaw("cp.ClinicNum, {$mDateCP} as month, SUM(cp.InsPayAmt) as val")
                ->whereBetween(\'cp.DateCP\', [$start, $end])
                ->where(\'cp.Status\', \'!=\', 0);' => '$qIns = DB::table(\'od_claim_procs as cp\')
                ->where(\'cp.office_id\', $officeId)
                ->selectRaw("cp.ClinicNum, {$mDateCP} as month, SUM(cp.InsPayAmt) as val")
                ->whereBetween(\'cp.DateCP\', [$start, $end])
                ->where(\'cp.Status\', \'!=\', 0);',

    // 16. Net Prod
    '$qGross = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$qGross = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    '$qAdj = DB::table(\'od_adjustments as adj\')
                ->selectRaw("adj.ClinicNum, {$mAdjDate} as month, SUM(adj.AdjAmt) as val")
                ->whereBetween(\'adj.AdjDate\', [$start, $end]);' => '$qAdj = DB::table(\'od_adjustments as adj\')
                ->where(\'adj.office_id\', $officeId)
                ->selectRaw("adj.ClinicNum, {$mAdjDate} as month, SUM(adj.AdjAmt) as val")
                ->whereBetween(\'adj.AdjDate\', [$start, $end]);',

    '$qWo = DB::table(\'od_claim_procs as pl\')
                ->selectRaw("pl.ClinicNum, {$mCpDate} as month, SUM(pl.WriteOff) as val")
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$qWo = DB::table(\'od_claim_procs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mCpDate} as month, SUM(pl.WriteOff) as val")
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',
];

foreach ($trendReplacements as $search => $replace) {
    if (strpos($code, $search) !== false) {
        $code = str_replace($search, $replace, $code);
    } else {
        echo "Warning in calculateTrendMetricBuckets: target not found:\n".substr($search, 0, 70)."\n";
    }
}

// 10. claims()
$code = str_replace(
    'public function claims(string $start, string $end, string $subtab = \'default\', array $clinics = []): array',
    'public function claims(string $start, string $end, string $subtab = \'default\', array $clinics = [], ?int $officeId = null): array',
    $code
);
$code = str_replace(
    '$qTab = DB::table(\'od_claim_procs as cp\')
            ->join(\'od_procedure_logs as pl\', \'cp.ProcNum\', \'=\', \'pl.ProcNum\')
            ->selectRaw(\'cp.ClinicNum, SUBSTRING(cp.ProcDate, 9, 2) as d_day, COUNT(*) as c\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'cp.ProcDate\', [$monthStart, $monthEnd]);',
    '$officeId = $this->resolveOfficeId($officeId);
        $this->clinicNames = $this->clinics->all($officeId);
        $qTab = DB::table(\'od_claim_procs as cp\')
            ->join(\'od_procedure_logs as pl\', function ($j) use ($officeId) { $j->on(\'cp.ProcNum\', \'=\', \'pl.ProcNum\')->where(\'pl.office_id\', \'=\', $officeId); })
            ->selectRaw(\'cp.ClinicNum, SUBSTRING(cp.ProcDate, 9, 2) as d_day, COUNT(*) as c\')
            ->where(\'cp.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereBetween(\'cp.ProcDate\', [$monthStart, $monthEnd]);',
    $code
);

// 11. compliance() & complianceRows()
$code = str_replace(
    'public function compliance(string $start, string $end, string $subtab = \'default\', array $clinics = []): array',
    'public function compliance(string $start, string $end, string $subtab = \'default\', array $clinics = [], ?int $officeId = null): array',
    $code
);
$code = str_replace(
    '$providers = DB::table(\'od_providers\')->get()->keyBy(\'ProvNum\');',
    '$officeId = $this->resolveOfficeId($officeId);
        $this->clinicNames = $this->clinics->all($officeId);
        $providers = DB::table(\'od_providers\')->where(\'office_id\', $officeId)->get()->keyBy(\'ProvNum\');',
    $code
);
$code = str_replace('$this->complianceRows($start, $end, $clinics, $providers)', '$this->complianceRows($start, $end, $clinics, $providers, $officeId)', $code);
$code = str_replace('$this->complianceRows($lyStart, $lyEnd, $clinics, $providers)', '$this->complianceRows($lyStart, $lyEnd, $clinics, $providers, $officeId)', $code);

// complianceRows()
$code = str_replace(
    'private function complianceRows(string $start, string $end, array $clinics, $providers): array',
    'private function complianceRows(string $start, string $end, array $clinics, $providers, int $officeId): array',
    $code
);
$code = str_replace(
    '$qLogs = DB::table("$logTable as pl")
            ->leftJoin("$codeTable as pc", \'pl.CodeNum\', \'=\', \'pc.CodeNum\')',
    '$qLogs = DB::table("$logTable as pl")
            ->leftJoin("$codeTable as pc", function ($j) use ($officeId) { $j->on(\'pl.CodeNum\', \'=\', \'pc.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
            ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace('$this->sumByClinicProvider(\'od_adjustments\', \'AdjAmt\', \'AdjDate\', $start, $end, $clinics)', '$this->sumByClinicProvider(\'od_adjustments\', \'AdjAmt\', \'AdjDate\', $start, $end, $clinics, $officeId)', $code);
$code = str_replace('$this->sumByClinicProvider(\'od_claim_procs\', \'WriteOff\', \'ProcDate\', $start, $end, $clinics)', '$this->sumByClinicProvider(\'od_claim_procs\', \'WriteOff\', \'ProcDate\', $start, $end, $clinics, $officeId)', $code);

$code = str_replace(
    '$drillQ = DB::table("$logTable as pl")
            ->join("$patTable as p", \'pl.PatNum\', \'=\', \'p.PatNum\')',
    '$drillQ = DB::table("$logTable as pl")
            ->join("$patTable as p", function ($j) use ($officeId) { $j->on(\'pl.PatNum\', \'=\', \'p.PatNum\')->where(\'p.office_id\', \'=\', $officeId); })
            ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$visitsQ = DB::table("$logTable as pl")
            ->join("$patTable as p", \'pl.PatNum\', \'=\', \'p.PatNum\')',
    '$visitsQ = DB::table("$logTable as pl")
            ->join("$patTable as p", function ($j) use ($officeId) { $j->on(\'pl.PatNum\', \'=\', \'p.PatNum\')->where(\'p.office_id\', \'=\', $officeId); })
            ->where(\'pl.office_id\', $officeId)',
    $code
);

// 12. marketing()
$code = str_replace(
    'public function marketing(string $start, string $end, ?string $subtab, array $clinics, string $zip = \'ALL\'): array',
    'public function marketing(string $start, string $end, ?string $subtab, array $clinics, string $zip = \'ALL\', ?int $officeId = null): array',
    $code
);
$code = str_replace(
    '// --- GLOBAL MARKETING CHARTS (ALWAYS FETCHED) ---
        // Get ZIPs list for filter
        $allZips = DB::table(\'od_patients\')
            ->select(\'Zip\')
            ->whereNotNull(\'Zip\')
            ->where(\'Zip\', \'!=\', \'\')
            ->distinct()
            ->pluck(\'Zip\')
            ->toArray();',
    '$officeId = $this->resolveOfficeId($officeId);
        $this->clinicNames = $this->clinics->all($officeId);
        $firstVisit = $this->patients->firstVisitCohort($officeId);

        // --- GLOBAL MARKETING CHARTS (ALWAYS FETCHED) ---
        // Get ZIPs list for filter
        $allZips = DB::table(\'od_patients\')
            ->where(\'office_id\', $officeId)
            ->select(\'Zip\')
            ->whereNotNull(\'Zip\')
            ->where(\'Zip\', \'!=\', \'\')
            ->distinct()
            ->pluck(\'Zip\')
            ->toArray();',
    $code
);
$code = str_replace(
    '$newVisits = $this->patientVisits->newPatientVisits($start, $end, $clinics);',
    '$newVisits = $this->patientVisits->newPatientVisits($start, $end, $clinics, [], $officeId);',
    $code
);
$code = str_replace(
    '$allOpsQuery = DB::table(\'od_procedure_logs as pl\')
            ->join(\'od_patients as p\', \'p.PatNum\', \'=\', \'pl.PatNum\')
            ->leftJoin(\'od_claim_procs as cp\', \'cp.PatNum\', \'=\', \'p.PatNum\')',
    '$allOpsQuery = DB::table(\'od_procedure_logs as pl\')
            ->join(\'od_patients as p\', function ($j) use ($officeId) { $j->on(\'p.PatNum\', \'=\', \'pl.PatNum\')->where(\'p.office_id\', \'=\', $officeId); })
            ->leftJoin(\'od_claim_procs as cp\', function ($j) use ($officeId) { $j->on(\'cp.PatNum\', \'=\', \'p.PatNum\')->where(\'cp.office_id\', \'=\', $officeId); })
            ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$gQuery = DB::table(\'od_procedure_logs as pl\')
                ->join(\'od_patients as p\', \'p.PatNum\', \'=\', \'pl.PatNum\')',
    '$gQuery = DB::table(\'od_procedure_logs as pl\')
                ->join(\'od_patients as p\', function ($j) use ($officeId) { $j->on(\'p.PatNum\', \'=\', \'pl.PatNum\')->where(\'p.office_id\', \'=\', $officeId); })
                ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$ageQuery = DB::table(\'od_procedure_logs as pl\')
                ->join(\'od_patients as p\', \'p.PatNum\', \'=\', \'pl.PatNum\')',
    '$ageQuery = DB::table(\'od_procedure_logs as pl\')
                ->join(\'od_patients as p\', function ($j) use ($officeId) { $j->on(\'p.PatNum\', \'=\', \'pl.PatNum\')->where(\'p.office_id\', \'=\', $officeId); })
                ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$goalQuery = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisit, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->join(\'od_patients as p\', \'p.PatNum\', \'=\', \'pl.PatNum\')',
    '$goalQuery = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisit, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->join(\'od_patients as p\', function ($j) use ($officeId) { $j->on(\'p.PatNum\', \'=\', \'pl.PatNum\')->where(\'p.office_id\', \'=\', $officeId); })
                ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$volQuery = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisit, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->join(\'od_patients as p\', \'p.PatNum\', \'=\', \'pl.PatNum\')',
    '$volQuery = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisit, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->join(\'od_patients as p\', function ($j) use ($officeId) { $j->on(\'p.PatNum\', \'=\', \'pl.PatNum\')->where(\'p.office_id\', \'=\', $officeId); })
                ->where(\'pl.office_id\', $officeId)',
    $code
);
$code = str_replace(
    '$patientsInfo = DB::table(\'od_patients\')
                ->select(\'PatNum\', \'LName\', \'FName\', \'HmPhone\', \'Email\')
                ->whereIn(\'PatNum\', $flatPatIds)',
    '$patientsInfo = DB::table(\'od_patients\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'LName\', \'FName\', \'HmPhone\', \'Email\')
                ->whereIn(\'PatNum\', $flatPatIds)',
    $code
);
$code = str_replace(
    '$lifetimeData = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', DB::raw(\'SUM(ProcFee) as total_fee\'), DB::raw(\'COUNT(DISTINCT ProcDate) as visit_count\'))
                ->whereIn(\'PatNum\', $flatPatIds)',
    '$lifetimeData = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', DB::raw(\'SUM(ProcFee) as total_fee\'), DB::raw(\'COUNT(DISTINCT ProcDate) as visit_count\'))
                ->whereIn(\'PatNum\', $flatPatIds)',
    $code
);

// 13. monthlyPracticeScorecards()
$code = str_replace(
    'public function monthlyPracticeScorecards(string $start, string $end, ?string $subtab, array $clinics): array',
    'public function monthlyPracticeScorecards(string $start, string $end, ?string $subtab, array $clinics, ?int $officeId = null): array',
    $code
);
$code = str_replace(
    '$officesData = $this->offices($start, $end, \'default\', $clinics);',
    '$officeId = $this->resolveOfficeId($officeId);
        $this->clinicNames = $this->clinics->all($officeId);
        $officesData = $this->offices($start, $end, \'default\', $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$cancellationsData = $this->cancellations($start, $end, \'default\', $clinics);',
    '$cancellationsData = $this->cancellations($start, $end, \'default\', $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$prodDetails = $this->productionDetails($start, $end, [\'provider\'], $clinics);',
    '$prodDetails = $this->productionDetails($start, $end, [\'provider\'], $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$lyOffices = $this->offices($lyStart, $lyEnd, \'default\', $clinics);',
    '$lyOffices = $this->offices($lyStart, $lyEnd, \'default\', $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$lyCancellations = $this->cancellations($lyStart, $lyEnd, \'default\', $clinics);',
    '$lyCancellations = $this->cancellations($lyStart, $lyEnd, \'default\', $clinics, $officeId);',
    $code
);
$code = str_replace(
    '$lyProdDetails = $this->productionDetails($lyStart, $lyEnd, [\'provider\'], $clinics);',
    '$lyProdDetails = $this->productionDetails($lyStart, $lyEnd, [\'provider\'], $clinics, $officeId);',
    $code
);

file_put_contents($svcFile, $code);
echo "OperationsAnalyticsService updated.\n";
