<?php

declare(strict_types=1);

namespace QuantumTecnology\HandlerBasicsExtension\Exceptions;

use Illuminate\Support\Str;
use Throwable;
use WeakMap;

/**
 * Código de referência de uma exceção, estável entre o report (log/Telescope) e
 * o render (resposta ao usuário): os dois passam pela MESMA instância de
 * Throwable, então o código é guardado por instância.
 *
 * WeakMap: a entrada some junto com a exceção — workers de fila e Octane vivem
 * muitas requisições, e um array estático vazaria memória.
 */
final class ErrorReference
{
    /** @var WeakMap<Throwable, string>|null */
    private static ?WeakMap $references = null;

    public static function for(Throwable $e): string
    {
        self::$references ??= new WeakMap();

        return self::$references[$e] ??= self::generate();
    }

    /**
     * 10 caracteres do trecho aleatório de um ULID: curto para o usuário ditar,
     * sem I, L, O e U (Crockford base32), e sem colisão prática.
     */
    private static function generate(): string
    {
        return mb_substr((string) Str::ulid(), -10);
    }
}
