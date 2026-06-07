<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'submitted', 'approved', 'rejected'])->default('todo');
            $table->date('due_date')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
