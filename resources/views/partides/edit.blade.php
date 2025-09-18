<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Partida') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                            <strong>Ui! Alguna cosa ha anat malament.</strong>
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('partides.update', ['partida' => $partida] + $query_params) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <!-- Columna 1: Tauler -->
                            <div class="md:col-span-1">
                                <h3 class="text-lg font-medium mb-2">Tauler</h3>
                                <div id="board" style="width: 100%; max-width: 400px;"></div>
                                <div class="mt-2 space-x-2">
                                    <button type="button" id="undoBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800">Desfer</button>
                                    <button type="button" id="startBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800">Inici</button>
                                </div>
                                <div class="mt-4">
                                    <label for="fen_inicial">FEN</label>
                                    <input type="text" name="fen_inicial" id="fen_inicial" value="{{ old('fen_inicial', $partida->fen_inicial) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    <button type="button" id="setFenBtn" class="mt-1 px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-800">Carregar FEN</button>
                                </div>
                            </div>

                            <!-- Columna 2: Dades -->
                            <div class="md:col-span-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="sm:col-span-2">
                                        <label for="event">Esdeveniment</label>
                                        <input type="text" name="event" id="event" value="{{ old('event', $partida->event) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="site">Lloc</label>
                                        <input type="text" name="site" id="site" value="{{ old('site', $partida->site) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <div>
                                        <label for="data_partida">Data</label>
                                        <input type="date" name="data_partida" id="data_partida" value="{{ old('data_partida', $partida->data_partida) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <div>
                                        <label for="ronda">Ronda</label>
                                        <input type="text" name="ronda" id="ronda" value="{{ old('ronda', $partida->ronda) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="eco">Codi ECO</label>
                                        <input type="text" name="eco" id="eco" value="{{ old('eco', $partida->eco) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <div class="sm:col-span-2 border-t pt-4 mt-4"> <h4 class="font-medium">Blanques</h4> </div>
                                    <div>
                                        <label for="nom_blanques">Nom</label>
                                        <input type="text" name="nom_blanques" id="nom_blanques" value="{{ old('nom_blanques', $partida->blanques->nom ?? '') }}" class="block mt-1 w-full" required>
                                    </div>
                                    <div>
                                        <label for="elo_blanques">ELO</label>
                                        <input type="number" name="elo_blanques" id="elo_blanques" value="{{ old('elo_blanques', $partida->elo_blanques) }}" class="block mt-1 w-full">
                                    </div>
                                    <div>
                                        <label for="titol_blanques">Títol</label>
                                        <input type="text" name="titol_blanques" id="titol_blanques" value="{{ old('titol_blanques', $partida->titol_blanques) }}" class="block mt-1 w-full">
                                    </div>
                                    <div>
                                        <label for="equip_blanques">Equip</label>
                                        <input type="text" name="equip_blanques" id="equip_blanques" value="{{ old('equip_blanques', $partida->equip_blanques) }}" class="block mt-1 w-full">
                                    </div>
                                    
                                    <div class="sm:col-span-2 border-t pt-4 mt-4"> <h4 class="font-medium">Negres</h4> </div>
                                    <div>
                                        <label for="nom_negres">Nom</label>
                                        <input type="text" name="nom_negres" id="nom_negres" value="{{ old('nom_negres', $partida->negres->nom ?? '') }}" class="block mt-1 w-full" required>
                                    </div>
                                    <div>
                                        <label for="elo_negres">ELO</label>
                                        <input type="number" name="elo_negres" id="elo_negres" value="{{ old('elo_negres', $partida->elo_negres) }}" class="block mt-1 w-full">
                                    </div>
                                     <div>
                                        <label for="titol_negres">Títol</label>
                                        <input type="text" name="titol_negres" id="titol_negres" value="{{ old('titol_negres', $partida->titol_negres) }}" class="block mt-1 w-full">
                                    </div>
                                    <div>
                                        <label for="equip_negres">Equip</label>
                                        <input type="text" name="equip_negres" id="equip_negres" value="{{ old('equip_negres', $partida->equip_negres) }}" class="block mt-1 w-full">
                                    </div>
                                    
                                     <div class="sm:col-span-2 border-t pt-4 mt-4">
                                        <label for="resultat">Resultat</label>
                                        <select name="resultat" id="resultat" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                            <option value="*" @selected(old('resultat', $partida->resultat) == '*')>*</option>
                                            <option value="1-0" @selected(old('resultat', $partida->resultat) == '1-0')>1-0</option>
                                            <option value="0-1" @selected(old('resultat', $partida->resultat) == '0-1')>0-1</option>
                                            <option value="1/2-1/2" @selected(old('resultat', $partida->resultat) == '1/2-1/2')>1/2-1/2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <textarea name="pgn_moves" id="pgn_moves" class="hidden">{{ old('pgn_moves', $partida->pgn_moves) }}</textarea>

                        <div class="mt-6 p-4 bg-gray-100 rounded">
                            <h4 class="font-semibold">PGN de la partida:</h4>
                            <div id="pgn-live-view" class="font-mono text-sm mt-2 whitespace-pre-wrap"></div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('partides.index', $query_params) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel·lar</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Actualitzar Partida</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.2/chess.min.js"></script>
        <script src="{{ asset('vendor/chessboard/chessboard-1.0.0.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                // Aquest JS és una còpia exacta del de create.blade.php
                let board = null;
                const game = new Chess();
                
                function onSnapEnd() { board.position(game.fen()); updatePgn(); }
                function updatePgn() {
                    const pgn = game.pgn({ max_width: 5, newline_char: ' ' });
                    $('#pgn_moves').val(pgn);
                    $('#pgn-live-view').text(pgn);
                }
                function onDrop(source, target) {
                    try {
                        const move = game.move({ from: source, to: target, promotion: 'q' });
                        if (move === null) return 'snapback';
                    } catch (e) { return 'snapback'; }
                }
                
                // Obtenim el PGN inicial del textarea ocult
                const initialPgn = $('#pgn_moves').val() || '';
                let initialPosition = 'start';

                // Si hi ha un PGN, carreguem la partida
                if (initialPgn) {
                    try {
                        game.load_pgn(initialPgn);
                        initialPosition = game.fen();
                    } catch (e) {
                        console.error("PGN inicial invàlid:", e);
                    }
                }

                const config = {
                    draggable: true,
                    position: initialPosition, // Important: la posició inicial és la de la partida!
                    onDrop: onDrop,
                    onSnapEnd: onSnapEnd,
                    pieceTheme: '/img/chesspieces/wikipedia/{piece}.png'
                };
                board = Chessboard('board', config);

                $('#undoBtn').on('click', function() { game.undo(); board.position(game.fen()); updatePgn(); });
                $('#startBtn').on('click', function() { game.reset(); board.position('start'); updatePgn(); });
                
                $('#setFenBtn').on('click', function() {
                    const fen = $('#fen_inicial').val();
                    if (fen) {
                        try {
                            game.load(fen);
                            board.position(fen);
                            updatePgn();
                        } catch (e) { alert("FEN invàlid!"); }
                    }
                });

                // Mostrem el PGN inicial al carregar la pàgina
                updatePgn();
            });
        </script>
    </x-slot>
</x-app-layout>