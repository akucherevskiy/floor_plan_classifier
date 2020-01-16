<?php

namespace App\Consumer;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class SaverConsumer implements ConsumerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * SaverConsumer constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        $body = unserialize($msg->body);
        foreach ($body as $iterator => $data) {
            $dataArr = explode(',', $data);
            if (count($dataArr) > 1 && ($lunId = (int)$dataArr[0]) > 0) {
                $this->em->persist((new Image())->setLunId($lunId));
                $ids[] = $lunId;
            }
        }
        $this->em->flush();

    }
}