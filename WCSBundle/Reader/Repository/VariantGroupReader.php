<?php

namespace Aliznet\WCSBundle\Reader\Repository;

use Pim\Bundle\BaseConnectorBundle\Reader\Repository\AbstractReader;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;

/**
 * Variant Group Reader.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class VariantGroupReader extends AbstractReader
{
    /** @var GroupRepositoryInterface */
    protected $repository;

    /**
     * @param GroupRepositoryInterface $repository
     */
    public function __construct(GroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function readItems()
    {
        return $this->repository->getAllVariantGroups();
    }
}
