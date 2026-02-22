<?php

use App\Exceptions\Handler;
use App\Http\Middleware\Api\CheckHttpMethod;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.method' => CheckHttpMethod::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
          $exceptions->render(function (Throwable $e, Request $request) {
            $handler = new Handler(app());
            return $handler->render($request, $e);
        });
        //impede redirecionamento para login
          $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Não autenticado.',
                    'data' => []
                ], 401);
            }
        });
        // Usar o Handler personalizado
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $allowedMethods = $e->getHeaders()['Allow'] ?? '';
                $methods = $allowedMethods ? explode(', ', $allowedMethods) : [];
                
                // Criar uma instância do Handler para usar a trait
                $handler = app(Handler::class);
                
                return $handler->errorResponse(
                    message: 'Método HTTP não permitido para esta rota.',
                    data: [
                        'allowed_methods' => $methods,
                        'received_method' => $request->method()
                    ],
                    code: 405
                )->header('Allow', $allowedMethods);
            }
        });
        
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $handler = app(Handler::class);
                
                return $handler->errorResponse(
                    message: 'Rota não encontrada.',
                    code: 404
                );
            }
        });
        
    })->create();