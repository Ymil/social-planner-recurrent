<?php

/**
 * Core plugin class for Social Planner
 *
 * @package social-planner
 * @author  Anton Lukin
 */

namespace Social_Planner_Recurrent;

if (!defined('ABSPATH')) {
	die;
}

/**
 * Social Planner Core class
 */
class Core
{
	/**
	 * Store list of publishing networks.
	 *
	 * @var array
	 */
	private static $networks = array();

	/**
	 * Entry point of core class.
	 */
	public static function add_hooks()
	{
		add_action('init', array(__CLASS__, 'registrar_custom_post_type'));
		add_action('plugins_loaded', array(__CLASS__, 'i18n'));

		Metabox::add_hooks();
		Scheduler::add_hooks();
	}


	public static function registrar_custom_post_type()
	{
		register_post_type(SOCIAL_PLANNER_RECURRENT_CUSTOM_POST_TYPE, array(
			'labels' => array(
				'name' => 'Publicaciones Planificadas',
				'singular_name' => 'Publicación Planificada',
			),
			'public' => true,
			'exclude_from_search' => true,
			// 'has_archive' => false,
			'supports' => array('title'),
		));
	}

	/**
	 * Default datetime format.
	 */
	public static function time_format()
	{
		$format = get_option('date_format') . ' ' . get_option('time_format');

		/**
		 * Filters scheduled and sent datetime format.
		 *
		 * @param string $format Datetime format.
		 */
		return apply_filters('social_planner_time_format', $format);
	}


	/**
	 * Loads the translation files.
	 */
	public static function i18n()
	{
		load_plugin_textdomain('social-planner', false, trailingslashit(dirname(plugin_basename(SOCIAL_PLANNER_FILE))) . 'languages');
	}
}
