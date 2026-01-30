<?php

use Illuminate\Support\Facades\Broadcast;
use App\Events\PaymentReceived;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('payment-channel', function () {
    return true; // You can implement authentication logic here if needed
});
