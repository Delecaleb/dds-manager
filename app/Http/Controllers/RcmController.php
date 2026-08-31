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

        $defaultStart = $request->input('start_date', now()->startOfMonth()->toDateString());
        $defaultEnd = $request->input('end_date', now()->toDateString());

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
        $defaultSortKey = $tab === 'patients_statements' ? 'statement_date' : 'date_created';
        $defaultSortDir = $tab === 'patients_statements' ? 'asc' : 'desc';
        $sortKey = $request->input('sort_key', $defaultSortKey);
        $sortDir = $request->input('sort_dir', $defaultSortDir);

        $data = match ($tab) {
            'payment_arrangement' => $this->rcmService->getPaymentArrangements($startDate, $endDate, $officeId, $tier, $search, $page, $perPage, $sortKey, $sortDir),
            'patients_statements' => $this->rcmService->getPatientsStatements($startDate, $endDate, $officeId, $tier, $search, $page, $perPage, $sortKey, $sortDir),
            'point_of_service' => $this->rcmService->getPointOfServiceCollections($startDate, $endDate, $officeId, $tier, $search, $page, $perPage, $sortKey, $sortDir),
            'adjustment' => $this->rcmService->getAdjustments($startDate, $endDate, $officeId, $tier, $search, $page, $perPage, $sortKey, $sortDir),
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
                fputcsv($handle, ['Patient', 'Patient ID', 'Claim ID', 'Office', 'Payor', 'Date Created', 'Date Submitted', 'Date Received', 'Last Visit Date', 'Date of Service', 'Claim Lag Days', 'Turn Around Time', 'Days Outstanding', 'Charge Lag Days', 'Line of Business', 'Service Codes', 'Description', 'Amount Submitted', 'Estimated']);
                $result = $this->rcmService->getClaimSubmissions($startDate, $endDate, $officeId, $tier, $search, 1, 5000, 'patient', 'asc');
                foreach ($result['items'] as $row) {
                    fputcsv($handle, [
                        $row['patient_name'] ?? '',
                        $row['patient_id'] ?? '',
                        $row['claim_id'] ?? '',
                        $row['office_name'] ?? '',
                        $row['payor'] ?? '',
                        $row['date_created'] ?? '',
                        $row['date_submitted'] ?? '',
                        $row['date_received'] ?? '',
                        $row['last_visit_date'] ?? '',
                        $row['date_of_service'] ?? '',
                        (string) ($row['claim_lag_days'] ?? 0),
                        (string) ($row['turn_around_time'] ?? 0),
                        (string) ($row['days_outstanding'] ?? 0),
                        (string) ($row['charge_lag_days'] ?? 0),
                        $row['line_of_business'] ?? 'General',
                        $row['service_codes'] ?? '',
                        $row['description'] ?? '',
                        $row['amount_submitted_formatted'] ?? '$ 0.00',
                        $row['estimated_formatted'] ?? '$ 0.00',
                    ]);
                }
            } elseif ($tab === 'point_of_service') {
                fputcsv($handle, ['Patient', 'Patient ID', 'Office', 'Claim ID', 'Date of Service', 'Provider ID', 'Provider', 'Line of Business', 'Service Code', 'Past Due Balance', 'Total Amount of Service', 'Estimated Insurance $', 'Estimated Patient $', 'Insurance Paid', 'Patient Paid', 'Total Paid', 'Uncollected Balance', 'Loan Amount']);
                $result = $this->rcmService->getPointOfServiceCollections($startDate, $endDate, $officeId, $tier, $search, 1, 5000);
                foreach ($result['items'] as $row) {
                    fputcsv($handle, [
                        $row['patient_name'],
                        $row['patient_id'],
                        $row['office_name'],
                        $row['claim_id'],
                        $row['date_of_service'],
                        $row['provider_id_code'],
                        $row['provider_name'],
                        $row['line_of_business'],
                        $row['service_code'],
                        $row['past_due_balance'],
                        $row['total_amount_service'],
                        $row['estimated_ins'],
                        $row['estimated_pat'],
                        $row['ins_paid'],
                        $row['pat_paid'],
                        $row['total_paid'],
                        $row['uncollected_balance'],
                        $row['loan_amount'],
                    ]);
                }
            } elseif ($tab === 'adjustment') {
                fputcsv($handle, ['Patient', 'Patient ID', 'Office', 'Date', 'Provider ID', 'Provider', 'Adjustment Type', 'Amount', 'Note']);
                $result = $this->rcmService->getAdjustments($startDate, $endDate, $officeId, $tier, $search, 1, 5000);
                foreach ($result['items'] as $row) {
                    fputcsv($handle, [
                        $row['patient_name'],
                        $row['patient_id'],
                        $row['office_name'],
                        $row['adj_date'],
                        $row['provider_id_code'],
                        $row['provider_name'],
                        $row['adj_type'],
                        $row['adj_amount'],
                        $row['note'],
                    ]);
                }
            } elseif ($tab === 'payment_arrangement') {
                fputcsv($handle, ['Patient', 'Patient ID', 'Office', 'Line of Business', 'Start Date', 'Creation Date', 'Last Pay Date', 'Loan Amount', 'Payment Frequency', 'Number of Payments', 'Installment Amount', 'Last Payment Amount', 'Remaining Balance', 'Days Past Due']);
                $result = $this->rcmService->getPaymentArrangements($startDate, $endDate, $officeId, $tier, $search, 1, 5000);
                foreach ($result['items'] as $row) {
                    fputcsv($handle, [
                        $row['patient_name'],
                        $row['patient_id'],
                        $row['office_name'],
                        $row['line_of_business'],
                        $row['start_date'],
                        $row['creation_date'],
                        $row['last_pay_date'],
                        $row['loan_amount'],
                        $row['payment_frequency'],
                        $row['number_of_payments'],
                        $row['installment_amount'],
                        $row['last_payment_amount'],
                        $row['remaining_balance'],
                        $row['days_past_due'],
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
                fputcsv($handle, ['Patient', 'Patient ID', 'Office', 'Statement Date', 'Balance Due Now', 'Due Date']);
                $result = $this->rcmService->getPatientsStatements($startDate, $endDate, $officeId, $tier, $search, 1, 5000, 'statement_date', 'asc');
                foreach ($result['items'] as $row) {
                    fputcsv($handle, [
                        $row['patient_name'],
                        $row['patient_id'],
                        $row['office_name'],
                        $row['statement_date'],
                        $row['balance_due_now_formatted'] ?? $row['balance_due_now'],
                        $row['due_date'],
                    ]);
                }
                if (! empty($result['summary'])) {
                    fputcsv($handle, ['Average:', '-', '-', '-', $result['summary']['average_formatted'] ?? '-', '-']);
                    fputcsv($handle, ['Total:', '-', '-', '-', $result['summary']['total_formatted'] ?? '-', '-']);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
