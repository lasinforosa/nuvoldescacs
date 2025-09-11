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
        Schema::create('identitats_jugador', function (Blueprint $table) {
            $table->id('id_identitat');
            $table->foreignId('id_persona')->constrained('persones', 'id_persona')->onDelete('cascade');
            $table->string('nom', 80);
            $table->enum('sexe', ['M', 'F', 'Altre'])->nullable();
            $table->string('best_title', 3)->nullable();
            $table->date('data_inici')->nullable();
            $table->date('data_final')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identitats_jugador');
    }
};
