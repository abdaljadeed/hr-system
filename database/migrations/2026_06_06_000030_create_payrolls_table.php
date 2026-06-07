<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->decimal('base_salary', 12, 2);
            $table->unsignedSmallInteger('worked_days')->default(0);
            $table->unsignedSmallInteger('absent_days')->default(0);
            $table->unsignedSmallInteger('unpaid_leave_days')->default(0);
            $table->decimal('total_bonuses', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->enum('status', ['draft', 'finalized', 'paid'])->default('draft');
            $table->foreignId('generated_by')->constrained('users');
            $table->dateTime('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
