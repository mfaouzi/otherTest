<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Processor Helper.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProcessorHelper extends AbstractConfigurableStepElement
{
    /**
     * @var string Channel code
     */
    protected $channel;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var array Normalizer context */
    protected $normalizerContext;

    /** @var array */
    protected $mediaAttributeTypes;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var string */
    protected $language;

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
        $this->channelManager = $channelManager;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->productBuilder = $productBuilder;
    }

    /**
     * @param product $product
     */
    public function process($product)
    {
        if (null !== $this->productBuilder) {
            $contextChannel = $this->channelManager->getChannelByCode($this->channel);
            $this->productBuilder->addMissingProductValues($product, [$contextChannel], $contextChannel->getLocales()->toArray());
        }
    }
    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help',
                ],
            ],
            'language' => [
                'options' => [
                    'required' => false,
                    'label'    => 'aliznet_wcs_export.export.language.label',
                    'help'     => 'aliznet_wcs_export.export.language.help',
                ],
            ],
        ];
    }

    /**
     * Get exportedAttributes.
     * 
     * @param string $attributetype attributetype
     * 
     * @return string $attributetype attributetype
     */
    public function processattributeType($attributetype)
    {
        $pimAttributeTypeInteger = array('pim_catalog_boolean', 'pim_catalog_number');
        $pimAttributeTypeFloat = array('pim_catalog_metric');
        $pimAttributeTypeDouble = array('pim_catalog_price_collection');
        $pimAttributeTypeString = array('pim_catalog_date', 'pim_catalog_file', 'pim_catalog_identifier', 'pim_catalog_image', 'pim_catalog_multiselect',
                                          'pim_catalog_simpleselect', 'pim_catalog_text', 'pim_catalog_textarea', );
        if (in_array($attributetype, $pimAttributeTypeInteger)) {
            $this->wcsattributetype = 'integer';
        } elseif (in_array($attributetype, $pimAttributeTypeFloat)) {
            $this->wcsattributetype = 'float';
        } elseif (in_array($attributetype, $pimAttributeTypeDouble)) {
            $this->wcsattributetype = 'double';
        } elseif (in_array($attributetype, $pimAttributeTypeString)) {
            $this->wcsattributetype = 'string';
        } else {
            $this->wcsattributetype = $attributetype;
        }

        return $this->wcsattributetype;
    }

    /**
     * Set channel.
     *
     * @param string $channelCode Channel code
     *
     * @return $this
     */
    public function setChannel($channelCode)
    {
        $this->channel = $channelCode;

        return $this;
    }

    /**
     * Get channel.
     *
     * @return string Channel code
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * get language.
     *
     * @return string language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set exported language.
     *
     * @param type $language
     *
     * @return \Aliznet\WCSBundle\Processor\ProcessorHelper
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get normalizer context.
     *
     * @return array $normalizerContext
     */
    protected function getNormalizerContext()
    {
        if (null === $this->normalizerContext) {
            $this->normalizerContext = [
                'scopeCode'   => $this->channel,
                'localeCodes' => $this->getLocaleCodes($this->channel),
            ];
        }

        return $this->normalizerContext;
    }

    /**
     * Get locale codes for a channel.
     *
     * @param string $channelCode
     *
     * @return array
     */
    protected function getLocaleCodes($channelCode)
    {
        $channel = $this->channelManager->getChannelByCode($channelCode);

        return $channel->getLocaleCodes();
    }

    /**
     * Fetch medias product values.
     *
     * @param ProductInterface $product
     *
     * @return ProductValueInterface[]
     */
    protected function getMediaProductValues(ProductInterface $product)
    {
        $values = [];
        foreach ($product->getValues() as $value) {
            if (\in_array($value->getAttribute()->getAttributeType(), $this->mediaAttributeTypes)) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
