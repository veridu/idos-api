<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\NotAllowed\Profile;

use App\Exception\NotAllowed;

/**
 * Candidate not allowed exception.
 *
 * @apiEndpointResponse 500 schema/error.json
 */
class CandidateException extends NotAllowed {
}
