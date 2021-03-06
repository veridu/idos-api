<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Functional\Flag;

use Test\Functional\AbstractFunctional;
use Test\Functional\Traits;

class ListAllTest extends AbstractFunctional {
    use Traits\RequiresAuth,
        Traits\RequiresCredentialToken,
        Traits\RejectsUserToken,
        Traits\RequiresIdentityToken;

    protected function setUp() {
        parent::setUp();

        $this->httpMethod = 'GET';
        $this->uri        = '/1.0/profiles/f67b96dcf96b49d713a520ce9f54053c/flags';
    }

    public function testSuccess() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader()
                ]
            )
        );

        $response = $this->process($request);
        $body     = json_decode((string) $response->getBody(), true);

        $this->assertNotEmpty($body);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($body['status']);

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'flag/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testCreatorNameFilter() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                    'QUERY_STRING'       => 'creator:name=idOS Machine Learning'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertCount(2, $body['data']);

        foreach ($body['data'] as $flag) {
            $this->assertContains($flag['slug'], ['first-name-mismatch', 'last-name-mismatch']);
            $this->assertContains($flag['attribute'], ['first-name', 'last-name']);
        }

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'flag/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testCreatorNameFilterMultiple() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                    'QUERY_STRING'       => 'creator:name=id*'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertCount(2, $body['data']);

        foreach ($body['data'] as $flag) {
            $this->assertContains($flag['slug'], ['first-name-mismatch', 'last-name-mismatch']);
            $this->assertContains($flag['attribute'], ['first-name', 'last-name']);
        }

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'flag/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testSlugFilter() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                    'QUERY_STRING'       => 'slug=first*'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertCount(1, $body['data']);

        foreach ($body['data'] as $flag) {
            $this->assertContains($flag['slug'], ['first-name-mismatch']);
            $this->assertContains($flag['attribute'], ['first-name']);
        }

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'flag/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testSlugFilterMultiple() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                    'QUERY_STRING'       => 'slug=*name*'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertCount(2, $body['data']);

        foreach ($body['data'] as $flag) {
            $this->assertContains($flag['slug'], ['first-name-mismatch', 'last-name-mismatch']);
            $this->assertContains($flag['attribute'], ['first-name', 'last-name']);
        }

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'flag/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testOrdering() {
        $orderableKeys = [
            'name',
            'slug',
            'attribute',
            'created_at',
            'updated_at'
        ];

        foreach (['ASC', 'DESC'] as $sort) {
            foreach ($orderableKeys as $orderableKey) {
                $request = $this->createRequest(
                    $this->createEnvironment(
                        [
                            'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                            'QUERY_STRING'       => 'filter:order=' . $orderableKey . '&filter:sort=' . $sort
                        ]
                    )
                );

                $response = $this->process($request);
                $this->assertSame(200, $response->getStatusCode());

                $body = json_decode((string) $response->getBody(), true);
                $this->assertNotEmpty($body);
                $this->assertTrue($body['status']);
                $this->assertCount(2, $body['data']);

                $keys = [];

                if (strpos(':', $orderableKey) !== false) {
                    $orderableKey = explode(':', $orderableKey);
                } else {
                    foreach ($body['data'] as $entity) {
                        $keys[] = $entity[$orderableKey] ?? null;
                    }

                    $orderedKeys = $keys;

                    if ($sort === 'ASC') {
                        sort($orderedKeys);
                    } else {
                        rsort($orderedKeys);
                    }

                    if ($orderedKeys !== $keys) {
                        $this->fail('Failed asserting correctly ordered elements (' . $orderableKey . ', ' . $sort . ')');
                    }
                }

                /*
                 * Validates Response using the Json Schema.
                 */
                $this->assertTrue(
                    $this->validateSchema(
                        'flag/listAll.json',
                        json_decode((string) $response->getBody())
                    ),
                    $this->schemaErrors
                );
            }
        }
    }
}
