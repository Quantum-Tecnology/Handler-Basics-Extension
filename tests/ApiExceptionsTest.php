<?php

declare(strict_types=1);

namespace QuantumTecnology\HandlerBasicsExtension\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use QuantumTecnology\HandlerBasicsExtension\Exceptions\ApiExceptions;
use QuantumTecnology\HandlerBasicsExtension\Exceptions\ApiResponseException;
use QuantumTecnology\HandlerBasicsExtension\Exceptions\ErrorReference;
use RuntimeException;
use TypeError;

/**
 * A regra de tradução exceção → resposta, ligada pelo caminho do Laravel 11+
 * (`ApiExceptions::configure` no withExceptions). O BaseHandlerTest roda os
 * MESMOS cenários pelo caminho do registro manual do BaseHandler.
 */
class ApiExceptionsTest extends TestCase
{
    protected function resolveApplicationExceptionHandler($app): void
    {
        parent::resolveApplicationExceptionHandler($app);

        // Equivalente a ->withExceptions(fn ($e) => ApiExceptions::configure($e)).
        ApiExceptions::configure(new \Illuminate\Foundation\Configuration\Exceptions(
            $app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)
        ));
    }

    protected function defineRoutes($router): void
    {
        Route::get('/type-error', static fn () => throw new TypeError('Argument #1 must be of type int, null given'));
        Route::get('/runtime', static fn () => throw new RuntimeException('SQLSTATE[42S22]: coluna secreta'));
        Route::get('/abort/{status}', static fn (int $status) => abort($status, 'Mensagem do abort'));
        Route::get('/validation', static fn () => throw ValidationException::withMessages(['cnpj' => ['CNPJ inválido.']]));
        Route::get('/model', static fn () => throw new ModelNotFoundException());
        Route::get('/auth', static fn () => throw new AuthenticationException('Unauthenticated.'));
        Route::get('/api-response', static fn () => throw new ApiResponseException(409, ['success' => false, 'message' => 'Conflito']));
        Route::get('/deny', static fn () => GateResponse::deny('Sem permissão')->authorize());
        Route::get('/deny-status', static fn () => GateResponse::denyWithStatus(404, 'Escondido')->authorize());
        Route::get('/deny-code', static fn () => throw new AuthorizationException('Negado', 'POLICY_X'));
    }

    /*
    |--------------------------------------------------------------------------
    | Erro inesperado (bug)
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function erroInesperadoEm500SemExporEstruturaInterna(): void
    {
        config(['app.debug' => false]);

        $response = $this->getJson('/runtime');

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'reference']);

        $body = $response->getContent();

        // Nada de estrutura interna para o usuário.
        $this->assertStringNotContainsString('SQLSTATE', $body);
        $this->assertStringNotContainsString('RuntimeException', $body);
        $this->assertStringNotContainsString('trace', $body);
    }

    #[Test]
    public function naoDizMaisQueAApiEstaEmManutencao(): void
    {
        config(['app.debug' => false]);

        $response = $this->getJson('/type-error');

        // Um bug falha igual em toda tentativa: 503 "tente mais tarde" mentia.
        $response->assertStatus(500);
        $this->assertStringNotContainsString('manutenção', $response->json('message'));
    }

    #[Test]
    public function aMensagemTrazOCodigoDeReferencia(): void
    {
        config(['app.debug' => false]);

        $response  = $this->getJson('/type-error');
        $reference = $response->json('reference');

        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{10}$/', $reference);
        $this->assertStringContainsString($reference, $response->json('message'));
    }

    #[Test]
    public function oMesmoCodigoVaiParaOContextoDoLog(): void
    {
        config(['app.debug' => false]);

        $logged = [];
        Event::listen(MessageLogged::class, static function (MessageLogged $e) use (&$logged): void {
            $logged[] = $e->context;
        });

        $reference = $this->getJson('/type-error')->json('reference');

        $this->assertNotEmpty($logged, 'A exceção deveria ter sido reportada.');
        $this->assertSame($reference, $logged[0]['reference'] ?? null, 'Log e resposta precisam do mesmo código.');
    }

    #[Test]
    public function cadaErroGanhaUmCodigoProprio(): void
    {
        config(['app.debug' => false]);

        $this->assertNotSame(
            $this->getJson('/type-error')->json('reference'),
            $this->getJson('/type-error')->json('reference'),
        );
    }

    #[Test]
    public function comDebugLigadoMostraOErroCompleto(): void
    {
        config(['app.debug' => true]);

        $response = $this->getJson('/runtime');

        // Ambiente de dev: o Laravel renderiza o erro com mensagem e trace.
        $response->assertStatus(500);
        $this->assertStringContainsString('SQLSTATE[42S22]: coluna secreta', $response->getContent());
        $this->assertNull($response->json('reference'));
    }

    /*
    |--------------------------------------------------------------------------
    | Indisponibilidade real mantém o 503
    |--------------------------------------------------------------------------
    */

    #[Test]
    #[DataProvider('statusHttp')]
    public function httpExceptionMantemOProprioStatus(int $status): void
    {
        config(['app.debug' => false]);

        $this->getJson("/abort/{$status}")
            ->assertStatus($status)
            ->assertJsonPath('message', 'Mensagem do abort');
    }

    public static function statusHttp(): array
    {
        return ['503 (manutenção de verdade)' => [503], '429' => [429], '418' => [418]];
    }

    /*
    |--------------------------------------------------------------------------
    | Exceções conhecidas: comportamento da 2.2 preservado
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function validacaoEm422ComOsErros(): void
    {
        $this->getJson('/validation')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Erro de validação!')
            ->assertJsonPath('data.cnpj.0', 'CNPJ inválido.');
    }

    #[Test]
    public function modelNaoEncontradoEm404(): void
    {
        $this->getJson('/model')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Sem resultados para a sua pesquisa!');
    }

    #[Test]
    public function naoAutenticadoEm401(): void
    {
        $this->getJson('/auth')->assertStatus(401)->assertJsonPath('success', false);
    }

    #[Test]
    public function apiResponseExceptionDevolveOEnvelopeComOCodigo(): void
    {
        $this->getJson('/api-response')
            ->assertStatus(409)
            ->assertExactJson(['success' => false, 'message' => 'Conflito']);
    }

    #[Test]
    public function apiResponseExceptionNaoEReportada(): void
    {
        $logged = 0;
        Event::listen(MessageLogged::class, static function () use (&$logged): void {
            ++$logged;
        });

        $this->getJson('/api-response');

        $this->assertSame(0, $logged);
    }

    /*
    |--------------------------------------------------------------------------
    | Autorização
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function negacaoDaPolicySemStatusVira403(): void
    {
        $this->getJson('/deny')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Sem permissão');
    }

    #[Test]
    public function negacaoComStatusRespeitaOStatusDaPolicy(): void
    {
        $this->getJson('/deny-status')->assertStatus(404);
    }

    #[Test]
    public function codigoDeNegocioNaoViraStatusHttp(): void
    {
        // Response::deny('...', 'POLICY_X'): o "code" é de negócio, não HTTP.
        $this->getJson('/deny-code')->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | ErrorReference
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function aReferenciaEEstavelParaAMesmaExcecao(): void
    {
        $e = new RuntimeException('x');

        $this->assertSame(ErrorReference::for($e), ErrorReference::for($e));
        $this->assertNotSame(ErrorReference::for($e), ErrorReference::for(new RuntimeException('x')));
    }
}
