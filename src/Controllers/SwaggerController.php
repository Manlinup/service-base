<?php

namespace Sak\Core\Controllers;

use Illuminate\Routing\Controller;

/**
 * Class SwaggerController
 * @package Sak\Core\Controllers
 */
class SwaggerController extends Controller
{
    /**
     *
     * @SWG\Swagger(
     *   @SWG\Info(
     *     title="Swagger API",
     *     version="1.0.0"
     *   )
     * )
     */
    public function getJSON()
    {
        if (config('app.env') == 'production') {
            return response()->json([], 200);
        }
        $swagger = \Swagger\scan(app_path('Http/Controllers/'));

        return response()->json($swagger, 200);
    }
}
