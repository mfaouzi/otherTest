<?php

namespace Aliznet\WCSBundle\Reader\ORM;

use Doctrine\ORM\EntityManager;

/**
 * Attribute Values Reader.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class AttributeValuesReader extends AttributeReaderHelper
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var int
     */
    protected $wcs;

    /**
     * @param EntityManager $em        The entity manager
     * @param string        $className The entity class name used
     */
    public function __construct(EntityManager $em, $className)
    {
        return parent::__construct($em, $className);
    }

    /**
     * Get Query.
     *
     * @return Query
     */
    public function getQuery()
    {
        $this->query = $this->em
                ->getRepository($this->className)
                ->createQueryBuilder('av')
                ->innerJoin('av.attribute', 'at')
                ->orderBy('av.attribute')
                ->addOrderBy('av.sortOrder');

        $qb = $this->query;
        $this->queryExludedWCSFields($qb, 'at');
        $this->queryAttributes($qb, 'at');

        $this->query = $this->query->getQuery();

        return $this->query;
    }
}
