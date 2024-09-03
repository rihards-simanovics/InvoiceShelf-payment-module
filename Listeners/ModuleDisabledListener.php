<?php

namespace Modules\Payments\Listeners;

use InvoiceShelf\Events\ModuleDisabledEvent;
use InvoiceShelf\Models\PaymentMethod;

class ModuleDisabledListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ModuleDisabledEvent $event
     * @return void
     */
    public function handle(ModuleDisabledEvent $event)
    {
        if ($event->module->name !== config('payments.name')) {
            return false;
        }

        PaymentMethod::where('type', PaymentMethod::TYPE_MODULE)->update(['active' => false]);
    }
}
