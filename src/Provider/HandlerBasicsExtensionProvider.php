<?php

namespace QuantumTecnology\HandlerBasicsExtension\Provider;

use Illuminate\Support\ServiceProvider;

class HandlerBasicsExtensionProvider extends ServiceProvider
{
    public $bindings = [
        ServerProvider::class => HandlerBasicsExtension::class,
        ServerProvider::class => ApiResponseException::class,
        ServerProvider::class => ApiResponseTrait::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
