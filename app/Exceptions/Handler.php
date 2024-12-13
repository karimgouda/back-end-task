<?php

namespace App\Exceptions;

use App\Http\Traits\apiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    use apiResponse;

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->apiResponse(401, 'Please login first');
    }

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
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->apiResponse(
                    403,
                    'Unauthorized',
                    'You do not have permission to access this resource.'
                );
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return $this->apiResponse(404, 'Resource Not Found');
            }
        });
    }
}
