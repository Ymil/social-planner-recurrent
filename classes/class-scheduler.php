<?php

/**
 * Scheduler class
 *
 * @package social-planner
 * @author  Lautaro Linquimán
 */

namespace Social_Planner_Recurrent;

use Exception;
use Social_Planner_Recurrent\Metabox;
use WP_Error;

if (!defined('ABSPATH')) {
	die;
}



/**
 * Settings page class
 */
class Scheduler
{
	/**
	 * Action hook of the event.
	 *
	 * @var string
	 */
	const SCHEDULE_HOOK = 'social_planner_recurrent_event';

	/**
	 * Add required schedule hooks.
	 */
	public static function add_hooks()
	{
		add_action(self::SCHEDULE_HOOK, array(__CLASS__, 'start_task'), 10, 2);
	}

	/**
	 * Schedule tasks from metabox.
	 *
	 * @param array   $tasks   List of saintized tasks.
	 * @param WP_Post $post    Post object.
	 */
	public static function schedule_task($post)
	{
		$post_id = $post->ID;
		$post_status = $post->post_status;
		$planned = self::get_planned_time($post_id);

		$task_is_ok = apply_filters('social_planner_task_validation', true, $post_id);
		if ($task_is_ok != true) {
			self::unschedule_task($post_id);
			throw new Exception($task_is_ok);
		}

		/**
		 * Filter post statuses that can be scheduled.
		 * You can add pending or private status here.
		 *
		 * @param array $statuses List of post statuses.
		 */
		$statuses = apply_filters('social_planner_post_statuses', array('future', 'publish'));

		// Unschedule and skip tasks with invalid post status.
		if (!in_array($post_status, $statuses, true)) {
			self::unschedule_task($post_id);
			throw new Exception(__("Invalid post status", "social-planner"));
		}

		if ($planned) {
			self::reschedule_task($planned, $post_id);
		}
	}

	/**
	 * Start schedule task, this function is execute by cron.
	 *
	 * @param string $key     Task key.
	 * @param int    $post_id Post ID.
	 */
	public static function start_task($cron_args)
	{
		$post_id = $cron_args[0];
		$task = new Task($post_id);
		$providers = $task->settings->providers;
		$post_to_publish = $task->get_next_publish();
		if(!$post_to_publish) return;

		foreach ($providers as $target) {

			$adapter_post = Array(
				"attachment" => $post_to_publish['img_id'],
				"excerpt" => $post_to_publish['content']
			);

			$output = self::send_task(
				$adapter_post,
				$target,
				$post_id
			);
		}

		// $task->move_post_to_publish($post_to_publish);
		$task->save();
	}

	/**
	 * Send scheduled task.
	 *
	 * @param array  $task    Scheduled task data.
	 * @param string $target  Target provider name.
	 * @param int    $post_id Post ID.
	 */
	private static function send_task($task, $target, $post_id)
	{
		$providers = \Social_Planner\Settings::get_providers();

		if (!isset($providers[$target])) {
			return new WP_Error('config', esc_html__('Provider settings not found', 'social-planner'));
		}

		$settings = $providers[$target];

		$class = \Social_Planner\Core::get_network_class($target);

		if (!method_exists($class, 'send_message')) {
			return new WP_Error('config', esc_html__('Sending method is missed', 'social-planner'));
		}

		$message = array();

		if (!empty($task['excerpt'])) {
			$message['excerpt'] = wp_specialchars_decode($task['excerpt']);
		}

		if (!empty($task['attachment'])) {
			$message['poster_id'] = $task['attachment'];
			$message['poster'] = get_attached_file($task['attachment']);
		}

		if (!empty($task['preview'])) {
			$message['preview'] = true;
		}

		// Current post ID permalink.
		$message['link'] = esc_url(get_permalink($post_id));

		// Add post ID to message array to leave filtering right before sending.
		$message['post_id'] = $post_id;

		/**
		 * Filter sending message array.
		 *
		 * @param array  $message Sending message data.
		 * @param string $target  Target provider name.
		 * @param array  $task    Scheduled task data.
		 */
		$message = apply_filters('social_planner_prepare_message', $message, $target, $task);

		return $class::send_message($message, $settings);
	}
	/**
	 * Get scheduled time by task arguments
	 *
	 * @param string $key     Task key.
	 * @param int    $post_id Post ID.
	 *
	 * @return int
	 */
	public static function get_scheduled_time($post_id)
	{
		return wp_next_scheduled(self::SCHEDULE_HOOK, self::sanitize_args($post_id));
	}

	/**
	 * Cancel task scheduling.
	 *
	 * @param string $key     Task key.
	 * @param int    $post_id Post ID.
	 */
	public static function unschedule_task($post_id)
	{
		$scheduled = self::get_scheduled_time($post_id);

		if ($scheduled) {
			wp_unschedule_event($scheduled, self::SCHEDULE_HOOK, self::sanitize_args($post_id));
		}
	}

	/**
	 * Reschedule task or create new one.
	 *
	 * @param int    $planned Timestamp for when to next run the event.
	 * @param string $key     Task key.
	 * @param int    $post_id Post ID.
	 */
	public static function reschedule_task($planned, $post_id)
	{
		$scheduled = self::get_scheduled_time($post_id);

		if ($scheduled) {
			wp_unschedule_event($scheduled, self::SCHEDULE_HOOK, self::sanitize_args($post_id));
		}

		wp_schedule_event($planned, 'weekly', self::SCHEDULE_HOOK, self::sanitize_args($post_id));
	}

	/**
	 * Set the time in UTC at which the user planned to publish.
	 *
	 * @param array $task The task settings.
	 */
	private static function get_planned_time($post_id)
	{
		$task = new Task($post_id);
		$day_of_week = $task->settings->date;
		$time = wp_date("H:i", $task->settings->time);

		return strtotime("next $day_of_week $time");
	}


	/**
	 * Sanitize scheduled args.
	 * This method exists for casting uniform variable types.
	 *
	 * @param int    $post_id Post ID.
	 *
	 * @return array
	 */
	private static function sanitize_args($post_id)
	{
		return array((int) $post_id);
	}
}
