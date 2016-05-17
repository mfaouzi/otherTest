<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Product Processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /** @var ChannelManager */
    protected $channelManager;

    /**
     * @var string Channel code
     */
    protected $channel;

    /**
     * @var localeRepository
     */
    protected $localeRepository;

    /**
     * @var string
     */
    protected $language;

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
        $this->em = $em;
        $this->channelManager = $channelManager;
        $this->localeRepository = $em->getRepository($localeClass);
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
        $productName = $item->getLabel();
        // Get Product code
        $codeProduct = $item->getCode();
        // Get products items
        $product = $item->getProducts()->first();
        $data['product'] = [];
        if (null != $product) {
            // Get item category
            $categories = $product->getCategoryCodes();
            // Get items prices
            $prices = $product->getValue('price')->getPrices();
            $i = 0;
            if (!empty($prices)) {
                $currencies = [];
                foreach ($prices as $price) {
                    $currencies[] = $price->getCurrency();
                }
                foreach ($currencies as $currency) {
                    $data['product'][$i]['PartNumber'] = $codeProduct;
                    $data['product'][$i]['Type'] = 'PRODUCT';
                    $data['product'][$i]['ParentPartNumber'] = '';
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
                            switch ($att) {
                                case 'price':
                                    $values = $product->getValue($att)->getPrice($currency)->getData();
                                    break;
                                case 'ListPrice':
                                    if (null != $product->getValue($att)) {
                                        $values = $product->getValue($att)->getPrice($currency)->getData();
                                    }
                                    break;
                                case 'name':
                                    $values = $productName;
                                    break;
                                default:
                                    $values = $product->getValue($att, $this->getLanguage(), $this->getChannel());
                                    break;
                            }
                            $data['product'][$i][$code] = $values;
                        }
                    }
                    ++$i;
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
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->getLanguages(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'aliznet_wcs_export.export.language.label',
                    'help'     => 'aliznet_wcs_export.export.language.help',
                ],
            ],
        ];
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
     * @return array of languages
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
     * get language.
     *
     * @return string language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set Language.
     *
     * @param string $language
     *
     * @return \Aliznet\WCSBundle\Processor\ProductProcessor
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }
}
