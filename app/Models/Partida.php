<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partida extends Model
{
    use HasFactory;

    protected $table = 'partides';
    protected $primaryKey = 'id_partida';

    /**
     * The attributes that are mass assignable.
     * Aquesta és la llista de camps que Laravel té permís per
     * guardar o actualitzar en operacions "massives" com create() o update().
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event',
        'site',
        'data_partida',
        'ronda',
        'resultat',
        'eco',
        'id_identitat_blanques',
        'id_identitat_negres',
        'elo_blanques',
        'elo_negres',
        'titol_blanques',
        'titol_negres',
        'equip_blanques',
        'equip_negres',
        'fen_inicial',
        'pgn_moves',
        'id_propietari',
        'estatus',
    ];

    /**
     * Defineix la relació: La partida pertany a una Identitat de blanques.
     */
    public function blanques(): BelongsTo
    {
        return $this->belongsTo(IdentitatJugador::class, 'id_identitat_blanques', 'id_identitat');
    }

    /**
     * Defineix la relació: La partida pertany a una Identitat de negres.
     */
    public function negres(): BelongsTo
    {
        return $this->belongsTo(IdentitatJugador::class, 'id_identitat_negres', 'id_identitat');
    }

    /**
     * Defineix la relació: La partida pertany a un Usuari (propietari).
     */
    public function propietari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_propietari', 'id');
    }
}