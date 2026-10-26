<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Office of assignment on employees: when set, signatory resolution
     * (SWA, DTR) matches the Part A office head by this office instead of
     * the employee's regular office. Nullable so existing records keep
     * resolving by office until an assignment office is entered.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('office_of_assignment')->nullable()->after('office');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('office_of_assignment');
        });
    }
};
