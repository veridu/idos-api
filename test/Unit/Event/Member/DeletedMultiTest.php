<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Unit\Event\Member;

use App\Entity\Company\Member;
use App\Event\Company\Member\DeletedMulti;
use Illuminate\Support\Collection;
use Jenssegers\Optimus\Optimus;
use Test\Unit\AbstractUnit;

class DeletedMultiTest extends AbstractUnit {
    public function testConstruct() {
        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $members = [];
        for ($i = 0; $i < 5; $i++) {
            $members[] = new Member([], $optimus);
        }

        $collection = new Collection($members);

        $deleted = new DeletedMulti($collection);

        $this->assertSame($collection, $deleted->members);
    }
}
