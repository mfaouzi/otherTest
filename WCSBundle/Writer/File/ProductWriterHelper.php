<?php

namespace Aliznet\WCSBundle\Writer\File;

use Akeneo\Component\Buffer\BufferFactory;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Component\Connector\Writer\File\CsvWriter as BaseCsvWriter;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;

/**
 * CSV Product and Items Writer.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductWriterHelper extends BaseCsvWriter
{
    /**
     * @var string
     */
    protected $directoryPath = '/tmp/';
    /**
     * Assert\NotBlank(groups={"Execution"})
     * Channel.
     *
     * @var string Channel code
     */
    protected $channel;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param BufferFactory             $bufferFactory
     * @param type                      $entityManager
     * @param ChannelManager            $channelManager
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        BufferFactory $bufferFactory,
        $entityManager,
        ChannelManager $channelManager
    ) {
        parent::__construct($filePathResolver, $bufferFactory);
        $this->entityManager = $entityManager;
        $this->channelManager = $channelManager;
    }

    /**
     * Set the file path.
     *
     * @param string $directoryPath
     *
     * @return FileWriter
     */
    public function setDirectoryPath($directoryPath)
    {
        $this->directoryPath = $directoryPath;
    }

    /**
     * Get the file path.
     *
     * @return string
     */
    public function getDirectoryPath()
    {
        return $this->directoryPath;
    }

    /**
     * Set the configured channel.
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get the configured channel.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Get the file path in which to write the data.
     *
     * @return string
     */
    public function getPath()
    {
        $step_title = $this->stepExecution->getStepName();
        $job_title = explode('.', $step_title);
        $variable = constant('Aliznet\WCSBundle\Resources\Constant\Constants::'.$job_title[2]);

        return $this->directoryPath.$variable;
    }

    /**
     * @param array $items
     */
    public function write(array $items)
    {
        $products = [];

        if (!is_dir(dirname($this->getPath()))) {
            mkdir(dirname($this->getPath()), 0777, true);
        }

        foreach ($items as $item) {
            $item['product'] = $this->formatMetricsColumns($item['product']);
            $products[] = $item['product'];
        }

        $this->items = array_merge($this->items, $products);
    }

    /**
     * @param array|AbstractProductMedia $media
     */
    public function sendMedia($media)
    {
        $filePath = null;
        $exportPath = null;

        if (is_array($media)) {
            $filePath = $media['filePath'];
            $exportPath = $media['exportPath'];
        } else {
            if ('' !== $media->getFileName()) {
                $filePath = $media->getFilePath();
            }
            $exportPath = $this->mediaManager->getExportPath($media);
        }

        if (null === $filePath) {
            return;
        }

        $dirname = dirname($exportPath);
    }

    /**
     * Flush items into a csv file.
     *
     * @throws RuntimeErrorException
     */
    public function flush()
    {
        $this->writtenFiles[$this->getPath()] = basename($this->getPath());

        $uniqueKeys = $this->getAllKeys($this->items);
        $fullItems = $this->mergeKeys($uniqueKeys);
        if (false === $csvFile = fopen($this->getPath(), 'w')) {
            throw new RuntimeErrorException('Failed to open file %path%', ['%path%' => $this->getPath()]);
        }

        $header = $this->isWithHeader() ? $uniqueKeys : [];
        if (false === fputcsv($csvFile, $header, $this->delimiter)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }

        foreach ($fullItems as $item) {
            if (false === fputcsv($csvFile, $item, $this->delimiter, $this->enclosure)) {
                throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
            } elseif ($this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }
    }

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return array(
            'directoryPath' => array(
                'options' => array(
                    'label' => 'aliznet_wcs_export.export.directoryPath.label',
                    'help'  => 'aliznet_wcs_export.export.directoryPath.help',
                ),
            ),
            'delimiter' => array(
                'options' => array(
                    'label' => 'pim_base_connector.export.delimiter.label',
                    'help'  => 'pim_base_connector.export.delimiter.help',
                ),
            ),
            'enclosure' => array(
                'options' => array(
                    'label' => 'pim_base_connector.export.enclosure.label',
                    'help'  => 'pim_base_connector.export.enclosure.help',
                ),
            ),
            'withHeader' => array(
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_base_connector.export.withHeader.label',
                    'help'  => 'pim_base_connector.export.withHeader.help',
                ),
            ),
                )
        ;
    }

    /**
     * Add channel code to metric attributes header columns.
     *
     * @param array $item
     *
     * @return array
     */
    protected function formatMetricsColumns($item)
    {
        $attributeEntity = $this->entityManager->getRepository('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $attributes = $attributeEntity->getNonIdentifierAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getBackendType() == 'metric') {
                if (array_key_exists($attribute->getCode(), $item)) {
                    $item[$attribute->getCode().'-'.$this->getChannel()] = $item[$attribute->getCode()];
                    unset($item[$attribute->getCode()]);
                }
            }
        }

        return $item;
    }

    /**
     * Remove all column of attributes with type media.
     *
     * @param array $item
     *
     * @return array
     */
    protected function removeMediaColumns($item)
    {
        $attributeEntity = $this->entityManager->getRepository('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $mediaAttributesCodes = $attributeEntity->findMediaAttributeCodes();
        foreach ($mediaAttributesCodes as $mediaAttributesCode) {
            if (array_key_exists($mediaAttributesCode, $item)) {
                unset($item[$mediaAttributesCode]);
            }
        }

        return $item;
    }

    /**
     * Get a set of all keys inside arrays.
     *
     * @param array $items
     *
     * @return array
     */
    protected function getAllKeys(array $items)
    {
        $intKeys = [];
        foreach ($items as $itemss) {
            foreach ($itemss as $item) {
                $intKeys[] = array_keys($item);
            }
        }
        if (0 === count($intKeys)) {
            return [];
        }
        $mergedKeys = call_user_func_array('array_merge', $intKeys);

        return array_unique($mergedKeys);
    }

    /**
     * Merge the keys in arrays.
     *
     * @param array $uniqueKeys
     *
     * @return array
     */
    protected function mergeKeys($uniqueKeys)
    {
        $uniqueKeys = array_fill_keys($uniqueKeys, '');
        $fullItems = [];
        foreach ($this->items as $itemss) {
            foreach ($itemss as $item) {
                $fullItems[] = array_merge($uniqueKeys, $item);
            }
        }

        return $fullItems;
    }
}
