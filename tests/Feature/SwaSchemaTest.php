<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SwaSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_swa_schema_tables_and_columns_exist(): void
    {
        $this->assertTrue(Schema::hasTable('swa_tasks'));
        $this->assertTrue(Schema::hasColumns('swa_tasks', [
            'subject_type',
            'subject_id',
            'task_name',
            'task_type',
            'sort_order',
            'is_active',
        ]));

        $this->assertTrue(Schema::hasTable('swa_reports'));
        $this->assertTrue(Schema::hasColumns('swa_reports', [
            'module_type',
            'subject_type',
            'subject_id',
            'generated_by',
            'office_head_signatory_id',
            'signatory_name',
            'signatory_office',
            'signatory_titles',
            'period_start_date',
            'period_end_date',
            'work_days',
        ]));

        $this->assertTrue(Schema::hasTable('swa_report_tasks'));
        $this->assertTrue(Schema::hasColumns('swa_report_tasks', [
            'swa_report_id',
            'source_task_id',
            'task_name',
            'task_type',
            'sort_order',
        ]));

        $this->assertTrue(Schema::hasTable('swa_report_task_daily_values'));
        $this->assertTrue(Schema::hasColumns('swa_report_task_daily_values', [
            'swa_report_task_id',
            'work_date',
            'numeric_value',
            'mark_value',
        ]));
    }
}
