<x-app-layout>
    <x-slot name="header">
        <!-- === INICI DE LA CAPÇALERA REESTRUCTURADA === -->
        <div class="flex justify-between items-center">

            <!-- Part Esquerra: Títol amb els noms -->
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <span>
                    {{ $partida->blanques->nom ?? 'Blanques' }}
                    @if(!empty($partida->titol_blanques) || !empty($partida->elo_blanques))
                        <span class="text-base font-normal text-gray-600">
                            ({{ $partida->titol_blanques }}{{ !empty($partida->titol_blanques) && !empty($partida->elo_blanques) ? ', ' : '' }}{{ $partida->elo_blanques }})
                        </span>
                    @endif
                </span>
                <span class="mx-2 font-normal">-</span>
                <span>
                    {{ $partida->negres->nom ?? 'Negres' }}
                    @if(!empty($partida->titol_negres) || !empty($partida->elo_negres))
                        <span class="text-base font-normal text-gray-600">
                            ({{ $partida->titol_negres }}{{ !empty($partida->titol_negres) && !empty($partida->elo_negres) ? ', ' : '' }}{{ $partida->elo_negres }})
                        </span>
                    @endif
                </span>
            </h2>

            <!-- Part Dreta: Botons de Navegació -->
            <div class="flex space-x-2">
                @if($partidaAnterior)
                    <a href="{{ route('partides.show', ['partida' => $partidaAnterior] + $query_params) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300" title="Partida Anterior">&larr; Anterior</a>
                @endif
                
                <a href="{{ route('partides.index', $query_params) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">Tornar a la Llista</a>
                
                @if($partidaSeguent)
                    <a href="{{ route('partides.show', ['partida' => $partidaSeguent] + $query_params) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300" title="Partida Següent">Següent &rarr;</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- Columna 1: Tauler i Controls -->
                        <div class="md:col-span-1">
                            <div id="board-container">
                                <div id="board" style="width: 100%; max-width: 400px;"></div>
                            </div>
                            <div class="mt-2 space-x-2">
                                <button id="startBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800">|<</button>
                                <button id="prevBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800"><</button>
                                <button id="nextBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800">></button>
                                <button id="endBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800">>|</button>                        
                                <button id="flipBtn" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-800">Girar</button>
                            </div>

                            <div class="mt-4 border-t pt-4">
                                <h4 class="font-medium mb-2">Personalització</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="piece-theme" class="text-sm">Peces</label>
                                        <select id="piece-theme" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 text-sm">
                                            <option value="wikipedia" data-format="png">Wikipedia (png)</option>
                                            <option value="berlin" data-format="svg">Berlin (svg)</option>  
                                            <option value="cburnett" data-format="svg">Cburnett (svg)</option> 
                                            <option value="chess_com" data-format="png">Chess_com (png)</option>
                                            <option value="chess_com" data-format="svg">Chess_com (svg)</option> 
                                            <option value="julius" data-format="svg">Julius (svg)</option>
                                            <option value="merida" data-format="svg">Merida (svg)</option>
                                            <option value="merida-new" data-format="svg">Merida new (svg)</option>
                                            <option value="usual" data-format="svg">Usual (svg)</option>
                                            
                                            <!-- Afegeix aquí més opcions amb el seu 'data-format' correcte -->
                                        </select>
                                    </div>
                                    <div>
                                        <label for="board-theme" class="text-sm">Colors Tauler</label>
                                        <select id="board-theme" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 text-sm">
                                            <option value="brown">Marró</option>
                                            <option value="green">Verd</option>
                                            <option value="blue">Blau</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- FI CORRECTE DE LA COLUMNA 1 -->

                        <!-- Columna 2: Informació i PGN -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium">{{ $partida->event ?? 'Partida' }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ $partida->site ?? 'Lloc desconegut' }} | {{ $partida->data_partida }} | Ronda: {{ $partida->ronda ?? 'N/D' }}
                            </p>
                    
                            <div class="mt-4 border-t pt-4">
                                <p><strong>Resultat:</strong> {{ $partida->resultat }}</p>
                                <p><strong>Obertura (ECO):</strong> {{ $partida->eco ?? 'No calculat' }}</p>
                                @if($partida->equip_blanques)
                                    <p><strong>Equip Blanques:</strong> {{ $partida->equip_blanques }}</p>
                                @endif
                                @if($partida->equip_negres)
                                    <p><strong>Equip Negres:</strong> {{ $partida->equip_negres }}</p>
                                @endif
                            </div>

                            <div class="mt-4 p-4 bg-gray-100 rounded">
                                <h4 class="font-semibold">Notació:</h4>
                                <div id="pgn-display" class="font-mono text-sm mt-2 whitespace-pre-wrap h-64 overflow-y-auto">
                                    {{-- El PGN es carregarà aquí amb JavaScript --}}
                                </div>
                            </div>
                        </div> <!-- FI CORRECTE DE LA COLUMNA 2 -->

                    </div> <!-- FI DE L'ESTRUCTURA DE GRAELLA -->
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
                let board = null;
                const game = new Chess();
                const pgnData = @json($partida->pgn_moves);
                let history = [];
                let currentMoveIndex = 0;

                let boardConfig = {
                    draggable: false,
                    position: 'start',
                    pieceTheme: '/img/chesspieces/wikipedia/{piece}.png'
                };

                const colorThemes = {
                    brown: { light: '#f0d9b5', dark: '#b58863' },
                    green: { light: '#e8e8e8', dark: '#7c986d' },
                    blue:  { light: '#dee3e6', dark: '#8ca2ad' }
                };

                function loadGameFromPgn() {
                    try {
                        game.load_pgn(pgnData || '');
                        history = game.history({ verbose: true });
                        game.reset();
                        currentMoveIndex = 0;
                        boardConfig.position = 'start';
                    } catch (e) {
                        console.error("Error carregant PGN:", e);
                        history = [];
                    }
                }
                
                function updateView() {
                    if (board) {
                        board.position(game.fen());
                    }
                    
                    let pgnHtml = '';
                    let moveNumber = 1;
                    for (let i = 0; i < history.length; i++) {
                        if (history[i].color === 'w') { pgnHtml += `${moveNumber}. `; }
                        let moveStyle = (i === currentMoveIndex - 1) ? 'bg-yellow-200' : '';
                        pgnHtml += `<span class="${moveStyle} p-1 rounded">${history[i].san}</span> `;
                        if (history[i].color === 'b') { moveNumber++; }
                    }
                    $('#pgn-display').html(pgnHtml);
                }

                function redrawBoard() {
                    if (board) board.destroy();
                    board = Chessboard('board', boardConfig);
                    setBoardTheme($('#board-theme').val());
                    updateView();
                }

                function setBoardTheme(themeName) {
                    const theme = colorThemes[themeName];
                    if (!theme) return;

                    $('#board .square-55d63').each(function() {
                        const isBlackSquare = ($(this).width() === $(this).height()) ?
                            ($(this).attr('class').indexOf('black') > -1) :
                            (($(this).parent().parent().index() + $(this).parent().index()) % 2 === 0);

                        $(this).css('background-color', isBlackSquare ? theme.dark : theme.light);
                    });
                }

                loadGameFromPgn();
                board = Chessboard('board', boardConfig);
                updateView();
                setBoardTheme('brown');

                $('#startBtn').on('click', function() { loadGameFromPgn(); redrawBoard(); });
                $('#prevBtn').on('click', function() { if (currentMoveIndex > 0) { currentMoveIndex--; game.undo(); updateView(); } });
                $('#nextBtn').on('click', function() { if (currentMoveIndex < history.length) { game.move(history[currentMoveIndex]); currentMoveIndex++; updateView(); } });
                $('#endBtn').on('click', function() { game.load_pgn(pgnData || ''); currentMoveIndex = history.length; updateView(); });
                $('#flipBtn').on('click', function() { board.flip(); });

                $('#piece-theme').on('change', function() {
                    const selected = $(this).find('option:selected');
                    const themeName = selected.val();
                    const format = selected.data('format');
                    
                    boardConfig.pieceTheme = `/img/chesspieces/${themeName}/{piece}.${format}`;
                    boardConfig.position = board.fen();
                    redrawBoard();
                });

                $('#board-theme').on('change', function() { setBoardTheme($(this).val()); });
            });

            // Navegació amb teclat
            $(document).on('keydown', function(e) {
                switch(e.key) {
                    case 'ArrowLeft': // Fletxa esquerra
                        e.preventDefault(); // Evitem que el navegador faci scroll
                        $('#prevBtn').click(); // Simulem un clic al botó "Enrere"
                        break;
                    
                    case 'ArrowRight': // Fletxa dreta
                        e.preventDefault();
                        $('#nextBtn').click(); // Simulem un clic al botó "Endavant"
                        break;

                    case 'Home': // Tecla Inici
                        e.preventDefault();
                        $('#startBtn').click(); // Simulem un clic al botó "Inici"
                        break;

                    case 'End': // Tecla Fi
                        e.preventDefault();
                        $('#endBtn').click(); // Simulem un clic al botó "Final"
                        break;
                }
            });

        </script>
    </x-slot>
</x-app-layout>