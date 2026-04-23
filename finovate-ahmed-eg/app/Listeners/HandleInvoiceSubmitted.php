<?php

namespace App\Listeners;

use App\Events\InvoiceSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleInvoiceSubmitted
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
    public function handle(InvoiceSubmitted $event): void
    {
        //
    }
}
