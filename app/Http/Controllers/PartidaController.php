<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Models\IdentitatJugador;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// Importem les classes que necessitarem de la nova llibreria
use PChess\Chess\Chess;
use App\Services\PgnParserService;

class PartidaController extends Controller
{
    public function index(Request $request)
    {
        // Validem que el paràmetre 'perPage' sigui un número permès
        $request->validate([
            'perPage' => 'sometimes|in:10,25,50,100',
            'search_player1' => 'nullable|string|max:80',
            'search_player2' => 'nullable|string|max:80',
            'search_event' => 'nullable|string|max:80',
            'search_site' => 'nullable|string|max:80',
            'search_eco' => 'nullable|string|max:10',
            'search_result' => 'nullable|string|in:1-0,0-1,1/2-1/2,*',
            'search_year_from' => 'nullable|integer|digits:4', 
            'search_year_to' => 'nullable|integer|digits:4|gte:search_year_from',
            'ignore_colors' => 'nullable|boolean',
        ]);


        // Agafem el valor de la URL, o posem 25 per defecte
        $perPage = $request->input('perPage', 25);

        // Comencem a construir la consulta a la base de dades pas a pas
        $query = Partida::query()->with(['blanques', 'negres']);

        $whitePlayer = $request->input('search_white');
        $blackPlayer = $request->input('search_black');
        $ignoreColors = $request->boolean('ignore_colors');

        // APLIQUEM ELS FILTRES SI EXISTEIXEN
        
        if ($whitePlayer && $blackPlayer) {
        // Cas 1: Tenim dos jugadors
        if ($ignoreColors) {
            // Ignorem colors: busquem (W vs B) OR (B vs W)
            $query->where(function ($q) use ($whitePlayer, $blackPlayer) {
                $q->where(function ($subQ) use ($whitePlayer, $blackPlayer) {
                    $subQ->whereHas('blanques', fn($p) => $p->where('nom', 'like', "%{$whitePlayer}%"))
                         ->whereHas('negres', fn($p) => $p->where('nom', 'like', "%{$blackPlayer}%"));
                })->orWhere(function ($subQ) use ($whitePlayer, $blackPlayer) {
                    $subQ->whereHas('blanques', fn($p) => $p->where('nom', 'like', "%{$blackPlayer}%"))
                         ->whereHas('negres', fn($p) => $p->where('nom', 'like', "%{$whitePlayer}%"));
                });
            });
        } else {
            // Colors específics: busquem W vs B
            $query->whereHas('blanques', fn($p) => $p->where('nom', 'like', "%{$whitePlayer}%"))
                  ->whereHas('negres', fn($p) => $p->where('nom', 'like', "%{$blackPlayer}%"));
        }
        } elseif ($whitePlayer) {
            // Cas 2: Només busquem un jugador de blanques
            $query->whereHas('blanques', fn($p) => $p->where('nom', 'like', "%{$whitePlayer}%"));
        } elseif ($blackPlayer) {
            // Cas 3: Només busquem un jugador de negres
            $query->whereHas('negres', fn($p) => $p->where('nom', 'like', "%{$blackPlayer}%"));
        }
        
        if ($request->filled('search_event')) {
            $query->where('event', 'like', '%' . $request->input('search_event') . '%');
        }
        if ($request->filled('search_site')) {
            $query->where('site', 'like', '%' . $request->input('search_site') . '%');
        }
        if ($request->filled('search_eco')) {
            $query->where('eco', 'like', $request->input('search_eco') . '%');
        }
        if ($request->filled('search_result')) {
            $query->where('resultat', $request->input('search_result'));
        }
        if ($request->filled('search_year_from')) {
            $query->whereYear('data_partida', '>=', $request->input('search_year_from'));
        }
        if ($request->filled('search_year_to')) {
            $query->whereYear('data_partida', '<=', $request->input('search_year_to'));
        }

        // Un cop construïda la consulta, apliquem l'ordre i la paginació
        $partides = $query->orderBy('data_partida', 'desc')
                      ->orderBy('event', 'asc')
                      ->orderBy('ronda', 'asc')
                      ->paginate($perPage);
    
        return view('partides.index', [
            'partides' => $partides,
            'search_inputs' => $request->all(),
        ]);
    }

    public function create()
    {
        return view('partides.create');
    }

    /**
     * Show the form for importing a PGN file.
     */
    public function showImportForm()
    {
        return view('partides.import');
    }

    /**
     * Handle the uploaded PGN file.
     */
    public function handleImport(Request $request)
    {
        $request->validate(['pgn_file' => 'required|file|mimes:pgn,txt']);
        $contingutPgn = file_get_contents($request->file('pgn_file')->getRealPath());

        // Pas 1: El Controlador fa la feina de separar el fitxer en partides individuals.
        // Aquesta expressió regular és la clau: busca el text que comença amb "[Event"
        $partidesText = preg_split('/(?=\[Event)/', $contingutPgn, -1, \PREG_SPLIT_NO_EMPTY);
        
        if (empty($partidesText)) {
            return back()->withErrors('El fitxer PGN no conté cap partida que es pugui reconèixer.');
        }
        
        // DEBUG
        dd($partidesText);

        $partidesImportades = 0;
        $errors = [];
        $failedPgns = []; // NOU: Array per guardar el text de les partides fallides
        $identitatsCache = []; // Cache per a aquesta importació

        // Funció auxiliar (la mateixa que a 'store' i 'update')
        $findOrCreateIdentity = function(string $nomJugador) use (&$identitatsCache): ?int {
            $nomJugador = trim($nomJugador);
            if (empty($nomJugador) || $nomJugador === '?') return null;
            if (isset($identitatsCache[$nomJugador])) { return $identitatsCache[$nomJugador]; }
            $identitat = \App\Models\IdentitatJugador::where('nom', $nomJugador)->first();
            if ($identitat) {
                $identitatsCache[$nomJugador] = $identitat->id_identitat;
                return $identitat->id_identitat;
            } 
            $persona = \App\Models\Persona::create();
            $novaIdentitat = $persona->identitats()->create(['nom' => $nomJugador]);
            $identitatsCache[$nomJugador] = $novaIdentitat->id_identitat;
            return $novaIdentitat->id_identitat;
        };

        // Pas 2: Iterem per cada text de partida individual
        foreach ($partidesText as $index => $pgnIndividual) {
            if (empty(trim($pgnIndividual))) continue;

            try {
                DB::beginTransaction();
                
                // Pas 3: Passem CADA partida individual al nostre parser especialista
                $parser = new PgnParserService($pgnIndividual);
                $headers = $parser->getHeaders();
                $movetext = $parser->getMovetext(); 

                if (empty($movetext) || empty($headers) || !isset($headers['White']) || !isset($headers['Black'])) {
                    throw new \Exception("Dades essencials (jugadors o jugades) no trobades.");
                }
                
                $nomBlanques = $headers['White'];
                $nomNegres = $headers['Black'];

                $idBlanques = $findOrCreateIdentity($nomBlanques);
                $idNegres = $findOrCreateIdentity($nomNegres);

                Partida::create([
                    'event' => $headers['Event'] ?? null,
                    'site' => $headers['Site'] ?? null,
                    'data_partida' => isset($headers['Date']) ? preg_replace('/[^0-9-]/', '', str_replace('.', '-', $headers['Date'])) : null,
                    'ronda' => $headers['Round'] ?? null,
                    'resultat' => $headers['Result'] ?? '*',
                    'eco' => $headers['ECO'] ?? null,
                    'camps_extra' => $headers['camps_extra'] ?? null,
                    'titol_blanques' => $headers['WhiteTitle'] ?? null,
                    'titol_negres' => $headers['BlackTitle'] ?? null,
                    'id_identitat_blanques' => $idBlanques,
                    'id_identitat_negres' => $idNegres,
                    'elo_blanques' => $headers['WhiteElo'] ?? null,
                    'elo_negres' => $headers['BlackElo'] ?? null,
                    'pgn_moves' => $movetext,
                    'id_propietari' => auth()->id(),
                    'estatus' => 'privada',
                ]);

                DB::commit();
                $partidesImportades++;

            } catch (\Exception $e) {
                DB::rollBack();
                $failedPgns[] = $pgnIndividual; 
                $errors[] = "Error a la partida #" . ($index + 1) . ": " . $e->getMessage();
            }
        }

        // Pas 4: Generem el missatge i el fitxer d'errors (si cal)
        if (!empty($failedPgns)) {
            $filename = date('Ymd_His') . '_errors_import.pgn';
            $errorContent = implode("\n\n", $failedPgns);
            Storage::disk('local')->put('imports/' . $filename, $errorContent);
        }

        $missatge = "";

        // Redirigim amb un missatge d'èxit o d'error
        if ($partidesImportades > 0) {
            $missatge = "S'han importat {$partidesImportades} partides correctament.";
            if (!empty($failedPgns)) {
                $missatge .= " Algunes partides han fallat. S'ha generat un fitxer d'errors.";
            }
            return redirect()->route('partides.index')->with('success', $missatge);
        } else {
            $errorMsg = 'No s\'ha pogut importar cap partida del fitxer.';
            if (!empty($failedPgns)) {
                $errorMsg .= " S'ha generat un fitxer d'errors.";
            }
            return redirect()->route('partides.import.form')->withErrors($errorMsg);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'nom_blanques' => 'required|string|max:80',
            'nom_negres' => 'required|string|max:80',
            'pgn_moves' => 'required|string',
            'ronda' => 'nullable|string|max:10',
            'titol_blanques' => 'nullable|string|max:3',
            'titol_negres' => 'nullable|string|max:3',
            'fen_inicial' => 'nullable|string|max:90',
            'eco' => 'nullable|string|max:10',
        ]);

        $findOrCreateIdentity = function(string $nomJugador): int {
            $identitat = IdentitatJugador::where('nom', $nomJugador)->first();
            if ($identitat) {
                return $identitat->id_identitat;
            } else {
                $persona = Persona::create();
                $novaIdentitat = $persona->identitats()->create(['nom' => $nomJugador]);
                return $novaIdentitat->id_identitat;
            }
        };

        try {
            DB::beginTransaction();
            $idBlanques = $findOrCreateIdentity($request->input('nom_blanques'));
            $idNegres = $findOrCreateIdentity($request->input('nom_negres'));
            Partida::create([
                'event' => $request->input('event'),
                'site' => $request->input('site'),
                'data_partida' => $request->input('data_partida'),
                'ronda' => $request->input('ronda'),
                'resultat' => $request->input('resultat'),
                'id_identitat_blanques' => $idBlanques,
                'id_identitat_negres' => $idNegres,
                'elo_blanques' => $request->input('elo_blanques'),
                'elo_negres' => $request->input('elo_negres'),
                'titol_blanques' => $request->input('titol_blanques'),
                'titol_negres' => $request->input('titol_negres'),
                'equip_blanques' => $request->input('equip_blanques'),
                'equip_negres' => $request->input('equip_negres'),
                'fen_inicial' => $request->input('fen_inicial'),
                'eco' => $request->input('eco'), 
                'pgn_moves' => $request->input('pgn_moves'),
                'id_propietari' => auth()->id(),
                'estatus' => 'privada',
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error en guardar la partida: ' . $e->getMessage());
        }

        return redirect()->route('partides.index')->with('success', 'Partida guardada correctament.');
    }

    public function show(Request $request, Partida $partida)
    {
        $partida->load(['blanques', 'negres', 'propietari']);
        
        // Construïm la consulta base AMB ELS FILTRES
        $query = Partida::query();
        
        if ($request->filled('search_black')) {
            $query->whereHas('negres', function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->input('search_black') . '%');
            });
        }
        if ($request->filled('search_event')) {
            $query->where('event', 'like', '%' . $request->input('search_event') . '%');
        }
        if ($request->filled('search_site')) {
            $query->where('site', 'like', '%' . $request->input('search_site') . '%');
        }
        if ($request->filled('search_eco')) {
            $query->where('eco', 'like', $request->input('search_eco') . '%');
        }
        if ($request->filled('search_result')) {
            $query->where('resultat', $request->input('search_result'));
        }
        
        // LÒGICA PER TROBAR LA PARTIDA PRÈVIA I LA SEGÜENT
        // Ens basem en el mateix ordre que la llista principal
        $ordre = [
            ['data_partida', 'desc'],
            ['event', 'asc'],
            ['ronda', 'asc'],
            ['id_partida', 'desc'] // Afegim ID per a un ordre consistent
        ];

        foreach ($ordre as $o) {
            $query->orderBy($o[0], $o[1]);
        }

        // Obtenim els IDs NOMÉS del resultat filtrat i ordenat
        $totsElsIdsFiltrats = $query->pluck('id_partida')->all();
        
        $currentIndex = array_search($partida->id_partida, $totsElsIdsFiltrats);

        $idAnterior = null;
        if ($currentIndex !== false && $currentIndex > 0) {
            $idAnterior = $totsElsIdsFiltrats[$currentIndex - 1];
        }

        $idSeguent = null;
        if ($currentIndex !== false && $currentIndex < (count($totsElsIdsFiltrats) - 1)) {
            $idSeguent = $totsElsIdsFiltrats[$currentIndex + 1];
        }
      
        return view('partides.show', [
            'partida' => $partida,
            'partidaAnterior' => $idAnterior ? Partida::find($idAnterior) : null,
            'partidaSeguent' => $idSeguent ? Partida::find($idSeguent) : null,
            'query_params' => $request->query(),
        ]);
    }

    public function edit(Partida $partida)
    {
        $partida->load(['blanques', 'negres']);
        // Passem la variable a la vista amb el nom 'partida' per no haver de canviar la vista
        return view('partides.edit', ['partida' => $partida]);
    }

    public function update(Request $request, Partida $partida)
    {
        // dd( $request->all());
        $request->validate([
            'nom_blanques' => 'required|string|max:80',
            'nom_negres' => 'required|string|max:80',
            'pgn_moves' => 'required|string',
            'ronda' => 'nullable|string|max:10',
            'titol_blanques' => 'nullable|string|max:3',
            'titol_negres' => 'nullable|string|max:3',
            'fen_inicial' => 'nullable|string|max:90',
            'eco' => 'nullable|string|max:10',
        ]);

        $findOrCreateIdentity = function(string $nomJugador): int {
            $identitat = IdentitatJugador::where('nom', $nomJugador)->first();
            if ($identitat) {
                return $identitat->id_identitat;
            } else {
                $persona = Persona::create();
                $novaIdentitat = $persona->identitats()->create(['nom' => $nomJugador]);
                return $novaIdentitat->id_identitat;
            }
        };
        
        try {
            DB::beginTransaction();
            $idBlanques = $findOrCreateIdentity($request->input('nom_blanques'));
            $idNegres = $findOrCreateIdentity($request->input('nom_negres'));

            // === LA PART CLAU I ARA COMPLETA ===
            $partida->update([
                'event' => $request->input('event'),
                'site' => $request->input('site'),
                'data_partida' => $request->input('data_partida'),
                'ronda' => $request->input('ronda'),
                'resultat' => $request->input('resultat'),
                'id_identitat_blanques' => $idBlanques,
                'id_identitat_negres' => $idNegres,
                'elo_blanques' => $request->input('elo_blanques'),
                'elo_negres' => $request->input('elo_negres'),
                'titol_blanques' => $request->input('titol_blanques'),
                'titol_negres' => $request->input('titol_negres'),
                'equip_blanques' => $request->input('equip_blanques'),
                'equip_negres' => $request->input('equip_negres'),
                'fen_inicial' => $request->input('fen_inicial'),
                'eco' => $request->input('eco'),
                'pgn_moves' => $request->input('pgn_moves'),
            ]);
            // dd($partide);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error en actualitzar la partida: ' . $e->getMessage());
        }

        return redirect()->route('partides.index')->with('success', 'Partida actualitzada correctament.');
    }

    /**
     * Remove multiple specified resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        // 1. Validem que rebem un array d'IDs
        $request->validate([
            'partida_ids' => 'required|array',
            'partida_ids.*' => 'integer|exists:partides,id_partida', // Valida que cada ID existeixi a la taula
        ]);

        $idsToDelete = $request->input('partida_ids');

        // 2. Esborrem totes les partides que tenen un ID que està dins de l'array
        Partida::whereIn('id_partida', $idsToDelete)->delete();

        // 3. Redirigim amb un missatge d'èxit
        return redirect()->route('partides.index')
                        ->with('success', count($idsToDelete) . ' partides han estat esborrades correctament.');
    }

    public function destroy(Partida $partida)
    {
        $partida->delete();
        return redirect()->route('partides.index')->with('success', 'Partida esborrada correctament.');
    }
}