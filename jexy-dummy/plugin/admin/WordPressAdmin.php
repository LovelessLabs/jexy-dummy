<?php

namespace Jexy\Dummy;

class WordPressAdmin
{
    public function __construct()
    {
        add_action('init', [$this, 'init']);
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init()
    {
        // do nothing
    }
}
