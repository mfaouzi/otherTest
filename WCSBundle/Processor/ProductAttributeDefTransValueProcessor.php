<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Product Attribute Value Processor for definition attributes.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductAttributeDefTransValueProcessor extends ProcessorHelper implements ItemProcessorInterface
{
    /**
     * @param ChannelManager          $channelManager
     * @param string[]                $mediaAttributeTypes
     * @param ProductBuilderInterface $productBuilder
     */
    public function __construct(
        ChannelManager $channelManager,
        ProductBuilderInterface $productBuilder = null,
        ManagerRegistry $managerRegistry,
        LocaleHelper $localeHelper,
        array $mediaAttributeTypes,
        $class
    ) {
        parent::__construct($channelManager, $mediaAttributeTypes, $productBuilder);
        $this->localeRepository = $managerRegistry->getRepository($class);
        $this->localeHelper = $localeHelper;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        $languages = $this->localeRepository->getActivatedLocaleCodes();
        $languagesChoices = [];
        foreach ($languages as $language) {
            $languagesChoices[$language] = $language;
        }

        return $languagesChoices;
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
            $options = $value->getOption();
            if (count($options) > 0) {
                if (!empty($productName) && !empty($options)) {
                    $data['product'][$i]['PartNumber'] = $productName;
                    $data['product'][$i]['Type'] = $this->processattributeType($options->getAttribute()->getAttributeType());
                    foreach ($this->getLanguages() as $language) {
                        $translation = $options->getAttribute()->setLocale($language);
                        $attrCode = $translation->getLabel();
                        $languagePrefix = explode('_', $language)[0];
                        $data['product'][$i][$this->localeHelper->getLocaleLabel($languagePrefix, 'en_US').'Name'] = $attrCode;
                        $data['product'][$i][$this->localeHelper->getLocaleLabel($languagePrefix, 'en_US').'Sequence'] = '0';
                    }
                    $data['product'][$i]['delete'] = '0';
                    ++$i;
                }
            }
        }

        return $data['product'];
    }
}
