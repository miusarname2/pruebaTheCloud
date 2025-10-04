<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_done' => 'sometimes|boolean',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|string|max:50',
            'creator_id' => 'sometimes|exists:users,id',
            'assigned_to_id' => 'sometimes|exists:users,id',
            'assignees' => 'sometimes|array',
            'assignees.*' => 'exists:users,id',
            'keywords' => 'sometimes|array',
            'keywords.*' => 'exists:keywords,id',
        ]);

        if ($request->has('is_done')) {
            $validated['is_done'] = filter_var($request->input('is_done'), FILTER_VALIDATE_BOOLEAN);
        }

        // Si no se envía creator_id, usar el usuario autenticado si existe
        if (!isset($validated['creator_id']) && $request->user()) {
            $validated['creator_id'] = $request->user()->id;
        }

        DB::beginTransaction();
        try {
            $task = Task::create($validated);

            // Sync many-to-many relations si vienen
            if (!empty($validated['assignees'])) {
                $task->assignees()->sync($validated['assignees']);
            }

            if (!empty($validated['keywords'])) {
                $task->keywords()->sync($validated['keywords']);
            }

            DB::commit();

            // Cargar relaciones para la respuesta
            $task->load('creator', 'assignees', 'keywords');

            return response()->json(['data' => $task], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::findOrFail($id);
        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_done' => 'sometimes|boolean',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|string|max:50',
            'creator_id' => 'sometimes|exists:users,id',
            'assigned_to_id' => 'sometimes|exists:users,id',
            'assignees' => 'sometimes|array',
            'assignees.*' => 'exists:users,id',
            'keywords' => 'sometimes|array',
            'keywords.*' => 'exists:keywords,id',
        ]);

        if ($request->has('is_done')) {
            $validated['is_done'] = filter_var($request->input('is_done'), FILTER_VALIDATE_BOOLEAN);
        }

        DB::beginTransaction();
        try {
            $task->update($validated);

            // Actualizar relaciones many-to-many (si vienen)
            if ($request->has('assignees')) {
                // si envían array vacío, esto lo desasigna => sync([])
                $task->assignees()->sync($validated['assignees'] ?? []);
            }

            if ($request->has('keywords')) {
                $task->keywords()->sync($validated['keywords'] ?? []);
            }

            DB::commit();

            $task->load('creator', 'assignees', 'keywords');

            return response()->json(['data' => $task], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $taskToDelete = Task::findOrFail($id);
        $taskToDelete->delete();
        return response()->json(['message' => 'Task deleted successfully.'], 200);
    }
}
