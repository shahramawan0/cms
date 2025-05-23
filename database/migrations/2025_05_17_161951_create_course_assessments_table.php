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
        Schema::create('course_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->enum('type', ['Assignment', 'Quiz', 'Midterm', 'Final']);
            $table->string('title');
            $table->integer('marks');
            $table->decimal('weightage_percent', 5, 2);
            $table->timestamps();
            
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_assessments');
    }
};
