<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Profile\Candidate;

use App\Entity\User;
use App\Event\AbstractEvent;
use Illuminate\Support\Collection;

/**
 * Deleted event for multiple candidates.
 */
class DeletedMulti extends AbstractEvent {
    /**
     * Event related User.
     *
     * @var \App\Entity\User
     */
    public $user;
    /**
     * Event related Candidates.
     *
     * @var \Illuminate\Support\Collection
     */
    public $candidates;

    /**
     * Class constructor.
     *
     * @param \App\Entity\User               $user
     * @param \Illuminate\Support\Collection $candidates
     *
     * @return void
     */
    public function __construct(User $user, Collection $candidates) {
        $this->user       = $user;
        $this->candidates = $candidates;
    }
}
