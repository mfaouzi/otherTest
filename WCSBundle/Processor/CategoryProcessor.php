<?php

namespace Aliznet\WCSBundle\Processor;

use Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor as BaseTransformerProcessor;

/**
 * Category processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class CategoryProcessor extends BaseTransformerProcessor
{
    /**
     * @var string
     */
    protected $language;

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
     * @param type $language
     *
     * @return \Aliznet\WCSBundle\Processor\CategoryProcessor
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return array(
            'language' => array(
                'options' => array(
                    'required' => false,
                    'select2'  => true,
                    'label'    => 'aliznet_wcs_export.export.language.label',
                    'help'     => 'aliznet_wcs_export.export.language.help',
                ),
            ),
        );
    }

    /**
     * @param Category $item
     *
     * @return CategoryInterface[]
     */
    public function process($item)
    {
        $result = array();
        $item->setLocale($this->getLanguage());
        if (null != $item->getParentCode()) {
            $translation = $item->getTranslation();
            $result['GroupIdentifier'] = $item->getCode();
            if ($item->getParent()->getParentCode() === null) {
                $result['TopGroup'] = 'true';
                $result['ParentGroupIdentifier'] = '';
            } else {
                $result['ParentGroupIdentifier'] = $item->getParentCode();
            }
            $result['Sequence'] = '1';
            $result['Name'] = $translation->getLabel();
            $result['ShortDescription'] = $translation->getDescription();
            $result['LongDescription'] = $translation->getLongDescription();
            $result['Thumbnail'] = $item->getThumbnail();
            $result['FullImage'] = $item->getFullImage();
            $result['Keyword'] = $translation->getKeyword();
            $result['Delete'] = '0';

            return $result;
        }
    }
}
