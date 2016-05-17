<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Product Attribute Value Processor for descriptions attributes.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductAttributeDescValueProcessor extends ProcessorHelper implements ItemProcessorInterface
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

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
        foreach ($this->getAttributes($product) as $attr) {
            $productName = $product->getValue('sku')->getProduct()->getLabel();
            $parentGroup = $attr->getGroup()->getCode();
            $code = $attr->getCode();
            $attrValue = $product->getValue($code, $this->getLanguage(), $this->getChannel());
            if (!empty($code) && $parentGroup === 'Descriptif') {
                if (!empty($productName) && !empty($attrValue) && !empty($attrValue->__toString())) {
                    $data['product'][$i]['PartNumber'] = $productName;
                    $data['product'][$i]['Type'] = $this->processattributeType($attr->getAttributeType());
                    $data['product'][$i]['Name'] = $code;
                    $data['product'][$i]['Sequence'] = '0';
                    $data['product'][$i]['Value'] = $attrValue->__toString();
                    $data['product'][$i]['delete'] = '0';
                    ++$i;
                }
            }
        }

        return $data['product'];
    }

    /**
     * @param type $product
     *
     * @return type
     */
    protected function getAttributes($product)
    {
        $attributes = [];

        foreach ($product->getValues() as $value) {
            if (!in_array($value->getAttribute(), $attributes)) {
                $attributes[] = $value->getAttribute();
            }
        }

        return $attributes;
    }
}
