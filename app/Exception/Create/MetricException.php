<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\Create;

use App\Exception\AppException;

/**
 * Metric create exception.
 *
 * @apiEndpointResponse 500 schema/error.json
 */
class MetricException extends AppException {
}
