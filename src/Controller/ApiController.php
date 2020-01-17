<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/images", name="images")
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $count = $this->getDoctrine()->getRepository(Attribute::class)->findBy(['attr' => 'count']);
        if (!$count) {
            $allCount = $this->getDoctrine()->getRepository(Image::class)->count([]);
            $count = new Attribute();
            $count->setAttr('count');
            $count->setValue($allCount);
        } else {
            $allCount = $count;
        }

        $list = $this->getDoctrine()->getRepository(Image::class)->getPaginatedList();
        $lastPage = round($allCount / 10);
        $currentPage = $request->query->get('page');
        $nextPage = $request->query->get('page') < $lastPage ? $currentPage + 1 : false;
        $previousPage = $request->query->get('page') > 0 ? $currentPage - 1 : false;

        return $this->render(
            'base.html.twig',
            [
                'list' => $list,
                'next_page' => $nextPage,
                'previous_page' => $previousPage,
                'last_page' => $lastPage,
                'count' => $allCount
            ]
        );
    }

    /**
     * @Route("/classify_image", name="classify")
     * @param Request $request
     * @return Response
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function classifyAction(Request $request)
    {
        $responseArr = [];
        $responseArr['render_answer'] = false;
        if ($image = $request->files->get('image')) {
            $client = new CurlHttpClient();
            $response = $client->request(
                'POST',
                'http://127.0.0.1:5000/classify',
                [
                    'body' => [
                        'image' => base64_encode((file_get_contents($image)))
                    ]
                ]
            );
            $classifierResponse = json_decode($response->getContent(), true);
            $responseArr['render_answer'] = true;
            $responseArr['is_floor_plan'] = $classifierResponse['data'][0]['is_plan'];
        }

        return $this->render('classifier.html.twig', $responseArr);
    }
}
