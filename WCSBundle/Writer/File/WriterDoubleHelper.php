<?php

namespace Aliznet\WCSBundle\Writer\File;

use Pim\Component\Connector\Writer\File\FilePathResolverInterface;

/**
 *  CSV Writer Double Helper.
 * 
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class WriterDoubleHelper extends WriterHelper
{
    /**
     * @param FilePathResolverInterface $filePathResolver
     */
    public function __construct(FilePathResolverInterface $filePathResolver)
    {
        parent::__construct($filePathResolver);
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
        foreach ($items as $itemss) {
            foreach ($itemss as $item) {
                $intKeys[] = array_keys($item);
            }
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
        foreach ($this->items as $itemss) {
            foreach ($itemss as $item) {
                $fullItems[] = array_merge($uniqueKeys, $item);
            }
        }

        return $fullItems;
    }
}
