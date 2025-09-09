<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Http\Response;
use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Tasks Management
 */
class TasksController extends Controller
{
    /**
     * List All Tasks
     *
     * Display a listing of the tasks.
     *
     * @queryParam category_id integer The ID of the category to filter tasks. Example: 1
     * @queryParam due_date date The due date to filter tasks (format: YYYY-MM-DD). Example: 2025-09-10
     * @queryParam due_date_days integer The number of days from today to filter tasks due within that range. Example: 7
     * @queryParam priority string The priority level to filter tasks. Enum: High, Medium, Low. Example: High
     * @queryParam completed string Filter tasks by their completion status. If parameter is present, only completed tasks are returned., Example: 1
     * @queryParam offset integer The number of tasks to skip for pagination. Example: 0
     * @queryParam limit integer The maximum number of tasks to return. Example: 10
     *
     * @response 200 {"success":true,"code":20000,"message":"Tasks retrieved successfully","data":[{"id":1,"content":"Ngopi Ngopi","details":"Di Rumah Nopal","priority":"1","due_date":"2025-09-08","category_id":2,"completed":0,"created_at":"2025-09-08T06:42:18.000000Z","updated_at":"2025-09-08T06:42:18.000000Z"}]}
     * @response 422 {"success":false,"code":42201,"message":"The given data was invalid.","errors":{"category_id":["The category id field must be an integer."]}}
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Validate json input
        $validation = Validator::make($request->all(), [
            'category_id' => 'sometimes|integer',
            'due_date' => 'sometimes|date',
            'due_date_days' => 'sometimes|integer|min:0',
            'priority' => 'sometimes|in:High,Medium,Low',
            'offset' => 'sometimes|integer|min:0',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validation->fails()) {
            return Response::json(key: 'VALIDATION_ERROR', additional_array: ['errors' => $validation->errors()]);
        }

        // get data from json
        $categoryId = $request->category_id ?? 0;
        $dueDate = $request->due_date ?? null;
        $dueDateDays = $request->due_date_days ?? 0;
        $priority = $request->priority ?? null;
        $completed = $request->completed ? true : false;
        $offset = $request->offset ?? 0;
        $limit = $request->limit ?? 3;

        $tasks = Tasks::baseQuery(
            dueDate: $dueDate,
            dueDateDays: $dueDateDays,
            priority: $priority,
            categoryId: (int) $categoryId,
            offset: (int) $offset,
            limit: (int) $limit,
            completed: $completed
        )->get();

        return Response::json(data: TaskResource::collection($tasks), message: 'Tasks retrieved successfully');
    }

    /**
     * Add New Task
     *
     * Add a new task to the database.
     *
     * @bodyParam content string required The content of the task. Example: Beli Koin Micin
     * @bodyParam details string The details of the task. Example: Langsung All In AKOWAKWOAW
     * @bodyParam priority string required The priority level of the task. Enum: High, Medium, Low. Example: High
     * @bodyParam due_date date The due date of the task (format: YYYY-MM-DD). Example: 2025-09-10
     * @bodyParam category_id integer required The ID of the category the task belongs to. Example: 1
     *
     * @response 200 {"success":true,"code":20000,"message":"Task created successfully"}
     * @response 422 {"success":false,"code":42201,"message":"The given data was invalid.","errors":{"content":["The content field must be a string."],"category_id":["The selected category id is invalid."]}}
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'content' => 'required|string|max:255',
            'details' => 'nullable|string|max:2048',
            'priority' => 'required|in:High,Medium,Low',
            'due_date' => 'nullable|date',
            'category_id' => 'nullable|integer',
        ]);

        if ($validation->fails()) {
            return Response::json(key: 'VALIDATION_ERROR', additional_array: ['errors' => $validation->errors()]);
        }

        Tasks::create([
            'content' => $request->content,
            'details' => $request->details,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'category_id' => $request->category_id ?? null,
        ]);

        return Response::json(key: 'SUCCESS_CREATED', message: 'Task created successfully');
    }

    /**
     * Delete Task
     *
     * Delete a task by its ID.
     *
     * @urlParam id integer required The ID of the task. Example: 1
     *
     * @response 200 {"success":true,"code":20000,"message":"Task deleted successfully"}
     * @response 404 {"success":false,"code":40401,"message":"Task not found"}
     *
     * @param int $id The ID of the task to delete.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $task = Tasks::find($id);

        if (!$task) {
            return Response::json(key: 'NOT_FOUND', message: 'Task not found');
        }

        $task->delete();

        return Response::json(message: 'Task deleted successfully');
    }

    /**
     * Update Task Data
     *
     * Update a task by its ID.
     *
     * @urlParam id integer required The ID of the task. Example: 1
     *
     * @bodyParam content string The content of the task. Example: Nambang Nikel
     * @bodyParam details string The details of the task. Example: Di Raja Ampat
     * @bodyParam priority string The priority level of the task. Enum: High, Medium, Low. Example: Medium
     * @bodyParam due_date date The due date of the task (format: YYYY-MM-DD). Example: 2025-09-15
     * @bodyParam category_id integer
     * The ID of the category the task belongs to. Example: 2
     * @bodyParam completed boolean The completion status of the task. Example: true
     *
     * @response 200 {"success":true,"code":20000,"message":"Task updated successfully"}
     * @response 404 {"success":false,"code":40401,"message":"Task not found"}
     * @response 422 {"success":false,"code":42201,"message":"The given data was invalid.","errors":{"content":["The content field must be a string."],"category_id":["The selected category id is invalid."]}}
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The ID of the task to update.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $task = Tasks::find($id);

        if (!$task) {
            return Response::json(key: 'NOT_FOUND', message: 'Task not found');
        }

        $validation = Validator::make($request->all(), [
            'content' => 'sometimes|required|string|max:255',
            'details' => 'sometimes|required|string|max:2048',
            'priority' => 'sometimes|required|in:High,Medium,Low',
            'due_date' => 'sometimes|required|date',
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'completed' => 'sometimes|required|boolean',
        ]);

        if ($validation->fails()) {
            return Response::json(key: 'VALIDATION_ERROR', additional_array: ['errors' => $validation->errors()]);
        }

        $task->update(
            $request->only(['content', 'details', 'priority', 'due_date', 'category_id', 'completed'])
        );

        return Response::json(message: 'Task updated successfully');
    }

    /**
     * Mark Task as Completed
     *
     * Mark a task as completed by its ID.
     *
     * @urlParam id integer required The ID of the task. Example: 1
     *
     * @response 200 {"success":true,"code":20000,"message":"Task marked as completed successfully"}
     * @response 404 {"success":false,"code":40401,"message":"Task not found"}
     *
     * @param int $id The ID of the task to mark as completed.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsCompleted($id)
    {
        $task = Tasks::find($id);

        if (!$task) {
            return Response::json(key: 'NOT_FOUND', message: 'Task not found');
        }

        $task->completed = true;
        $task->save();

        return Response::json(message: 'Task marked as completed successfully');
    }
}
