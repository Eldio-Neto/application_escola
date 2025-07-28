<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'cpf' => 'nullable|string|max:14',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'cpf' => $request->cpf,
            'user_type' => 'student', // Por padrão, novos usuários são estudantes
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuário cadastrado com sucesso',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'cpf' => 'nullable|string|max:14',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'phone', 'birth_date', 'cpf']));

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso',
            'data' => [
                'user' => $user->fresh()
            ]
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha atual incorreta'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Senha alterada com sucesso'
        ]);
    }

    public function getAllUsers(Request $request)
    {
        $query = User::with(['enrollments', 'payments']);

        // Filtros
        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount(['enrollments', 'payments'])
                      ->orderBy('created_at', 'desc')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'cpf' => 'nullable|string|max:14',
            'user_type' => 'required|in:student,admin,instructor',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'name', 'email', 'phone', 'birth_date', 'cpf', 'user_type', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso',
            'data' => $user->fresh()
        ]);
    }

    public function deleteUser(Request $request, User $user)
    {
        // Não permitir que o admin delete a si mesmo
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode deletar seu próprio usuário'
            ], 422);
        }

        // Verificar se o usuário tem matrículas ativas
        $activeEnrollments = $user->enrollments()->where('status', 'active')->count();
        if ($activeEnrollments > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível deletar usuário com matrículas ativas'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuário deletado com sucesso'
        ]);
    }
}
