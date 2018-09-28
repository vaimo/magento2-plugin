<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Model;

use Yotpo\Yotpo\Api\QueueRepositoryInterface;
use Yotpo\Yotpo\Model\Spi\QueueResourceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Yotpo\Yotpo\Api\Data\QueueInterface;
use Yotpo\Yotpo\Api\Data\QueueInterfaceFactory;
use Yotpo\Yotpo\Api\Data\QueueSearchResultInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteriaBuilder;

class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var QueueResourceInterface
     */
    private $queueResource;

    /**
     * @var QueueInterfaceFactory
     */
    private $queueFactory;

    /**
     * @var QueueSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        QueueResourceInterface $queueResource,
        QueueInterfaceFactory $queueFactory,
        QueueSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {

        $this->queueResource = $queueResource;
        $this->queueFactory = $queueFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }

    public function getListQueued($entityType = '', $pageSize = 100)
    {
        $this->searchCriteriaBuilder
            ->addFilter(QueueInterface::STATUS, QueueInterface::STATUS_QUEUED)
            ->setPageSize($pageSize);

        if ($entityType) {
            $this->searchCriteriaBuilder->addFilter(QueueInterface::ENTITY_TYPE, $entityType);
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->getList($searchCriteria);
    }

    /**
     * @param QueueInterface $entity
     * @return QueueInterface
     * @throws CouldNotSaveException
     */
    public function save(QueueInterface $entity)
    {
        try {
            $this->queueResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the order queue.'), $e);
        }

        return $entity;
    }
}