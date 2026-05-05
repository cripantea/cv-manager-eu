<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'locked', 'archived'])->default('draft');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('nationality')->nullable();
            $table->string('current_function')->nullable();
            $table->string('profile_for')->nullable();
            $table->date('date_available')->nullable();
            $table->enum('education_level', ['master', 'bachelor', 'secondary'])->nullable();
            $table->integer('years_after_secondary')->nullable();
            $table->date('it_career_start')->nullable();
            $table->text('profile_summary')->nullable();
            $table->json('languages')->nullable();
            $table->enum('contract_type', ['permanent', 'non-permanent', 'freelancer'])->nullable();
            $table->string('proposed_level')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cvs');
    }
};
