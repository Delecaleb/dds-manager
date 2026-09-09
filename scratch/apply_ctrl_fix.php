<?php

$ctrlFile = 'c:/xampp/htdocs/dds-manager/app/Http/Controllers/OperationsController.php';
$svcFile = 'c:/xampp/htdocs/dds-manager/app/Services/OpenDental/OperationsAnalyticsService.php';

// Backup original files if not backed up yet
if (! file_exists($ctrlFile.'.orig')) {
    copy($ctrlFile, $ctrlFile.'.orig');
}
if (! file_exists($svcFile.'.orig')) {
    copy($svcFile, $svcFile.'.orig');
}

// ---------------------------------------------------------------------
// 1. UPDATE OperationsController.php
// ---------------------------------------------------------------------
$ctrlCode = file_get_contents($ctrlFile.'.orig');

// index()
$oldIndex = <<<'PHP'
    public function index(string $tab = 'offices', ?string $subtab = null)
    {
        if (! array_key_exists($tab, $this->tabs())) {
            abort(404);
        }

        return view('operations.index', [
            'tabs' => $this->tabs(),
            'subtabsByTab' => $this->subtabsByTab(),
            'activeTab' => $tab,
            'activeSubtab' => $subtab ?: $this->defaultSubtab($tab),
        ]);
    }
PHP;

$newIndex = <<<'PHP'
    public function index(string $tab = 'offices', ?string $subtab = null)
    {
        if (! array_key_exists($tab, $this->tabs())) {
            abort(404);
        }

        $officeId = Office::getActiveOfficeId() ?? 1;
        $clinics = $this->clinics->all($officeId);

        return view('operations.index', [
            'tabs' => $this->tabs(),
            'subtabsByTab' => $this->subtabsByTab(),
            'activeTab' => $tab,
            'activeSubtab' => $subtab ?: $this->defaultSubtab($tab),
            'clinics' => $clinics,
        ]);
    }
PHP;

$ctrlCode = str_replace($oldIndex, $newIndex, $ctrlCode);

// data()
$oldDataHead = <<<'PHP'
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->endOfMonth()->toDateString());
        $subtab = $subtab ?: $this->defaultSubtab($tab);
        $clinics = array_filter(explode(',', (string) $request->input('clinics', '')), 'strlen');
PHP;

$newDataHead = <<<'PHP'
        $officeId = Office::getActiveOfficeId() ?? 1;
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->endOfMonth()->toDateString());
        $subtab = $subtab ?: $this->defaultSubtab($tab);
        $clinics = array_filter(explode(',', (string) $request->input('clinics', '')), 'strlen');
PHP;

$ctrlCode = str_replace($oldDataHead, $newDataHead, $ctrlCode);

$ctrlCode = str_replace('$service->offices($start, $end, $subtab, $clinics)', '$service->offices($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->productionDetails($start, $end, $group, $clinics)', '$service->productionDetails($start, $end, $group, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->cancellations($start, $end, $subtab, $clinics)', '$service->cancellations($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->payors($start, $end, $subtab, $clinics)', '$service->payors($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->providers($start, $end, $subtab, $clinics)', '$service->providers($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->performance($start, $end, $subtab, $clinics)', '$service->performance($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->services($start, $end, $subtab, $clinics)', '$service->services($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->trends($start, $end, $subtab, $clinics, $metric, $lob)', '$service->trends($start, $end, $subtab, $clinics, $metric, $lob, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->claims($start, $end, $subtab, $clinics)', '$service->claims($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->compliance($start, $end, $subtab, $clinics)', '$service->compliance($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->marketing($start, $end, $subtab, $clinics, $zip)', '$service->marketing($start, $end, $subtab, $clinics, $zip, $officeId)', $ctrlCode);
$ctrlCode = str_replace('$service->monthlyPracticeScorecards($start, $end, $subtab, $clinics)', '$service->monthlyPracticeScorecards($start, $end, $subtab, $clinics, $officeId)', $ctrlCode);

// drilldown() top initialization
$oldDrillTop = <<<'PHP'
    public function drilldown(Request $request)
    {
        $metric = $request->input('metric');
        $clinicNum = $request->input('clinic_num');
        $provNum = $request->input('prov_num');
        $start = $request->input('start_date', $request->input('start', now()->startOfMonth()->toDateString()));
        $end = $request->input('end_date', $request->input('end', now()->toDateString()));

        if ($request->input('subtab') === 'last-year') {
            $start = Carbon::parse($start)->subYear()->toDateString();
            $end = Carbon::parse($end)->subYear()->toDateString();
        }

        $title = 'Drilldown';
        $columns = [];
        $rows = [];
        $totals = null;

        $providerInfo = null;
        if ($provNum) {
            $p = OdProvider::where('ProvNum', $provNum)->first();
            if ($p) {
                $name = trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? ''));
                $providerInfo = [
                    'name' => $name ?: 'Provider '.$p->ProvNum,
                    'id' => $p->ProvNum.($p->Abbr ? ' - '.$p->Abbr : ''),
                ];
            }
        }

        $providers = OdProvider::all()->keyBy('ProvNum');

        $resolveProvName = function ($provNum) use ($providers) {
            $p = $providers->get($provNum);
            if (! $p) {
                return 'Provider '.$provNum;
            }
            $abbr = trim($p->Abbr ?? '');
            $lName = trim($p->LName ?? '');
            $fName = trim($p->FName ?: $p->PName ?: ($p->PreferredName ?? ''));
            if ($fName !== '' && $lName !== '') {
                return "$fName $lName";
            }

            return $lName ?: ($fName ?: ($abbr ?: 'Provider '.$provNum));
        };

        // Common Provider mapping
        $provMap = $providers->mapWithKeys(function ($p) use ($resolveProvName) {
            return [$p->ProvNum => $resolveProvName($p->ProvNum)];
        })->toArray();

        // Common Patient mapping
        $mapPatients = function ($patNums) {
            return OdPatient::whereIn('PatNum', $patNums)->get()->mapWithKeys(function ($p) {
                return [$p->PatNum => $p->LName.', '.$p->FName];
            })->toArray();
        };

        $formatProv = function ($pNum) use ($providers, $provMap) {
            if (! $pNum) {
                return ['id' => '—', 'name' => ['label' => 'Unknown', 'link' => false, 'prov_num' => 0]];
            }
            $p = $providers->get($pNum);
            $abbr = $p ? ($p->Abbr ?? '') : '';
            $provIdStr = $pNum.($abbr ? ' - '.strtoupper($abbr) : '');
            $provName = $p ? trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? '')) : ($provMap[$pNum] ?? 'Unknown');
            if (! $provName) {
                $provName = $provMap[$pNum] ?? ('Provider '.$pNum);
            }

            return [
                'id' => $provIdStr,
                'name' => [
                    'label' => $provName,
                    'link' => true,
                    'prov_num' => $pNum,
                ],
            ];
        };
PHP;

$newDrillTop = <<<'PHP'
    public function drilldown(Request $request)
    {
        $officeId = Office::getActiveOfficeId() ?? 1;
        $metric = $request->input('metric');
        $clinicNum = $request->input('clinic_num');
        $provNum = $request->input('prov_num');
        $start = $request->input('start_date', $request->input('start', now()->startOfMonth()->toDateString()));
        $end = $request->input('end_date', $request->input('end', now()->toDateString()));

        if ($request->input('subtab') === 'last-year') {
            $start = Carbon::parse($start)->subYear()->toDateString();
            $end = Carbon::parse($end)->subYear()->toDateString();
        }

        $title = 'Drilldown';
        $columns = [];
        $rows = [];
        $totals = null;

        $providerInfo = null;
        if ($provNum) {
            $p = OdProvider::where('office_id', $officeId)->where('ProvNum', $provNum)->first();
            if ($p) {
                $name = trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? ''));
                $providerInfo = [
                    'name' => $name ?: 'Provider '.$p->ProvNum,
                    'id' => $p->ProvNum.($p->Abbr ? ' - '.$p->Abbr : ''),
                ];
            }
        }

        $providers = OdProvider::where('office_id', $officeId)->get()->keyBy('ProvNum');

        $resolveProvName = function ($provNum) use ($providers) {
            $p = $providers->get($provNum);
            if (! $p) {
                return 'Provider '.$provNum;
            }
            $abbr = trim($p->Abbr ?? '');
            $lName = trim($p->LName ?? '');
            $fName = trim($p->FName ?: $p->PName ?: ($p->PreferredName ?? ''));
            if ($fName !== '' && $lName !== '') {
                return "$fName $lName";
            }

            return $lName ?: ($fName ?: ($abbr ?: 'Provider '.$provNum));
        };

        // Common Provider mapping
        $provMap = $providers->mapWithKeys(function ($p) use ($resolveProvName) {
            return [$p->ProvNum => $resolveProvName($p->ProvNum)];
        })->toArray();

        // Common Patient mapping
        $mapPatients = function ($patNums) use ($officeId) {
            return OdPatient::where('office_id', $officeId)->whereIn('PatNum', $patNums)->get()->mapWithKeys(function ($p) {
                return [$p->PatNum => $p->LName.', '.$p->FName];
            })->toArray();
        };

        $formatProv = function ($pNum) use ($providers, $provMap) {
            if (! $pNum) {
                return ['id' => '—', 'name' => ['label' => 'Unknown', 'link' => false, 'prov_num' => 0]];
            }
            $p = $providers->get($pNum);
            $abbr = $p ? ($p->Abbr ?? '') : '';
            $provIdStr = $pNum.($abbr ? ' - '.strtoupper($abbr) : '');
            $provName = $p ? trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? '')) : ($provMap[$pNum] ?? 'Unknown');
            if (! $provName) {
                $provName = $provMap[$pNum] ?? ('Provider '.$pNum);
            }

            return [
                'id' => $provIdStr,
                'name' => [
                    'label' => $provName,
                    'link' => true,
                    'prov_num' => $pNum,
                ],
            ];
        };
PHP;

$ctrlCode = str_replace($oldDrillTop, $newDrillTop, $ctrlCode);

// Drilldown replacements
$ctrlReplacements = [
    // sched_production fallback
    '->join(\'od_procedure_logs as pl\', \'a.AptNum\', \'=\', \'pl.AptNum\')' => '->join(\'od_procedure_logs as pl\', function ($j) use ($officeId) { $j->on(\'a.AptNum\', \'=\', \'pl.AptNum\')->where(\'pl.office_id\', \'=\', $officeId); })',
    '$apptsQuery = DB::table(\'od_appointments as a\')' => '$apptsQuery = DB::table(\'od_appointments as a\')->where(\'a.office_id\', $officeId)',

    // actual_production
    '$query = DB::table(\'od_procedure_logs as pl\')
                ->leftJoin(\'od_procedures as pc\', \'pl.CodeNum\', \'=\', \'pc.CodeNum\')' => '$query = DB::table(\'od_procedure_logs as pl\')
                ->leftJoin(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pl.CodeNum\', \'=\', \'pc.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
                ->where(\'pl.office_id\', $officeId)',

    // actual_collection
    '$splitsQuery = DB::table(\'od_pay_splits\')
                ->select(\'PatNum\', \'ProvNum\', \'DatePay\', \'SplitAmt\')' => '$splitsQuery = DB::table(\'od_pay_splits\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'DatePay\', \'SplitAmt\')',

    '$claimsQuery = DB::table(\'od_claim_procs\')
                ->select(\'PatNum\', \'ProvNum\', \'DateCP as DatePay\', \'InsPayAmt as SplitAmt\')' => '$claimsQuery = DB::table(\'od_claim_procs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'DateCP as DatePay\', \'InsPayAmt as SplitAmt\')',

    // actual_pts_visit
    '$logsQuery = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())' => '$logsQuery = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())',

    '$adjsQuery = DB::table(\'od_adjustments\')
                ->select(\'PatNum\', \'ProvNum\', \'AdjDate\', \'AdjAmt\')' => '$adjsQuery = DB::table(\'od_adjustments\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'AdjDate\', \'AdjAmt\')',

    '$wosQuery = DB::table(\'od_claim_procs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'WriteOff\')' => '$wosQuery = DB::table(\'od_claim_procs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'WriteOff\')',

    // actual_npt_visit
    '$nptVisits = $this->patientVisits->newPatientVisits($start, $end, $clinicNums);' => '$nptVisits = $this->patientVisits->newPatientVisits($start, $end, $clinicNums, [], $officeId);',

    // sched_pts_visit fallback
    '$apptsQuery = DB::table(\'od_appointments\')
                    ->select(\'AptNum\', \'PatNum\', \'ProvNum\', \'AptDateTime\', \'AptStatus\', \'ProcDescript\')
                    ->whereNotIn(\'AptStatus\', [6])' => '$apptsQuery = DB::table(\'od_appointments\')
                    ->where(\'office_id\', $officeId)
                    ->select(\'AptNum\', \'PatNum\', \'ProvNum\', \'AptDateTime\', \'AptStatus\', \'ProcDescript\')
                    ->whereNotIn(\'AptStatus\', [6])',

    // sched_new_pts_visit fallback
    '$apptsQuery = DB::table(\'od_appointments\')
                    ->select(\'AptNum\', \'PatNum\', \'ProvNum\', \'AptDateTime\', \'AptStatus\', \'ProcDescript\')
                    ->where(\'IsNewPatient\', 1)' => '$apptsQuery = DB::table(\'od_appointments\')
                    ->where(\'office_id\', $officeId)
                    ->select(\'AptNum\', \'PatNum\', \'ProvNum\', \'AptDateTime\', \'AptStatus\', \'ProcDescript\')
                    ->where(\'IsNewPatient\', 1)',

    // open_appt_hours
    '$apptQuery = DB::table(\'od_appointments\')
                ->where(\'office_id\', $officeId)
                ->whereIn(\'AptStatus\', [1, 2])
                ->whereBetween(\'AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);' => '$apptQuery = DB::table(\'od_appointments\')
                ->where(\'office_id\', $officeId)
                ->whereIn(\'AptStatus\', [1, 2])
                ->whereBetween(\'AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',

    // gross
    '$logsQuery = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'ProcDate\', [$start, $end]);' => '$logsQuery = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'ProcDate\', [$start, $end]);',

    // adjustment
    '$adjsQuery = DB::table(\'od_adjustments\')
                ->select(\'PatNum\', \'ProvNum\', \'AdjDate\', \'AdjAmt\', \'AdjType\')' => '$adjsQuery = DB::table(\'od_adjustments\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'AdjDate\', \'AdjAmt\', \'AdjType\')',

    '$wosQuery = DB::table(\'od_claim_procs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate as AdjDate\', \'WriteOff\')' => '$wosQuery = DB::table(\'od_claim_procs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate as AdjDate\', \'WriteOff\')',

    'DB::table(\'od_procedure_logs\')
                        ->where(\'PatNum\', $adj->PatNum)' => 'DB::table(\'od_procedure_logs\')
                        ->where(\'office_id\', $officeId)
                        ->where(\'PatNum\', $adj->PatNum)',

    'DB::table(\'od_procedure_logs\')
                        ->where(\'PatNum\', $wo->PatNum)' => 'DB::table(\'od_procedure_logs\')
                        ->where(\'office_id\', $officeId)
                        ->where(\'PatNum\', $wo->PatNum)',

    // collection
    '$splitsQuery = DB::table(\'od_pay_splits as s\')
                ->leftJoin(\'od_payments as p\', \'p.PayNum\', \'=\', \'s.PayNum\')' => '$splitsQuery = DB::table(\'od_pay_splits as s\')
                ->leftJoin(\'od_payments as p\', function ($j) use ($officeId) { $j->on(\'p.PayNum\', \'=\', \'s.PayNum\')->where(\'p.office_id\', \'=\', $officeId); })
                ->where(\'s.office_id\', $officeId)',

    '$claimsQuery = DB::table(\'od_claim_procs as cp\')
                ->leftJoin(\'od_claim_payments as cpay\', \'cpay.ClaimPaymentNum\', \'=\', \'cp.ClaimPaymentNum\')' => '$claimsQuery = DB::table(\'od_claim_procs as cp\')
                ->leftJoin(\'od_claim_payments as cpay\', function ($j) use ($officeId) { $j->on(\'cpay.ClaimPaymentNum\', \'=\', \'cp.ClaimPaymentNum\')->where(\'cpay.office_id\', \'=\', $officeId); })
                ->where(\'cp.office_id\', $officeId)',

    // net / coll_pct
    '$logsQuery = DB::table(\'od_procedure_logs\')->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')->whereIn(\'ProcStatus\', ProcStatus::completed())->whereBetween(\'ProcDate\', [$start, $end]);' => '$logsQuery = DB::table(\'od_procedure_logs\')->where(\'office_id\', $officeId)->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')->whereIn(\'ProcStatus\', ProcStatus::completed())->whereBetween(\'ProcDate\', [$start, $end]);',
    '$adjsQuery = DB::table(\'od_adjustments\')->select(\'PatNum\', \'ProvNum\', \'AdjDate\', \'AdjAmt\')->whereBetween(\'AdjDate\', [$start, $end]);' => '$adjsQuery = DB::table(\'od_adjustments\')->where(\'office_id\', $officeId)->select(\'PatNum\', \'ProvNum\', \'AdjDate\', \'AdjAmt\')->whereBetween(\'AdjDate\', [$start, $end]);',
    '$wosQuery = DB::table(\'od_claim_procs\')->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'WriteOff\')->whereBetween(\'ProcDate\', [$start, $end]);' => '$wosQuery = DB::table(\'od_claim_procs\')->where(\'office_id\', $officeId)->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'WriteOff\')->whereBetween(\'ProcDate\', [$start, $end]);',
    '$splitsQuery = DB::table(\'od_pay_splits\')->select(\'PatNum\', \'ProvNum\', \'DatePay\', \'SplitAmt\')->whereBetween(\'DatePay\', [$start, $end]);' => '$splitsQuery = DB::table(\'od_pay_splits\')->where(\'office_id\', $officeId)->select(\'PatNum\', \'ProvNum\', \'DatePay\', \'SplitAmt\')->whereBetween(\'DatePay\', [$start, $end]);',
    '$insSplitsQuery = DB::table(\'od_claim_procs\')
                ->select(\'PatNum\', \'ProvNum\', \'DateCP as DatePay\', \'InsPayAmt as SplitAmt\')' => '$insSplitsQuery = DB::table(\'od_claim_procs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'DateCP as DatePay\', \'InsPayAmt as SplitAmt\')',

    // pts_visit
    '$logsQuery = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProcDate\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())' => '$logsQuery = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProcDate\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())',

    // npt_visit
    '$firstVisitSubQ = $this->patients->firstVisitCohort();' => '$firstVisitSubQ = $this->patients->firstVisitCohort($officeId);',
    '$logsQuery = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisitSubQ, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->select(\'pl.PatNum\', \'pl.ProcDate\')' => '$logsQuery = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisitSubQ, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->where(\'pl.office_id\', $officeId)
                ->select(\'pl.PatNum\', \'pl.ProcDate\')',

    // cancellation
    '$query = DB::table(\'od_appointments as a\')
                ->select(
                    \'a.AptNum\',
                    \'a.PatNum\',
                    \'a.ProvNum\',
                    \'a.AptDateTime\',
                    \'a.Note\',
                    \'a.ProcDescript\'
                )
                ->where(\'a.AptStatus\', \'5\')' => '$query = DB::table(\'od_appointments as a\')
                ->select(
                    \'a.AptNum\',
                    \'a.PatNum\',
                    \'a.ProvNum\',
                    \'a.AptDateTime\',
                    \'a.Note\',
                    \'a.ProcDescript\'
                )
                ->where(\'a.office_id\', $officeId)
                ->where(\'a.AptStatus\', \'5\')',

    '$providers = OdProvider::whereIn(\'ProvNum\', $appts->pluck(\'ProvNum\')->unique())' => '$providers = OdProvider::where(\'office_id\', $officeId)->whereIn(\'ProvNum\', $appts->pluck(\'ProvNum\')->unique())',

    '$fees = DB::table(\'od_procedure_logs\')
                    ->selectRaw(\'AptNum, SUM(ProcFee) as total_fee\')
                    ->whereIn(\'AptNum\', $aptNums)' => '$fees = DB::table(\'od_procedure_logs\')
                    ->where(\'office_id\', $officeId)
                    ->selectRaw(\'AptNum, SUM(ProcFee) as total_fee\')
                    ->whereIn(\'AptNum\', $aptNums)',

    // total_appointments
    '$query = DB::table(\'od_appointments as a\')
                ->select(
                    \'a.AptNum\',
                    \'a.PatNum\',
                    \'a.ProvNum\',
                    \'a.AptStatus\',
                    \'a.AptDateTime\',
                    \'a.Note\',
                    \'a.ProcDescript\'
                )
                ->whereBetween(\'a.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);' => '$query = DB::table(\'od_appointments as a\')
                ->select(
                    \'a.AptNum\',
                    \'a.PatNum\',
                    \'a.ProvNum\',
                    \'a.AptStatus\',
                    \'a.AptDateTime\',
                    \'a.Note\',
                    \'a.ProcDescript\'
                )
                ->where(\'a.office_id\', $officeId)
                ->whereBetween(\'a.AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\']);',

    // working_days
    '$query = DB::table(\'od_procedure_logs\')
                ->selectRaw(\'ProcDate, COUNT(DISTINCT PatNum) as pts_visits, COUNT(*) as procedures, SUM(ProcFee) as production\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())' => '$query = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->selectRaw(\'ProcDate, COUNT(DISTINCT PatNum) as pts_visits, COUNT(*) as procedures, SUM(ProcFee) as production\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())',

    // unique_pts
    '$logs = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProcDate\', \'ProvNum\')
                ->when(! empty($clinicNum) && $clinicNum !== \'0\' && $clinicNum != 0, fn ($q) => $q->where(\'ClinicNum\', $clinicNum))
                ->whereIn(\'ProcStatus\', ProcStatus::completed())' => '$logs = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProcDate\', \'ProvNum\')
                ->when(! empty($clinicNum) && $clinicNum !== \'0\' && $clinicNum != 0, fn ($q) => $q->where(\'ClinicNum\', $clinicNum))
                ->whereIn(\'ProcStatus\', ProcStatus::completed())',

    '$clinicName = $clinicNum ? $this->clinics->name((int) $clinicNum) : \'All Offices\';' => '$clinicName = $clinicNum ? $this->clinics->name((int) $clinicNum, $officeId) : \'All Offices\';',

    // new_patient_prod
    '$nptLogs = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisitSubQ, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->join(\'od_procedures as pc\', \'pl.CodeNum\', \'=\', \'pc.CodeNum\')' => '$nptLogs = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisitSubQ, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->join(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pl.CodeNum\', \'=\', \'pc.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
                ->where(\'pl.office_id\', $officeId)',

    // act_pts
    '$activePts = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisitSubQ, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->select(\'pl.PatNum\', \'fv.first_date\')' => '$activePts = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstVisitSubQ, \'fv\', \'pl.PatNum\', \'=\', \'fv.PatNum\')
                ->where(\'pl.office_id\', $officeId)
                ->select(\'pl.PatNum\', \'fv.first_date\')',

    '$reservations = DB::table(\'od_appointments\')
                    ->select(\'PatNum\')' => '$reservations = DB::table(\'od_appointments\')
                    ->where(\'office_id\', $officeId)
                    ->select(\'PatNum\')',

    // act_pts_count
    '$logs = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\')
                ->when(! empty($clinicNum) && $clinicNum !== \'0\' && $clinicNum != 0, fn ($q) => $q->where(\'ClinicNum\', $clinicNum))
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'ProcDate\', [$startWindow, $end.\' 23:59:59\'])' => '$logs = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\')
                ->when(! empty($clinicNum) && $clinicNum !== \'0\' && $clinicNum != 0, fn ($q) => $q->where(\'ClinicNum\', $clinicNum))
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'ProcDate\', [$startWindow, $end.\' 23:59:59\'])',

    // retention
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())' => '$firstProcs = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())',

    '$patsAll = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw(\'pl.PatNum, MAX(pl.ProcDate) as last_date\')' => '$patsAll = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw(\'pl.PatNum, MAX(pl.ProcDate) as last_date\')',

    // working_days (second occurrence)
    '$logsQuery = DB::table(\'od_procedure_logs\')
                ->select(
                    DB::raw(\'DATE(ProcDate) as proc_date\'),
                    \'ProvNum\',
                    DB::raw(\'SUM(ProcFee) as total_prod\')
                )' => '$logsQuery = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(
                    DB::raw(\'DATE(ProcDate) as proc_date\'),
                    \'ProvNum\',
                    DB::raw(\'SUM(ProcFee) as total_prod\')
                )',

    // open_appt_hours (second occurrence)
    '$schedQuery = DB::table(\'od_schedules\')
                ->select(\'ProvNum\', \'SchedDate\', \'StartTime\', \'StopTime\')' => '$schedQuery = DB::table(\'od_schedules\')
                ->where(\'office_id\', $officeId)
                ->select(\'ProvNum\', \'SchedDate\', \'StartTime\', \'StopTime\')',

    '$apptQuery = DB::table(\'od_appointments\')
                ->select(\'ProvNum\', \'AptDateTime\', \'Pattern\')' => '$apptQuery = DB::table(\'od_appointments\')
                ->where(\'office_id\', $officeId)
                ->select(\'ProvNum\', \'AptDateTime\', \'Pattern\')',

    // unscheduled_tx (second occurrence)
    '$query = DB::table(\'od_procedure_logs as pl\')
                ->leftJoin(\'od_procedures as pc\', \'pl.CodeNum\', \'=\', \'pc.CodeNum\')
                ->select(\'pl.PatNum\', \'pl.ProvNum\', \'pl.ProcDate\', \'pl.ProcFee\', \'pc.ProcCode\', \'pc.Descript\')
                ->whereIn(\'pl.ProcStatus\', [1, \'1\', \'TP\'])' => '$query = DB::table(\'od_procedure_logs as pl\')
                ->leftJoin(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pl.CodeNum\', \'=\', \'pc.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
                ->select(\'pl.PatNum\', \'pl.ProvNum\', \'pl.ProcDate\', \'pl.ProcFee\', \'pc.ProcCode\', \'pc.Descript\')
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', [1, \'1\', \'TP\'])',

    // booked_production
    '$completedLogs = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'ProcDate\', [$start, $end])' => '$completedLogs = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'ProcDate\', [$start, $end])',

    '$targetLogs = $isCompleted ? $completedLogs : DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')' => '$targetLogs = $isCompleted ? $completedLogs : DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')',

    // actual_prod_vs_goal
    '$logs = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'ProcDate\', [$start, $end])
                ->when($provNum, fn ($q) => $q->where(\'ProvNum\', $provNum))' => '$logs = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'ProcDate\', [$start, $end])
                ->when($provNum, fn ($q) => $q->where(\'ProvNum\', $provNum))',

    // actual_vs_sched_prod
    '$schedLogs = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereNotIn(\'ProcStatus\', ProcStatus::completed())' => '$schedLogs = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\', \'ProcFee\')
                ->whereNotIn(\'ProcStatus\', ProcStatus::completed())',

    // act_vs_sched_pts
    '$completedLogs = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())' => '$completedLogs = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'ProcDate\')
                ->whereIn(\'ProcStatus\', ProcStatus::completed())',

    '$appts = DB::table(\'od_appointments\')
                ->select(\'PatNum\', \'ProvNum\', \'AptDateTime\')
                ->whereIn(\'AptStatus\', [1, 2])
                ->whereBetween(\'AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\'])' => '$appts = DB::table(\'od_appointments\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'AptDateTime\')
                ->whereIn(\'AptStatus\', [1, 2])
                ->whereBetween(\'AptDateTime\', [$start.\' 00:00:00\', $end.\' 23:59:59\'])',

    // act_vs_sched_npts
    '$schedNptAppts = DB::table(\'od_appointments\')
                ->select(\'PatNum\', \'ProvNum\', \'AptDateTime\')
                ->where(\'IsNewPatient\', 1)' => '$schedNptAppts = DB::table(\'od_appointments\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', \'ProvNum\', \'AptDateTime\')
                ->where(\'IsNewPatient\', 1)',

    // claims_day / claims
    '$procsQuery = DB::table(\'od_claim_procs as cp\')
                ->join(\'od_procedure_logs as pl\', \'cp.ProcNum\', \'=\', \'pl.ProcNum\')
                ->leftJoin(\'od_procedures as pc\', \'pc.CodeNum\', \'=\', \'pl.CodeNum\')' => '$procsQuery = DB::table(\'od_claim_procs as cp\')
                ->join(\'od_procedure_logs as pl\', function ($j) use ($officeId) { $j->on(\'cp.ProcNum\', \'=\', \'pl.ProcNum\')->where(\'pl.office_id\', \'=\', $officeId); })
                ->leftJoin(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pc.CodeNum\', \'=\', \'pl.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
                ->where(\'cp.office_id\', $officeId)',
];

foreach ($ctrlReplacements as $search => $replace) {
    if (strpos($ctrlCode, $search) !== false) {
        $ctrlCode = str_replace($search, $replace, $ctrlCode);
    } else {
        echo "Warning in OperationsController: target not found:\n".substr($search, 0, 70)."\n";
    }
}

file_put_contents($ctrlFile, $ctrlCode);
echo "OperationsController updated.\n";
