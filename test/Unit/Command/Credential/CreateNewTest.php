<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Unit\Command\Credential;

use App\Command\Company\Credential\CreateNew;
use Test\Unit\AbstractUnit;

class CreateNewTest extends AbstractUnit {
    public function testSetParameters() {
        $command = new CreateNew();
        $this->assertNull($command->name);
        $this->assertFalse($command->production);
        $this->assertNull($command->companyId);

        $this->assertInstanceOf(
            CreateNew::class,
            $command->setParameters([])
        );
        $this->assertNull($command->name);
        $this->assertFalse($command->production);
        $this->assertNull($command->companyId);

        $command->setParameters(['name' => 'a']);
        $this->assertSame('a', $command->name);
        $this->assertFalse($command->production);
        $this->assertNull($command->companyId);

        $command->setParameters(['production' => true]);
        $this->assertSame('a', $command->name);
        $this->assertTrue($command->production);
        $this->assertNull($command->companyId);

        $command->setParameters(['companyId' => 1]);
        $this->assertSame('a', $command->name);
        $this->assertTrue($command->production);
        $this->assertSame(1, $command->companyId);
    }
}
