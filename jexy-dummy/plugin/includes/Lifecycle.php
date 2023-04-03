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
            'Text Domain' => 'Text Domain',
            'Domain Path' => 'Domain Path',
            'GitHub Plugin URI' => 'GitHub Plugin URI',
        ], 'plugin');

        $this->pluginFile = $file;
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
}
