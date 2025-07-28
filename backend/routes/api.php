<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AsaasPaymentController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\PaymentController as MainPaymentController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas públicas de autenticação
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rotas públicas de cursos (para visitantes)
Route::get('courses', [CourseController::class, 'index']);
Route::get('courses/{course}', [CourseController::class, 'show']);

// Rotas autenticadas
Route::middleware('auth:sanctum')->group(function () {
    // Autenticação
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/admin', [DashboardController::class, 'adminDashboard']);
    Route::get('dashboard/student', [DashboardController::class, 'studentDashboard']);

    // Cursos (operações administrativas)
    Route::post('courses', [CourseController::class, 'store']);
    Route::put('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);
    Route::prefix('courses/{course}')->group(function () {
        Route::post('sessions', [CourseController::class, 'addSession']);
        Route::put('sessions/{session}', [CourseController::class, 'updateSession']);
        Route::delete('sessions/{session}', [CourseController::class, 'removeSession']);
    });

    // Matrículas
    Route::prefix('enrollments')->group(function () {
        Route::get('/', [EnrollmentController::class, 'index']);
        Route::post('/', [EnrollmentController::class, 'store']); // Para se matricular
        Route::get('{enrollment}', [EnrollmentController::class, 'show']);
        Route::post('{enrollment}/complete', [EnrollmentController::class, 'complete']);
        Route::post('{enrollment}/cancel', [EnrollmentController::class, 'cancel']);
        Route::get('statistics', [EnrollmentController::class, 'statistics']);
    });

    // Pagamentos Integrados (Sistema Principal)
    Route::prefix('payment')->group(function () {
        Route::get('config', [MainPaymentController::class, 'getPaymentConfig']);
        Route::post('calculate-installments', [MainPaymentController::class, 'calculateInstallments']);
        Route::post('pix', [MainPaymentController::class, 'processPix']);
        Route::post('credit-card', [MainPaymentController::class, 'processCreditCard']);
        Route::post('boleto', [MainPaymentController::class, 'processBoleto']);
        Route::get('{paymentId}/status', [MainPaymentController::class, 'checkPaymentStatus']);
    });

    // Configurações de Pagamento
    Route::prefix('payment-settings')->group(function () {
        Route::get('/', [PaymentSettingsController::class, 'index']);
        Route::get('{key}', [PaymentSettingsController::class, 'show']);
        Route::post('calculate-installments', [PaymentSettingsController::class, 'simulateInstallments']);
        Route::get('report', [PaymentSettingsController::class, 'getReport']);
        
        // Rotas administrativas
        Route::middleware('admin')->group(function () {
            Route::put('installments', [PaymentSettingsController::class, 'updateInstallmentConfig']);
            Route::put('interest-rates', [PaymentSettingsController::class, 'updateInterestRates']);
            Route::post('reset-default', [PaymentSettingsController::class, 'resetToDefault']);
        });
    });

    // Pagamentos (Sistema Antigo - Manter por compatibilidade)
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']); // Rota genérica para criar pagamento
        Route::get('{payment}', [PaymentController::class, 'show']);
        Route::post('credit-card', [PaymentController::class, 'createCreditCardPayment']);
        Route::post('pix', [PaymentController::class, 'processPix']);
        Route::post('boleto', [PaymentController::class, 'createBoleto']);
        Route::get('test-getnet', [PaymentController::class, 'testGetnetConnection']);
    });

    // Pagamentos Asaas
    Route::prefix('asaas')->group(function () {
        Route::post('pix', [AsaasPaymentController::class, 'processPix']);
        Route::post('credit-card', [AsaasPaymentController::class, 'processCreditCard']);
        Route::post('boleto', [AsaasPaymentController::class, 'processBoleto']);
        Route::get('test-connection', [AsaasPaymentController::class, 'testConnection']);
    });

    // Alunos de um curso específico (apenas admin)
    Route::get('courses/{course}/students', [EnrollmentController::class, 'courseStudents']);
    
    // Rotas administrativas
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Usuários
        Route::get('users', [AuthController::class, 'getAllUsers']);
        Route::post('users', [AuthController::class, 'register']); // Criar usuário admin
        Route::put('users/{user}', [AuthController::class, 'updateUser']);
        Route::delete('users/{user}', [AuthController::class, 'deleteUser']);
        
        // Pagamentos
        Route::get('payments', [PaymentController::class, 'getAllPayments']);
        Route::put('payments/{payment}/status', [PaymentController::class, 'updatePaymentStatus']);
        
        // Cursos
        Route::get('courses', [CourseController::class, 'index']);
        Route::post('courses', [CourseController::class, 'store']);
        Route::put('courses/{course}', [CourseController::class, 'update']);
        Route::delete('courses/{course}', [CourseController::class, 'destroy']);
        Route::get('courses/{course}/students', [CourseController::class, 'getCourseStudents']);
        
        // Matrículas
        Route::get('enrollments', [EnrollmentController::class, 'index']);
        Route::put('enrollments/{enrollment}/status', [EnrollmentController::class, 'updateStatus']);
        
        // Estatísticas
        Route::get('statistics', [DashboardController::class, 'adminDashboard']);
    });
});

// Webhooks públicos (sem autenticação)
Route::prefix('webhook')->group(function () {
    Route::post('getnet', [WebhookController::class, 'getnetWebhook']);
    Route::post('asaas', [WebhookController::class, 'asaasWebhook']);
    Route::post('test-webhook', [WebhookController::class, 'testWebhook']); // Apenas desenvolvimento
});

// Webhook da Getnet (público) - Compatibilidade
Route::post('webhook/getnet', [WebhookController::class, 'getnetWebhook']);

// Rota para teste
Route::get('test', function () {
    return response()->json([
        'message' => 'API funcionando!',
        'timestamp' => now()->toISOString()
    ]);
});
