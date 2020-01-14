<?php
namespace App\MessageHandler;

use App\Message\Grabber;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GrabberHandler implements MessageHandlerInterface
{
    public function __invoke(Grabber $message)
    {
        var_dump($message->getContent());
    }
}