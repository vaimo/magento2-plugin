<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Api\Data;

interface QueueInterface
{
    const TABLE_NAME = 'yotpo_queue';

    const QUEUE_ID = 'queue_id';
    const STATUS = 'status';
    const ENTITY_TYPE = 'entity_type';
    const ENTITY_ID = 'entity_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const MESSAGE = 'message';

    const STATUS_QUEUED = 0;
    const STATUS_PROCESSED = 1;
    const STATUS_FAILED = 2;
}