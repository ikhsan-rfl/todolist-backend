<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Response;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Category Management
 */
class CategoryController extends Controller
{
    /**
     * List All Categories
     *
     * Show a list of all categories with the count of tasks in each category.
     *
     * @response 200 {"success":true,"code":20000,"message":"Categories retrieved successfully","data":[{"id":1,"name":"Pekerjaan","created_at":"2025-09-08T06:42:18.000000Z","updated_at":"2025-09-08T06:42:18.000000Z","tasks_count":2},{"id":2,"name":"Pribadi","created_at":"2025-09-08T06:42:18.000000Z","updated_at":"2025-09-08T06:42:18.000000Z","tasks_count":3}]}
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::baseQuery()->withCount(['tasks' => function ($query) {
            $query->where('completed', false);
        }])->get();
        return Response::json(data: CategoryResource::collection($categories), message: 'Categories retrieved successfully');
    }

    /**
     * Add New Category
     *
     * Add a new category to the database.
     *
     * @bodyParam name string required The name of the category. Example: Work
     *
     * @response 200 {"success":true,"code":20000,"message":"Category created successfully"}
     * @response 422 {"success":false,"code":42201,"message":"The given data was invalid.","errors":{"name":["The name field is required."]}}
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validation->fails()) {
            return Response::json(key: 'VALIDATION_ERROR', additional_array: ['errors' => $validation->errors()]);
        }

        Category::create([
            'name' => $request->name,
        ]);

        return Response::json(key: 'SUCCESS_CREATED', message: 'Category created successfully');
    }

    /**
     * Delete Category
     *
     * Delete a category by its ID.
     *
     * @urlParam id integer required The ID of the category. Example: 1
     *
     * @response 200 {"success":true,"code":20000,"message":"Category deleted successfully"}
     * @response 404 {"success":false,"code":40401,"message":"Category not found"}
     *
     * @param int $id The ID of the category to delete.
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return Response::json(key: 'NOT_FOUND', message: 'Category not found');
        }

        $category->delete();

        return Response::json(message: 'Category deleted successfully');
    }
}
