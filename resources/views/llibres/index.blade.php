<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('La meva Biblioteca') }}
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

                    <div class="flex justify-between items-center mb-4">
                        <a href="{{ route('llibres.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Afegir Llibre Nou
                        </a>
                        
                        <form action="{{ route('llibres.index') }}" method="GET">
                            <input type="text" name="cerca" placeholder="Busca..." value="{{ $cerca ?? '' }}" class="rounded-md shadow-sm border-gray-300">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800">Cerca</button>
                        </form>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 mt-6">
                        <!-- ... la resta de la taula (thead, tbody) és la mateixa que a la resposta anterior ... -->
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Títol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nota</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($llibres as $llibre)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $llibre->titol }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $llibre->autor }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $llibre->categoria }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $llibre->temes }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $llibre->nota }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('llibres.edit', $llibre) }}" class="inline-flex items-center px-3 py-1 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                            Editar
                                        </a>
                                        <form method="POST" action="{{ route('llibres.destroy', $llibre) }}" class="inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-800">
                                                Esborrar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No s'han trobat llibres (o cap coincideix amb la cerca).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
    
    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const deleteForms = document.querySelectorAll('.delete-form');
                deleteForms.forEach(form => {
                    form.addEventListener('submit', function (event) {
                        if (!confirm('Estàs segura que vols esborrar aquest llibre?')) {
                            event.preventDefault();
                        }
                    });
                });
            });
        </script>
    </x-slot>
</x-app-layout>