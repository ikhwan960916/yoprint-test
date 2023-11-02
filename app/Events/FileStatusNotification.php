<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\UserFileUpload;
use App\Models\User;
use App\Data\FileStatus;

class FileStatusNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public FileStatus $file_status;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, FileStatus $file_status)
    {
        $this->user = $user;
        $this->file_status = $file_status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('file-upload-status.' . $this->user->id);
    }
}
