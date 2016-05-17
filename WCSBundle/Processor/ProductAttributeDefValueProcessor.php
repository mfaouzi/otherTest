<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Product Attribute Value Processor for definitions attributes.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductAttributeDefValueProcessor extends ProcessorHelper implements ItemProcessorInterface
{
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
     * @param product $product
     *
     * @return array
     */
    public function process($product)
    {
        parent::process($product);
        $data['product'] = [];

        return $this->fillProductData($product, $data);
    }

    protected function fillProductData($product, $data)
    {
        $i = 0;
        foreach ($product->getValues() as $value) {
            $productName = $product->getValue('sku')->getProduct()->getLabel();
            $attrCode = '';
            $attrValue = '';
            $options = $value->getOption();
            if (count($options) > 0) {
                if (!empty($options)) {
                    $options->setLocale($this->getLanguage());
                    $attrCode = $value->getAttribute()->getCode();
                    $attrValue = $options->getOptionValue()->getValue();
                }
            }
            if (!empty($productName) && !empty($attrCode) && !empty($attrValue)) {
                $data['product'][$i]['PartNumber'] = $productName;
                $data['product'][$i]['Name'] = $attrCode;
                $data['product'][$i]['value'] = $attrValue;
                $data['product'][$i]['delete'] = '0';
                ++$i;
            }
        }

        return $data['product'];
    }
}
