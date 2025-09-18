<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Models\IdentitatJugador;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\PgnParserService;


class PartidaController extends Controller
{
    public function index(Request $request)
    {
        // Validem que el paràmetre 'perPage' sigui un número permès
        $request->validate([
            'perPage' => 'sometimes|in:25,50,100,250,500',
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
        $perPage = $request->input('perPage', 50);

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
            if ($ignoreColors) {
            $query->where(function ($q) use ($whitePlayer) {
                $q->whereHas('blanques', fn($p) => $p->where('nom', 'like', "%{$whitePlayer}%"))
                  ->orWhereHas('negres', fn($p) => $p->where('nom', 'like', "%{$whitePlayer}%"));
            });
            } else {
                $query->whereHas('blanques', fn($p) => $p->where('nom', 'like', "%{$whitePlayer}%"));
            }
        } elseif ($blackPlayer) {
            /// Cas 3: Només jugador de negres. El checkbox decideix.
            if ($ignoreColors) {
                $query->where(function ($q) use ($blackPlayer) {
                    $q->whereHas('blanques', fn($p) => $p->where('nom', 'like', "%{$blackPlayer}%"))
                    ->orWhereHas('negres', fn($p) => $p->where('nom', 'like', "%{$blackPlayer}%"));
                });
            } else {
                $query->whereHas('negres', fn($p) => $p->where('nom', 'like', "%{$blackPlayer}%"));
            }
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


    public function parsePgnFromText(Request $request)
    {
        $request->validate(['pgn_text' => 'required|string']);
        $pgnText = $request->input('pgn_text');

        try {
            $parser = new PgnParserService($pgnText);
            $headers = $parser->getHeaders();
            $movetext = $parser->getMovetext();

            if (empty($movetext) || empty($headers)) {
                return response()->json(['error' => 'No s\'han pogut trobar dades de PGN vàlides.'], 400);
            }

            // DEBUG
            dd($headers, $movetext);

            return response()->json([
                'headers' => $headers,
                'movetext' => $movetext,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error processant el PGN: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle the uploaded PGN file.
     */
    public function handleImport(Request $request)
    {
        $request->validate(['pgn_file' => 'required|file|mimes:pgn,txt']);
        $contingutPgn = file_get_contents($request->file('pgn_file')->getRealPath());

        // --- INICI DE LA LÒGICA DE SEPARACIÓ MANUAL I ROBUSTA ---
        $lines = explode("\n", str_replace("\r", "", $contingutPgn));
        $partidesText = [];
        $currentPgn = '';

        foreach ($lines as $line) {
            // La condició exacta: la línia comença amb '[Event' seguit d'un espai o cometes
            if (preg_match('/^\[Event(\s+|")/', trim($line)) && !empty(trim($currentPgn))) {
                $partidesText[] = $currentPgn;
                $currentPgn = ''; // Reiniciem per a la nova partida
            }
            $currentPgn .= $line . "\n";
        }
        // Afegim l'última partida que quedava al buffer
        if (!empty(trim($currentPgn))) {
            $partidesText[] = $currentPgn;
        }
        // --- FI DE LA LÒGICA DE SEPARACIÓ ---
        
        if (empty($partidesText)) {
            return back()->withErrors('El fitxer PGN no conté cap partida que es pugui reconèixer.');
        }

        $partidesImportades = 0;
        $errors = [];
        $failedPgns = [];
        $identitatsCache = [];

        foreach ($partidesText as $index => $pgnIndividual) {
            if (empty(trim($pgnIndividual))) continue;

            try {
                // Utilitzem la nostra funció centralitzada per processar i guardar
                $this->processAndSavePgn($pgnIndividual, $identitatsCache);
                $partidesImportades++;
            } catch (\Exception $e) {
                // Si la funció falla, guardem el PGN original i l'error
                $failedPgns[] = $pgnIndividual;
                $errors[] = "Error a la partida #" . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        $errorFile = null;
        if (!empty($failedPgns)) {
            $filename = date('Ymd_His') . '_errors_import.pgn';
            $errorContent = implode("\n\n", $failedPgns);
            Storage::disk('local')->put('imports/' . $filename, $errorContent);
            $errorFile = $filename;
        }

        $missatge = "";
        if ($partidesImportades > 0) {
            $missatge = "S'han importat {$partidesImportades} partides correctament.";
            if ($errorFile) {
                $missatge .= " Algunes partides han fallat. S'ha generat un fitxer d'errors.";
            }
            return redirect()->route('partides.index')->with('success', $missatge);
        } else {
            $errorMsg = 'No s\'ha pogut importar cap partida del fitxer.';
            if ($errorFile) {
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

    public function edit(Request $request, Partida $partida)
    {
        $partida->load(['blanques', 'negres']);
        // Passem la variable a la vista amb el nom 'partida' per no haver de canviar la vista
        return view('partides.edit', [
        'partida' => $partida,
        'query_params' => $request->query() // Passem tots els paràmetres (page, perPage, filtres)
    ]);
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
        // NOU: Redirigim a la ruta 'partides.index' amb tots els paràmetres de la URL anterior
        return redirect()->route('partides.index', $request->except(['_token', '_method', 'partida_ids']))
                     ->with('success', count($idsToDelete) . ' partides han estat esborrades correctament.');
    }

    public function destroy(Partida $partida)
    {
        $partida->delete();
        // NOU: Redirigim a la ruta 'partides.index' amb tots els paràmetres de la URL anterior
        return redirect()->route('partides.index', request()->query())
                     ->with('success', 'Partida esborrada correctament.');
    }

    public function handlePaste(Request $request)
    {
        $request->validate(['pgn_text' => 'required|string']);
        $pgnText = $request->input('pgn_text');

        // Intentem separar per si s'han enganxat múltiples partides, però només agafem la primera
        $partidesText = preg_split('/(?=\[Event(\s+|"))/', $pgnText, -1, \PREG_SPLIT_NO_EMPTY);
        if (empty($partidesText)) {
            return back()->withInput()->withErrors('El text PGN no conté cap partida reconeixible.');
        }
        $pgnIndividual = $partidesText[0];
        
        // DEBUG
        // dd($pgnIndividual);

        try {
            // Cridem a la nostra funció privada centralitzada
            $cache = []; // Passem una cache buida
            $this->processAndSavePgn($pgnIndividual, $cache);
        } catch (\Exception $e) {
            // Si falla, tornem al formulari de creació amb l'error
            // AQUESTA ÉS LA PART CLAU: Redirigim SEMPRE amb un error clar.
            return redirect()->route('partides.create')
                             ->withInput()
                             ->withErrors('Error al processar el PGN: ' . $e->getMessage());
        }

        // Si tot va bé, redirigim a la llista amb un missatge d'èxit
        return redirect()->route('partides.index')->with('success', 'Partida creada correctament des del PGN.');
    }

    // --- INICI DE LES FUNCIONS PRIVADES CENTRALITZADES ---

    private function findOrCreateIdentity(string $nomJugador, array &$cache): ?int
    {
        $nomJugador = trim($nomJugador);
        if (empty($nomJugador) || $nomJugador === '?') return null;

        if (isset($cache[$nomJugador])) { return $cache[$nomJugador]; }

        $identitat = IdentitatJugador::where('nom', $nomJugador)->first();
        if ($identitat) {
            $cache[$nomJugador] = $identitat->id_identitat;
            return $identitat->id_identitat;
        } 
        
        $persona = Persona::create();
        $novaIdentitat = $persona->identitats()->create(['nom' => $nomJugador]);
        $cache[$nomJugador] = $novaIdentitat->id_identitat;
        return $novaIdentitat->id_identitat;
    }

    private function processAndSavePgn(string $pgnIndividual, array &$identitatsCache)
    {
        // AQUESTA ÉS LA FUNCIÓ QUE FALTAVA
        DB::beginTransaction();
        try {
            $parser = new PgnParserService($pgnIndividual);
            $headers = $parser->getHeaders();
            $movetext = $parser->getMovetext(); 

            // DEBUG
            // dd($headers, $movetext);

            if (empty($movetext) || !isset($headers['White']) || !isset($headers['Black'])) {
                throw new \Exception("Dades essencials (jugadors o jugades) no trobades.");
            }

            $idBlanques = $this->findOrCreateIdentity($headers['White'], $identitatsCache);
            $idNegres = $this->findOrCreateIdentity($headers['Black'], $identitatsCache);

            // DEBUG
            // dd(isset($headers['Date']) ? preg_replace('/[^0-9-]/', '', str_replace('.', '-', $headers['Date'])) : null);

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
        } catch (\Exception $e) {
            DB::rollBack();
            // Propaguem l'excepció perquè la funció que crida (handleImport) sàpiga que ha fallat
            throw $e;
        }
    }
    
    // --- FI DE LES FUNCIONS PRIVADES ---
}
