<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard do administrador
     */
    public function adminDashboard(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], 403);
        }

        // Estatísticas gerais
        $totalRevenue = Payment::paid()->sum('amount');
        
        $stats = [
            'totalCourses' => Course::count(),
            'activeCourses' => Course::active()->count(),
            'totalUsers' => User::count(),
            'totalStudents' => User::where('user_type', 'student')->count(),
            'totalEnrollments' => Enrollment::count(),
            'activeEnrollments' => Enrollment::active()->count(),
            'totalRevenue' => (float) $totalRevenue,
            'pendingPayments' => Payment::pending()->count(),
            'paidPayments' => Payment::paid()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Dashboard do estudante
     */
    public function studentDashboard(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], 403);
        }

        // Estatísticas do estudante
        $stats = [
            'total_enrollments' => $user->enrollments()->count(),
            'active_enrollments' => $user->enrollments()->active()->count(),
            'completed_enrollments' => $user->enrollments()->completed()->count(),
            'total_payments' => $user->payments()->sum('amount'),
            'pending_payments' => $user->payments()->pending()->count(),
        ];

        // Cursos ativos
        $activeCourses = $user->enrollments()
            ->active()
            ->with(['course.sessions' => function ($query) {
                $query->upcoming()->orderBy('start_datetime')->limit(3);
            }])
            ->get()
            ->map(function ($enrollment) {
                return [
                    'enrollment_id' => $enrollment->id,
                    'course' => $enrollment->course,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'next_sessions' => $enrollment->course->sessions,
                ];
            });

        // Próximas sessões
        $upcomingSessions = DB::table('course_sessions')
            ->join('courses', 'course_sessions.course_id', '=', 'courses.id')
            ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.user_id', $user->id)
            ->where('enrollments.status', 'active')
            ->where('course_sessions.start_datetime', '>=', now())
            ->select(
                'course_sessions.*',
                'courses.name as course_name',
                'courses.image as course_image'
            )
            ->orderBy('course_sessions.start_datetime')
            ->limit(5)
            ->get();

        // Pagamentos recentes
        $recentPayments = $user->payments()
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'active_courses' => $activeCourses,
                'upcoming_sessions' => $upcomingSessions,
                'recent_payments' => $recentPayments,
            ]
        ]);
    }

    /**
     * Dashboard geral (redireciona baseado no tipo de usuário)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard($request);
        } else {
            return $this->studentDashboard($request);
        }
    }
}
