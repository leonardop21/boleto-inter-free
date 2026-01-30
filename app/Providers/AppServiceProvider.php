<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Interfaces\CustomerInterface',
            'App\Repositories\CustomerRepository',
          );
        
          $this->app->bind(
            'App\Interfaces\ServiceInterface',
            'App\Repositories\ServiceRepository',
          );

          $this->app->bind(
            'App\Interfaces\BoletoInterface',
            'App\Repositories\BoletoRepository'
          );
          $this->app->bind(
            'App\Interfaces\HolidayInterface',
            'App\Repositories\HolidayRepository'
          );
          $this->app->bind(
            'App\Interfaces\NfeInterface',
            'App\Repositories\NfeRepository'
          );
          $this->app->bind(
            'App\Interfaces\CustomerNfeTypeInterface',
            'App\Repositories\CustomerNfeTypeRepository'
          );

          $this->app->bind(
            'App\Interfaces\PixInterface',
            'App\Repositories\PixRepository'
          );

          $this->app->bind(
            'App\Interfaces\SendInterface',
            'App\Repositories\SendRepository'
          );

          $this->app->bind( 
            'App\Interfaces\NotifishInterface',
            'App\Repositories\NotifishRepository'
          );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      \Carbon\Carbon::setLocale($this->app->getLocale());
    }
}
