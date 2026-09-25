<?php

namespace App\Services\Sync;

use Illuminate\Support\Facades\Schema;

/**
 * The allowlist of tables the OD Data Explorer may read, compare and repair.
 *
 * Only OpenDental data is reachable here — never application tables such as
 * users, offices (API keys) or sessions. Table name, local table, primary key
 * and business date come from each module's sync service; this class only adds
 * what is explorer-specific (compare columns, a reconciliation date column for
 * tables whose sync has no date window, and legacy aliases).
 */
class OpenDentalTableCatalog
{
    /**
     * Reconciliation date column for tables whose sync service defines no dateColumn().
     *
     * @var array<string, string>
     */
    private const DATE_COLUMNS = [
        'adjustment' => 'AdjDate',
        'paysplit' => 'DatePay',
        'payment' => 'PayDate',
        'treatplan' => 'DateTP',
        'treatplanattach' => 'SecDateTEdit',
        'schedule' => 'SchedDate',
        'recall' => 'DateDue',
        'claimpayment' => 'SecDateTEdit',
        'payplancharge' => 'ChargeDate',
        'deposit' => 'DateDeposit',
        'statement' => 'DateSent',
        'insplan' => 'SecDateTEdit',
    ];

    /**
     * Columns compared for value drift. Tables not listed compare every column.
     *
     * @var array<string, list<string>>
     */
    private const COMPARE_COLUMNS = [
        'paysplit' => ['PayNum', 'PatNum', 'ProvNum', 'ProcNum', 'ClinicNum', 'SplitAmt', 'DatePay', 'SecDateTEdit'],
        'payment' => ['PatNum', 'PayDate', 'PayAmt', 'PayType', 'ClinicNum', 'DepositNum', 'Receipt'],
        'claimproc' => ['ClaimNum', 'ProcNum', 'PatNum', 'ProvNum', 'Status', 'InsPayAmt', 'FeeBilled', 'DedApplied', 'ProcDate'],
        'adjustment' => ['PatNum', 'AdjDate', 'AdjAmt', 'AdjType', 'ProvNum', 'ClinicNum', 'ProcNum'],
        'procedurelog' => ['PatNum', 'ProcDate', 'CodeNum', 'ProcFee', 'ProvNum', 'ClinicNum', 'ProcStatus'],
        'appointment' => ['PatNum', 'AptDateTime', 'AptStatus', 'ProvNum', 'Op', 'ClinicNum', 'Pattern'],
        'histappointment' => ['HistApptNum', 'AptNum', 'PatNum', 'AptDateTime', 'AptStatus', 'HistDateTStamp'],
        'claimpayment' => ['CheckDate', 'CheckAmt', 'CheckNum', 'DepositNum', 'ClinicNum'],
        'deposit' => ['DateDeposit', 'Amount'],
        'patient' => ['PatNum', 'LName', 'FName', 'MiddleI', 'Birthdate', 'PatStatus', 'Gender', 'Position', 'SecDateEntry'],
        'treatplanattach' => ['TreatPlanAttachNum', 'TreatPlanNum', 'ProcNum', 'Priority', 'SecDateTEdit'],
        'insplan' => ['PlanNum', 'GroupName', 'GroupNum', 'PlanType', 'CarrierNum'],
        'statement' => ['StatementNum', 'PatNum', 'DateSent', 'IsSent', 'Mode_'],
    ];

    /**
     * Legacy names still sent by saved links / older clients.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'treatmentplan' => 'treatplan',
        'treatmentplans' => 'treatplan',
        'historyappointment' => 'histappointment',
        'patientbalances' => 'patientbalance',
    ];

    /** @var array<string, OpenDentalTable>|null */
    private ?array $tables = null;

    public function __construct(
        private readonly SyncReportService $modules,
    ) {}

    /**
     * @return array<string, OpenDentalTable> keyed by OpenDental table name
     */
    public function all(): array
    {
        return $this->tables ??= $this->build();
    }

    /**
     * Resolve a user-supplied table name. Accepts the OpenDental name, the
     * local table, the module key, or a known alias. Anything else — including
     * every non-OpenDental application table — resolves to null.
     */
    public function resolve(string $name): ?OpenDentalTable
    {
        $name = strtolower(trim($name));

        if ($name === '') {
            return null;
        }

        $tables = $this->all();
        $name = self::ALIASES[$name] ?? $name;

        if (isset($tables[$name])) {
            return $tables[$name];
        }

        foreach ($tables as $table) {
            $local = strtolower($table->localTable);

            if (in_array($name, array_filter([
                $local,
                rtrim($local, 's'),
                str_replace('_', '', $local),
                $table->module,
            ]), true)) {
                return $table;
            }
        }

        return null;
    }

    /**
     * @return array<string, OpenDentalTable>
     */
    private function build(): array
    {
        $tables = [];

        foreach ($this->modules->getModuleDefinitions() as $moduleKey => $definition) {
            $serviceClass = $definition['service_class'] ?? null;

            if ($serviceClass === null || ! is_subclass_of($serviceClass, BaseQuerySyncService::class)) {
                continue;
            }

            $meta = app($serviceClass)->describe();

            if (! Schema::hasTable($meta['local_table'])) {
                continue;
            }

            $key = strtolower($meta['od_table']);

            $tables[$key] = new OpenDentalTable(
                key: $key,
                localTable: $meta['local_table'],
                primaryKey: $meta['primary_key'],
                dateColumn: $meta['date_column'] ?? self::DATE_COLUMNS[$key] ?? null,
                compareColumns: self::COMPARE_COLUMNS[$key] ?? [],
                module: $moduleKey,
                serviceClass: $serviceClass,
                existsInOpenDental: true,
            );
        }

        // Local rollup built from od_patients — readable, but not an OpenDental table.
        if (Schema::hasTable('od_patient_balances')) {
            $tables['patientbalance'] = new OpenDentalTable(
                key: 'patientbalance',
                localTable: 'od_patient_balances',
                primaryKey: 'PatNum',
                dateColumn: null,
                compareColumns: [],
                module: 'patient_balance',
                serviceClass: null,
                existsInOpenDental: false,
            );
        }

        ksort($tables);

        return $tables;
    }
}
