<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Event\Feature;

use App\Entity\Feature;
use App\Event\Feature\DeletedMulti;
use Illuminate\Support\Collection;
use Jenssegers\Optimus\Optimus;
use Test\Unit\AbstractUnit;

class DeletedMultiTest extends AbstractUnit {
    public function testConstruct() {
        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $features = [];
        for($i = 0; $i < 5; $i++)
            $features[] = new Feature([], $optimus);

        $collection = new Collection($features);

        $created = new DeletedMulti($collection);

        $this->assertSame($collection, $created->features);
    }
}
