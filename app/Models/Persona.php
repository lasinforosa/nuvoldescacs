<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'persones';
    protected $primaryKey = 'id_persona';
    
    // No definim $fillable aquí perquè normalment no crearem "persones" des d'un formulari directe,
    // sinó com a conseqüència de crear una nova identitat.

    /**
     * Defineix la relació: Una Persona té moltes Identitats.
     */
    public function identitats(): HasMany
    {
        return $this->hasMany(IdentitatJugador::class, 'id_persona', 'id_persona');
    }
}
