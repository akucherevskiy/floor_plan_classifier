<?php

namespace App\Consumer;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;

class SaverConsumer implements ConsumerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var ContainerInterface */
    private $container;

    /**
     * SaverConsumer constructor.
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        $body = unserialize($msg->body);
        $ids = [];
        foreach ($body as $iterator => $data) {
            $dataArr = explode(',', $data);
            if (count($dataArr) > 1 && ($lunId = (int)$dataArr[0]) > 0) {
                $this->em->persist((new Image())->setLunId($lunId));
                $ids[] = $lunId;
                if (count($ids) == 5) {
                    $this->container->get('old_sound_rabbit_mq.image_classifier_producer')->publish(serialize($ids));
                    $ids = [];
                }
            }
        }
        $this->em->flush();

        $this->container->get('old_sound_rabbit_mq.image_classifier_producer')->publish(serialize($ids));
    }
}