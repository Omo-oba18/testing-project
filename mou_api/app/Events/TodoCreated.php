<?php

namespace App\Events;

use App\Todo;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TodoCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $todo;

    /**
     * Create a new event instance.
     */
    public function __construct(Todo $todo)
    {
        $this->todo = $todo;
    }
}
