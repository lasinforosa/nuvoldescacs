<?php

namespace App\Http\Controllers;

use App\Models\IdentitatJugador;
use App\Models\Partida;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JugadorController extends Controller
{
    /**
     * Mostra una llista de totes les identitats de jugador.
     */
    public function index()
    {
        // Obtenim totes les identitats i comptem les seves partides de manera eficient
        $identitats = IdentitatJugador::withCount(['partidesBlanques', 'partidesNegres'])
                                        ->orderBy('nom', 'asc')
                                        ->get();
        return view('jugadors.index', ['identitats' => $identitats]);
    }

    /**
     * Aquesta serà la funció per fusionar les identitats. La deixem preparada.
     */
    public function merge(Request $request)
    {
        // 1. Validació
        $validated = $request->validate([
            'master_id' => 'required|integer|exists:identitats_jugador,id_identitat',
            'identitat_ids' => 'required|array|min:2',
            'identitat_ids.*' => 'integer|exists:identitats_jugador,id_identitat',
        ]);

        $masterId = $validated['master_id'];
        $allIds = $validated['identitat_ids'];

        // Assegurem que la mestra està dins de les seleccionades
        if (!in_array($masterId, $allIds)) {
            return back()->withErrors('La identitat principal ha de ser una de les seleccionades.');
        }

        // Separem les identitats "esclaves"
        $slaveIds = array_diff($allIds, [$masterId]);
        
        $masterIdentity = IdentitatJugador::findOrFail($masterId);
        $masterPersonId = $masterIdentity->id_persona;

        try {
            DB::beginTransaction();

            // 2. Reassignem partides
            Partida::whereIn('id_identitat_blanques', $slaveIds)->update(['id_identitat_blanques' => $masterId]);
            Partida::whereIn('id_identitat_negres', $slaveIds)->update(['id_identitat_negres' => $masterId]);

            // 3. Associem totes les identitats esclaves a la persona mestra (per mantenir l'historial)
            // Aquesta és una alternativa a esborrar-les
            IdentitatJugador::whereIn('id_identitat', $slaveIds)->update(['id_persona' => $masterPersonId]);
            
            // 4. Eliminem les identitats esclaves (ara ja no tenen partides)
            IdentitatJugador::whereIn('id_identitat', $slaveIds)->delete();

            // 5. Netegem les persones que han quedat òrfenes
            Persona::whereDoesntHave('identitats')->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error durant la fusió: ' . $e->getMessage());
        }

        return redirect()->route('jugadors.index')
                         ->with('success', count($slaveIds) . ' identitat(s) han estat fusionades correctament a "' . $masterIdentity->nom . '".');
    }
}
   