<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestió de Jugadors i Identitats') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div class="mb-4 px-4 py-2 bg-green-100 border border-green-200 text-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 px-4 py-2 bg-red-100 border border-red-200 text-red-700 rounded-md">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-sm text-gray-600 mb-4">
                        Per fusionar, selecciona les identitats duplicades amb la casella de selecció. Després, marca una d'elles com la principal (mestra) amb el botó de ràdio.
                    </p>
                    

                    <form method="POST" action="{{ route('jugadors.merge') }}" id="merge-form" onsubmit="return validateMergeForm()">
                        @csrf

                        <div class="mb-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-indigo-800">
                                Fusionar Seleccionats
                            </button>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 w-12">Sel.</th>
                                    <th class="px-6 py-3 w-12">Principal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom de la Identitat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Persona Original</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Partides Jugades</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($identitats as $identitat)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <!-- Checkbox per seleccionar la fila -->
                                            <input type="checkbox" name="identitat_ids[]" value="{{ $identitat->id_identitat }}" class="identitat-checkbox">
                                        </td>
                                        <td class="px-6 py-4">
                                            <!-- Botó de ràdio per triar la mestra -->
                                            <input type="radio" name="master_id" value="{{ $identitat->id_identitat }}" class="identitat-radio">
                                        </td>
                                        <td class="px-6 py-4 font-medium">{{ $identitat->nom }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $identitat->id_persona }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $identitat->partides_blanques_count + $identitat->partides_negres_count }}
                                        </td>
                                    </tr>
                                @empty
                                    <!-- ... -->
                                @endforelse
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function validateMergeForm() {
                const checkedBoxes = document.querySelectorAll('.identitat-checkbox:checked');
                const masterRadio = document.querySelector('.identitat-radio:checked');

                if (checkedBoxes.length < 2) {
                    alert('Si us plau, selecciona almenys dues identitats per fusionar.');
                    return false; // Atura l'enviament del formulari
                }

                if (!masterRadio) {
                    alert('Si us plau, marca una de les identitats seleccionades com a principal.');
                    return false;
                }
                
                let masterIsChecked = false;
                checkedBoxes.forEach(checkbox => {
                    if (checkbox.value === masterRadio.value) {
                        masterIsChecked = true;
                    }
                });

                if (!masterIsChecked) {
                    alert('La identitat principal ha de ser una de les seleccionades.');
                    return false;
                }

                return confirm(`Estàs segura que vols fusionar ${checkedBoxes.length} identitats en una de sola? Aquesta acció no es pot desfer.`);
            }
        </script>
    </x-slot>

</x-app-layout>