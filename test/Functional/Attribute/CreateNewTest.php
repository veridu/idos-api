<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Functional\Attribute;

use Slim\Http\Response;
use Slim\Http\Uri;
use Test\Functional\AbstractFunctional;
use Test\Functional\Traits\HasAuthCredentialToken;
use Test\Functional\Traits\HasAuthMiddleware;

class CreateNewTest extends AbstractFunctional {
    //use HasAuthMiddleware;
    //use HasAuthCredentialToken;

    protected function setUp() {
        $this->httpMethod = 'POST';
        $this->uri        = '/1.0/profiles/fd1fde2f31535a266ea7f70fdf224079/attributes';
    }

    public function testSuccess() {
        $environment = $this->createEnvironment(
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'QUERY_STRING'      => 'credentialToken=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJlZjk3MGZmYWQxZjEyNTNhMjE4MmE4ODY2NzIzMzk5MSIsInN1YiI6IjRjOTE4NGYzN2NmZjAxYmNkYzMyZGM0ODZlYzM2OTYxIn0.oeiD9R7FlnMBiDW3UClRO39nvbMM-TTZkyedYaSysCc'
            ]
        );

        $request = $this->createRequest(
            $environment,
            json_encode([
                    'name'  => 'attribute-test',
                    'value' => 'value-test'
            ])
        );

        $response = $this->process($request);

        $body = json_decode($response->getBody(), true);

        $this->assertNotEmpty($body);

        $this->assertEquals(201, $response->getStatusCode());

        $this->assertTrue($body['status']);
        $this->assertSame('attribute-test', $body['data']['name']);
        $this->assertSame('value-test', $body['data']['value']);
        /*
         * Validates Json Schema against Json Response'
         */
        $this->assertTrue(
            $this->validateSchema(
                'attribute/createNew.json',
                json_decode($response->getBody())
            ),
                $this->schemaErrors
            );

    }
}
