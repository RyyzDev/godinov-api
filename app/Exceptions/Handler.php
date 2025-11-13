<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */

// ... di dalam class Handler ...

public function register(): void
{
    $this->renderable(function (ModelNotFoundException $e, $request) {
        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data yang Anda cari tidak ditemukan.',
                'error_type' => 'ModelNotFound'
            ], Response::HTTP_NOT_FOUND);
        }
    });
}
}