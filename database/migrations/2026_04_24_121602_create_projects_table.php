<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->cascadeOnDelete();
            $table->string('project_name')->nullable();
            $table->string('employer')->nullable();
            $table->string('client')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('project_size', ['S', 'M', 'L', 'XL'])->nullable();
            $table->text('description')->nullable();
            $table->json('roles')->nullable();
            $table->json('responsibilities')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
