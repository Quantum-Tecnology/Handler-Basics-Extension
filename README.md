<h3 align="center">Handler-Basics-Extension for Laravel</h3>

## 🧐 Sobre <a name = "about"></a>

Este pacote inclui uma classe de extensão desacoplada que contem os principais tratamentos para exception para o laravel,
tambem inclui um layout default do symfony que funciona no postman, e retornos default que podem ser utilizados em qualquer lugar do sistema.

Sempre que possivel ele sera atualizado, e esta aberto para a comunidade sugerir melhorias.

## 🏁 Para utilizar o pack

Para utilizar a classe, basta instalar ela utilizando o comando do composer:

```
composer require quantumtecnology/handler-basics-extension
```

### Laravel 11+ (recomendado)

No `bootstrap/app.php`:

```php
use QuantumTecnology\HandlerBasicsExtension\Exceptions\ApiExceptions;

->withExceptions(function (Exceptions $exceptions): void {
    ApiExceptions::configure($exceptions);
})
```

> ⚠️ **Só instalar o pacote não liga o tratamento.** Sem essa linha, as exceções seguem o padrão do Laravel, e as negações de policy, os 404 e as validações saem fora do envelope `{success, message, data}`.

### Laravel 10 (ou registro manual)

```php
$this->app->singleton(ExceptionHandler::class, BaseHandler::class);
```

É a mesma regra do `ApiExceptions`. Os testes rodam os mesmos cenários pelos dois caminhos.

Os retornos padrão da trait `ApiResponseTrait` (`okResponse`, `notFoundResponse`...) podem ser usados em qualquer lugar do sistema.

## 🛡️ Erros em produção

Com `APP_DEBUG=false`, o usuário **não vê estrutura interna**: nada de classe, SQL, arquivo ou stack trace. O que ele recebe depende do tipo do erro:

| Exceção | Status | Mensagem |
|---|---|---|
| `ValidationException` | 422 | "Erro de validação!" + os erros em `data` |
| `ModelNotFoundException` | 404 | "Sem resultados para a sua pesquisa!" |
| `AuthenticationException` | 401 | a da exceção |
| `AuthorizationException` (policy/gate) | o status da policy, ou **403** | a da negação |
| `HttpException` (`abort()`, `php artisan down`) | o da exceção (inclusive **503**) | a da exceção |
| `ApiResponseException` | o código dela | o envelope dela |
| **Qualquer outra** (bug: `TypeError`, null...) | **500** | "Ocorreu um erro inesperado. Se persistir, informe o código `XXXXXXXXXX` ao suporte." |

O **código de referência** volta no campo `reference` da resposta e **também vai para o contexto do log**, e portanto para o Telescope. Com o código que o usuário mandou, você acha o erro exato.

Com `APP_DEBUG=true` (ambiente de dev, ou ligado por um dev interno), o erro não mapeado vai para o renderizador do Laravel, com mensagem e trace completos.

> **Por que 500, e não 503 "em manutenção":** 503 significa "indisponível, tente de novo", e é assim que usuário, balanceador e retry automático o leem. Um bug falha igual em toda tentativa. O 503 fica para a indisponibilidade real, como `php artisan down` ou `abort(503)`.

## 🎈 Recursos

- `ApiExceptions` / `BaseHandler`: tradução de exceção em resposta da API (acima).
- `ApiResponseTrait`: os retornos mais usados numa API, no envelope `{success, message, data, pagination}`, com export CSV via `Accept: text/csv`.
- `ApiResponseException`: interrompe o fluxo devolvendo uma resposta pronta. Não é reportada no log.
- **Sentry (opcional):** se o projeto tiver o `sentry/sentry-laravel` instalado, o `BaseHandler` reporta a ele. O pacote não depende mais do Sentry.

## 🧪 Testes

```
composer test
```

## 🧐 Outras Bibliotecas

- [Enum-Basics-Extension](https://packagist.org/packages/quantumtecnology/enum-basics-extension) - Utilizado para auxiliar nas Classes de Enums;
- [SetSchema-Trait](https://packagist.org/packages/quantumtecnology/setschema-trait-postgresql) - Suprir a necessidade de setar os schemas automaticamente do PostgreSQL;
- [Validate-Trait](https://packagist.org/packages/quantumtecnology/validate-trait) - Bindar os Requests automaticamente de acordo com o caminho do Service Pattern;
- [PerPage-Trait](https://packagist.org/packages/quantumtecnology/perpage-trait) - Padronizar a quantidade do paginate na api inteira e definir uma quantidade máxima;

## ⛏️ Ferramentas

- [php](https://www.php.net/) - linguagem
- [laravel](https://laravel.com/) - framework

## ✍️ Autor

- [@Luis Gustavo Santarosa Pinto](https://github.com/QuantumTecnology) - Idea & Initial work
