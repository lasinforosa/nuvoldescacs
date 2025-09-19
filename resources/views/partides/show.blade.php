<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <span>{{ $partida->blanques->nom ?? 'Blanques' }}</span> <span class="text-base font-normal text-gray-600">({{ $partida->elo_blanques }})</span>
                <span class="mx-2 font-normal">-</span>
                <span>{{ $partida->negres->nom ?? 'Negres' }}</span> <span class="text-base font-normal text-gray-600">({{ $partida->elo_negres }})</span>
            </h2>
            <div class="flex space-x-2">
                @if($partidaAnterior)
                    <a href="{{ route('partides.show', ['partida' => $partidaAnterior] + request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">&larr; Anterior</a>
                @endif
                <a href="{{ route('partides.index', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">Tornar a la Llista</a>
                @if($partidaSeguent)
                    <a href="{{ route('partides.show', ['partida' => $partidaSeguent] + request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">Següent &rarr;</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row gap-6">
                        
                        <div class="flex items-start gap-4">
                            <div id="eval-bar-container" class="h-[400px] w-4 bg-gray-300 rounded-full flex flex-col overflow-hidden">
                                <div id="eval-bar-white" class="bg-white transition-all duration-300" style="height: 50%;"></div>
                                <div id="eval-bar-black" class="bg-gray-800 transition-all duration-300" style="height: 50%;"></div>
                            </div>
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
                                                <!-- Les teves opcions de peces van aquí -->
                                                <option value="wikipedia" data-format="png">Wikipedia (png)</option>    
                                                <option value="berlin" data-format="svg">Berlin (svg)</option>
                                                <option value="cburnett" data-format="svg">Cburnett (svg)</option> 
                                                <option value="chess_com" data-format="png">Chess.com (png)</option>
                                                <option value="julius" data-format="svg">Julius (svg)</option>
                                                <option value="merida" data-format="svg">Merida (svg)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="board-theme" class="text-sm">Colors</label>
                                            <select id="board-theme" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 text-sm">
                                                <option value="brown">Marró</option>
                                                <option value="green">Verd</option>
                                                <option value="blue">Blau</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-lg font-medium">{{ $partida->event ?? 'Partida' }}</h3>
                            <p class="text-sm text-gray-600">{{ $partida->site }} | {{ $partida->data_partida }} | Ronda: {{ $partida->ronda }}</p>
                            <div class="mt-4 border-t pt-4">
                                <p><strong>Resultat:</strong> {{ $partida->resultat }}</p>
                                <p><strong>ECO:</strong> {{ $partida->eco }}</p>
                                @if($partida->equip_blanques)<p><strong>Equip Blanques:</strong> {{ $partida->equip_blanques }}</p>@endif
                                @if($partida->equip_negres)<p><strong>Equip Negres:</strong> {{ $partida->equip_negres }}</p>@endif
                            </div>
                            <div class="mt-4 p-4 bg-gray-100 rounded">
                                <h4 class="font-semibold">Notació:</h4>
                                <div id="pgn-tree-container" class="font-mono text-sm mt-2 whitespace-normal h-64 overflow-y-auto"></div>
                            </div>
                            <div id="analysis-container" class="mt-4 p-3 bg-gray-800 text-white rounded-lg font-mono text-sm hidden">
                                <div id="analysis-evaluation">Valoració: --</div>
                                <div id="analysis-best-line" class="mt-1">Millor línia: --</div>
                            </div>
                            <div id="stockfish-monitor" class="mt-4 p-2 bg-black text-green-400 font-mono text-xs rounded h-32 overflow-y-auto hidden">
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
                const game = new Chess();  // L'estat del joc que l'usuari està veient
                const pgnData = @json($partida->pgn_moves);
                let history = [];
                let currentMoveIndex = -1;
                
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
                
                let stockfish = null, isAnalyzing = false, isStockfishReady = false, analysisDebounceTimeout = null;

                // --- 2. FUNCIONS ---
                function pgnToTree(pgn) {
                    const pgnWithoutNewlines = pgn.replace(/(\r\n|\n|\r)/gm, " ").replace(/\s+/g, ' ');
                    
                    // EXPRESSIÓ REGULAR CORREGIDA PER ACCEPTAR ENROCS (O-O i O-O-O)
                    const tokens = pgnWithoutNewlines.match(/\(|\)|\{[^}]*\}|\$\d+|O-O-O|O-O|[NBKRQ]?[a-h]?[1-8]?x?[a-h][1-8](?:=[NBQR])?[+#?!=]*/g) || [];
                    
                    let tree = { moves: [] };
                    let path = [tree];
                    for (const token of tokens) {
                        if (!token) continue;
                        let currentNode = path[path.length - 1];
                        if (token === '(') {
                            let lastMove = currentNode.moves[currentNode.moves.length - 1];
                            if (!lastMove) continue;
                            if (!lastMove.variants) lastMove.variants = [];
                            let newVariant = { moves: [] };
                            lastMove.variants.push(newVariant);
                            path.push(newVariant);
                        } else if (token === ')') {
                            if (path.length > 1) path.pop();
                        } else if (token.startsWith('{')) {
                            let lastNode = currentNode.moves.length > 0 ? currentNode.moves[currentNode.moves.length - 1] : currentNode;
                            if (!lastNode.comments) lastNode.comments = [];
                            lastNode.comments.push(token.substring(1, token.length - 1).trim());
                        } else if (!/^\d+\.|\.\.$/.test(token) && !/1-0|0-1|1\/2-1\/2|\*/.test(token)) {
                            currentNode.moves.push({ san: token });
                        }
                    }
                    return tree;
                }

                            // === LA NOVA I MILLORADA FUNCIÓ renderTree ===
                function renderTree(node, container, initialPgnMoves = []) {
                    for (let i = 0; i < node.moves.length; i++) {
                        const moveData = node.moves[i];

                        // Creem una instància de joc per a AQUESTA línia de joc
                        let localGame = new Chess();
                        // Carreguem la història de la línia principal fins a aquest punt
                        initialPgnMoves.forEach(move => localGame.move(move));

                        const turn = localGame.turn();
                        const moveNumber = Math.floor(localGame.history().length / 2) + 1;

                        // LÒGICA DE NUMERACIÓ CORREGIDA I SIMPLIFICADA
                        if (turn === 'w') {
                            container.append(`<span class="font-bold mr-1">${moveNumber}.</span>`);
                        } else if (i === 0) {
                            container.append(`<span class="font-bold mr-1">${moveNumber}...</span>`);
                        }

                        const moveResult = localGame.move(moveData.san, { sloppy: true });
                        if (!moveResult) continue;

                        const moveClasses = [
                            'cursor-pointer', 'hover:bg-yellow-300', 'p-1', 'rounded', 'move-span',
                            isVariant ? 'font-normal text-gray-700' : 'font-bold'
                        ];
                        const moveSpan = $(`<span class="${moveClasses.join(' ')}" data-fen="${localGame.fen()}">${moveData.san}</span>`);

                        container.append(moveSpan);
                        container.append(' ');

                        if (moveData.comments) { container.append(`<em class="text-blue-600 mx-1">{ ${moveData.comments.join(' ')} }</em> ` }

                        if (moveData.variants && moveData.variants.length > 0) {
                            for (const variant of moveData.variants) {
                                const variantContainer = $('<div class="ml-4 border-l-2 border-gray-300 pl-2 mt-1"></div>');
                                container.append(variantContainer);

                                // La clau és aquí: passem la història de jugades actual
                                const newInitialMoves = [...initialPgnMoves, moveData.san];
                                renderTree(variant, variantContainer, newInitialMoves);
                            }
                        }
                        
                        
                        const fenBeforeMove = localGame.fen();
                        const moveResult = localGame.move(moveData.san, { sloppy: true });
                        if (!moveResult) continue;

                        // ESTIL FINAL: Negreta i subratllat per a la línia principal
                        const moveSpan = $(`<span class="cursor-pointer hover:bg-yellow-300 p-1 rounded move-span font-bold" data-fen="${localGame.fen()}">${moveData.san}</span>`);
                        
                        container.append(moveSpan);
                        container.append(' ');
                        
                        

                    if (isVariant) {
                        // La teva idea: un petit indicador visual per a les variants
                        container.append('<span class="text-indigo-500 mr-1">&raquo;</span>');
                    }

                    for (let i = 0; i < node.moves.length; i++) {
                        const moveData = node.moves[i];
                        
                        const turn = localGame.turn();
                        const moveNumber = Math.floor(localGame.history().length / 2) + 1;
      
                        // LÒGICA DE NUMERACIÓ CORREGIDA I SIMPLIFICADA
                        if (turn === 'w') {
                            container.append(`<span class="font-bold mr-1">${moveNumber}.</span>`);
                        } else if (i === 0) {
                            // Només per a l'inici d'una variant que comença amb negres
                            container.append(`<span class="font-bold mr-1">${moveNumber}...</span>`);
                        }
                        
                        const fenBeforeMove = localGame.fen();
                        const moveResult = localGame.move(moveData.san, { sloppy: true });
                        if (!moveResult) continue;

                        // ESTIL FINAL: Negreta i subratllat per a la línia principal
                        
                        const moveSpan = $(`<span class="${moveClasses.join(' ')}" data-fen="${localGame.fen()}">${moveData.san}</span>`);
                        
                        container.append(moveSpan);
                        container.append(' ');
                        
                        if (moveData.comments) {
                            container.append(`<em class="text-blue-600 mx-1">{ ${moveData.comments.join(' ')} }</em> `);
                        }
                        
                        if (moveData.variants && moveData.variants.length > 0) {
                            for (const variant of moveData.variants) {
                                // Creem un contenidor per a la variant amb el sagnat
                                const variantContainer = $('<div class="ml-4 border-l-2 border-gray-300 pl-2 mt-1"></div>');
                                container.append(variantContainer);
                                // Utilitzem 'localGame' per a la recursió, no 'game'
                                container.append(" ["); container.append(fenBeforeMove); container.append("] ");
                                renderTree(variant, variantContainer, new Chess(fenBeforeMove), true);
                            }
                        }
                    }                  
                }
 
                function loadGameFromPgn() {
                    try {
                        const tempGame = new Chess();
                        tempGame.load_pgn(pgnData || '');
                        history = tempGame.history({ verbose: true });
                    } catch (e) {
                        $('#pgn-tree-container').html('<p class="text-red-500">Error: PGN invàlid.</p>');
                        history = [];
                    }
                }

                function renderPgnTree() {
                    let pgnHtml = '';
                    let moveNumber = 1;
                    history.forEach((move, i) => {
                        if (move.color === 'w') pgnHtml += `<span class="font-bold mr-1">${moveNumber}.</span>`;
                        pgnHtml += `<span class="cursor-pointer hover:bg-yellow-300 p-1 rounded move-span" data-move-index="${i}">${move.san}</span> `;
                        if (move.color === 'b') moveNumber++;
                    });
                    $('#pgn-tree-container').html(pgnHtml);
                }

                function updatePgnTextView() {
                    let pgnHtml = '';
                    let moveNumber = 1;
                    for (let i = 0; i < history.length; i++) {
                        if (history[i].color === 'w') { pgnHtml += `<span class="font-bold mr-1">${moveNumber}.</span>`; }
                        
                        // AFEGIM L'ATRIBUT 'data-move-index' PER PODER IDENTIFICAR CADA JUGADA
                        let moveStyle = (i === currentMoveIndex) ? 'bg-yellow-200' : '';
                        pgnHtml += `<span class="${moveStyle} p-1 rounded cursor-pointer hover:bg-yellow-300 move-span" data-move-index="${i}">${history[i].san}</span> `;
                        
                        if (history[i].color === 'b') { moveNumber++; }
                    }
                    $('#pgn-tree-container').html(pgnHtml);
                }

                function goToMove(index) {
                    game.reset();
                    for (let i = 0; i <= index; i++) {
                        game.move(history[i].san);
                    }
                    currentMoveIndex = index;
                    board.position(game.fen());
                    
                    $('.move-span').removeClass('bg-yellow-200');
                    if (index > -1) {
                        $(`.move-span[data-move-index=${index}]`).addClass('bg-yellow-200');
                    }
                    
                    if (isAnalyzing) analyzePosition();
                }

                function updateView() {
                    board.position(game.fen());
                    updatePgnTextView();
                    if (isAnalyzing) analyzePosition();
                }

                function redrawBoard() {
                    if (board) board.destroy();
                    board = Chessboard('board', boardConfig);
                    setBoardTheme($('#board-theme').val());
                }

                function setBoardTheme(themeName) {
                    const theme = colorThemes[themeName];
                    if (!theme) return;
                    setTimeout(() => {
                        $('#board .square-55d63').each(function() {
                            const isBlackSquare = ($(this).width() === $(this).height()) ?
                                ($(this).attr('class').indexOf('black') > -1) :
                                (($(this).parent().parent().index() + $(this).parent().index()) % 2 === 0);
                            $(this).css('background-color', isBlackSquare ? theme.dark : theme.light);
                        });
                    }, 50);
                }
                
                function analyzePosition() { 
                    if (!isAnalyzing || !isStockfishReady) return;
                    clearTimeout(analysisDebounceTimeout);
                    analysisDebounceTimeout = setTimeout(() => {
                        stockfish.postMessage('stop');
                        // LA CLAU: Sempre utilitzem el FEN de l'objecte 'game' principal
                        stockfish.postMessage('position fen ' + game.fen()); 
                        stockfish.postMessage('go depth 18');
                    }, 250);
                }

                function initializeStockfish() { 
                     if (stockfish) { analyzePosition(); return; }
                    $('#analysis-evaluation').text(`Carregant motor...`);
                    stockfish = new Worker("{{ asset('vendor/stockfish/stockfish.js') }}#stockfish.wasm");
                    
                    stockfish.onmessage = function(event) {
                        const message = event.data;
                        $('#stockfish-monitor').append(`<p>< ${message}</p>`).scrollTop($('#stockfish-monitor')[0].scrollHeight);
                        
                        if (message === 'uciok') {
                            isStockfishReady = true;
                            stockfish.postMessage('ucinewgame');
                            analyzePosition();
                        } else if (message.startsWith('info depth')) {
                            const currentFen = game.fen(); // Guardem el FEN actual
                            const turn = game.turn();
                            
                            if (message.includes('score cp')) {
                                const scoreMatch = message.match(/score cp (-?\d+)/);
                                const pvMatch = message.match(/pv (.+)/);
                                if (scoreMatch && pvMatch) {
                                    const bestLineSan = translateLanToSan(pvMatch[1], currentFen);
                                    let displayScore = (scoreMatch[1] / 100).toFixed(2);
                                    if (turn === 'b') displayScore = (-displayScore).toFixed(2);
                                    
                                    $('#analysis-evaluation').text(`Valoració: ${displayScore}`);
                                    $('#analysis-best-line').text(`Millor línia: ${bestLineSan}`);
                                    updateEvalBar(scoreMatch[1], turn);
                                }
                            } else if (message.includes('score mate')) {
                                const scoreMatch = message.match(/score mate (-?\d+)/);
                                const pvMatch = message.match(/pv (.+)/);
                                if (scoreMatch && pvMatch) {
                                    const bestLineSan = translateLanToSan(pvMatch[1], currentFen);
                                    let displayMate = `Mat en ${scoreMatch[1]}`;
                                    if (turn === 'b') displayMate = `Mat en ${-scoreMatch[1]}`;
                                    
                                    $('#analysis-evaluation').text(`Valoració: ${displayMate}`);
                                    $('#analysis-best-line').text(`Millor línia: ${bestLineSan}`);
                                    updateEvalBar('M' + scoreMatch[1], turn);
                                }
                            }
                        }
                    };
                    stockfish.postMessage('uci');
                }

                function setAnalysisState(active) { 
                    isAnalyzing = active;
                    if (active) {
                        $('#analysis-container, #stockfish-monitor').removeClass('hidden');
                        $('#analyzeBtn').text('Aturar Anàlisi').removeClass('bg-purple-600').addClass('bg-red-600');
                        if (!stockfish) initializeStockfish();
                        else analyzePosition();
                    } else {
                        if (stockfish) stockfish.postMessage('stop');
                        $('#analysis-container, #stockfish-monitor').addClass('hidden');
                        $('#analyzeBtn').text('Analitzar').removeClass('bg-red-600').addClass('bg-purple-600');
                    }
                }

                function translateLanToSan(lanMoves, currentFen) {
                    // ARA REP EL FEN ACTUAL PER SER ROBUSTA
                    const tempGame = new Chess(currentFen);
                    let sanLine = '';
                    const moves = lanMoves.split(' ');
                    for (const lan of moves) {
                        const moveResult = tempGame.move(lan, { sloppy: true });
                        if (moveResult) sanLine += moveResult.san + ' ';
                    }
                    return sanLine.trim();
                }

                function updateEvalBar(evaluation, turn) {
                    let score = 0;
                    if (evaluation.startsWith('M')) {
                        score = evaluation.includes('-') ? -1000 : 1000;
                    } else {
                        score = parseInt(evaluation);
                    }
                    if (turn === 'b') score = -score;
                    const cappedScore = Math.max(-800, Math.min(800, score));
                    const whiteHeight = 50 + (cappedScore / 800) * 50;
                    $('#eval-bar-white').css({ 'height': `${whiteHeight}%`, 'width': '100%' });
                    $('#eval-bar-black').css({ 'height': `${100 - whiteHeight}%`, 'width': '100%' });
                }

                // --- 3. INICIALITZACIÓ I GESTORS D'ESDEVENIMENTS ---
                board = Chessboard('board', boardConfig);
                setBoardTheme('blue');

                if (pgnData) {
                    const moveTree = pgnToTree(pgnData);
                    renderTree(moveTree, $('#pgn-tree-container'), []);
                    board.position('start'); // Assegurem posició inicial visual
                } else {
                    $('#pgn-tree-container').html('<p>No hi ha jugades.</p>');
                }

                // Gestor de clics (ara funciona)
                $('#pgn-tree-container').on('click', '.move-span', function() {
                    const fen = $(this).data('fen');
                    if (fen) {
                        // 1. Actualitzem l'estat del joc principal
                        game.load(fen);
                        // 2. Actualitzem el tauler visual
                        board.position(fen);
                        
                        // 3. Actualitzem el ressaltat
                        $('.move-span').removeClass('bg-yellow-200');
                        $(this).addClass('bg-yellow-200');
                        
                        // 4. Demanem la nova anàlisi
                        if (isAnalyzing) {
                            analyzePosition();
                        }
                    }
                });


                // Desactivem els botons de navegació seqüencial
                $('#startBtn, #prevBtn, #nextBtn, #endBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                /*
                $('#startBtn').on('click', () => { game.reset(); currentMoveIndex = -1; updateView(); });
                $('#prevBtn').on('click', () => { if(currentMoveIndex >= 0) { game.undo(); currentMoveIndex--; updateView(); }});
                $('#nextBtn').on('click', () => { if(currentMoveIndex < history.length - 1) { currentMoveIndex++; game.move(history[currentMoveIndex].san); updateView(); }});
                $('#endBtn').on('click', () => { game.load_pgn(pgnData || ''); currentMoveIndex = history.length - 1; updateView(); });
                */

                
                // CONTROL DEL TAULER (AMB LA CORRECCIÓ PER A 'flip')
                $('#flipBtn').on('click', () => {
                    board.flip();
                    // Tornem a aplicar el tema de colors després de girar
                    setBoardTheme($('#board-theme').val());
                });

                $('#analyzeBtn').on('click', () => setAnalysisState(!isAnalyzing));
                
                $('#piece-theme').on('change', function() {
                    const selected = $(this).find('option:selected');
                    boardConfig.pieceTheme = `/img/chesspieces/${selected.val()}/{piece}.${selected.data('format')}`;
                    boardConfig.position = board.fen();
                    redrawBoard();
                });
                $('#board-theme').on('change', () => setBoardTheme($('#board-theme').val()));

                $(document).on('keydown', function(e) {
                    if (e.key === 'ArrowLeft') { e.preventDefault(); $('#prevBtn').click(); }
                    if (e.key === 'ArrowRight') { e.preventDefault(); $('#nextBtn').click(); }
                    if (e.key === 'Home') { e.preventDefault(); $('#startBtn').click(); }
                    if (e.key === 'End') { e.preventDefault(); $('#endBtn').click(); }
                });

                $(window).on('beforeunload', () => { if (stockfish) stockfish.terminate(); });
            });
        </script>
    </x-slot>
</x-app-layout>