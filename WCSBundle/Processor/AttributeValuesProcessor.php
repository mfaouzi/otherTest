<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Attribute Values Processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class AttributeValuesProcessor extends ProcessorHelper implements ItemProcessorInterface
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
     * @param Attribute $item
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
        $result['Sequence'] = 1;
        $result['Displayable'] = 'true';
        $result['StoreDisplay'] = '0';
        $result['Comparable'] = '';
        $result['Facetable'] = '';
        $result['Searchable'] = ($item->isUseableAsGridFilter()) ? 'True' : 'False';
        $result['Merchandisable'] = '';
        $result['Name'] = $item->setLocale($this->getLanguage())->getLabel();
        $i = 1;
        foreach ($item->getOptions() as $value) {
            $value->setLocale($this->getLanguage());
            $result['AllowedValue'.$i] = $value->getOptionValue()->getValue();
            ++$i;
        }
        $result['Delete'] = '';

        return $result;
    }
}
