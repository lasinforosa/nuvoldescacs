<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Llibre extends Model
{
    use HasFactory;

    // Indiquem a Laravel el nom real de la nostra taula
    protected $table = 'llibres';

    // Indiquem la nostra clau primària personalitzada
    protected $primaryKey = 'id_llibres';

    // Definim els camps que es poden omplir massivament (des de formularis)
    protected $fillable = [
        'categoria',
        'lloc',
        'autor',
        'titol',
        'temes',
        'nota',
    ];
}
