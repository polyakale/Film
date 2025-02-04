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
        Schema::create('tasks', function (Blueprint $table) {
            $table->Integer('id')->autoIncrement();
            $table->Integer('filmId')->nullable();
            $table->Integer('personId')->nullable();
            $table->Integer('roleId')->nullable();
            $table->foreign('filmId')->references('id')->on('films');
            $table->foreign('personId')->references('id')->on('people');
            $table->foreign('roleId')->references('id')->on('roles');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
