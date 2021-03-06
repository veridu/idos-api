<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\NotFound\Profile;

use App\Exception\NotFound;

/**
 * Recommendation not found exception.
 *
 * @apiEndpointResponse 404 schema/error.json
 */
class RecommendationException extends NotFound {
}
