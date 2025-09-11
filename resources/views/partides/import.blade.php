<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importar Partides des de PGN') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <p class="mb-4">Selecciona un fitxer de text (.pgn o .txt) que contingui una o més partides en format PGN.</p>

                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                            <strong>Error:</strong> {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- IMPORTANT: enctype="multipart/form-data" és essencial per pujar fitxers -->
                    <form method="POST" action="{{ route('partides.import.handle') }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label for="pgn_file" class="block font-medium text-sm text-gray-700">Fitxer PGN</label>
                            <input id="pgn_file" class="block mt-1 w-full" type="file" name="pgn_file" required />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('partides.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Cancel·lar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Importar Fitxer
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>