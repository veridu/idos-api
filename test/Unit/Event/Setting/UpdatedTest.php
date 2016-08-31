<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Event\Setting;

use App\Entity\Setting;
use App\Event\Setting\Updated;
use Jenssegers\Optimus\Optimus;
use Test\Unit\AbstractUnit;

class UpdatedTest extends AbstractUnit {
    public function testConstruct() {
        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting = new Setting([], $optimus);

        $updated = new Updated($setting);

        $this->assertInstanceOf(Setting::class, $updated->setting);
    }
}
