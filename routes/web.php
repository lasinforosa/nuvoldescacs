<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LlibreController;
use App\Http\Controllers\PartidaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JugadorController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/partides/paste', [PartidaController::class, 'handlePaste'])->name('partides.paste');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('llibres', LlibreController::class);

    // rutes de la BBDD escacs
    // Rutes per a la importació de partides
    Route::get('/partides/importar', [PartidaController::class, 'showImportForm'])->name('partides.import.form');
    Route::post('/partides/importar', [PartidaController::class, 'handleImport'])->name('partides.import.handle');

    Route::delete('/partides/bulk-destroy', [PartidaController::class, 'bulkDestroy'])->name('partides.bulk.destroy');

    // La nostra ruta resource ha d'anar després per no crear conflictes
    Route::resource('partides', PartidaController::class)->parameters([
    'partides' => 'partida'     
    ]);

    Route::get('/jugadors', [JugadorController::class, 'index'])->name('jugadors.index');
    Route::post('/jugadors/merge', [JugadorController::class, 'merge'])->name('jugadors.merge');
    
});

require __DIR__.'/auth.php';
