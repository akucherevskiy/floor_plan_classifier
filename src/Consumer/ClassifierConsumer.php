<?php

namespace App\Consumer;

use App\Entity\Image;
use App\Services\Classifier;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\{ClientExceptionInterface as ClientException,
    RedirectionExceptionInterface as RedirectionException,
    ServerExceptionInterface as ServerException,
    TransportExceptionInterface as TransportException
};

class ClassifierConsumer implements ConsumerInterface
{
    public const BASE_EXT = '.jpg';

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
     * @return void
     * @throws ClientException|RedirectionException|ServerException|TransportException
     */
    public function execute(AMQPMessage $msg)
    {
        $body = unserialize($msg->body);
        foreach ($body as $lunId) {
            $response = (new CurlHttpClient())->request('GET', $_ENV['BASE_PATH'] . $lunId . self::BASE_EXT);
            if (
                Response::HTTP_OK != $response->getStatusCode() ||
                "image/jpeg" != $response->getHeaders()['content-type'][0]
            ) {
                return;
            }
            $classifierResult = Classifier::classify($response->getContent());
            if (!$classifierResult['success']) {
                return;
            }
            if ($isPlan = $classifierResult['data']['is_plan']) {
                $image = $this->em->getRepository(Image::class)->findOneBy(['lunId' => $lunId]);
                $image->setIsFloorPlan($isPlan);
            }
        }
        $this->em->flush();
    }
}
