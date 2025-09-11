<?php

namespace App\Http\Controllers;

use App\Models\Llibre;
use Illuminate\Http\Request;

class LlibreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // <-- CORRECCIÓ APLICADA
    {
        $cerca = $request->input('cerca');

        $llibresQuery = Llibre::query();

        if ($cerca) {
            $llibresQuery->where('titol', 'like', "%{$cerca}%")
                         ->orWhere('autor', 'like', "%{$cerca}%")
                         ->orWhere('temes', 'like', "%{$cerca}%")
                         ->orWhere('categoria', 'like', "%{$cerca}%")
                         ->orWhere('lloc', 'like', "%{$cerca}%")
                         ->orWhere('nota', 'like', "%{$cerca}%");
        }
        
        $llibres = $llibresQuery->orderBy('autor')->get();

        return view('llibres.index', [
            'llibres' => $llibres,
            'cerca' => $cerca,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('llibres.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titol' => 'required|string|max:200',
            'autor' => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:50',
            'lloc' => 'nullable|string|max:50',
            'temes' => 'nullable|string|max:200',
            'nota' => 'nullable|string|max:100',
        ]);

        Llibre::create($request->all());

        return redirect()->route('llibres.index')
                         ->with('success', 'Llibre afegit correctament.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Llibre $llibre)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Llibre $llibre)
    {
        return view('llibres.edit', ['llibre' => $llibre]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Llibre $llibre)
    {
        $request->validate([
            'titol' => 'required|string|max:200',
            'autor' => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:50',
            'lloc' => 'nullable|string|max:50',
            'temes' => 'nullable|string|max:200',
            'nota' => 'nullable|string|max:100',
        ]);

        $llibre->update($request->all());

        return redirect()->route('llibres.index')
                         ->with('success', 'Llibre actualitzat correctament.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Llibre $llibre)
    {
        $llibre->delete();

        return redirect()->route('llibres.index')
                         ->with('success', 'Llibre esborrat correctament.');
    }
}