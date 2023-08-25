<?php
/**
 * Plugin Name: Social Planner Reccurent
 * Plugin URI: https://github.com/antonlukin/social-planner
 * Description: This plugin automatically publishes posts from your blog to your social media accounts on Facebook, Twitter, VK.com, Telegram.
 * Domain Path: /lang
 * Author: Anton Lukin
 * Author URI: https://wpset.org
 * Requires at least: 5.3
 * Tested up to: 6.2
 * Version: 1.3.1
 *
 * Text Domain: social-planner
 *
 * @package social-planner
 * @author  Anton Lukin
 */

namespace Social_Planner_Recurrent;

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// /**
//  * Plugin version.
//  */
// define( 'SOCIAL_PLANNER_VERSION', '1.3.0' );

// /**
//  * Plugin admin menu slug.
//  */
// define( 'SOCIAL_PLANNER_SLUG', 'social-planner' );

// /**
//  * Main plugin file.
//  */
// define( 'SOCIAL_PLANNER_FILE', __FILE__ );

/**
 * Shortcut constant to the path of this file.
 */
define( 'SOCIAL_PLANNER_RECURRENT_DIR', __DIR__ );

/**
 * Plugin dir url.
 */
define( 'SOCIAL_PLANNER_RECURRENT_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Custom post type name.
 */

define( 'SOCIAL_PLANNER_RECURRENT_CUSTOM_POST_TYPE', 'sc_planned_posts' );

/**
 * Include the core plugin class.
 */
require_once SOCIAL_PLANNER_RECURRENT_DIR . '/classes/class-core.php';

/**
 * Include the metabox class.
 */
require_once SOCIAL_PLANNER_RECURRENT_DIR . '/classes/class-metabox.php';

/**
 * Include the scheduler class.
 */
require_once SOCIAL_PLANNER_RECURRENT_DIR . '/classes/class-scheduler.php';

/**
 * Include the task class.
 */
require_once SOCIAL_PLANNER_RECURRENT_DIR . '/classes/class-task.php';

/**
 * Start with core plugin method.
 */
Core::add_hooks();



function tu_funcion_despues_de_cargar_admin() {
    if($_GET['test'] == 1) {
		// Scheduler::schedule_task(get_post(1));
		// Scheduler::send_task()
		Scheduler::start_task(Array(163));
		exit;
	}
}

add_action('admin_init', '\Social_Planner_Recurrent\tu_funcion_despues_de_cargar_admin');
