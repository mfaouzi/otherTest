<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Product Attribute Value Processor for descriptions attributes.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductAttributeDescTransValueProcessor extends ProcessorHelper implements ItemProcessorInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $localeRepository;

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

    protected function fillProductData($product, $data)
    {
        $i = 0;
        foreach ($this->getAttributes($product) as $attr) {
            $parentGroup = $attr->getGroup()->getCode();

            $productName = $product->getValue('sku')->getProduct()->getLabel();
            if (!empty($productName) && $parentGroup === 'Descriptif') {
                $data['product'][$i]['PartNumber'] = $productName;
                $data['product'][$i]['Type'] = $this->processattributeType($attr->getAttributeType());

                foreach ($this->getLanguages() as $language) {
                    $attr->setLocale($language);
                    $code = $attr->getTranslation()->getLabel();
                    $attrValue = $product->getValue($attr->getCode(), $language, $this->getChannel());
                    $languagePrefix = explode('_', $language)[0];

                    if (!empty($attrValue)) {
                        $data['product'][$i][$this->localeHelper->getLocaleLabel($languagePrefix, 'en_US').'Name'] = $code;
                        $data['product'][$i][$this->localeHelper->getLocaleLabel($languagePrefix, 'en_US').'Sequence'] = '0';
                        $data['product'][$i][$this->localeHelper->getLocaleLabel($languagePrefix, 'en_US').'Value'] = $attrValue->__toString();
                    }

                    $data['product'][$i]['delete'] = '0';
                }
                ++$i;
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
