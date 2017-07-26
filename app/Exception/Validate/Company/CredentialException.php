<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\Validate\Company;

use App\Exception\AppException;

/**
 * Credential validate exception.
 *
 * @apiEndpointResponse 400 schema/error.json
 */
class CredentialException extends AppException {
}
