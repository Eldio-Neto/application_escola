<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Listar matrículas do usuário ou todas (admin)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Enrollment::with(['course.sessions', 'payment']);
        
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        } else {
            $query->with('user');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $enrollments = $query->orderBy('enrolled_at', 'desc')
                           ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $enrollments
        ]);
    }

    /**
     * Mostrar detalhes de uma matrícula
     */
    public function show(Enrollment $enrollment)
    {
        $user = request()->user();

        // Verificar se o usuário pode ver esta matrícula
        if (!$user->isAdmin() && $enrollment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], 403);
        }

        $enrollment->load([
            'course.sessions' => function ($query) {
                $query->orderBy('start_datetime');
            },
            'payment',
            'user'
        ]);

        return response()->json([
            'success' => true,
            'data' => $enrollment
        ]);
    }

    /**
     * Marcar matrícula como concluída (apenas admin)
     */
    public function complete(Enrollment $enrollment)
    {
        $user = request()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], 403);
        }

        if ($enrollment->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Matrícula já foi concluída'
            ], 422);
        }

        $enrollment->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Matrícula marcada como concluída',
            'data' => $enrollment->fresh()
        ]);
    }

    /**
     * Cancelar matrícula (apenas admin)
     */
    public function cancel(Enrollment $enrollment)
    {
        $user = request()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], 403);
        }

        if ($enrollment->isCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Matrícula já foi cancelada'
            ], 422);
        }

        $enrollment->markAsCancelled();

        return response()->json([
            'success' => true,
            'message' => 'Matrícula cancelada com sucesso',
            'data' => $enrollment->fresh()
        ]);
    }

    /**
     * Listar alunos de um curso específico (apenas admin)
     */
    public function courseStudents(Course $course, Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], 403);
        }

        $query = $course->enrollments()->with(['user', 'payment']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderBy('enrolled_at', 'desc')
                           ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $course,
                'enrollments' => $enrollments
            ]
        ]);
    }

    /**
     * Estatísticas das matrículas (apenas admin)
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], 403);
        }

        $stats = [
            'total_enrollments' => Enrollment::count(),
            'active_enrollments' => Enrollment::active()->count(),
            'completed_enrollments' => Enrollment::completed()->count(),
            'cancelled_enrollments' => Enrollment::cancelled()->count(),
            'enrollments_by_course' => Enrollment::with('course')
                ->selectRaw('course_id, count(*) as total')
                ->groupBy('course_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'course_name' => $item->course->name,
                        'total_enrollments' => $item->total
                    ];
                }),
            'recent_enrollments' => Enrollment::with(['user', 'course'])
                ->orderBy('enrolled_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
