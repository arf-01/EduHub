<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;



class QuizStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $quizId;
    public $roomName;
    public $startDatetime;
    public $endDatetime;

    public function __construct($quizId, $roomName, $startDatetime, $duration)
    {
        $this->quizId = $quizId;
        $this->roomName = $roomName;
        $this->startDatetime = $startDatetime;
        $this->endDatetime = $startDatetime->copy()->addSeconds($duration);
    }

    public function broadcastOn()
    {
        return new Channel('room.' . $this->roomName);
    }

    public function broadcastWith()
    {
        return [
            'quizId' => $this->quizId,
            'startDatetime' => $this->startDatetime,
            'endDatetime' => $this->endDatetime,
        ];
    }
}
