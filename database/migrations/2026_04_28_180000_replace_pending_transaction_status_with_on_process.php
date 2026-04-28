<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('employee_fund_transactions')
            ->where('transaction_status', 'pending')
            ->update(['transaction_status' => 'on_process']);

        $this->setDefault('on_process');
    }

    public function down(): void
    {
        DB::table('employee_fund_transactions')
            ->where('transaction_status', 'on_process')
            ->update(['transaction_status' => 'pending']);

        $this->setDefault('pending');
    }

    private function setDefault(string $status): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE employee_fund_transactions MODIFY transaction_status VARCHAR(255) NOT NULL DEFAULT '{$status}'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE employee_fund_transactions ALTER COLUMN transaction_status SET DEFAULT '{$status}'");
        }
    }
};
