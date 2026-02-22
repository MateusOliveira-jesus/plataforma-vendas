<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\Api\ResponseApi;
use App\Models\User;

class UserController extends Controller
{
    use ResponseApi;
    public function getUsers()
    {

        // Aqui você pode implementar a lógica para obter os dados do usuário
        // Por exemplo, usando o modelo User para buscar o usuário autenticado
        $user = User::all();
        return $user
            ? $this->successResponse(data: ['countUsers' => $user->count(), 'users' => $user->toArray()], message: 'Usuários encontrados com sucesso!')
            : $this->errorResponse(message: 'Usuário não encontrado', code: 404);
    }
}
