<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Yotpo\Yotpo\Api\Data\QueueInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '3.0.0', '<')) {
            /**
             * Create table 'yotpo_queue'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable(QueueInterface::TABLE_NAME))
                ->addColumn(
                    QueueInterface::QUEUE_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Queue Id'
                )
                ->addColumn(
                    QueueInterface::STATUS,
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->addColumn(
                    QueueInterface::ENTITY_TYPE,
                    Table::TYPE_TEXT,
                    20,
                    ['nullable' => false],
                    'Entity Type'
                )
                ->addColumn(
                    QueueInterface::ENTITY_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Entity Id'
                )
                ->addColumn(
                    QueueInterface::CREATED_AT,
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    QueueInterface::UPDATED_AT,
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->addColumn(
                    QueueInterface::MESSAGE,
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Message'
                )
                ->addIndex($setup->getIdxName(QueueInterface::TABLE_NAME, QueueInterface::STATUS), QueueInterface::STATUS)
                ->setComment('Yotpo Order Queue');

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}