<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\Api\ResponseApi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    use ResponseApi;

    /**
     * Registro de usuário
     */
    public function register(Request $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'cpf_cnpj' => 'nullable|string|max:20|unique:users',
            'birth_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Erro de validação',
                code: 422,
                data: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $userData = $validator->validated();
            $userData['password'] = Hash::make($userData['password']);
            
            $user = User::create($userData);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse(
                data: [
                    'user' => $user->only(['id', 'name', 'email', 'phone', 'cpf_cnpj', 'birth_date', 'avatar_url', 'created_at']),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
                message: 'Usuário registrado com sucesso!',
                code: 201
            );
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao registrar usuário: ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse(
                message: 'Erro ao registrar usuário',
                code: 500,
                data: ['error' => "Erro ao registrar usuário: "]
            );
        }
    }

    /**
     * Login do usuário
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Erro de validação',
                code: 422,
                data: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse(
                    message: 'Credenciais inválidas',
                    code: 401
                );
            }

            if (!$user->is_active) {
                return $this->errorResponse(
                    message: 'Conta desativada. Entre em contato com o administrador.',
                    code: 403
                );
            }

            $deviceName = $request->device_name ?? $request->header('User-Agent', 'Unknown Device');
            $token = $user->createToken($deviceName)->plainTextToken;

            // Registrar último login
            $user->updateLastLogin($request->ip());

            return $this->successResponse(
                data: [
                    'user' => $user->only(['id', 'name', 'email', 'is_admin', 'is_active', 'phone', 'avatar_url', 'last_login_at']),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('sanctum.expiration') ? config('sanctum.expiration') * 60 : null,
                ],
                message: 'Login realizado com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao realizar login',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Logout do usuário
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(
                message: 'Logout realizado com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao realizar logout',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Logout de todos os dispositivos
     */
    public function logoutAll(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return $this->successResponse(
                message: 'Logout de todos os dispositivos realizado com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao realizar logout',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Obter usuário autenticado
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            
            return $this->successResponse(
                data: [
                    'user' => $user->only([
                        'id', 'name', 'email', 'email_verified_at', 'is_admin', 'is_active',
                        'phone', 'avatar_url', 'cpf_cnpj', 'birth_date', 'gender',
                        'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'zip_code',
                        'last_login_at', 'last_login_ip', 'created_at', 'updated_at'
                    ])
                ],
                message: 'Dados do usuário obtidos com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao obter dados do usuário',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Atualizar perfil do usuário
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'cpf_cnpj' => 'nullable|string|max:20|unique:users,cpf_cnpj,' . $user->id,
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Erro de validação',
                code: 422,
                data: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $user->update($validator->validated());

            return $this->successResponse(
                data: [
                    'user' => $user->only(['id', 'name', 'email', 'phone', 'avatar_url', 'updated_at'])
                ],
                message: 'Perfil atualizado com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao atualizar perfil',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Alterar senha
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Erro de validação',
                code: 422,
                data: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $user = $request->user();

            // Verificar senha atual
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse(
                    message: 'Senha atual incorreta',
                    code: 422
                );
            }

            // Atualizar senha
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Revogar todos os tokens (opcional - força novo login em todos os dispositivos)
            // $user->tokens()->delete();

            return $this->successResponse(
                message: 'Senha alterada com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao alterar senha',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Esqueci minha senha - solicitar reset
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Erro de validação',
                code: 422,
                data: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse(
                    message: 'Link de redefinição de senha enviado para seu email!'
                );
            }

            return $this->errorResponse(
                message: 'Não foi possível enviar o link de redefinição.',
                code: 400,
                data: ['status' => $status]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao processar solicitação',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Resetar senha
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Erro de validação',
                code: 422,
                data: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(
                    message: 'Senha redefinida com sucesso!'
                );
            }

            return $this->errorResponse(
                message: 'Não foi possível redefinir a senha.',
                code: 400,
                data: ['status' => $status]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao redefinir senha',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Listar tokens do usuário (dispositivos)
     */
    public function listTokens(Request $request)
    {
        try {
            $tokens = $request->user()->tokens()
                ->select(['id', 'name', 'last_used_at', 'created_at', 'expires_at'])
                ->get();

            return $this->successResponse(
                data: ['tokens' => $tokens],
                message: 'Tokens listados com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao listar tokens',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Revogar token específico
     */
    public function revokeToken(Request $request, $tokenId)
    {
        try {
            $token = $request->user()->tokens()->where('id', $tokenId)->first();

            if (!$token) {
                return $this->errorResponse(
                    message: 'Token não encontrado',
                    code: 404
                );
            }

            $token->delete();

            return $this->successResponse(
                message: 'Token revogado com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao revogar token',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Upload de avatar
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Erro de validação',
                code: 422,
                data: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $user = $request->user();
            
            // Excluir avatar antigo se existir
            if ($user->avatar && \Storage::exists('public/avatars/' . $user->avatar)) {
                \Storage::delete('public/avatars/' . $user->avatar);
            }

            // Upload do novo avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $filename = basename($path);

            $user->avatar = $filename;
            $user->save();

            return $this->successResponse(
                data: [
                    'user' => $user->only(['id', 'name', 'email', 'avatar_url'])
                ],
                message: 'Avatar atualizado com sucesso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Erro ao fazer upload do avatar',
                code: 500,
                data: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Verificar se token é válido
     */
    public function checkToken(Request $request)
    {
        try {
            $user = $request->user();
            
            return $this->successResponse(
                data: [
                    'valid' => true,
                    'user' => $user->only(['id', 'name', 'email', 'is_admin'])
                ],
                message: 'Token válido'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Token inválido ou expirado',
                code: 401
            );
        }
    }
}