<?php

namespace Aliznet\WCSBundle\Writer\File;

use Akeneo\Component\Buffer\BufferFactory;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;

/**
 * CSV Product and Items with prices in columns writer.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductPriceWriter extends ProductWriterHelper
{
    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param BufferFactory             $bufferFactory
     * @param type                      $entityManager
     * @param ChannelManager            $channelManager
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        BufferFactory $bufferFactory,
        $entityManager,
        ChannelManager $channelManager
    ) {
        parent::__construct($filePathResolver, $bufferFactory, $entityManager, $channelManager);
    }

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return parent::getConfigurationFields();
    }
    /**
     * Get a set of all keys inside arrays.
     *
     * @param array $items
     *
     * @return array
     */
    protected function getAllKeys(array $items)
    {
        $intKeys = [];
        foreach ($items as $item) {
            $intKeys[] = array_keys($item);
        }
        if (0 === count($intKeys)) {
            return [];
        }
        $mergedKeys = call_user_func_array('array_merge', $intKeys);

        return array_unique($mergedKeys);
    }

    /**
     * Merge the keys in arrays.
     *
     * @param array $uniqueKeys
     *
     * @return array
     */
    protected function mergeKeys($uniqueKeys)
    {
        $uniqueKeys = array_fill_keys($uniqueKeys, '');
        $fullItems = [];
        foreach ($this->items as $item) {
            $fullItems[] = array_merge($uniqueKeys, $item);
        }

        return $fullItems;
    }
}
