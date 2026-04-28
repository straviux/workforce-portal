<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_transaction_employees', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->nullable()->after('fund_transaction_id');
        });

        $groupedEmployees = DB::table('fund_transaction_employees')
            ->select(['id', 'fund_transaction_id'])
            ->orderBy('fund_transaction_id')
            ->orderByRaw('COALESCE(monthly_compensation, amount, 0) DESC')
            ->orderBy('id')
            ->get()
            ->groupBy('fund_transaction_id');

        foreach ($groupedEmployees as $employees) {
            foreach ($employees->values() as $index => $employee) {
                DB::table('fund_transaction_employees')
                    ->where('id', $employee->id)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('fund_transaction_employees', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
