<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Functional\Hook;

use Test\Functional\AbstractFunctional;
use Test\Functional\Traits;

class CreateNewTest extends AbstractFunctional {
    use Traits\RequiresAuth,
        Traits\RequiresIdentityToken,
        Traits\RejectsUserToken,
        Traits\RejectsCredentialToken;

    protected function setUp() {
        parent::setUp();

        $this->httpMethod = 'POST';
        $this->uri        = '/1.0/companies/veridu-ltd/credentials/4c9184f37cff01bcdc32dc486ec36961/hooks';
    }

    public function testSuccess() {
        $environment = $this->createEnvironment(
            [
                'REQUEST_URI'        => '/1.0/companies/veridu-ltd/credentials/4c9184f37cff01bcdc32dc486ec36961/hooks',
                'HTTP_CONTENT_TYPE'  => 'application/json',
                'HTTP_AUTHORIZATION' => $this->identityTokenHeader()
            ]
        );

        $request = $this->createRequest(
            $environment,
            json_encode(
                [
                    'trigger'    => 'trigger.test',
                    'url'        => 'http://127.0.0.1/hook.php',
                    'subscribed' => false
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(201, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertSame('trigger.test', $body['data']['trigger']);
        $this->assertSame('http://127.0.0.1/hook.php', $body['data']['url']);
        $this->assertFalse($body['data']['subscribed']);

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'hook/createNew.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testErrorCredentialDoesntBelongToCompany() {
        $environment = $this->createEnvironment(
            [
                'REQUEST_URI'        => '/1.0/companies/veridu-ltd/credentials/1e772b1e4d57560422e07565600aca48/hooks',
                'HTTP_CONTENT_TYPE'  => 'application/json',
                'HTTP_AUTHORIZATION' => $this->identityTokenHeader()
            ]
        );

        $request = $this->createRequest(
            $environment,
            json_encode(
                [
                    'trigger'    => 'trigger.test',
                    'url'        => 'http://test.com/example.php',
                    'subscribed' => false,
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(404, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertFalse($body['status']);

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'error.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testEmptyTrigger() {
        $environment = $this->createEnvironment(
            [
                'REQUEST_URI'        => '/1.0/companies/veridu-ltd/credentials/4c9184f37cff01bcdc32dc486ec36961/hooks',
                'HTTP_CONTENT_TYPE'  => 'application/json',
                'HTTP_AUTHORIZATION' => $this->identityTokenHeader()
            ]
        );

        $request = $this->createRequest(
            $environment,
            json_encode(
                [
                    'trigger'    => '',
                    'url'        => 'http://test.com/example.php',
                    'subscribed' => false,
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertFalse($body['status']);

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'error.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testEmptyUrl() {
        $environment = $this->createEnvironment(
            [
                'REQUEST_URI'        => '/1.0/companies/veridu-ltd/credentials/4c9184f37cff01bcdc32dc486ec36961/hooks',
                'HTTP_CONTENT_TYPE'  => 'application/json',
                'HTTP_AUTHORIZATION' => $this->identityTokenHeader()
            ]
        );

        $request = $this->createRequest(
            $environment,
            json_encode(
                [
                    'trigger'    => 'trigger.test',
                    'url'        => '',
                    'subscribed' => false,
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertFalse($body['status']);

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'error.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testInvalidUrl() {
        $environment = $this->createEnvironment(
            [
                'REQUEST_URI'        => '/1.0/companies/veridu-ltd/credentials/4c9184f37cff01bcdc32dc486ec36961/hooks',
                'HTTP_CONTENT_TYPE'  => 'application/json',
                'HTTP_AUTHORIZATION' => $this->identityTokenHeader()
            ]
        );

        $request = $this->createRequest(
            $environment,
            json_encode(
                [
                    'trigger'    => 'trigger.test',
                    'url'        => 'invalidurl',
                    'subscribed' => false,
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertFalse($body['status']);

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'error.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testInvalidTrigger() {
        $environment = $this->createEnvironment(
            [
                'REQUEST_URI'        => '/1.0/companies/veridu-ltd/credentials/4c9184f37cff01bcdc32dc486ec36961/hooks',
                'HTTP_CONTENT_TYPE'  => 'application/json',
                'HTTP_AUTHORIZATION' => $this->identityTokenHeader()
            ]
        );

        $request = $this->createRequest(
            $environment,
            json_encode(
                [
                    'trigger'    => 'trigger.test.verylongstring.verylongstring.verylongstring.verylongstring.verylongstring',
                    'url'        => 'invalidurl',
                    'subscribed' => false,
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertFalse($body['status']);

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'error.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }
}
