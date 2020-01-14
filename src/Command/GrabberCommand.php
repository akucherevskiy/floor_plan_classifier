<?php

namespace App\Command;

use App\FileLoader;
use App\Message\Grabber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class GrabberCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'app:grab-images';

    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();
        $this->messageBus = $bus;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $before = round(microtime(true) * 1000);
        $i = 0;
        $success = FileLoader::fileGetContentsChunked(
            50000,
            function ($chunk, &$handle, $iteration) {
                $chunkArr = explode("\n", $chunk);
                if ($iteration == 0) {
                    array_shift($chunkArr);
                }
                $this->messageBus->dispatch(new Grabber($chunkArr[0]));
            }
        );

        if (!$success) {
            dd('failed');
        }

        $after = round(microtime(true) * 1000);

        dd($i, $after - $before);
        var_dump('success loaded 1M pictures');
        return 0;
    }
}