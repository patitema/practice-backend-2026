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
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_id')->nullable(false);
            $table->unsignedBigInteger('respondent_id')->nullable(false);
            $table->timestamp('completed_at')->useCurrent();
            
            $table->foreign('survey_id')->references('id')->on('surveys');
            $table->foreign('respondent_id')->references('id')->on('users');
            $table->unique(['survey_id', 'respondent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
