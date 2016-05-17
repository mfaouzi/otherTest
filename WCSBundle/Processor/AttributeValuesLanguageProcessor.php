<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Attribute Values with Language Processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class AttributeValuesLanguageProcessor extends ProcessorHelper implements ItemProcessorInterface
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
     * @param Attribute values $item
     *
     * @return array
     */
    public function process($item)
    {
        $result = array();
        $i = 0;
        foreach ($item->getOptionValues() as $value) {
            $result[$i]['Identifier'] = $item->getAttribute()->getCode();
            $result[$i]['ValueIdentifier'] = $item->getCode();
            $result[$i]['Sequence'] = 1;
            $result[$i]['value'] = $value->getValue();
            $language = $value->getLocale();
            $variable = constant('Aliznet\WCSBundle\Resources\Constant\Constants::'.$language);
            $result[$i]['LanguageId'] = $variable;
            $result[$i]['Delete'] = '';
            ++$i;
        }

        return $result;
    }
}
