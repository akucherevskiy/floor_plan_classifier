<?php

namespace App\Consumer;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;

class ClassifierConsumer implements ConsumerInterface
{
    const BASE_PATH = 'https://storage.googleapis.com/lun-test/layouts/';
    const BASE_EXT = '.jpg';

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
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function execute(AMQPMessage $msg)
    {
        $body = unserialize($msg->body);
        $client = new CurlHttpClient();
        foreach ($body as $iterator => $item) {
            $response = $client->request('GET', self::BASE_PATH . $item . self::BASE_EXT);
            if (Response::HTTP_OK != $response->getStatusCode() || "image/jpeg" != $response->getHeaders()['content-type'][0]) {
                return;
            }
            try {
                $response = $client->request(
                    'POST',
                    'http://127.0.0.1:5000/classify',
                    [
                        'body' => [
                            'image' => base64_encode($response->getContent())
                        ]
                    ]
                );

                $classifierResponse = json_decode($response->getContent(), true);
                if (!$classifierResponse['success']) {
                    return;
                }
                if ($classifierResponse['data'][0]['is_plan']) {
                    var_dump($item);
                    /** @var Image $image */
                    $image = $this->em->getRepository(Image::class)->findOneBy(['lunId' => $item]);
                    $image->setIsFloorPlan($classifierResponse['data'][0]['is_plan']);
                }
            } catch (\Exception $exception) {
                dd($exception->getMessage());
            }
        }
        $this->em->flush();
    }
}