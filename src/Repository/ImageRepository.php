<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\{Request, RequestStack};

class ImageRepository extends ServiceEntityRepository
{
    public const START_PAGE = 1;
    public const MAX_PER_PAGE = 30;
    public const PER_PAGE = 10;

    /** @var Request */
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

    public function getPaginatedList($page, $perPage)
    {
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
