<?php

namespace App\Services;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\{ClientExceptionInterface as ClientException,
    RedirectionExceptionInterface as RedirectionException,
    ServerExceptionInterface as ServerException,
    TransportExceptionInterface as TransportException
};

class Classifier
{
    /**
     * @param $image
     * @return array
     * @throws ClientException|RedirectionException|ServerException|TransportException
     */
    public static function classify($image): array
    {
        try {
            $response = (new CurlHttpClient())->request(
                'POST',
                $_ENV['CLASSIFIER_URL'],
                ['body' => ['image' => base64_encode($image)]]
            );

            $response = json_decode($response->getContent(), true);
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'data' => [
                    'error' => $exception->getMessage()
                ]
            ];
        }
        return [
            'success' => $response['success'] ?? false,
            'data' => [
                'error' => $response['data']['error'] ?? '',
                'is_plan' => $response['data']['is_plan'] ?? false
            ]
        ];
    }
}
