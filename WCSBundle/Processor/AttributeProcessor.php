<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Attribute Processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class AttributeProcessor extends ProcessorHelper implements ItemProcessorInterface
{
    /**
     * @var string
     */
    protected $wcsattributetype;

    /**
     * @param ChannelManager          $channelManager
     * @param string[]                $mediaAttributeTypes
     * @param ProductBuilderInterface $productBuilder
     */
    public function __construct(
        ChannelManager $channelManager,
        array $mediaAttributeTypes,
        ProductBuilderInterface $productBuilder = null
    ) {
        parent::__construct($channelManager, $mediaAttributeTypes, $productBuilder);
    }

    /**
     * @param type $item
     *
     * @return array
     */
    public function process($item)
    {
        $result = [];
        $result['Identifier'] = $item->getCode();
        $result['type'] = $this->processattributeType($item->getAttributeType());
        $group = $item->getGroup()->getCode();
        $item_group = '';
        switch ($group) {
            case 'Descriptif':
                $item_group = 'AssignedValues';
                break;
            case 'Defining':
                $item_group = 'AllowedValues';
                break;
            default:
                $item_group = '';
                break;
        }
        $result['AttributeType'] = $item_group;
        $result['Name'] = $item->setLocale($this->getLanguage())->getLabel();
        $result['label_'.$this->getLanguage()] = $item->setLocale($this->getLanguage())->getLabel();
        $result['Sequence'] = 1;
        $result['Displayable'] = 'True';
        $result['Searchable'] = ($item->isUseableAsGridFilter()) ? 'True' : 'False';
        $result['Comparable'] = '';
        $result['StoreDisplay'] = '1';
        $result['Delete'] = '';

        return $result;
    }
}
