<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Services\OpenDental\RcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RcmController extends Controller
{
    public function __construct(
        protected RcmService $rcmService
    ) {}

    /**
     * Display the RCM main view.
     */
    public function index(Request $request): View
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $activeOfficeId = Office::getActiveOfficeId();

        $defaultStart = '2025-01-01';
        $defaultEnd = '2025-12-31';

        return view('rcm.index', [
            'offices' => $offices,
            'activeOfficeId' => $activeOfficeId,
            'defaultStart' => $defaultStart,
            'defaultEnd' => $defaultEnd,
        ]);
    }

    /**
     * Fetch RCM tab data as JSON.
     */
    public function data(Request $request): JsonResponse
    {
        $tab = $request->input('tab', 'claim_submissions');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $officeId = $request->input('office_id') !== 'all' ? (int) $request->input('office_id') : null;
        $tier = $request->input('tier');
        $search = $request->input('search');
        $page = max((int) $request->input('page', 1), 1);
        $perPage = max((int) $request->input('per_page', 30), 5);
        $sortKey = $request->input('sort_key', 'date_created');
        $sortDir = $request->input('sort_dir', 'desc');

        $data = match ($tab) {
            'payment_arrangement' => $this->rcmService->getPaymentArrangements($startDate, $endDate, $officeId, $search, $page, $perPage),
            'patients_statements' => $this->rcmService->getPatientsStatements($startDate, $endDate, $officeId, $search, $page, $perPage),
            'point_of_service' => $this->rcmService->getPointOfServiceCollections($startDate, $endDate, $officeId, $search, $page, $perPage),
            'adjustment' => $this->rcmService->getAdjustments($startDate, $endDate, $officeId, $search, $page, $perPage),
            'dashboard' => $this->rcmService->getDashboardMetrics($startDate, $endDate, $officeId),
            'collection_refund' => $this->rcmService->getCollectionRefunds($startDate, $endDate, $officeId, $search, $page, $perPage),
            'payor_overview' => $this->rcmService->getPayorOverview($startDate, $endDate, $officeId, $search, $page, $perPage),
            default => $this->rcmService->getClaimSubmissions($startDate, $endDate, $officeId, $tier, $search, $page, $perPage, $sortKey, $sortDir),
        };

        return response()->json([
            'tab' => $tab,
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Export RCM tab data to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $tab = $request->input('tab', 'claim_submissions');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $officeId = $request->input('office_id') !== 'all' ? (int) $request->input('office_id') : null;
        $tier = $request->input('tier');
        $search = $request->input('search');

        $filename = "rcm_{$tab}_".now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tab, $startDate, $endDate, $officeId, $tier, $search) {
            $handle = fopen('php://output', 'w');

            if ($tab === 'claim_submissions') {
                fputcsv($handle, ['Patient', 'Patient ID', 'Claim ID', 'Office', 'Payor', 'Date Created', 'Date Submitted', 'Date Received', 'Last Visit Date', 'Date of Service', 'Claim Fee', 'Ins Pay Est', 'Ins Paid', 'WriteOff', 'Status']);
                $result = $this->rcmService->getClaimSubmissions($startDate, $endDate, $officeId, $tier, $search, 1, 5000);
                foreach ($result['items'] as $row) {
                    fputcsv($handle, [
                        $row['patient_name'],
                        $row['patient_id'],
                        $row['claim_id'],
                        $row['office_name'],
                        $row['payor'],
                        $row['date_created'],
                        $row['date_submitted'],
                        $row['date_received'],
                        $row['last_visit_date'],
                        $row['date_of_service'],
                        $row['claim_fee'],
                        $row['ins_pay_est'],
                        $row['ins_paid'],
                        $row['write_off'],
                        $row['status'],
                    ]);
                }
            } elseif ($tab === 'payor_overview') {
                fputcsv($handle, ['Payor', 'Total Claims', 'Total Submitted', 'Total Paid', 'Total Write-Off', 'Reimbursement Rate', 'Avg Turnaround']);
                $result = $this->rcmService->getPayorOverview($startDate, $endDate, $officeId, $search, 1, 5000);
                foreach ($result['items'] as $row) {
                    fputcsv($handle, [
                        $row['payor_name'],
                        $row['total_claims'],
                        $row['total_submitted'],
                        $row['total_paid'],
                        $row['total_write_off'],
                        $row['reimbursement_rate'],
                        $row['avg_turnaround_days'],
                    ]);
                }
            } else {
                fputcsv($handle, ['Patient', 'Patient ID', 'Office', 'Details', 'Amount', 'Date']);
                $result = $this->rcmService->getPatientsStatements($startDate, $endDate, $officeId, $search, 1, 5000);
                foreach ($result['items'] as $row) {
                    fputcsv($handle, [
                        $row['patient_name'],
                        $row['patient_id'],
                        $row['office_name'],
                        'Total Balance',
                        $row['bal_total'],
                        $row['last_statement_date'],
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
