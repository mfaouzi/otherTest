<?php

namespace Aliznet\WCSBundle\Processor;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor as BaseTransformerProcessor;
use Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Category with multi language Processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class CategoryMultiLangProcessor extends BaseTransformerProcessor
{
    /**
       * @var type 
       */
      protected $localeHelper;

      /**
       * @var type 
       */
      protected $localeRepository;

      /**
       * @var string
       */
      protected $language;

    public function __construct(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        EntityTransformerInterface $transformer,
        ManagerRegistry $managerRegistry,
        LocaleHelper $localeHelper,
        $class
        ) {
        $this->localeHelper = $localeHelper;
        $this->localeRepository = $managerRegistry->getRepository($class);
        parent::__construct($validator, $translator, $transformer, $managerRegistry, $class);
    }
    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return parent::getConfigurationFields();
    }

    /**
     * @param Category $item
     *
     * @return array
     */
    public function process($item)
    {
        $result = array();
        if (null != $item->getParentCode()) {
            $result['GroupIdentifier'] = $item->getCode();
            if ($item->getParent()->getParentCode() === null) {
                $result['TopGroup'] = 'true';
                $result['ParentGroupIdentifier'] = '';
            } else {
                $result['ParentGroupIdentifier'] = $item->getParentCode();
            }
            $result['Sequence'] = '1';

            foreach ($this->getLanguages() as $language) {
                $item->setLocale($language);
                $translation = $item->getTranslation();
                $languagePrefix = explode('_', $language)[0];
                $result[$this->localeHelper->getLocaleLabel($languagePrefix, 'en_US').'Name'] = $translation->getLabel();
                $result[$this->localeHelper->getLocaleLabel($languagePrefix, 'en_US').'ShortDescription'] = $translation->getDescription();
                $result[$this->localeHelper->getLocaleLabel($languagePrefix, 'en_US').'LongDescription'] = $translation->getLongDescription();
            }
            $result['Delete'] = '0';

            return $result;
        }
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
}
