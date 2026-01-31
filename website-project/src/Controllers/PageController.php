<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * @deprecated This controller is obsolete in an API-first architecture. 
 * Static pages are now the responsibility of the frontend application.
 * This will be removed in a future refactoring.
 */
class PageController extends BaseController
{
    public function show($page)
    {
        $this->jsonResponse([
            'status' => 'success',
            'deprecated' => true,
            'message' => 'This endpoint is deprecated. Page content should be handled by the frontend.',
            'data' => [
                'page' => $page,
                'title' => ucwords(str_replace('-', ' ', $page))
            ]
        ]);
    }
}
