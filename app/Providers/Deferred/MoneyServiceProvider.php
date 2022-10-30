<?php

namespace MotionArray\Providers\Deferred;

use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Exchange\SwapExchange;
use MotionArray\Providers\ServicesProvider;

class MoneyServiceProvider extends ServicesProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function register()
    {
        $this->app->bind(Converter::class, function () {
            return new Converter(new ISOCurrencies(), $this->app->make(SwapExchange::class));
        });
    }

    public function provides()
    {
        return [Converter::class];
    }
}
