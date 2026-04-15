<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            $table->string('office')->nullable()->after('name');
            $table->text('title')->nullable()->change(); // expand to text to accommodate JSON
        });

        // Migrate existing plain-string titles to JSON arrays
        DB::table('signatories')
            ->whereNotNull('title')
            ->get()
            ->each(function ($row) {
                $decoded = json_decode($row->title, true);
                if (! is_array($decoded)) {
                    DB::table('signatories')
                        ->where('id', $row->id)
                        ->update(['title' => json_encode([$row->title])]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            $table->dropColumn('office');
            $table->string('title')->nullable()->change();
        });
    }
};
