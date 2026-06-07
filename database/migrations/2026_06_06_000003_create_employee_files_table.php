<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['cv', 'contract', 'certificate', 'id_document', 'other']);
            $table->string('title');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_files');
    }
};
