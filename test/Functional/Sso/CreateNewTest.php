<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Functional\Sso;

use Interop\Container\ContainerInterface;
use OAuth\Common\Storage\Memory;
use OAuth\OAuth2\Service\Facebook;
use Test\Functional\AbstractFunctional;

class CreateNewTest extends AbstractFunctional {
    protected function setUp() {
        $this->httpMethod = 'POST';
        $this->uri        = '/1.0/sso';

        $this->getApp()->getContainer()['ssoAuth'] = function (ContainerInterface $container) : callable {
            return function ($providerName, $key, $secret) {
                $facebookMock = $this->getMockBuilder(Facebook::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getStorage', 'request', 'service'])
                    ->getMock();

                $facebookMock->method('getStorage')
                    ->willReturn(new Memory());

                $facebookMock->method('request')
                    ->willReturn('{"id" : "123"}');

                $facebookMock->method('service')
                    ->willReturn('Facebook');

                return $facebookMock;
            };
        };
    }

    public function testSuccess() {
        $environment = $this->createEnvironment(
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
            ]
        );

        $providerName     = 'facebook';
        $credentialPubKey = '4c9184f37cff01bcdc32dc486ec36961';
        $accessToken      = 'EAAEO02ZBeZBwMBAHF5DHSVt7gIUR75zeTlUoJUOFdM6rNUNVWBZCR97GHbFgkskqIe2UKPDIPxQy2WZAAyw4gGZCX3Cllz4WfUU3xnr9jPzvPwbirhAXN26ZAR2E7vfHTsjZA5rFgbKXGaqChU1HlzL';

        $request = $this->createRequest(
            $environment, json_encode(
                [
                    'provider'     => $providerName,
                    'credential'   => $credentialPubKey,
                    'access_token' => $accessToken
                ]
            )
        );
        $response = $this->process($request);

        $this->assertSame(201, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema('sso/createNew.json', json_decode((string) $response->getBody())),
            $this->schemaErrors
        );
    }
}
