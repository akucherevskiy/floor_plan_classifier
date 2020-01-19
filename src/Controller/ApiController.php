<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Services\Classifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\{ClientExceptionInterface as ClientException,
    RedirectionExceptionInterface as RedirectionException,
    ServerExceptionInterface as ServerException,
    TransportExceptionInterface as TransportException
};

class ApiController extends AbstractController
{
    /**
     * @Route("/images", name="images")
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var ImageRepository $imageRepo */
        $imageRepo = $em->getRepository(Image::class);
        $count = $em->getRepository(Attribute::class)->findBy(['attr' => 'count']);
        if (!$count) {
            /** @var int $count */
            $count = $imageRepo->count([]);
            $em->persist((new Attribute())->setAttr('count')->setValue($count));
            $em->flush();
        } else {
            $count = $count[0]->getValue();
        }
        $lastPage = round($count / ImageRepository::PER_PAGE);
        $currentPage = $request->query->get('page');

        return $this->render(
            'list.html.twig',
            [
                'list' => $imageRepo->getPaginatedList($currentPage, $request->query->get('per_page')),
                'next_page' => $currentPage < $lastPage ? $currentPage + 1 : false,
                'previous_page' => $currentPage > 0 ? $currentPage - 1 : false,
                'last_page' => $lastPage,
                'count' => $count,
                'base_path' => $_ENV['BASE_PATH']
            ]
        );
    }

    /**
     * @Route("/classify_image", name="classify")
     * @param Request $request
     * @return Response
     * @throws ClientException|RedirectionException|ServerException|TransportException
     */
    public function classifyAction(Request $request)
    {
        $image = $request->files->get('image');
        if ($image) {
            $classifierResult = Classifier::classify(file_get_contents($image));
            if (!$classifierResult['success']) {
                return $this->json($classifierResult['data']['error'], Response::HTTP_BAD_REQUEST);
            }
            $data['is_floor_plan'] = $classifierResult['data']['is_plan'];
        }

        return $this->render('classifier.html.twig', $data ?? []);
    }
}
