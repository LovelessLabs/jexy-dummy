<?php

/**
 * Jexy Dummy
 *
 * @package           jexy-dummy
 * @author            Clay Loveless <clay@php.net>
 * @license           MIT
 *
 * @wordpress-plugin
 * Plugin Name:       Jexy Dummy
 * Plugin URI:        https://jexy.com/
 * Description:       A do-nothing plugin for testing stuff.
 * Version:           RELEASE_VERSION
 * Author:            Loveless Labs
 * Author URI:        https://lovelesslabs.com/
 * GitHub URI:        https://github.com/LovelessLabs/jexy-dummy
 * License:           MIT
 * Requires PHP:      7.4
 * Requires at least: 6.1
 * Text Domain:       jexy-dummy
 * Domain Path:       /plugin/languages
 */

defined('ABSPATH') || exit;

$loader = require_once __DIR__ . '/vendor/autoload.php';

\Jexy\Dummy\Lifecycle::init(__FILE__);
