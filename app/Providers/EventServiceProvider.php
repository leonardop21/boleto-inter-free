<?php

namespace App\Providers;

use App\Models\Pix;
use App\Models\Boleto;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Nfe;
use App\Models\CustomerNfeType;
use App\Observers\BoletoObServe;
use App\Observers\ServiceObserver;
use App\Observers\CustomerObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Observers\CustomerNfeTypeObserver;
use App\Observers\PixObserver;
use App\Observers\NfeObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
       Customer::observe(CustomerObserver::class);
       Service::observe(ServiceObserver::class);
       Boleto::observe(BoletoObServe::class);
       Pix::observe(PixObserver::class);
       CustomerNfeType::observe(CustomerNfeTypeObserver::class);
       Nfe::observe(NfeObserver::class);
    }
}
