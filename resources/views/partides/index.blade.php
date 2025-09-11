<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Base de Dades de Partides') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session('success'))
                        <div class="mb-4 px-4 py-2 bg-green-100 border border-green-200 text-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Formulari de Filtre Avançat -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg border">
                        <form action="{{ route('partides.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 items-end">
                            
                            <!-- Cerca per Jugadors -->
                            <div>
                                <label for="search_white" class="block text-sm font-medium text-gray-700">Blanques</label>
                                <input type="text" name="search_white" id="search_white" value="{{ $search_inputs['search_white'] ?? '' }}" placeholder="Cognom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="search_black" class="block text-sm font-medium text-gray-700">Negres</label>
                                <input type="text" name="search_black" id="search_black" value="{{ $search_inputs['search_black'] ?? '' }}" placeholder="Cognom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            
                            <!-- NOU: Checkbox per ignorar colors -->
                            <div class="flex items-center pt-5">
                                <input type="checkbox" name="ignore_colors" id="ignore_colors" value="1" @checked(request('ignore_colors')) class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="ignore_colors" class="ml-2 block text-sm text-gray-900">Ignorar colors</label>
                            </div>

                            <!-- Cerca per Dates -->
                            <div>
                                <label for="search_year_from" class="block text-sm font-medium text-gray-700">Des de (any)</label>
                                <input type="number" name="search_year_from" id="search_year_from" value="{{ $search_inputs['search_year_from'] ?? '' }}" placeholder="Ex: 1980" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="search_year_to" class="block text-sm font-medium text-gray-700">Fins a (any)</label>
                                <input type="number" name="search_year_to" id="search_year_to" value="{{ $search_inputs['search_year_to'] ?? '' }}" placeholder="Ex: 1990" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <!-- Altres Filtres -->
                            <div>
                                <label for="search_event" class="block text-sm font-medium text-gray-700">Esdeveniment</label>
                                <input type="text" name="search_event" id="search_event" value="{{ $search_inputs['search_event'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="search_site" class="block text-sm font-medium text-gray-700">Lloc</label>
                                <input type="text" name="search_site" id="search_site" value="{{ $search_inputs['search_site'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="search_eco" class="block text-sm font-medium text-gray-700">ECO</label>
                                <input type="text" name="search_eco" id="search_eco" value="{{ $search_inputs['search_eco'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="search_result" class="block text-sm font-medium text-gray-700">Resultat</label>
                                <select name="search_result" id="search_result" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Tots</option>
                                    <option value="1-0" @selected(request('search_result') == '1-0')>1-0</option>
                                    <option value="0-1" @selected(request('search_result') == '0-1')>0-1</option>
                                    <option value="1/2-1/2" @selected(request('search_result') == '1/2-1/2')>1/2-1/2</option>
                                    <option value="*" @selected(request('search_result') == '*')>*</option>
                                </select>
                            </div>

                            <!-- Botons -->
                            <div class="lg:col-span-2 flex space-x-2">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-blue-800">Filtrar</button>
                                <a href="{{ route('partides.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md text-xs uppercase font-semibold hover:bg-gray-400">Netejar</a>
                            </div>
                        </form>
                    </div>

                    <div class="flex justify-between items-center mb-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('partides.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase font-semibold hover:bg-gray-700">Introduir Partida</a>
                            <a href="{{ route('partides.import.form') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-blue-800">Importar PGN</a>
                            <button type="submit" form="bulk-delete-form" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-red-800">Esborrar Seleccionades</button>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                            <form action="{{ route('partides.index', request()->except('perPage')) }}" method="GET">
                                <label for="perPage">Files:</label>
                                <select name="perPage" id="perPage" onchange="this.form.submit()" class="rounded-md shadow-sm border-gray-300 text-sm">
                                    <option value="10" @selected(request('perPage', 25) == 10)>10</option>
                                    <option value="25" @selected(request('perPage', 25) == 25)>25</option>
                                    <option value="50" @selected(request('perPage', 25) == 50)>50</option>
                                    <option value="100" @selected(request('perPage', 25) == 100)>100</option>
                                </select>
                                @foreach (request()->except('perPage') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                            </form>
                            <div class="text-gray-600">
                                Mostrant <strong>{{ $partides->count() }}</strong> de <strong>{{ $partides->total() }}</strong> partides
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('partides.bulk.destroy') }}" id="bulk-delete-form">
                        @csrf
                        @method('DELETE')
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2"><input type="checkbox" id="select-all-checkbox"></th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Data</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Blanques</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Negres</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Res.</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Esdeveniment</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Lloc</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Rda.</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">ECO</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Accions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($partides as $partida)
                                        <tr class="game-row">
                                            <td class="px-4 py-2"><input type="checkbox" name="partida_ids[]" value="{{ $partida->id_partida }}" class="row-checkbox"></td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $partida->data_partida }}</td>
                                            <td class="px-4 py-2">{{ $partida->blanques->nom ?? 'N/D' }}</td>
                                            <td class="px-4 py-2">{{ $partida->negres->nom ?? 'N/D' }}</td>
                                            <td class="px-4 py-2 text-center">{{ str_replace('-1/2', '', $partida->resultat) }}</td>
                                            <td class="px-4 py-2">{{ Str::limit($partida->event, 25) }}</td>
                                            <td class="px-4 py-2">{{ Str::limit($partida->site, 25) }}</td>
                                            <td class="px-4 py-2 text-center">{{ $partida->ronda }}</td>
                                            <td class="px-4 py-2">{{ $partida->eco }}</td>
                                            <td class="px-4 py-2 text-sm font-medium whitespace-nowrap">
                                                <!-- BOTONS AMB ESTILS COMPLETES -->                                              
                                                <a href="{{ route('partides.show', ['partida' => $partida] + request()->query()) }}" class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-md text-xs hover:bg-green-800">Veure</a>
                                                <a href="{{ route('partides.edit', ['partida' => $partida] + request()->query()) }}" class="inline-flex items-center px-3 py-1 bg-indigo-500 text-white rounded-md text-xs hover:bg-indigo-700 ml-2">Editar</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" class="text-center p-4">Encara no hi ha cap partida a la base de dades.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="mt-4">
                        {{ $partides->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selectAllCheckbox = document.getElementById('select-all-checkbox');
                const rowCheckboxes = document.querySelectorAll('.row-checkbox');
                const bulkDeleteForm = document.getElementById('bulk-delete-form');

                function toggleRowHighlight(checkbox) {
                    const row = checkbox.closest('.game-row');
                    if (checkbox.checked) {
                        row.classList.add('bg-yellow-100');
                    } else {
                        row.classList.remove('bg-yellow-100');
                    }
                }

                selectAllCheckbox.addEventListener('change', function () {
                    rowCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                        toggleRowHighlight(checkbox);
                    });
                });

                rowCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        toggleRowHighlight(this);
                        // Si desmarquem una, el "seleccionar tot" també s'ha de desmarcar
                        if (!this.checked) {
                            selectAllCheckbox.checked = false;
                        }
                    });
                });

                bulkDeleteForm.addEventListener('submit', function (event) {
                    const selected = document.querySelectorAll('.row-checkbox:checked').length;
                    if (selected === 0) {
                        alert('Si us plau, selecciona almenys una partida per esborrar.');
                        event.preventDefault(); // Atura l'enviament del formulari
                        return;
                    }
                    if (!confirm(`Estàs segura que vols esborrar les ${selected} partides seleccionades? Aquesta acció no es pot desfer.`)) {
                        event.preventDefault();
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>