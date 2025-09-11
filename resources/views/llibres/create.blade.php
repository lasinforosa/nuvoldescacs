<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Afegir un Llibre Nou') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Mostra errors de validació si n'hi ha -->
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="font-medium text-red-600">{{ __('Ui! Alguna cosa ha anat malament.') }}</div>
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Inici del formulari -->
                    <form method="POST" action="{{ route('llibres.store') }}">
                        @csrf <!-- Token de seguretat de Laravel, molt important! -->

                        <!-- Títol -->
                        <div class="mt-4">
                            <label for="titol" class="block font-medium text-sm text-gray-700">Títol</label>
                            <input id="titol" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="titol" value="{{ old('titol') }}" required autofocus />
                        </div>

                        <!-- Autor -->
                        <div class="mt-4">
                            <label for="autor" class="block font-medium text-sm text-gray-700">Autor</label>
                            <input id="autor" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="autor" value="{{ old('autor') }}" />
                        </div>

                        <!-- Categoria -->
                        <div class="mt-4">
                            <label for="categoria" class="block font-medium text-sm text-gray-700">Categoria</label>
                            <input id="categoria" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="categoria" value="{{ old('categoria') }}" />
                        </div>

                        <!-- Lloc -->
                        <div class="mt-4">
                            <label for="lloc" class="block font-medium text-sm text-gray-700">Lloc</label>
                            <input id="lloc" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="lloc" value="{{ old('lloc') }}" />
                        </div>
                        
                        <!-- Temes -->
                        <div class="mt-4">
                            <label for="temes" class="block font-medium text-sm text-gray-700">Temes</label>
                            <input id="temes" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="temes" value="{{ old('temes') }}" />
                        </div>
                        
                        <!-- Nota -->
                        <div class="mt-4">
                            <label for="nota" class="block font-medium text-sm text-gray-700">Nota</label>
                            <input id="nota" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="nota" value="{{ old('nota') }}" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('llibres.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Cancel·lar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Guardar Llibre
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>