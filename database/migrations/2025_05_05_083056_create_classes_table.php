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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->string('name')->nullable(false);
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active'); 
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
