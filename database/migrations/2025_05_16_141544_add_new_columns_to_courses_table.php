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
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('total_marks')->nullable()->after('description');
            $table->integer('credit_hours')->nullable()->after('total_marks');
            
            // Remove columns
            $table->dropColumn(['language', 'level', 'book_name', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Re-add dropped columns
            $table->string('language')->nullable();
            $table->string('level')->nullable();
            $table->string('book_name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Remove new columns
            $table->dropColumn(['total_marks', 'credit_hours']);
        });
    }
};
