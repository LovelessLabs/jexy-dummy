<?php

declare(strict_types=1);

namespace Jexy\Dummy;

class Lifecycle
{
    /**
     * Plugin instance property
     *
     * @var Lifecycle|null
     */
    private static $instance;

    private $pluginFile;
    private $meta = array();
    private $pluginSlug;
    private $cacheKey;
    private $cacheAllowed = true;
    public $updater;

    /**
     * Plugin instantiation method
     *
     * @return Lifecycle
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = self::init(dirname(__FILE__, 2) . '/init.php');
        }

        return self::$instance;
    }

    private function __construct(string $file)
    {
        register_activation_hook($file, [__CLASS__, 'onActivation']);
        register_deactivation_hook($file, [__CLASS__, 'onDeactivation']);

        $this->meta = get_file_data($file, [
            'Version' => 'Version',
            'TextDomain' => 'Text Domain',
            'DomainPath' => 'Domain Path',
            'GitHubRepo' => 'GitHub Repo',
            'ReleaseChannels' => 'Release Channels',
            'RepoVisibility' => 'Repo Visibility',
        ], 'plugin');

        $this->pluginFile = $file;
        $this->pluginSlug = plugin_basename($file);
        $this->cacheKey = $this->pluginSlug . '_updater';

        // add_action('plugins_loaded', [$this, 'onPluginsLoaded']);

        // all the GitHub updater logic for releases is setup like this:
        $updater = require_once(dirname(__DIR__) . '/vendor/dubya/foundation/plugin/updateViaGitHub.php');
        $this->updater = $updater($this->pluginFile);
    }

    public static function onActivation()
    {
        // do nothing
    }

    public static function onDeactivation()
    {
        // do nothing
    }

    public static function init(string $file)
    {
        self::$instance = new self($file);

        return self::$instance;
    }

    /**
     * Handle Plugin Updates
     *
     */
    public function onPluginsLoaded()
    {
        // add_filter('pre_set_site_transient_update_plugins', [$this, 'onPreSetSiteTransientUpdatePlugins']);

        // add_filter('plugins_api', [$this, 'onPluginsApi'], 20, 3);
        // add_filter('site_transient_update_plugins', [$this, 'onSiteTransientUpdatePlugins']);
        // add_action('upgrader_process_complete', [$this, 'onUpgraderProcessComplete'], 10, 2);

        add_filter('update_plugins_jexy.com', [$this, 'onUpdateJexyPlugins'], 10, 4);
    }
}
