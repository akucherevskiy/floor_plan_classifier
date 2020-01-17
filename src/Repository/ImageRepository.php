<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ImageRepository extends ServiceEntityRepository
{
    const START_PAGE = 1;
    const MAX_PER_PAGE = 30;
    const PER_PAGE = 10;

    /**
     * @var Request
     */
    private $request;

    /**
     * @required
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function getPaginatedList()
    {
        $page = $this->request->query->get('page');
        $perPage = $this->request->query->get('per_page');

        $page = (null == $page || $page < 0) ? self::START_PAGE : $page;
        $maxPerPage = self::MAX_PER_PAGE;
        if (null == $page || $perPage <= 0) {
            $perPage = self::PER_PAGE;
        } elseif ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        return $this->createQueryBuilder('image')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult();
    }
}