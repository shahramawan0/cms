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
        Schema::table('users', function (Blueprint $table) {
            // Teacher-specific
            $table->string('qualification')->nullable();
            $table->integer('experience_years')->nullable();
            $table->string('specialization')->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('account_title')->nullable();
            $table->string('account_number')->nullable();

            // Student-specific
            $table->string('roll_number')->nullable();
            $table->string('class')->nullable();
            $table->string('section')->nullable();
            $table->date('admission_date')->nullable();

            // Shared
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();

            // Hierarchy
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();


            // Foreign keys
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

            $table->dropColumn([
                'qualification',
                'experience_years',
                'specialization',
                'joining_date',
                'salary',
                'account_title',
                'account_number',
                'roll_number',
                'class',
                'section',
                'admission_date',
                'gender',
                'dob',
                'admin_id',
                'teacher_id',
                'created_by',
                'updated_by'
            ]);
        });
    }
};
