<?php

namespace Jexy\Dummy;

class Plugin
{
    public $admin;
    public $public;
    public $api;

    public $page;
    public $screen;
    /**
     * Plugin instance property
     *
     * @var Plugin|null
     */
    private static $instance;

    /**
     * Plugin instantiation method
     *
     * @return Plugin
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        if (is_multisite()) {
            $this->page = 'settings.php?page=jexy-dummy';
            $this->screen = 'settings_page_jexy-dummy';
        } else {
            $this->page = 'options-general.php?page=jexy-dummy';
            $this->screen = 'settings_page_jexy-dummy';
        }

        $this->admin = new WordPressAdmin();
        $this->public = new WordPressPublic();
        $this->api = new Api();
    }

    public function addActionsAndFilters()
    {
        add_action('deactivate_plugin', ['Lifecycle', 'onDeactivation']);
        add_action('rest_api_init', [$this->api, 'restApiInit']);
    }

    public function init()
    {
        // do nothing
    }
}
