<?php

namespace QuantumTecnology\HandlerBasicsExtension\Exceptions;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use QuantumTecnology\HandlerBasicsExtension\Traits\ApiResponseTrait;

class BaseHandler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        ApiResponseException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
    public function register()
    {
        if (!config('app.debug')) {
            $this->reportable(function (\Throwable $e) {
                if (app()->bound('sentry')) {
                    app('sentry')->captureException($e);
                }
            });
        }
    }

    protected function renderExceptionContent(\Throwable $e)
    {
        return $this->renderExceptionWithSymfony($e, config('app.debug'));
    }

    public function render($request, \Throwable $e)
    {
        $callback = match (true) {
            $e instanceof ApiResponseException   => response()->json($e->getApiResponse(), $e->getCode()),
            $e instanceof ValidationException    => $this->customResponse(status: Response::HTTP_UNPROCESSABLE_ENTITY, message: 'Erro de validação!', data: $e->errors()),
            $e instanceof ModelNotFoundException => $this->customResponse(status: Response::HTTP_NOT_FOUND, message: 'Sem resultados para a sua pesquisa!'),
            $e instanceof NotFoundHttpException  => $this->customResponse(status: Response::HTTP_NOT_FOUND, message: $e->getMessage()),
            $e instanceof AuthorizationException => $this->customResponse(status: Response::HTTP_FORBIDDEN, message: $e->getMessage()),
            $e instanceof HttpException          => $this->customResponse(status: $e->getStatusCode(), message: $e->getMessage()),
            $e instanceof QueryException         => $this->customResponse(status: Response::HTTP_BAD_REQUEST, message: $e->getMessage()),
            !config('app.debug')                 => $this->customResponse(status: Response::HTTP_SERVICE_UNAVAILABLE, message: 'A API está temporariamente em manutenção, tente novamente mais tarde!'),
            default                              => false,
        };

        if ($callback instanceof JsonResponse) {
            return $callback;
        }

        return parent::render($request, $e);
    }
}
