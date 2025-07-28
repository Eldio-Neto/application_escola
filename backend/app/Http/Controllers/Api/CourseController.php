<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Course::with(['sessions' => function ($query) {
            $query->upcoming()->orderBy('start_datetime');
        }]);

        if ($request->has('active')) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'workload_hours' => 'nullable|integer|min:1',
            'modules' => 'nullable|array',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.order' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
            'sessions' => 'array',
            'sessions.*.title' => 'required|string|max:255',
            'sessions.*.description' => 'nullable|string',
            'sessions.*.start_datetime' => 'required|date',
            'sessions.*.end_datetime' => 'required|date|after:sessions.*.start_datetime',
            'sessions.*.max_participants' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'description', 'price', 'workload_hours', 'modules', 'active']);

        // Upload da imagem
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'courses/' . $filename;

            // Salvar a imagem na pasta public
            Storage::disk('public')->putFileAs('courses', $image, $filename);
            $data['image'] = $path;
        }

        $course = Course::create($data);

        // Criar sessões
        if ($request->has('sessions')) {
            foreach ($request->sessions as $sessionData) {
                $course->sessions()->create($sessionData);
            }
        }

        $course->load('sessions');

        return response()->json([
            'success' => true,
            'message' => 'Curso criado com sucesso',
            'data' => $course
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $course->load(['sessions' => function ($query) {
            $query->orderBy('start_datetime');
        }]);

        return response()->json([
            'success' => true,
            'data' => $course
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'workload_hours' => 'nullable|integer|min:1',
            'modules' => 'nullable|array',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.order' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'description', 'price', 'workload_hours', 'modules', 'active']);

        // Upload da imagem
        if ($request->hasFile('image')) {
            // Deletar imagem anterior
            if ($course->image) {
                Storage::disk('public')->delete($course->image);
            }

            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'courses/' . $filename;

            // Salvar a imagem na pasta public
            Storage::disk('public')->putFileAs('courses', $image, $filename);
            $data['image'] = $path;
        }

        $course->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Curso atualizado com sucesso',
            'data' => $course->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        // Verificar se há matrículas ativas
        if ($course->enrollments()->where('status', 'active')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir um curso com matrículas ativas'
            ], 422);
        }

        // Deletar imagem
        if ($course->image) {
            Storage::delete($course->image);
        }

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Curso excluído com sucesso'
        ]);
    }

    /**
     * Adicionar sessão ao curso
     */
    public function addSession(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'max_participants' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $session = $course->sessions()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Sessão adicionada com sucesso',
            'data' => $session
        ], 201);
    }

    /**
     * Atualizar sessão do curso
     */
    public function updateSession(Request $request, Course $course, CourseSession $session)
    {
        if ($session->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão não pertence a este curso'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'max_participants' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $session->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Sessão atualizada com sucesso',
            'data' => $session->fresh()
        ]);
    }

    /**
     * Remover sessão do curso
     */
    public function removeSession(Course $course, CourseSession $session)
    {
        if ($session->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão não pertence a este curso'
            ], 404);
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sessão removida com sucesso'
        ]);
    }

    /**
     * Obter estudantes de um curso (admin)
     */
    public function getCourseStudents(Course $course)
    {
        $students = $course->enrollments()
            ->with(['user', 'payment'])
            ->get()
            ->map(function ($enrollment) {
                return [
                    'enrollment_id' => $enrollment->id,
                    'user' => $enrollment->user,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'status' => $enrollment->status,
                    'payment' => $enrollment->payment
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $course,
                'students' => $students,
                'total_students' => $students->count()
            ]
        ]);
    }
}
