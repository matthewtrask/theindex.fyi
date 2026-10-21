<?php

use App\Http\Middleware\LogPageView;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts();
        $middleware->append(SecurityHeaders::class);

        $middleware->web(append: [
            LogPageView::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // render API errors as the JSON:API error documents described in docs/openapi.yaml
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') || $request->is('api/docs', 'api/openapi.yaml')) {
                return null;
            }

            $status = match (true) {
                $e instanceof ModelNotFoundException => 404,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => 500,
            };

            if ($status === 500 && config('app.debug')) {
                return null;
            }

            $detail = match ($status) {
                404 => 'The requested resource does not exist.',
                429 => 'Rate limit exceeded. Please slow down.',
                default => Response::$statusTexts[$status] ?? 'Error',
            };

            return response()->json(
                ['errors' => [['status' => (string) $status, 'title' => Response::$statusTexts[$status] ?? 'Error', 'detail' => $detail]]],
                $status,
                $e instanceof HttpExceptionInterface ? $e->getHeaders() : [],
            )->header('Content-Type', 'application/vnd.api+json');
        });
    })->create();
