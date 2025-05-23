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
        Schema::table('result_uploads', function (Blueprint $table) {
                // Assignment columns
            $table->decimal('assignment1', 8, 2)->nullable()->after('total_marks');
            $table->decimal('assignment2', 8, 2)->nullable()->after('assignment1');
            $table->decimal('assignment3', 8, 2)->nullable()->after('assignment2');
            $table->decimal('assignment4', 8, 2)->nullable()->after('assignment3');
            
            // Quiz columns
            $table->decimal('quiz1', 8, 2)->nullable()->after('assignment4');
            $table->decimal('quiz2', 8, 2)->nullable()->after('quiz1');
            $table->decimal('quiz3', 8, 2)->nullable()->after('quiz2');
            
            // Exam columns
            $table->decimal('midterm', 8, 2)->nullable()->after('quiz3');
            $table->decimal('final', 8, 2)->nullable()->after('midterm');
            
            // Total columns
            $table->decimal('obtained_total', 8, 2)->nullable()->after('final');
            $table->decimal('course_total', 8, 2)->nullable()->after('obtained_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_uploads', function (Blueprint $table) {
            $table->dropColumn([
                'assignment1',
                'assignment2',
                'assignment3',
                'assignment4',
                'quiz1',
                'quiz2',
                'quiz3',
                'midterm',
                'final',
                'obtained_total',
                'course_total'
            ]);
        });
    }
};
