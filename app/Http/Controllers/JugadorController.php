<?php

namespace App\Http\Controllers;

use App\Models\IdentitatJugador;
use App\Models\Partida;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JugadorController extends Controller
{
    public function index()
    {
        // La funció withCount és més eficient que carregar totes les partides
        $identitats = IdentitatJugador::withCount(['partidesBlanques', 'partidesNegres'])
                                        ->orderBy('nom', 'asc')
                                        ->get();

        return view('jugadors.index', [
            'identitats' => $identitats,
        ]);
    }

    public function merge(Request $request)
    {
        // 1. Validació de les dades que arriben del formulari
        $validated = $request->validate([
            'master_id' => 'required|integer|exists:identitats_jugador,id_identitat',
            'identitat_ids' => 'required|array|min:2',
            'identitat_ids.*' => 'integer|exists:identitats_jugador,id_identitat',
        ], [
            'master_id.required' => 'Has de seleccionar una identitat com a principal.',
            'identitat_ids.min' => 'Has de seleccionar almenys dues identitats per fusionar.',
        ]);

        $masterId = $validated['master_id'];
        $allSelectedIds = $validated['identitat_ids'];

        // Comprovació de seguretat: la identitat mestra ha de ser una de les seleccionades.
        if (!in_array($masterId, $allSelectedIds)) {
            return back()->withErrors('La identitat principal ha de ser una de les seleccionades amb el checkbox.');
        }

        // Separem els IDs "esclaus" (els que seran fusionats i eliminats)
        $slaveIds = array_diff($allSelectedIds, [$masterId]);
        
        $masterIdentity = IdentitatJugador::findOrFail($masterId);
        $masterPersonId = $masterIdentity->id_persona;

        // Comencem una transacció. Si alguna cosa falla, tot es desfarà.
        DB::beginTransaction();
        try {
            // 2. Reassignem les partides on les identitats esclaves jugaven amb BLANQUES
            Partida::whereIn('id_identitat_blanques', $slaveIds)
                   ->update(['id_identitat_blanques' => $masterId]);

            // 3. Reassignem les partides on jugaven amb NEGRES
            Partida::whereIn('id_identitat_negres', $slaveIds)
                   ->update(['id_identitat_negres' => $masterId]);

            // 4. (Opcional, però recomanat) Reassignem totes les identitats a la mateixa Persona
            // Això manté un historial, encara que les esborrem després.
            $slavePersonIds = IdentitatJugador::whereIn('id_identitat', $slaveIds)->pluck('id_persona')->unique();
            IdentitatJugador::whereIn('id_identitat', $slaveIds)->update(['id_persona' => $masterPersonId]);

            // 5. Eliminem les identitats "esclaves", que ja no tenen cap partida associada
            IdentitatJugador::whereIn('id_identitat', $slaveIds)->delete();

            // 6. Netegem les 'Persones' que han quedat òrfenes (sense cap identitat)
            // No esborrem la persona mestra, només les esclaves que hagin quedat buides.
            Persona::whereIn('id_persona', $slavePersonIds)
                   ->whereDoesntHave('identitats')
                   ->delete();

            // Si tot ha anat bé, confirmem els canvis a la base de dades
            DB::commit();

        } catch (\Exception $e) {
            // Si hi ha qualsevol error, desfem tots els canvis
            DB::rollBack();
            return back()->withErrors('Error inesperat durant la fusió: ' . $e->getMessage());
        }

        return redirect()->route('jugadors.index')
                         ->with('success', count($slaveIds) . ' identitat(s) han estat fusionades correctament a "' . $masterIdentity->nom . '".');
    }
}