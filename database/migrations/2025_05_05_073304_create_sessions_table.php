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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id')->nullable();
            $table->string('session_name')->nullable(false);
            $table->date('start_date')->nullable(false);
            $table->date('end_date')->nullable(false);
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('set null');

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
