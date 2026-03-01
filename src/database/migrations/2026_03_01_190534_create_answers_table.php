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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('response_id')->nullable(false);
            $table->unsignedBigInteger('question_id')->nullable(false);
            $table->unsignedBigInteger('option_id')->nullable();
            $table->string('text_value', 255)->nullable();
            
            $table->foreign('response_id')->references('id')->on('responses');
            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('option_id')->references('id')->on('options');
            $table->unique(['response_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
