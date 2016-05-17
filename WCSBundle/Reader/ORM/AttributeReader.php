<?php

namespace Aliznet\WCSBundle\Reader\ORM;

use Doctrine\ORM\EntityManager;

/**
 * Attribute Reader.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class AttributeReader extends AttributeReaderHelper
{
    /*
     * @var localeRepository
     */

    protected $localeRepository;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var int
     */
    protected $groupNumber;

    /**
     * @var int
     */
    protected $wcs;

    /**
     * @param EntityManager $em
     * @param type          $className
     * @param type          $localeClass
     */
    public function __construct(
            EntityManager $em,
            $className,
            $localeClass
    ) {
        $this->em = $em;
        $this->className = $className;
        $this->groupNumber = 0;
        $this->localeRepository = $em->getRepository($localeClass);
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
     * Set language.
     *
     * @param string $language language
     *
     * @return AbstractProcessor
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return query
     */
    public function getQuery()
    {
        $qb = $this->em
                ->getRepository($this->className)
                ->createQueryBuilder('a')
                ->leftJoin('a.translations', 'at', 'WITH', 'at.locale='.'\''.$this->getLanguage().'\'');

        $this->queryExludedWCSFields($qb, 'a');
        $this->queryAttributes($qb, 'a');
        $this->query = $qb->getQuery();

        return $this->query;
    }

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return array_merge(array(
            'language' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->getLanguages(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'aliznet_wcs_export.export.language.label',
                    'help'     => 'aliznet_wcs_export.export.language.help',
                ),
            ),
                ), parent::getConfigurationFields());
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
