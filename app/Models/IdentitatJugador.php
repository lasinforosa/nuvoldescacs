<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdentitatJugador extends Model
{
    use HasFactory;

    protected $table = 'identitats_jugador';
    protected $primaryKey = 'id_identitat';

    protected $fillable = [
        'id_persona',
        'nom',
        'sexe',
        'best_title',
        'data_inici',
        'data_final',
    ];

    /**
     * Defineix la relació inversa: Una Identitat pertany a una Persona.
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * Defineix la relació: Aquesta Identitat ha jugat moltes partides com a blanques.
     */
    public function partidesBlanques(): HasMany
    {
        return $this->hasMany(Partida::class, 'id_identitat_blanques', 'id_identitat');
    }

    /**
     * Defineix la relació: Aquesta Identitat ha jugat moltes partides com a negres.
     */
    public function partidesNegres(): HasMany
    {
        return $this->hasMany(Partida::class, 'id_identitat_negres', 'id_identitat');
    }
}