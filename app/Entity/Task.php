<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Entity;

use App\Extension\SecureFields;

/**
 * Task Entity.
 *
 * @apiEntity schema/user/processEntity.json
 *
 * @property int     $id
 * @property string  $name
 * @property string  $event
 * @property string  $message
 * @property bool $running
 * @property bool $success
 * @property int     $process_id
 * @property int     $created_at
 * @property int     $updated_at
 */
class Task extends AbstractEntity {
    use SecureFields;

    /**
     * {@inheritdoc}
     */
    protected $visible = ['id', 'name', 'event', 'running', 'success', 'created_at', 'updated_at'];
    /**
     * {@inheritdoc}
     */
    protected $dates = ['created_at', 'updated_at'];
    /**
     * The attributes that should be secured.
     *
     * @var array
     */
    protected $secure = ['message'];
}
