<?php

namespace Jexy\Dummy;

class WordPressAdmin
{
    public function __construct()
    {
        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        // do nothing
    }
}
