<?php

namespace Aliznet\WCSBundle\Processor;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Product Processor with prices in columns..
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductPriceProcessor extends ProductProcessor
{
    /**
     * @param EntityManager  $em             The entity manager
     * @param ChannelManager $channelManager
     * @param type           $localeClass
     */
    public function __construct(
        EntityManager $em,
        ChannelManager $channelManager,
        $localeClass
    ) {
        return parent::__construct($em, $channelManager, $localeClass);
    }

    /**
     * @param type $item
     *
     * @return array of product values
     */
    public function process($item)
    {
	$values = '';
        // Set Language of translation:
        $item->setLocale($this->getLanguage());
        // Get Product Name
        $product_name = $item->getLabel();
        // Get Product code
        $code_product = $item->getCode();
        // Get products items
        $product = $item->getProducts()->first();
        $data['product'] = [];
        // Get item category
        if (null != $product) {
            $categories = $product->getCategoryCodes();
            // Get items prices
            $prices = $product->getValue('price')->getPrices();
            $data['product']['PartNumber'] = $code_product;
            $data['product']['Type'] = 'PRODUCT';
            $data['product']['ParentPartNumber'] = '';
            $data['product']['Sequence'] = '1';
            $data['product']['ParentGroupIdentifier'] = (empty($categories)) ? '' : $categories[0];

            if (!empty($prices)) {
                $currencies = [];
                foreach ($prices as $price) {
                    $currencies[] = $price->getCurrency();
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
                                        if (null != $product->getValue($att)) {
                                            $values = $product->getValue($att)->getPrice($currency)->getData();
                                            $code = 'AlternativeListPrice'.$i;
                                            $valid = true;
                                        }
                                        break;
                                    case 'name':
                                        $values = $product_name;
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
        }

        return $data;
    }

    /**
     * return array of configuration Fields.
     */
    public function getConfigurationFields()
    {
        return parent::getConfigurationFields();
    }
}
