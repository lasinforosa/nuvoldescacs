<?php
use App\Http\Controllers\PartidaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Registre d'API per a l'APP. 
| Les llegeix RouteServiceProvider i son asignades
| al "api" middleware 
|
*/

Route::post('/parse-pgn', [PartidaController::class, 'parsePgnFromText']);