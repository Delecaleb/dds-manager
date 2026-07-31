<?php

use App\Http\Controllers\AgingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositSlipController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\FrontOfficeController;
use App\Http\Controllers\HygieneRecallController;
use App\Http\Controllers\KpisController;
use App\Http\Controllers\OpenDentalExplorerController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderPortalController;
use App\Http\Controllers\TxMinerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/location-stats', [DashboardController::class, 'locationStats'])->name('dashboard.location-stats');
    Route::get('/dashboard/financials-per-location', [DashboardController::class, 'financialsPerLocationData'])->name('dashboard.financials-per-location');
    Route::get('/dashboard/patient-visits-per-location', [DashboardController::class, 'patientVisitsPerLocationData'])->name('dashboard.patient-visits-per-location');
    Route::get('/dashboard/providers', [DashboardController::class, 'providerPerformance'])->name('dashboard.providers');
    Route::get('/dashboard/providers/{id}', [DashboardController::class, 'providerDetails'])->name('dashboard.provider-details');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('patients/data', [PatientController::class, 'data'])->name('patients.data');
    Route::get('patients/{id}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('patients/{id}/treatment-plans', [PatientController::class, 'showTreatment']);
    Route::get('patients/{id}/ar', [PatientController::class, 'showArLive']);
    Route::get('patients/{id}/family', [PatientController::class, 'showFamily']);
    Route::get('patients/{id}/employer', [PatientController::class, 'showEmployer']);
    Route::get('aging', [AgingController::class, 'index'])->name('aging.index');
    Route::get('aging/data', [AgingController::class, 'data'])->name('aging.data');

    Route::get('kpis', [KpisController::class, 'index'])->name('kpis.index');
    Route::get('kpis/hygiene', [KpisController::class, 'hygiene'])->name('kpis.hygiene');
    Route::get('kpis/hygiene-providers', [KpisController::class, 'hygieneProviders'])->name('kpis.hygiene-providers');
    Route::get('kpis/doctor', [KpisController::class, 'doctor'])->name('kpis.doctor');
    Route::get('kpis/doctor-providers', [KpisController::class, 'doctorProviders'])->name('kpis.doctor-providers');
    Route::get('kpis/office', [KpisController::class, 'office'])->name('kpis.office');
    Route::get('kpis/endo', [KpisController::class, 'endo'])->name('kpis.endo');
    Route::get('kpis/endo-providers', [KpisController::class, 'endoProviders'])->name('kpis.endo-providers');
    Route::get('kpis/perio', [KpisController::class, 'perio'])->name('kpis.perio');
    Route::get('kpis/perio-providers', [KpisController::class, 'perioProviders'])->name('kpis.perio-providers');
    Route::get('kpis/ortho', [KpisController::class, 'ortho'])->name('kpis.ortho');
    Route::get('kpis/ortho-providers', [KpisController::class, 'orthoProviders'])->name('kpis.ortho-providers');
    Route::get('kpis/os', [KpisController::class, 'os'])->name('kpis.os');
    Route::get('kpis/os-providers', [KpisController::class, 'osProviders'])->name('kpis.os-providers');
    Route::get('kpis/pedo', [KpisController::class, 'pedo'])->name('kpis.pedo');
    Route::get('kpis/pedo-providers', [KpisController::class, 'pedoProviders'])->name('kpis.pedo-providers');

    Route::get('provider-portal', [ProviderPortalController::class, 'index'])->name('provider-portal.index');
    Route::get('provider-portal/providers', [ProviderPortalController::class, 'providers'])->name('provider-portal.providers');
    Route::get('provider-portal/chart', [ProviderPortalController::class, 'chart'])->name('provider-portal.chart');
    Route::get('provider-portal/table', [ProviderPortalController::class, 'table'])->name('provider-portal.table');

    Route::get('financials', [FinancialController::class, 'index'])->name('financials.index');
    Route::get('financials/data', [FinancialController::class, 'data'])->name('financials.data');
    Route::get('financials/breakdown', [FinancialController::class, 'breakdown'])->name('financials.breakdown');
    Route::get('financials/score-cards', [FinancialController::class, 'scoreCards'])->name('financials.score-cards');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/data', [CalendarController::class, 'getData'])->name('calendar.data');
    Route::get('calendar/stats', [CalendarController::class, 'stats'])->name('calendar.stats');
    Route::get('calendar/resources', [CalendarController::class, 'getResources'])->name('calendar.resources');
    Route::get('calendar/appointments-details-data', [CalendarController::class, 'appointmentsDetailsData'])->name('calendar.appointments-details-data');
    Route::get('calendar/appointment-capacity-data', [CalendarController::class, 'appointmentCapacityData'])->name('calendar.appointment-capacity-data');
    Route::get('calendar/capacity-breakdown', [CalendarController::class, 'capacityBreakdown'])->name('calendar.capacity-breakdown');
    Route::get('calendar/scheduled-production-breakdown', [CalendarController::class, 'scheduledProductionBreakdown'])->name('calendar.scheduled-production-breakdown');
    Route::get('calendar/monthly-summary', [CalendarController::class, 'monthlySummary'])->name('calendar.monthly-summary');
    Route::get('deposits', [DepositSlipController::class, 'index'])->name('deposits.index');
    Route::get('deposits/data', [DepositSlipController::class, 'data'])->name('deposits.data');

    Route::get('eod', function () {
        return view('eod.index');
    })->name('eod.index');
    Route::get('huddle', function () {
        return view('huddle.index');
    })->name('huddle.index');
    Route::get('operations', [OperationsController::class, 'index'])->name('operations.index');
    Route::get('operations/offices/drilldown', [OperationsController::class, 'drilldown'])->name('operations.drilldown');
    Route::get('operations/data/{tab}/{subtab?}', [OperationsController::class, 'data'])->name('operations.data');
    Route::get('operations/{tab}/{subtab?}', [OperationsController::class, 'index'])->name('operations.tab');
    Route::get('snapshot', function () {
        return view('snapshot.index');
    })->name('snapshot.index');
    Route::get('front-office', [FrontOfficeController::class, 'index'])->name('front-office.index');
    Route::get('front-office/stats', [FrontOfficeController::class, 'stats'])->name('front-office.stats');
    Route::get('front-office/broken-appointments', [FrontOfficeController::class, 'brokenAppointments'])->name('front-office.broken-appointments');
    Route::get('front-office/tasks', [FrontOfficeController::class, 'tasks'])->name('front-office.tasks');
    Route::get('front-office/tasks-data', [FrontOfficeController::class, 'tasksData'])->name('front-office.tasks-data');
    Route::get('front-office/collections', [FrontOfficeController::class, 'collections'])->name('front-office.collections');
    Route::get('front-office/collections-data', [FrontOfficeController::class, 'collectionsData'])->name('front-office.collections-data');
    Route::get('front-office/collections-stats', [FrontOfficeController::class, 'collectionsStats'])->name('front-office.collections-stats');
    Route::get('front-office/kpis', [FrontOfficeController::class, 'kpis'])->name('front-office.kpis');
    Route::get('front-office/kpi-data', [FrontOfficeController::class, 'kpiData'])->name('front-office.kpi-data');
    Route::get('front-office/performance', [FrontOfficeController::class, 'performance'])->name('front-office.performance');
    Route::get('front-office/performance-stats', [FrontOfficeController::class, 'performanceStats'])->name('front-office.performance-stats');
    Route::get('front-office/performance-reminders-data', [FrontOfficeController::class, 'performanceRemindersData'])->name('front-office.performance-reminders-data');
    Route::get('front-office/performance-non-reminders-data', [FrontOfficeController::class, 'performanceNonRemindersData'])->name('front-office.performance-non-reminders-data');
    Route::get('front-office/performance-totals-data', [FrontOfficeController::class, 'performanceTotalsData'])->name('front-office.performance-totals-data');

    Route::get('rcm', function () {
        return view('rcm.index');
    })->name('rcm.index');
    Route::get('provisioner', function () {
        return view('provisioner.index');
    })->name('provisioner.index');

    Route::get('tx-miner', [TxMinerController::class, 'index'])->name('tx-miner.index');
    Route::get('tx-miner/data', [TxMinerController::class, 'data'])->name('tx-miner.data');

    Route::get('hygiene-recall', [HygieneRecallController::class, 'index'])->name('hygiene-recall.index');
    Route::get('hygiene-recall/data', [HygieneRecallController::class, 'data'])->name('hygiene-recall.data');

    Route::get('open-dental-explorer', [OpenDentalExplorerController::class, 'index'])->name('od-explorer.index');
    Route::get('open-dental-explorer/tables', [OpenDentalExplorerController::class, 'tables'])->name('od-explorer.tables');
    Route::get('open-dental-explorer/columns', [OpenDentalExplorerController::class, 'columns'])->name('od-explorer.columns');
    Route::post('open-dental-explorer/query', [OpenDentalExplorerController::class, 'query'])->name('od-explorer.query');
    Route::post('open-dental-explorer/sync-to-local', [OpenDentalExplorerController::class, 'syncToLocal'])->name('od-explorer.sync');
    Route::get('open-dental-explorer/sync-checkpoints', [OpenDentalExplorerController::class, 'syncCheckpoints'])->name('od-explorer.checkpoints');
    Route::post('open-dental-explorer/reset-sync-checkpoint', [OpenDentalExplorerController::class, 'resetSyncCheckpoint'])->name('od-explorer.reset-checkpoint');
});

require __DIR__.'/auth.php';
