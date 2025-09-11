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
        Schema::create('partides', function (Blueprint $table) {
            $table->id('id_partida');
            $table->string('event', 80)->nullable();
            $table->string('site', 80)->nullable();
            $table->date('data_partida')->nullable();
            $table->string('ronda', 10)->nullable();
            $table->string('resultat', 10)->nullable();
            $table->string('eco', 10)->nullable();
            
            $table->foreignId('id_identitat_blanques')->nullable()->constrained('identitats_jugador', 'id_identitat')->onDelete('set null');
            $table->foreignId('id_identitat_negres')->nullable()->constrained('identitats_jugador', 'id_identitat')->onDelete('set null');

            $table->integer('elo_blanques')->nullable();
            $table->integer('elo_negres')->nullable();
            $table->string('titol_blanques', 3)->nullable();
            $table->string('titol_negres', 3)->nullable();
            $table->string('equip_blanques', 80)->nullable();
            $table->string('equip_negres', 80)->nullable();

            $table->string('fen_inicial', 90)->nullable();
            $table->text('pgn_moves');

            $table->foreignId('id_propietari')->constrained('users', 'id')->onDelete('cascade');
            $table->enum('estatus', ['privada', 'publica', 'club'])->default('privada');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partides');
    }
};
