<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('active')) {
            $query->active();
        }

        if ($request->has('with_courses')) {
            $query->with(['courses' => function($q) {
                $q->active()->with(['sessions' => function($session) {
                    $session->upcoming()->orderBy('start_datetime');
                }]);
            }]);
        }

        $categories = $query->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:100',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'description', 'color', 'icon', 'active']);
        $data['slug'] = Str::slug($data['name']);

        // Check if slug already exists
        $counter = 1;
        $originalSlug = $data['slug'];
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoria criada com sucesso',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['courses' => function($query) {
            $query->active()->with(['sessions' => function($session) {
                $session->upcoming()->orderBy('start_datetime');
            }]);
        }]);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:100',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'description', 'color', 'icon', 'active']);
        
        // Update slug if name changed
        if ($data['name'] !== $category->name) {
            $data['slug'] = Str::slug($data['name']);
            
            // Check if slug already exists
            $counter = 1;
            $originalSlug = $data['slug'];
            while (Category::where('slug', $data['slug'])->where('id', '!=', $category->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoria atualizada com sucesso',
            'data' => $category->fresh()
        ]);
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has courses
        if ($category->courses()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir uma categoria que possui cursos'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoria excluída com sucesso'
        ]);
    }

    /**
     * Get courses by category
     */
    public function courses(Category $category, Request $request)
    {
        $query = $category->courses()->active();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->with(['sessions' => function ($query) {
            $query->upcoming()->orderBy('start_datetime');
        }])->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }
}
