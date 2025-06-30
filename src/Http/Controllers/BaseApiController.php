<?php

namespace Botble\Api\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;

/**
 * Base API Controller for all API endpoints
 *
 * This controller serves as the base for all API controllers.
 * API enabled/disabled state is handled by ApiEnabledMiddleware,
 * so individual controllers don't need to check this.
 */
class BaseApiController extends BaseController
{
    // Base functionality for API controllers
    // API enabled check is handled by middleware
}
