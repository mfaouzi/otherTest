<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Items Processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ItemProcessor extends ProcessorHelper implements ItemProcessorInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var localeRepository
     */
    protected $localeRepository;

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
        $this->em = $em;
        $this->localeRepository = $em->getRepository($localeClass);
        parent::__construct($channelManager, $mediaAttributeTypes, $productBuilder);
    }

    /**
     * @param type $product
     *
     * @return array
     */
    public function process($product)
    {
        parent::process($product);

        $groups = $product->getGroupCodes();
        $categories = $product->getCategoryCodes();

        $prices = $product->getValue('price')->getPrices();

        $i = 0;
        $data['product'] = [];
        $data['media'] = [];
        if (!empty($prices)) {
            $currencies = [];
            foreach ($prices as $price) {
                $currencies[] = $price->getCurrency();
            }
            foreach ($currencies as $currency) {
                $mediaValues = $this->getMediaProductValues($product);

                foreach ($mediaValues as $mediaValue) {
                    $data['media'][$i][] = $this->serializer->normalize(
                            $mediaValue->getMedia(), 'flat', ['field_name' => 'media', 'prepare_copy' => true, 'value' => $mediaValue]
                    );
                }

                $data['product'][$i]['PartNumber'] = $product->getValue('sku')->getProduct()->getLabel();
                $data['product'][$i]['Type'] = 'ITEM';
                $data['product'][$i]['ParentPartNumber'] = (empty($groups)) ? '' : $groups[0];
                $data['product'][$i]['Sequence'] = '1';
                $data['product'][$i]['ParentGroupIdentifier'] = (empty($categories)) ? '' : $categories[0];
                $data['product'][$i]['Currency'] = $currency;

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
                        $values = $product->getValue($att, $this->getLanguage(), $this->getChannel());
                        if ($att == 'price') {
                            $values = $product->getValue($att)->getPrice($currency)->getData();
                        }
                        if ($att == 'ListPrice') {
                            $values = $product->getValue($att)->getPrice($currency)->getData();
                        }
                        $data['product'][$i][$code] = $values;
                    }
                }
                ++$i;
            }
        }

        return $data;
    }
}
