<?php

/**
 * Plugin Name:     TP Dashboard
 * Plugin URI:      https://themespond.com/plugins/tp-dashboard
 * Description:     ThemesPond Dashboard
 * Author:          ThemesPond
 * Author URI:      https://themespond.com
 * Text Domain:     tp-dashboard
 * Domain Path:     /languages/
 * Version:         1.0
 *
 * @package		TP     
 * @subpackage		Tp_Dashboard
 */

$plugin_url = plugin_dir_url(__FILE__);

 // $plugin_url = get_template_directory_uri(__FILE__) ."/required/tp-premium/"; // If license is theme

/**
 * First, we need autoload via Composer to make everything works.
 */
require trailingslashit( __DIR__ ) . '/vendor/autoload.php';

/**
 * Next, load the bootstrap file.
 */
require trailingslashit( __DIR__ ) . '/bootstrap.php';

$tp_premium = new TP\Premium\Premium('plugin','tp-dashboard', '1.0.1', $plugin_url);

