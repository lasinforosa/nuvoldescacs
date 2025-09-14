<x-app-layout>

    <!-- === INICI DE LA CAPÇALERA REESTRUCTURADA === -->
    <x-slot name="header">
        <div class="flex justify-between items-center">
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
            <div class="flex space-x-2">
                @if($partidaAnterior)
                    <a href="{{ route('partides.show', ['partida' => $partidaAnterior] + request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300" title="Partida Anterior">&larr; Anterior</a>
                @endif
                <a href="{{ route('partides.index', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">Tornar a la Llista</a>
                @if($partidaSeguent)
                    <a href="{{ route('partides.show', ['partida' => $partidaSeguent] + request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300" title="Partida Següent">Següent &rarr;</a>
                @endif
            </div>
        </div>
    </x-slot>
    <!-- === FI DE LA CAPÇALERA REESTRUCTURADA === -->

    <!-- === COS PRINCIPAL === -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row gap-6">
                         
                        <!-- Columna Esquerra: Barra i Tauler -->
                        <div class="flex items-start gap-4">
                            <!-- Barra de Valoració -->
                            <div id="eval-bar-container" class="h-[400px] w-4 bg-gray-300 rounded-full flex flex-col overflow-hidden">
                                <div id="eval-bar-white" class="bg-white transition-all duration-300" style="height: 50%;"></div>
                                <div id="eval-bar-black" class="bg-gray-800 transition-all duration-300" style="height: 50%;"></div>
                            </div>

                            <!-- Tauler i Controls -->
                            <div>
                                <div id="board" style="width: 400px;"></div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button id="startBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800">|<</button>
                                    <button id="prevBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800"><</button>
                                    <button id="nextBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800">></button>
                                    <button id="endBtn" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-800">>|</button>                        
                                    <button id="flipBtn" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-800">Girar</button>
                                    <button id="analyzeBtn" class="px-3 py-1 bg-purple-600 text-white rounded text-sm hover:bg-purple-800">Analitzar</button>
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
                                            <label for="board-theme" class="text-sm">Colors</label>
                                            <select id="board-theme" class="block mt-1 ...">
                                                <option value="brown">Marró</option>
                                                <option value="green">Verd</option>
                                                <option value="blue">Blau</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Dreta: Informació i PGN -->
                        <div class="flex-1">
                            
                            <h3 class="text-lg font-medium">{{ $partida->event ?? 'Partida' }}</h3>
                            <p class="text-sm text-gray-600">{{ $partida->site }} | {{ $partida->data_partida }} | Ronda: {{ $partida->ronda }}</p>

                            <div class="mt-4 border-t pt-4">
                                <!-- ... (info de resultat, eco, equips) ... -->
                                <p><strong>Resultat:</strong> {{ $partida->resultat }}</p>
                                <p><strong>ECO:</strong> {{ $partida->eco }}</p>
                                <!-- ... (info equips) ... -->
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
                            <div id="analysis-container" class="mb-4 p-3 bg-gray-800 text-white rounded-lg font-mono text-sm hidden">
                                <div id="analysis-evaluation">Valoració: --</div>
                                <div id="analysis-best-line" class="mt-1">Millor línia: --</div>
                            </div>
                            <div id="stockfish-monitor" class="mb-4 p-2 bg-black text-green-400 font-mono text-xs rounded h-32 overflow-y-auto hidden">
                                <p><strong>Monitor de Stockfish:</strong></p>
                            </div>
                        </div>
                    </div>
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
                // --- 1. VARIABLES I DADES ---
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

                let stockfish = null;
                let isAnalyzing = false;
                let isStockfishReady = false;
                
                // --- 2. FUNCIONS D'AJUDA ---
                function loadGameFromPgn() {
                    try {
                        game.load_pgn(pgnData || '');
                        history = game.history({ verbose: true });
                        game.reset();
                        currentMoveIndex = 0;
                        boardConfig.position = 'start';
                    } catch (e) { console.error("Error carregant PGN:", e); history = []; }
                }

                function updatePgnTextView() {
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

                function updateView() {
                    if (board) board.position(game.fen());
                    updatePgnTextView();
                    if (isAnalyzing) {
                        analyzePosition();
                    }
                }

                function redrawBoard() {
                    if (board) board.destroy();
                    board = Chessboard('board', boardConfig);
                    setBoardTheme($('#board-theme').val());
                }

                function setBoardTheme(themeName) {
                    const theme = colorThemes[themeName];
                    if (!theme) return;
                    $('#board .square-55d63').each(function() {
                        const isBlackSquare = ($(this).attr('class').indexOf('black-3c85d') > -1);
                        $(this).css('background-color', isBlackSquare ? theme.dark : theme.light);
                    });
                }

                function updateEvalBar(evaluation) {
                    let score = 0;
                    if (evaluation.startsWith('M')) { score = evaluation.includes('-') ? -1000 : 1000; } 
                    else { score = parseInt(evaluation); }
                    const cappedScore = Math.max(-800, Math.min(800, score));
                    const whiteHeight = 50 + (cappedScore / 800) * 50;
                    $('#eval-bar-white').css({ 'height': `${whiteHeight}%`, 'width': '100%' });
                    $('#eval-bar-black').css({ 'height': `${100 - whiteHeight}%`, 'width': '100%' });
                }
                
                function analyzePosition() {
                    if (!isAnalyzing || !isStockfishReady) return;
                    const fen = game.fen();
                    $('#stockfish-monitor').append(`<p class="text-yellow-400">> position fen ${fen}</p>`);
                    stockfish.postMessage('position fen ' + fen);
                    $('#stockfish-monitor').append(`<p class="text-yellow-400">> go depth 18</p>`);
                    stockfish.postMessage('go depth 18');
                }

                function initializeStockfish() {
                    if (stockfish) {
                        analyzePosition(); // Si ja existeix, simplement analitzem
                        return;
                    }

                    $('#analysis-evaluation').text(`Carregant motor...`);
                    
                    stockfish = new Worker("{{ asset('vendor/stockfish/stockfish.js') }}#stockfish.wasm");
                    
                    
                    // Aquesta és la part que estava trencada
                    stockfish.onmessage = function(event) {
                        const message = event.data;
                        
                        // Mostrem SEMPRE el que rebem per depurar
                        $('#stockfish-monitor').append(`<p>< ${message}</p>`);
                        $('#stockfish-monitor').scrollTop($('#stockfish-monitor')[0].scrollHeight);

                        if (message === 'uciok') {
                            isStockfishReady = true;
                            stockfish.postMessage('ucinewgame');
                            analyzePosition();
                        } else if (message.startsWith('info depth')) {
                            if (message.includes('score cp')) {
                                const scoreMatch = message.match(/score cp (-?\d+)/);
                                const pvMatch = message.match(/pv (.+)/);
                                if (scoreMatch && pvMatch) {
                                    $('#analysis-evaluation').text(`Valoració: ${ (scoreMatch[1] / 100).toFixed(2) }`);
                                    $('#analysis-best-line').text(`Millor línia: ${pvMatch[1]}`);
                                    updateEvalBar(scoreMatch[1]);
                                }
                            } else if (message.includes('score mate')) {
                                const scoreMatch = message.match(/score mate (-?\d+)/);
                                const pvMatch = message.match(/pv (.+)/);
                                if (scoreMatch && pvMatch) {
                                    $('#analysis-evaluation').text(`Valoració: Mat en ${scoreMatch[1]}`);
                                    $('#analysis-best-line').text(`Millor línia: ${pvMatch[1]}`);
                                    updateEvalBar('M' + scoreMatch[1]);
                                }
                            }
                        }
                        else if (message.startsWith('bestmove')) {
                        // Quan acaba el càlcul, podem fer alguna cosa si volem
                        }
                    };

                    stockfish.onerror = function(e) {
                        $('#stockfish-monitor').append(`<p class="text-red-500">ERROR DEL WORKER: ${e.message}</p>`);
                    };
                    
                    stockfish.postMessage('uci');
                }
                   
                    
                function startAnalysis() {
                    isAnalyzing = true;
                    $('#analysis-container').removeClass('hidden');
                    $('#stockfish-monitor').removeClass('hidden');
                    $('#analyzeBtn').text('Aturar Anàlisi').removeClass('bg-purple-600').addClass('bg-red-600');
                    initializeStockfish();
                }

                function stopAnalysis() {
                    if (stockfish) { stockfish.postMessage('stop'); }
                    isAnalyzing = false;
                    $('#analysis-container').addClass('hidden');
                    $('#stockfish-monitor').addClass('hidden');
                    $('#analyzeBtn').text('Analitzar').removeClass('bg-red-600').addClass('bg-purple-600');
                }

                // --- 3. INICIALITZACIÓ I GESTORS D'ESDEVENIMENTS ---
                
                loadGameFromPgn();
                board = Chessboard('board', boardConfig);
                updateView();
                setBoardTheme('brown');

                // Navegació de la partida
                $('#startBtn').on('click', function() { loadGameFromPgn(); updateView(); });
                $('#prevBtn').on('click', function() { if (currentMoveIndex > 0) { currentMoveIndex--; game.undo(); updateView(); } });
                $('#nextBtn').on('click', function() { if (currentMoveIndex < history.length) { game.move(history[currentMoveIndex]); currentMoveIndex++; updateView(); } });
                $('#endBtn').on('click', function() { game.load_pgn(pgnData || ''); currentMoveIndex = history.length; updateView(); });
                
                // Controls del tauler
                $('#flipBtn').on('click', function() { board.flip(); });
                $('#analyzeBtn').on('click', function() { if (isAnalyzing) { stopAnalysis(); } else { startAnalysis(); } });
                $('#piece-theme').on('change', function() {
                    const selected = $(this).find('option:selected');
                    boardConfig.pieceTheme = `/img/chesspieces/${selected.val()}/{piece}.${selected.data('format')}`;
                    boardConfig.position = board.fen();
                    redrawBoard();
                });
                $('#board-theme').on('change', function() { setBoardTheme($(this).val()); });

                // Controls del teclat
                $(document).on('keydown', function(e) {
                    if (e.key === 'ArrowLeft') { e.preventDefault(); $('#prevBtn').click(); }
                    if (e.key === 'ArrowRight') { e.preventDefault(); $('#nextBtn').click(); }
                    if (e.key === 'Home') { e.preventDefault(); $('#startBtn').click(); }
                    if (e.key === 'End') { e.preventDefault(); $('#endBtn').click(); }
                });

                // Neteja en sortir de la pàgina
                $(window).on('beforeunload', function() { if (stockfish) { stockfish.terminate(); } });
             });
    
        </script>
    </x-slot>
</x-app-layout>