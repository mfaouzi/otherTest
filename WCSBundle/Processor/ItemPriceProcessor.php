<?php

namespace Aliznet\WCSBundle\Processor;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Items Processor with prices in columns.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ItemPriceProcessor extends ItemProcessor
{
    /**
     * @param EntityManager           $em
     * @param ChannelManager          $channelManager
     * @param array                   $mediaAttributeTypes
     * @param type                    $localeClass
     * @param ProductBuilderInterface $productBuilder
     */
    public function __construct(
        EntityManager $em,
        ChannelManager $channelManager,
        array $mediaAttributeTypes,
        $localeClass,
        ProductBuilderInterface $productBuilder = null
    ) {
        parent::__construct($em, $channelManager, $mediaAttributeTypes, $localeClass, $productBuilder);
    }

    /**
     * @param type $product
     *
     * @return array
     */
    public function process($product)
    {
        parent::process($product);
        $data['product'] = [];
        $data['media'] = [];

        $groups = $product->getGroupCodes();
        $categories = $product->getCategoryCodes();
        $prices = $product->getValue('price')->getPrices();

        $data['product']['PartNumber'] = $product->getValue('sku')->getProduct()->getLabel();
        $data['product']['Type'] = 'ITEM';
        $data['product']['ParentPartNumber'] = (empty($groups)) ? '' : $groups[0];
        $data['product']['Sequence'] = '1';
        $data['product']['ParentGroupIdentifier'] = (empty($categories)) ? '' : $categories[0];

        if (!empty($prices)) {
            $currencies = [];
            foreach ($prices as $price) {
                $currencies[] = $price->getCurrency();
            }
            $mediaValues = $this->getMediaProductValues($product);
            foreach ($mediaValues as $mediaValue) {
                $data['media'][$i][] = $this->serializer->normalize(
                        $mediaValue->getMedia(), 'flat', ['field_name' => 'media', 'prepare_copy' => true, 'value' => $mediaValue]
                );
            }

            $filename = 'products_atrributes.txt';
            $dir = (dirname(dirname(__FILE__)));
            $file = $dir.'/Resources/doc/'.$filename;

            $attributes = [];
            if (file_exists($file) && file($file)) {
                $lines = file($file);
                foreach ($lines as $line) {
                    $att = str_replace(array("\r\n", "\n", "\r"), '', $line);
                    $attr = explode('=>', $att);
                    $csvHeader = $attr[0];
                    $attributeCode = $attr[1];
                    $attributes[$csvHeader] = $attributeCode;
                }

                foreach ($attributes as $code => $att) {
                    $i = 1;
                    foreach ($currencies as $currency) {
                        $valid = false;
                        switch ($att) {
                            case 'price':
                                $values = $product->getValue($att)->getPrice($currency)->getData();
                                break;
                            case 'ListPrice':
                                $values = $product->getValue($att)->getPrice($currency)->getData();
                                $code = 'AlternativeListPrice'.$i;
                                $valid = true;
                                break;
                            default:
                                $values = $product->getValue($att, $this->getLanguage(), $this->getChannel());
                                break;
                        }
                        if ($valid) {
                            $data['product']['AlternativeCurrency'.$i] = $currency;
                            $data['product'][$code] = $values;
                        } else {
                            $data['product'][$code] = $values;
                        }
                        ++$i;
                    }
                }
            }
        }

        return $data;
    }
}
