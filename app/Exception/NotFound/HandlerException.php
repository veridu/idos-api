<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\NotFound;

use App\Exception\NotFound;

/**
 * Handler not found exception.
 *
 * @apiEndpointResponse 500 schema/error.json
 */
class HandlerException extends NotFound {
}
