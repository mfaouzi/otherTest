<?php

namespace Aliznet\WCSBundle\Reader\Doctrine;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel as ChannelConstraint;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductReaderHelper.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ItemReaderHelper extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /**
     * @var int
     */
    protected $limit = 10;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Execution"})
     * @ChannelConstraint
     */
    protected $channel;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var AbstractQuery
     */
    protected $query;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var null|int[]
     */
    protected $ids = null;

    /**
     * @var ArrayIterator
     */
    protected $products;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;

    /**
     * @var CompletenessManager
     */
    protected $completenessManager;

    /**
     * @var MetricConverter
     */
    protected $metricConverter;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var bool
     */
    protected $missingCompleteness;

    /**
     * @var date
     */
    protected $exportFrom = '1970-01-01 01:00:00';

    /**
     * @var string
     */
    protected $batchExportID = 1;

    /**
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * @var bool
     */
    protected $isComplete = true;

    /**
     * @var localeRepository
     */
    protected $localeRepository;

    /**
     * @var string
     */
    protected $language;

    /**
     * @param ProductRepositoryInterface $repository
     * @param ChannelManager             $channelManager
     * @param type                       $localeClass
     * @param CompletenessManager        $completenessManager
     * @param MetricConverter            $metricConverter
     * @param EntityManager              $entityManager
     * @param type                       $missingCompleteness
     */
    public function __construct(
            ProductRepositoryInterface $repository,
            ChannelManager $channelManager,
            $localeClass,
            CompletenessManager $completenessManager,
            MetricConverter $metricConverter,
            EntityManager $entityManager,
            $missingCompleteness = true
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->channelManager = $channelManager;
        $this->completenessManager = $completenessManager;
        $this->metricConverter = $metricConverter;
        $this->products = new \ArrayIterator();
        $this->missingCompleteness = $missingCompleteness;
        $this->localeRepository = $entityManager->getRepository($localeClass);
    }

    /**
     * @param type $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * get exportFrom.
     *
     * @return string exportFrom
     */
    public function getExportFrom()
    {
        return $this->exportFrom;
    }

    /**
     * Set exportFrom.
     *
     * @param string $exportFrom exportFrom
     *
     * @return AbstractProcessor
     */
    public function setExportFrom($exportFrom)
    {
        $this->exportFrom = $exportFrom;

        return $this;
    }

    /**
     * get batchExportID.
     *
     * @return string batchExportID
     */
    public function getBatchExportID()
    {
        return $this->batchExportID;
    }

    /**
     * Set $batchExportID.
     *
     * @param string batchExportID $batchExportID
     *
     * @return AbstractProcessor
     */
    public function setBatchExportID($batchExportID)
    {
        $this->batchExportID = $batchExportID;

        return $this;
    }

    /**
     * get isEnabled.
     *
     * @return bool isEnabled
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set isEnabled.
     *
     * @param string isEnabled $isEnabled
     *
     * @return AbstractProcessor
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * get isComplete.
     *
     * @return bool isComplete
     */
    public function getIsComplete()
    {
        return $this->isComplete;
    }

    /**
     * Set isComplete.
     *
     * @param string isComplete $isComplete
     *
     * @return AbstractProcessor
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;

        return $this;
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
     * @param string $language excludedCategories
     *
     * @return AbstractProcessor
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Set query used by the reader.
     *
     * @param AbstractQuery $query
     *
     * @throws \InvalidArgumentException
     */
    public function setQuery(AbstractQuery $query)
    {
        if (!is_a($query, 'Doctrine\ORM\AbstractQuery', true)) {
            throw new \InvalidArgumentException(
            sprintf(
                    '$query must be a Doctrine\ORM\AbstractQuery instance, got "%s"', is_object($query) ? get_class($query) : $query
            )
            );
        }
        $this->query = $query;
    }

    /**
     * Get query to execute.
     *
     * @return AbstractQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return product
     */
    public function read()
    {
        $product = null;

        if (!$this->products->valid()) {
            $this->products = $this->getNextProducts();
        }

        if (null !== $this->products) {
            $product = $this->products->current();
            $this->products->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        if (null !== $product) {
            $this->metricConverter->convert($product, $this->channel);
        }

        return $product;
    }

    /**
     * @return arrayiterator
     */
    public function initialize()
    {
        $this->query = null;
        $this->entityManager->clear();
        $this->ids = null;
        $this->offset = 0;
        $this->products = new \ArrayIterator();
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param int $limit
     *
     * @return ORMProductReader
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
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
     * @return array
     */
    public function getConfigurationFields()
    {
        return array(
            'channel' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help',
                ),
            ),
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
            'exportFrom' => array(
                'required' => false,
                'options'  => array(
                    'help'  => 'aliznet_wcs_export.export.exportFrom.help',
                    'label' => 'aliznet_wcs_export.export.exportFrom.label',
                ),
            ),
            'isEnabled' => array(
                'type'     => 'switch',
                'required' => false,
                'options'  => array(
                    'help'  => 'aliznet_wcs_export.export.isEnabled.help',
                    'label' => 'aliznet_wcs_export.export.isEnabled.label',
                ),
            ),
            'isComplete' => array(
                'type'     => 'switch',
                'required' => false,
                'options'  => array(
                    'help'  => 'aliznet_wcs_export.export.isComplete.help',
                    'label' => 'aliznet_wcs_export.export.isComplete.label',
                ),
            ),
        );
    }

    /**
     * Get the date use to filter the product collection.
     *
     * @return string
     */
    protected function getDateFilter()
    {
        $q = $this->entityManager->createQuery("select MAX(je.endTime) from Akeneo\Component\Batch\Model\JobExecution je where je.jobInstance = ".$this->getBatchExportID());

        $lastJobDate = $q->getOneOrNullResult();

        $date = (isset($lastJobDate[1]) && $lastJobDate[1] != null) ? $lastJobDate[1] : '1970-01-01 01:00:00';

        return ($this->getExportFrom() != '') ? $this->getExportFrom() : $date;
    }

    /**
     * Get next products batch from DB.
     *
     * @return \ArrayIterator
     */
    protected function getNextProducts()
    {
        $this->entityManager->clear();
        $products = null;

        if (null === $this->ids) {
            $this->ids = $this->getIds();
        }

        $currentIds = array_slice($this->ids, $this->offset, $this->limit);

        if (!empty($currentIds)) {
            $items = $this->repository->findByIds($currentIds);
            $products = new \ArrayIterator($items);
            $this->offset += $this->limit;
        }

        return $products;
    }

    /**
     * Get ids of products which are completes and in channel.
     *
     * @return array
     */
    protected function getIds()
    {
        if (!is_object($this->channel)) {
            $this->channel = $this->channelManager->getChannelByCode($this->channel);
        }

        if ($this->missingCompleteness) {
            $this->completenessManager->generateMissingForChannel($this->channel);
        }

        $this->query = $this->ALIZNETBuildByChannelAndCompleteness($this->channel, $this->getIsComplete());

        $rootAlias = current($this->query->getRootAliases());
        $rootIdExpr = sprintf('%s.id', $rootAlias);

        $from = current($this->query->getDQLPart('from'));

        $this->query
                ->select($rootIdExpr)
                ->resetDQLPart('from')
                ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
                ->andWhere(
                        $this->query->expr()->orX(
                                $this->query->expr()->gte($from->getAlias().'.updated', ':updated')
                        )
                )
                ->setParameter('updated', $this->getDateFilter())
                ->setParameter('enabled', $this->getIsEnabled())
                ->groupBy($rootIdExpr);

        $results = $this->query->getQuery()->getArrayResult();

        return array_keys($results);
    }

    /**
     * Get product collection by channel and completness.
     *
     * @param type $channel
     * @param type $isComplete
     *
     * @return type
     */
    protected function ALIZNETBuildByChannelAndCompleteness($channel, $isComplete)
    {
        $scope = $channel->getCode();

        $qb = $this->repository->buildByScope($scope);

        $rootAlias = $qb->getRootAlias();

        if ($isComplete == 1) {
            $complete = $qb->expr()->eq('pCompleteness.ratio', '100');
        } else {
            $complete = $qb->expr()->lte('pCompleteness.ratio', '100');
        }

        $expression = 'pCompleteness.product = '.$rootAlias.' AND '.$complete.' AND '.$qb->expr()->eq('pCompleteness.channel', $channel->getId());

        $rootEntity = current($qb->getRootEntities());
        $completenessMapping = $this->entityManager->getClassMetadata($rootEntity)->getAssociationMapping('completenesses');
        $completenessClass = $completenessMapping['targetEntity'];
        $qb->innerJoin($completenessClass, 'pCompleteness', 'WITH', $expression);

        $treeId = $channel->getCategory()->getId();
        $expression = $qb->expr()->eq('pCategory.root', $treeId);
        $qb->innerJoin($rootAlias.'.categories', 'pCategory', 'WITH', $expression);

        return $qb;
    }
}
