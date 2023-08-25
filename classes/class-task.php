<?php
namespace Social_Planner_Recurrent;

class Task_Settings
{
    public string $date;
    public string $time;
    public array $providers = array();
}

class Task
{

    /**
     * Post meta key to store tasks.
     *
     * @var string
     */
    const META_TASKS = '_sc_payload';

    private $post_id;
    // Declare private variables typing array
    private $post_meta;
    public array $posts = array();

    public array $posted_posts = array();
    public Task_Settings $settings;
    // Create constructor
    public function __construct($post_id = null)
    {
        if ($post_id) {
            $this->post_id = $post_id;
        } else {
            $this->post_id = get_the_ID();
        }

        if (is_null($this->post_id)) {
            throw new Exception("No post ID");
        }
        $this->get_post_meta();
        $this->get_posts();
        $this->get_posted_posts();
        $this->get_settings();
    }

    public function get_post_meta()
    {
        $this->post_meta = get_post_meta($this->post_id, self::META_TASKS, true);
    }
    private function get_posts()
    {
        $task = $this->post_meta;

        if (!$task) {
            $posts = array();
        } else {
            $task_data = json_decode($task, true);
            if (!array_key_exists("posts", $task_data)) {
                $task_data["posts"] = array();
            }
            $posts = $task_data["posts"];
        }

        $this->posts = $posts;
    }

    function get_posted_posts()
    {
        $task = $this->post_meta;

        if (!$task) {
            $posted_posts = array();
        } else {
            $task_data = json_decode($task, true);
            if(!array_key_exists("posted_posts", $task_data)){
                $task_data["posted_posts"] = array();
            }
            $posted_posts = $task_data["posted_posts"];
        }
        $this->posted_posts = $posted_posts;
    }

    private function get_settings()
    {
        $this->settings = new Task_Settings();
        $task = $this->post_meta;

        if (!$task) {
            $settings = array();
        } else {
            $task_data = json_decode($task, true);
            if (!array_key_exists("settings", $task_data)) {
                $task_data["settings"] = array();
            }
            $settings = $task_data["settings"];
        }

        if(!array_key_exists("date", $settings)){
            $settings["date"] = date("Y-m-d");
        }
        if(!array_key_exists("time", $settings)){
            $settings["time"] = date("H:i");
        }
        if(!array_key_exists("providers", $settings)){
            $settings["providers"] = array();
        }
        $this->settings->date = $settings["date"];
        $this->settings->time = $settings["time"];
        $this->settings->providers = $settings["providers"];

    }

    public function get_next_publish()
    {
        $posts = $this->posts;
        if (count($posts) == 0) {
            return false;
        }
        $next_post = array_shift(array_values($posts));
        return $next_post;
    }

    public function move_post_to_publish($post): bool
    {
        $posts = $this->posts;
        if (count($posts) == 0) {
            return false;
        }

        $post_index = $post["index"];
        unset($posts[$post_index]);
        $this->set_posts($posts);


        $post["executed_at"] = wp_date("Y-m-d H:i:s");
        $this->posted_posts[] = $post;

        return true;
    }

    public function set_posts(array $posts): void
    {
        $this->posts = $posts;
    }

    public function set_settings(array $settings): void
    {
        $this->settings->date = $settings["date"];
        $this->settings->time = $settings["time"];
        $this->settings->providers = $settings["providers"];
    }

    public function save(): void
    {
        $task = array(
            "posts" => $this->posts,
            "posted_posts" => $this->posted_posts,
            "settings" => $this->settings
        );
        update_post_meta($this->post_id, self::META_TASKS, json_encode($task));
    }

}
