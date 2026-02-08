<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Api\ResponseApi;
use App\Models\User;
class UserController extends Controller
{
    use ResponseApi;
      public function getUser()
      {

          // Aqui você pode implementar a lógica para obter os dados do usuário
          // Por exemplo, usando o modelo User para buscar o usuário autenticado
          $user = User::all(); 

          if ($user) {
              return response()->json([
                  'status' => 'success',
                  'data' => $user,
              ]);
          } else {
              return response()->json([
                  'status' => 'error',
                  'message' => 'Usuário não autenticado',
              ], 401);
          }
      }
}
