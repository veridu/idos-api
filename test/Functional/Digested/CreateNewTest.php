<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Functional\Digested;

use Slim\Http\Response;
use Slim\Http\Uri;
use Test\Functional\AbstractFunctional;
use Test\Functional\Traits;

class CreateNewTest extends AbstractFunctional {
    use Traits\RequiresAuth,
        Traits\RequiresCredentialToken,
        Traits\RejectsUserToken,
        Traits\RejectsCompanyToken;

    protected function setUp() {
        $this->httpMethod = 'POST';
        $this->uri        = '/1.0/profiles/fd1fde2f31535a266ea7f70fdf224079/sources/1860914067/digested';
    }

    public function testSuccess() {
        $environment = $this->createEnvironment(
            [
                'HTTP_CONTENT_TYPE'  => 'application/json',
                'HTTP_AUTHORIZATION' => $this->credentialTokenHeader()
            ]
        );

        $request = $this->createRequest(
            $environment,
            json_encode(
                [
                    'name'  => 'name-test',
                    'value' => 'value-test'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(201, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertSame('name-test', $body['data']['name']);
        $this->assertSame('value-test', $body['data']['value']);
        /*
         * Validates Json Schema against Json Response'
         */
        $this->assertTrue(
            $this->validateSchema(
                'digested/createNew.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );

    }
}
