<?php

namespace App\Listeners;

use App\Events\InvoiceValidated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleInvoiceValidated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvoiceValidated $event): void
    {
        //
    }
}
