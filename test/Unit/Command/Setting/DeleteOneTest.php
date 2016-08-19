<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Command\Setting;

use App\Command\Setting\DeleteOne;
use Test\Unit\Command\AbstractCommandTest;

class DeleteOneTest extends AbstractCommandTest {
    public function testSetParameters() {
        $command = new DeleteOne();

        $this->assertInstanceOf(
            DeleteOne::class,
            $command->setParameters([])
        );

        $attributes = [
            'settingId' => [
                'property' => 'settingId',
                'policy'   => 'private'
            ]
        ];

        $this->assertSetParameters(DeleteOne::class, $attributes);

    }
}
