<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Company\Setting;

use App\Entity\Company\Setting;
use App\Event\AbstractEvent;

/**
 * Deleted event.
 */
class Deleted extends AbstractEvent {
    /**
     * Event related Setting.
     *
     * @var \App\Entity\Company\Setting
     */
    public $setting;

    /**
     * Class constructor.
     *
     * @param \App\Entity\Company\Setting $setting
     *
     * @return void
     */
    public function __construct(Setting $setting) {
        $this->setting = $setting;
    }
}
