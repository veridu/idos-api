<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Profile\Source;

use App\Entity\Company\Credential;
use App\Entity\Profile\Source;
use App\Entity\User;
use App\Event\AbstractServiceQueueEvent;

/**
 * Created event.
 */
class Created extends AbstractServiceQueueEvent {
    /**
     * Event related User.
     *
     * @var App\Entity\User
     */
    public $user;
    /**
     * Event related Source.
     *
     * @var App\Entity\Profile\Source
     */
    public $source;
    /**
     * Event related IP Address.
     *
     * @var string
     */
    public $ipAddr;

    /**
     * Class constructor.
     *
     * @param App\Entity\User   $user
     * @param App\Entity\Source $source
     * @param string            $ipAddr
     *
     * @return void
     */
    public function __construct(Source $source, User $user, Credential $credential, string $ipAddr) {
        $this->user       = $user;
        $this->source     = $source;
        $this->credential = $credential;
        $this->ipAddr     = $ipAddr;
    }

    /**
     * {inheritdoc}.
     */
    public function getServiceHandlerPayload(array $merge = []) : array {
        if (property_exists($this->source->tags, 'token_secret')) {
            $merge['tokenSecret'] = $this->source->tags->token_secret;
        }

        return array_merge([
            'accessToken'  => $this->source->tags->access_token,
            'providerName' => $this->source->name,
            'publicKey'    => $this->credential->public,
            'sourceId'     => $this->source->id,
            'userName'     => $this->user->username
            ], $merge
        );
    }

    /**
     * Gets the event identifier.
     *
     * @return string
     **/
    public function __toString() {
        return sprintf('idos:source.%s.created', strtolower($this->source->name));
    }
}
