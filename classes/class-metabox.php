<?php

/**
 * Metabox class
 *
 * @package social-planner-recurrent
 * @author  Lautaro Linquimán
 */

namespace Social_Planner_Recurrent;

if (!defined('ABSPATH')) {
	die;
}

/**
 * Metabox class
 */
class Metabox
{
	/**
	 * Metabox ID.
	 *
	 * @var string
	 */
	const METABOX_ID = 'social-planner_recurrent-metabox';

	/**
	 * Metabox nonce field
	 */
	const METABOX_NONCE = 'social_planner_recurrent_metabox_nonce';

	/**
	 * Add hooks to handle metabox.
	 */
	public static function add_hooks()
	{

		add_action('add_meta_boxes', array(__CLASS__, 'add_metabox'));
		add_action('save_post', array(__CLASS__, 'save_metabox'), 10, 2);
	}

	/**
	 * Add plugin page in WordPress menu.
	 */
	public static function add_metabox()
	{
		/**
		 * Easy way to hide metabox.
		 *
		 * @param bool $hide_metabox Set true to hide metabox.
		 */
		$hide_metabox = apply_filters('social_planner_recurrent_hide_metabox', false);

		if ($hide_metabox) {
			return;
		}

		if (SOCIAL_PLANNER_RECURRENT_CUSTOM_POST_TYPE != get_post_type(get_the_ID())) {
			return;
		}

		add_meta_box(
			self::METABOX_ID,
			esc_html__('Social Planner', 'social-planner-recurrent'),
			array(__CLASS__, 'display_metabox'),
			SOCIAL_PLANNER_RECURRENT_CUSTOM_POST_TYPE,
			'advanced'
		);

		// Add required assets and objects.
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_styles'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
	}

	/**
	 * Display metabox.
	 */
	public static function display_metabox()
	{
		/**
		 * Fires before dashboard widget displaying.
		 */
		do_action('social_planner_recurrent_metabox_before');

		printf(
			'<p class="hide-if-js">%s</p>',
			esc_html__('This metabox requires JavaScript. Enable it in your browser settings, please.', 'social-planner-recurrent')
		);

		// $task = self::get_task(get_the_ID());
		$task = new Task();
		$posts = $task->posts;
		$settings = $task->settings;
		$date = $settings->date;
		$time = $settings->time;
		$posts = json_encode($posts);
		$posted_posts = "[]";
		$task_providers = json_encode($settings->providers);
		$settings_providers = json_encode(\Social_Planner\Settings::get_providers());
		echo <<<EOF
			<script>
				const SC_PRE_DATA_PROVIDERS=$settings_providers;
				const SC_PRE_DATA_TASKS_PROVIDERS=$task_providers;
				const SC_PRE_DATA_DATE="$date";
				const SC_PRE_DATA_TIME="$time";
				const SC_PRE_DATA_POSTS=$posts;
				const SC_PRE_DATA_POSTED_POSTS=$posted_posts;
			</script>
		EOF;
		// var_dump(\Social_Planner\Metabox::get_providers());
		echo '<div id="SC_CONTENT"></div>';

		wp_nonce_field('metabox', self::METABOX_NONCE);
	}

	/**
	 * Save metabox fields.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save_metabox(int $post_id, \WP_Post $post)
	{
		if (
			array_key_exists("sc_date", $_POST) &&
			array_key_exists("sc_time", $_POST) &&
			array_key_exists("sc_content_post", $_POST)
		) {
			$task = new Task();


			$posts_contents = $_POST['sc_content_post'];
			$posts = array();
			for ($i = 0; $i < count($posts_contents); $i++) {
				$posts[] = [
					'content' => $posts_contents[$i],
					'img_id' => $_POST['sc_img_id'][$i],
					'img_url' => $_POST['sc_img_url'][$i],
					'index' => $_POST['sc_index'][$i]
				];
			}

			$task->set_posts($posts);

			$date = $_POST['sc_date'];
			$time = $_POST['sc_time'];
			if (!array_key_exists("sc_task_providers", $_POST)){
				$_POST["sc_task_providers"] = array();
			}
			$providers = $_POST["sc_task_providers"];
			$settings = array(
				"date" => $date,
				"time" => date("H:i", strtotime($time)),
				"providers" => $providers
			);

			$task->set_settings($settings);

			$task->save();

			Scheduler::schedule_task($post);
		}
	}

	/**
	 * Enqueue metabox styles.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public static function enqueue_styles($hook_suffix)
	{
		if (!in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
			return;
		}

		$screen = get_current_screen();


		wp_enqueue_style(
			'social-planner-metabox',
			SOCIAL_PLANNER_RECURRENT_URL . '/assets/styles.css',
			array(),
			SOCIAL_PLANNER_VERSION,
			'all'
		);
	}

	/**
	 * Enqueue metabox scripts.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public static function enqueue_scripts($hook_suffix)
	{


		$post = get_post();

		wp_enqueue_media();

		wp_enqueue_script(
			'social-planner-metabox',
			SOCIAL_PLANNER_RECURRENT_URL . '/assets/main.js',
			array('wp-i18n', 'wp-data'),
			SOCIAL_PLANNER_VERSION,
			true
		);

		wp_set_script_translations(
			'social-planner-metabox',
			'social-planner-recurrent',
			plugin_dir_path(SOCIAL_PLANNER_FILE) . 'languages'
		);
	}

}
