<?php

declare(strict_types=1);

namespace QuantumTecnology\HandlerBasicsExtension\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use QuantumTecnology\HandlerBasicsExtension\Traits\ApiResponseTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Tradução de exceção em resposta da API, no envelope do ApiResponseTrait.
 *
 * Laravel 11+ (bootstrap/app.php):
 *
 *     ->withExceptions(function (Exceptions $exceptions): void {
 *         ApiExceptions::configure($exceptions);
 *     })
 *
 * Com APP_DEBUG=false, o erro que não é de nenhum tipo conhecido (bug: TypeError,
 * null onde não devia...) responde 500 com mensagem genérica — o usuário não vê
 * estrutura interna — e um CÓDIGO DE REFERÊNCIA, que também vai para o contexto
 * do log (e, portanto, do Telescope). Com o código, o suporte acha o erro exato.
 *
 * Não é 503: 503 diz "indisponível, tente de novo", e um bug falha igual em toda
 * tentativa. Indisponibilidade real (php artisan down, abort(503)) chega como
 * HttpException e mantém o status dela.
 *
 * Com APP_DEBUG=true, devolve null: o Laravel renderiza o erro completo.
 */
final class ApiExceptions
{
    use ApiResponseTrait;

    public static function configure(Exceptions $exceptions): void
    {
        $exceptions->dontReport(ApiResponseException::class);

        $exceptions->context(
            static fn (Throwable $e): array => ['reference' => ErrorReference::for($e)]
        );

        $exceptions->render(
            static fn (Throwable $e, Request $request): ?JsonResponse => self::render($e, $request)
        );
    }

    /**
     * Resposta JSON para a exceção, ou null para deixar o Laravel renderizar.
     */
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        $api = new self();

        // No withExceptions o Laravel já converteu ModelNotFoundException em
        // NotFoundHttpException (prepareException) — a original vem em previous.
        if ($e instanceof NotFoundHttpException && $e->getPrevious() instanceof ModelNotFoundException) {
            $e = $e->getPrevious();
        }

        return match (true) {
            $e instanceof ApiResponseException    => response()->json($e->getApiResponse(), $e->getCode()),
            $e instanceof ValidationException     => $api->customResponse(status: Response::HTTP_UNPROCESSABLE_ENTITY, message: 'Erro de validação!', data: $e->errors()),
            $e instanceof ModelNotFoundException  => $api->customResponse(status: Response::HTTP_NOT_FOUND, message: 'Sem resultados para a sua pesquisa!'),
            $e instanceof NotFoundHttpException   => $api->customResponse(status: Response::HTTP_NOT_FOUND, message: $e->getMessage()),
            $e instanceof AuthorizationException  => $api->customResponse(status: self::authorizationStatus($e), message: $e->getMessage()),
            $e instanceof AuthenticationException => $api->customResponse(status: Response::HTTP_UNAUTHORIZED, message: $e->getMessage()),
            $e instanceof HttpException           => $api->customResponse(status: $e->getStatusCode(), message: $e->getMessage()),
            !config('app.debug')                  => $api->unexpectedError($e),
            default                               => null,
        };
    }

    private function unexpectedError(Throwable $e): JsonResponse
    {
        $reference = ErrorReference::for($e);

        return $this->customResponse(
            message: __('Ocorreu um erro inesperado. Se persistir, informe o código :reference ao suporte.', ['reference' => $reference]),
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            arrayToAppend: ['reference' => $reference],
        );
    }

    /**
     * Status do AuthorizationException: o definido na policy (Response::denyWithStatus
     * ou ->withStatus) ou, sem ele, 403. O getCode() da exceção é o "código" de
     * negócio do Response::deny(), não um status HTTP — vale 0 quando ausente, e
     * 0 não é status válido (o JsonResponse lança e a negação vira 500).
     */
    private static function authorizationStatus(AuthorizationException $e): int
    {
        $status = $e->status();

        if (null !== $status) {
            return $status;
        }

        $code = (int) $e->getCode();

        return $code >= 400 && $code < 600 ? $code : Response::HTTP_FORBIDDEN;
    }
}
