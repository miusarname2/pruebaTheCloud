<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Keywords;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class KeywordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $keywords = Keywords::all();
        return response()->json($keywords);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:keywords,name',
        ]);

        try {
            $keyword = Keywords::create(['name' => $validated['name']]);
            return response()->json(['data' => $keyword], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error creando la keyword',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kyword = Keywords::findOrFail($id);
        return response()->json($kyword);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $keyword = Keywords::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:keywords,name,' . $keyword->id,
        ]);

        try {
            $keyword->update(['name' => $validated['name']]);
            return response()->json(['data' => $keyword], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error actualizando la keyword',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $keywordToDelete = Keywords::findOrFail($id);
        $keywordToDelete->delete();
        return response()->json(['message' => 'Keyword deleted successfully.'], 200);
    }

    /**
     * Asociar keywords a una tarea.
     *
     * Soporta:
     * - Enviar "keyword_ids": [1,2,3] -> asocia keywords existentes.
     * - Enviar "names": ["foo","bar"] -> creará (si no existen) y asociará.
     * - Si envías "sync" = true -> reemplaza las asociaciones (sync), si no -> attach.
     */
    public function attachToTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);

        $validated = $request->validate([
            'keyword_ids' => 'sometimes|array',
            'keyword_ids.*' => 'integer|exists:keywords,id',
            'names' => 'sometimes|array',
            'names.*' => 'string|max:255',
            'sync' => 'sometimes|boolean'
        ]);

        DB::beginTransaction();
        try {
            $idsToAttach = [];

            // ids directos
            if (!empty($validated['keyword_ids'])) {
                $idsToAttach = array_merge($idsToAttach, $validated['keyword_ids']);
            }

            // names: crear si no existen y obtener ids
            if (!empty($validated['names'])) {
                foreach ($validated['names'] as $name) {
                    $k = Keywords::firstOrCreate(['name' => $name]);
                    $idsToAttach[] = $k->id;
                }
            }

            // quitar duplicados
            $idsToAttach = array_values(array_unique($idsToAttach));

            if (!empty($idsToAttach)) {
                if (!empty($validated['sync']) && $validated['sync']) {
                    $task->keywords()->sync($idsToAttach);
                } else {
                    // attach preservando existentes
                    $task->keywords()->syncWithoutDetaching($idsToAttach);
                }
            }

            DB::commit();

            $task->load('keywords');
            return response()->json(['data' => $task->keywords], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error asociando keywords a la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Desasociar una keyword específica de una tarea
    public function detachFromTask($taskId, $keywordId)
    {
        $task = Task::findOrFail($taskId);
        $keyword = Keywords::findOrFail($keywordId);

        try {
            $task->keywords()->detach($keyword->id);
            return response()->json(['message' => 'Keyword detached from task'], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error desasociando keyword',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Reemplazar (sync) las keywords de una tarea usando keyword_ids y/o names (comportamiento similar a attachToTask con sync=true)
    public function syncForTask(Request $request, $taskId)
    {
        $request->merge(['sync' => true]);
        return $this->attachToTask($request, $taskId);
    }
}
