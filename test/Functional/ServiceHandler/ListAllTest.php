<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Functional\ServiceHandler;

use Test\Functional\AbstractFunctional;
use Test\Functional\Traits\HasAuthMiddleware;

class ListAllTest extends AbstractFunctional {
    use HasAuthMiddleware;

    protected function setUp() {
        $this->httpMethod = 'GET';
        $this->uri        = '/1.0/service-handler';
    }

    public function testSuccess() {
        $request    = $this->createRequest($this->createEnvironment());

        $response   = $this->process($request);
        $body       = json_decode($response->getBody(), true);

        $this->assertNotEmpty($body);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($body['status']);

        /*
         * Validates Json Schema against Json Response
         */
        $this->assertTrue(
            $this->validateSchema(
                'serviceHandler/listAll.json',
                json_decode($response->getBody())
            ),
            $this->schemaErrors
        );
    }

}
