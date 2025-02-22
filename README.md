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

extender ela na sua classe handle dentro de exceptions, e utilizar os retornos default que esta dentro da trait ApiResponseTrait a vontade.

Pronto, ja é para estar funcionando.

## 🎈 Recursos

Nele existem algumas ferramentas uteis.

- BaseEnum:
  - Layout do Symfony que funciona no postman.
  - Extensão do handle com os principais tipos de retornos.
  - Uma trait com diversos retornos mais utilizados dentro de uma api.
  - Tambem vem preparado para se comunicar com o Sentry.

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
