<?php

namespace App\Command;

use App\Entity\Attribute;
use App\FileLoader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GrabberCommand extends Command
{
    const CHUNK_SIZE = 50000;

    /** @var string */
    protected static $defaultName = 'app:grab-images';

    /** @var EntityManagerInterface */
    private $em;

    /** @var ContainerInterface */
    private $container;

    /**
     * GrabberCommand constructor.
     * @param EntityManagerInterface $em
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        parent::__construct();

        $this->em = $em;
        $this->container = $container;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $success = FileLoader::fileGetContentsChunked(
            self::CHUNK_SIZE,
            function ($chunk, $iteration) {
                $chunkArr = explode("\n", $chunk);
                if ($iteration == 0) {
                    array_shift($chunkArr);
                }
                $this->container->get('old_sound_rabbit_mq.image_data_saving_producer')->publish(serialize($chunkArr));
            }
        );

        if (!$success) {
            dd('failed');
        }

        $countAttr = $this->em->getRepository(Attribute::class)->findOneBy(['attr' => 'count']);
        if ($countAttr) {
            $this->em->remove($countAttr);
            $this->em->flush();
        }

        var_dump('success loaded 1M pictures');

        return 0;
    }
}