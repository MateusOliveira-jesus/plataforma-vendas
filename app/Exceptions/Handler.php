<?php

namespace App\Exceptions;

use App\Traits\Api\ResponseApi;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    use ResponseApi;
    
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->errorResponse(
                    message: 'Não autenticado. Faça login primeiro.',
                    code: 401
                );
            }
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $allowedMethods = $e->getHeaders()['Allow'] ?? '';
                $methods = $allowedMethods ? explode(', ', $allowedMethods) : [];
                
                $response = $this->errorResponse(
                    message: 'Método HTTP não permitido.',
                    data: [
                        'allowed_methods' => $methods,
                        'received_method' => $request->method()
                    ],
                    code: 405
                );
                
                return $response->header('Allow', $allowedMethods);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->errorResponse(
                    message: 'Rota não encontrada.',
                    code: 404
                );
            }
        });

        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->errorResponse(
                    message: 'Erro de validação.',
                    data: ['errors' => $e->errors()],
                    code: 422
                );
            }
        });

        // Capturar todos os outros erros para API
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $statusCode = method_exists($e, 'getStatusCode') 
                    ? $e->getStatusCode() 
                    : 500;
                
                $data = [];
                if (config('app.debug')) {
                    $data = [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ];
                }
                
                return $this->errorResponse(
                    message: config('app.debug') 
                        ? $e->getMessage() 
                        : 'Erro interno do servidor.',
                    data: $data,
                    code: $statusCode
                );
            }
        });
    }
}