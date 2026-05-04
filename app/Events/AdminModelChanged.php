<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminModelChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $model;
    public int|string $id;
    public string $action;

    public function __construct(string $model, $id, string $action = 'updated')
    {
        $this->model = $model;
        $this->id = $id;
        $this->action = $action;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('admin');
    }

    public function broadcastWith(): array
    {
        return [
            'model' => $this->model,
            'id' => $this->id,
            'action' => $this->action,
        ];
    }
}
