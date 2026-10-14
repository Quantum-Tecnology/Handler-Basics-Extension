<?php

namespace QuantumTecnology\HandlerBasicsExtension\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use QuantumTecnology\HandlerBasicsExtension\Traits\ApiResponseTrait;

/**
 * Handler para registro manual (Laravel ≤ 10, ou quem faz
 * `singleton(ExceptionHandler::class, BaseHandler::class)`).
 *
 * No Laravel 11+ prefira `ApiExceptions::configure($exceptions)` no
 * `withExceptions` do bootstrap/app.php: é a mesma regra, sem trocar o handler
 * do framework.
 */
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
        // Mesmo código de referência da resposta, no contexto do log/Telescope.
        $this->buildContextUsing(
            static fn (\Throwable $e): array => ['reference' => ErrorReference::for($e)]
        );

        // Sentry é opcional (suggest): só reporta se o projeto o tiver instalado.
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
        return ApiExceptions::render($e, $request) ?? parent::render($request, $e);
    }
}
