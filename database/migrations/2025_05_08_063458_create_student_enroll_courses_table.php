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
        Schema::create('student_enroll_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('st_enroll_id');
            $table->unsignedBigInteger('course_id');
        
            // Add context columns
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
        
            $table->timestamps();
        
        
            $table->foreign('st_enroll_id')->references('id')->on('student_enrollments')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enroll_courses');
    }
};
