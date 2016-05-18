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
        $data['product'] = [];
        return $this->fillProductData($product, $data);
    }

    protected function fillProductData($item, $data)
    {
        // Set Language of translation:
        $item->setLocale($this->getLanguage());
        $data['product'] = [];
        $index = 0;
        
        foreach ($item->getAxisAttributes() as $attribute)
        {
            $attribute->setLocale($this->getLanguage());
            $data['product'][$index]['PartNumber'] = $item->getCode();
            $data['product'][$index]['Type'] = $this->processattributeType($attribute->getAttributeType());
            $data['product'][$index]['Name'] = $attribute->getCode();
            $data['product'][$index]['Sequence'] = $i+1;
            $j = 0;
            foreach ($attribute->getOptions() as $option)
            {
                $data['product'][$index]['AllowedValue'.($j+1)] = $option->getCode();
                $j++;
            }
            $data['product'][$i]['Delete'] = 0;
            $index ++;
        }

        
        return $data['product'];
    }
}
