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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_id')->nullable(false);
            $table->unsignedBigInteger('type')->nullable(false);
            $table->string('text', 255)->nullable(false);
            $table->unsignedBigInteger('order')->nullable(false);
            $table->boolean('required')->default(false);
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('survey_id')->references('id')->on('surveys');
            $table->foreign('type')->references('id')->on('types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
