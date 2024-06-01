<?php
/**
 * Plugin Name: WP Dummy Post Creator
 * Description: Creates dummy posts with content and category based on the specified options.
 * Version: 1.0
 * Author: Mayank Pandya
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dummy_Post_Creator {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_options_page('Dummy Post Creator Settings', 'Dummy Post Creator', 'manage_options', 'dummy-post-creator', array($this, 'create_admin_page'));
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h2>Dummy Post Creator Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('dummy_post_creator_settings'); ?>
                <?php do_settings_sections('dummy-post-creator'); ?>
                <?php submit_button('Create Dummy Posts'); ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting('dummy_post_creator_settings', 'dummy_post_creator_options', array($this, 'sanitize_options'));
        add_settings_section('dummy_post_creator_section', 'Dummy Post Creator Options', array($this, 'section_callback'), 'dummy-post-creator');
        add_settings_field('number_of_posts', 'Number of Posts', array($this, 'number_of_posts_callback'), 'dummy-post-creator', 'dummy_post_creator_section');
        add_settings_field('content_limit', 'Content Limit', array($this, 'content_limit_callback'), 'dummy-post-creator', 'dummy_post_creator_section');
        add_settings_field('category', 'Select Category', array($this, 'category_callback'), 'dummy-post-creator', 'dummy_post_creator_section');
    }

    public function sanitize_options($input) {
        $sanitized_input = array();
        if (isset($input['number_of_posts'])) {
            $sanitized_input['number_of_posts'] = absint($input['number_of_posts']);
        }
        if (isset($input['content_limit'])) {
            $sanitized_input['content_limit'] = absint($input['content_limit']);
        }
        if (isset($input['category'])) {
            $sanitized_input['category'] = absint($input['category']);
        }
        return $sanitized_input;
    }

    public function section_callback() {
        echo '<p>Enter the options for dummy post creation.</p>';
    }

    public function number_of_posts_callback() {
        $options = get_option('dummy_post_creator_options');
        $number_of_posts = isset($options['number_of_posts']) ? $options['number_of_posts'] : '';
        echo '<input type="number" name="dummy_post_creator_options[number_of_posts]" value="' . esc_attr($number_of_posts) . '" />';
    }

    public function content_limit_callback() {
        $options = get_option('dummy_post_creator_options');
        $content_limit = isset($options['content_limit']) ? $options['content_limit'] : '';
        echo '<input type="number" name="dummy_post_creator_options[content_limit]" value="' . esc_attr($content_limit) . '" />';
    }

    public function category_callback() {
        $categories = get_categories();
        $options = get_option('dummy_post_creator_options');
        $selected_category = isset($options['category']) ? $options['category'] : '';
        echo '<select name="dummy_post_creator_options[category]">';
        foreach ($categories as $category) {
            $selected = ($selected_category == $category->term_id) ? 'selected' : '';
            echo '<option value="' . esc_attr($category->term_id) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
        }
        echo '</select>';
    }

    public function create_dummy_posts() {
        $options = get_option('dummy_post_creator_options');
        $number_of_posts = isset($options['number_of_posts']) ? $options['number_of_posts'] : 0;
        $content_limit = isset($options['content_limit']) ? $options['content_limit'] : 100;
        $category = isset($options['category']) ? $options['category'] : 0;

        $category = ($category == 0) ? 1 : $category; // Default category

        for ($i = 0; $i < $number_of_posts; $i++) {
            $post_content = $this->generate_dummy_content($content_limit);
            $post_title = 'Dummy Post ' . ($i + 1);

            $post_data = array(
                'post_title'    => $post_title,
                'post_content'  => $post_content,
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'post',
                'post_category' => array($category)
            );

            wp_insert_post($post_data);
        }
    }

    private function generate_dummy_content($content_limit) {
        $content = get_dummy_content();
        return substr($content, 0, $content_limit);
    }
}


$dummy_post_creator = new Dummy_Post_Creator();

// Hook to create dummy posts when settings are saved
add_action('admin_init', 'dummy_post_creator_on_settings_save');

function dummy_post_creator_on_settings_save() {
    $dummy_post_creator = new Dummy_Post_Creator();
    $dummy_post_creator->create_dummy_posts();
}

function get_dummy_content() {
    return "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
}