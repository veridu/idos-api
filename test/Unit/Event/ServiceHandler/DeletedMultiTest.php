<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Event\ServiceHandler;

use App\Entity\ServiceHandler;
use App\Event\ServiceHandler\DeletedMulti;
use Illuminate\Support\Collection;
use Jenssegers\Optimus\Optimus;
use Test\Unit\AbstractUnit;

class DeletedMultiTest extends AbstractUnit {
    public function testConstruct() {
        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serviceHandlers = [];
        for($i = 0; $i < 5; $i++)
            $serviceHandlers[] = new ServiceHandler([], $optimus);

        $collection = new Collection($serviceHandlers);

        $deleted = new DeletedMulti($collection);

        $this->assertSame($collection, $deleted->serviceHandlers);
    }
}
