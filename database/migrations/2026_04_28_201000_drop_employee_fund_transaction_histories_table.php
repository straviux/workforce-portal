<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('employee_fund_transaction_histories');
    }

    public function down(): void
    {
        Schema::create('employee_fund_transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_fund_transaction_id');
            $table->string('action', 50);
            $table->string('description');
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('remarks')->nullable();
            $table->json('details')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();

            $table->foreign('employee_fund_transaction_id', 'eft_history_transaction_fk')
                ->references('id')
                ->on('employee_fund_transactions')
                ->cascadeOnDelete();

            $table->foreign('performed_by', 'eft_history_performed_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};