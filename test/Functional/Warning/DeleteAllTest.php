<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Functional\Warning;

use App\Helper\Token;
use Slim\Http\Response;
use Slim\Http\Uri;
use Test\Functional\AbstractFunctional;
use Test\Functional\Traits\HasAuthMiddleware;

class DeleteAllTest extends AbstractFunctional {
    use HasAuthMiddleware;
    /**
     * @FIXME The HasAuthCredentialToken runs a wrong credentials test
     *        but we don't generate tokens yet, so there are no wrong credentials
     *        when token generations is implemented, please fix this by uncommenting the next line
     */
    // use HasAuthCredentialToken;

    protected function setUp() {
        $this->httpMethod = 'DELETE';
        $this->token      = Token::generateCredentialToken(
            '4c9184f37cff01bcdc32dc486ec36961', // Credential id 1 public key
            '2c17c6393771ee3048ae34d6b380c5ec', // Credential id 1 private key
            '4c9184f37cff01bcdc32dc486ec36961'  // Credential id 1 public key
        );
        $this->userName = '9fd9f63e0d6487537569075da85a0c7f';

        $this->populate(
            sprintf('/1.0/profiles/%s/warnings', $this->userName),
            'POST',
            [
                'QUERY_STRING' => sprintf('credentialToken=%s', $this->token),
            ]
        );
        $this->entity = $this->getRandomEntity();
        $this->uri    = sprintf('/1.0/profiles/%s/warnings', $this->userName);
    }

    /**
     * @group lol
     */
    public function testSuccess() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'QUERY_STRING' => sprintf('credentialToken=%s', $this->token)
                ]
            )
        );
        $response = $this->process($request);

        $body = json_decode($response->getBody(), true);

        // success assertions
        $this->assertNotEmpty($body);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($body['status']);

        // refreshes the $entities prop
        $this->populate($this->uri);
        // checks if all entities were deleted
        $this->assertEquals(0, count($this->entities));

        /*
         * Validates Json Schema with Json Response
         */
        $this->assertTrue(
            $this->validateSchema(
                'feature/deleteAll.json',
                json_decode($response->getBody())
            ),
            $this->schemaErrors
        );
    }
}
