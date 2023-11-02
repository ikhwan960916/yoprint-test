<?php

namespace App\Listeners;

use App\Events\FileStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FileStatusNotificationListener
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
     * @param  \App\Events\FileStatusNotification  $event
     * @return void
     */
    public function handle(FileStatusNotification $event)
    {
        //
    }
}
