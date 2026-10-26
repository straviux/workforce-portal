<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Offices replace the signatory office mappings: a main office has
     * assignments (specific offices/programs) under it, and each office or
     * assignment carries its own assigned office head. Existing mapping
     * rows become top-level offices with their head, and every distinct
     * office name already in use (employees, signatory heads,
     * certifications, fund vouchers) is seeded as a top-level office so
     * the admin organizes rather than retypes. The mappings table is
     * dropped afterwards; it was never run anywhere, but the drop is
     * guarded all the same.
     */
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('office_head_signatory_id')->nullable()->constrained('signatories')->nullOnDelete();
            $table->timestamps();
        });

        $this->seedFromMappings();
        $this->seedFromOfficeNames();

        Schema::dropIfExists('signatory_office_mappings');
    }

    public function down(): void
    {
        Schema::dropIfExists('offices');
    }

    private function seedFromMappings(): void
    {
        if (! Schema::hasTable('signatory_office_mappings')) {
            return;
        }

        $mappings = DB::table('signatory_office_mappings')
            ->orderBy('office')
            ->get();

        foreach ($mappings as $mapping) {
            $name = trim((string) $mapping->office);

            if ($name === '') {
                continue;
            }

            $exists = DB::table('offices')
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('offices')->insert([
                'name' => $name,
                'parent_id' => null,
                'office_head_signatory_id' => $mapping->office_head_signatory_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedFromOfficeNames(): void
    {
        $names = collect()
            ->merge(DB::table('employees')->pluck('office'))
            ->merge(DB::table('employees')->pluck('office_of_assignment'))
            ->merge(DB::table('signatories')->where('part', 'A')->pluck('office'))
            ->merge(DB::table('certifications')->pluck('office'))
            ->merge(DB::table('employee_fund_transactions')->pluck('office'))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn (string $name) => mb_strtolower($name))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        foreach ($names as $name) {
            $exists = DB::table('offices')
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('offices')->insert([
                'name' => $name,
                'parent_id' => null,
                'office_head_signatory_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
