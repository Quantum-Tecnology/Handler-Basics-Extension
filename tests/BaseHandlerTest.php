<?php

declare(strict_types=1);

namespace QuantumTecnology\HandlerBasicsExtension\Tests;

use Illuminate\Contracts\Debug\ExceptionHandler;
use QuantumTecnology\HandlerBasicsExtension\Exceptions\BaseHandler;

/**
 * Os cenários do ApiExceptionsTest pelo caminho do registro manual
 * (`singleton(ExceptionHandler::class, BaseHandler::class)`).
 *
 * Aqui as exceções chegam CRUAS ao render — sem o prepareException do Laravel
 * — e foi por esse caminho que a negação de policy sem status virava status 0.
 */
final class BaseHandlerTest extends ApiExceptionsTest
{
    protected function resolveApplicationExceptionHandler($app): void
    {
        $app->singleton(ExceptionHandler::class, BaseHandler::class);
    }
}
