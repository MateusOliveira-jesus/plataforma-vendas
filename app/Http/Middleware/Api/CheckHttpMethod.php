<?php

namespace App\Http\Middleware\Api;

use App\Traits\Api\ResponseApi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHttpMethod
{
    use ResponseApi;
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$allowedMethods): Response
    {
        if (!in_array($request->method(), $allowedMethods)) {
            $response = $this->errorResponse(
                message: 'Método HTTP não permitido para esta rota.',
                data: [
                    'allowed_methods' => $allowedMethods,
                    'received_method' => $request->method()
                ],
                code: 405
            );
            
            // Adicionar cabeçalho Allow conforme especificação HTTP
            return $response->header('Allow', implode(', ', $allowedMethods));
        }

        return $next($request);
    }
}