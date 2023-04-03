<?php

namespace Jexy\Dummy;

class Api
{
    public function __construct()
    {
        // add_action('rest_api_init', [$this, 'restApiInit']);
    }

    public function restApiInit()
    {
        register_rest_route('jexy-dummy/v1', '/hello', [
            'methods' => 'GET',
            'callback' => [$this, 'hello'],
        ]);
    }

    public function hello()
    {
        return 'Hello, world!';
    }
}
