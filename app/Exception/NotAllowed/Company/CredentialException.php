<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\NotAllowed\Company;

use App\Exception\NotAllowed;

/**
 * Credential not allowed exception.
 *
 * @apiEndpointResponse 500 schema/error.json
 */
class CredentialException extends NotAllowed {
}
