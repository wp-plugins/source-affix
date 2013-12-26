<?php

/**
 * Source Affix
 *
 * @package   Source_Affix_Admin
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://nilambar.net
 * @copyright 2013 Nilambar Sharma
 */

/**
 * Source Affix Admin class.
 *
 * @package Source_Affix_Admin
 * @author  Nilambar Sharma <nilambar@outlook.com>
 */
class Source_Affix_Admin
{

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct()
    {

        /*
         * Call $plugin_slug from public plugin class.
         *
         */
        $plugin = Source_Affix::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();

        // Load admin style sheet and JavaScript.
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Add the options page and menu item.
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));


        /*
         * Add an action link pointing to the options page.
         */
        $plugin_basename = plugin_basename(plugin_dir_path(__FILE__) . 'source-affix.php');
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_action_links'));

        /*
         * Define custom functionality.
         */

        // Add the post meta box to the post editor
        add_action('add_meta_boxes', array($this, 'add_sa_metabox'));
        add_action('save_post', array($this, 'save_sa_source'));

        add_action('admin_init', array($this, 'sa_plugin_register_settings'));
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register and enqueue admin-specific style sheet.
     *
     * @since     1.0.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_styles()
    {

        if (!isset($this->plugin_screen_hook_suffix))
        {
            return;
        }

        $screen = get_current_screen();
        if ($this->plugin_screen_hook_suffix == $screen->id)
        {
            wp_enqueue_style($this->plugin_slug . '-admin-styles', plugins_url('css/admin.css', __FILE__), array(), Source_Affix::VERSION);
        }
    }

    /**
     * Register and enqueue admin-specific JavaScript.
     *
     * @since     1.0.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_scripts()
    {

        if (!isset($this->plugin_screen_hook_suffix))
        {
            return;
        }

        $screen = get_current_screen();
        if ($this->plugin_screen_hook_suffix == $screen->id)
        {
            wp_enqueue_script($this->plugin_slug . '-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'), Source_Affix::VERSION);
        }
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu()
    {

        /*
         * Add a settings page for this plugin to the Settings menu.
         */
        $this->plugin_screen_hook_suffix = add_options_page(
                __('Source Affix Settings Page', $this->plugin_slug), __('Source Affix', $this->plugin_slug), 'manage_options', $this->plugin_slug, array($this, 'display_plugin_admin_page')
        );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page()
    {
        // Check that the user is allowed to update options
        if (!current_user_can('manage_options'))
        {
            wp_die('You do not have sufficient permissions to access this page.');
        }
        $options = get_option('sa_plugin_options');
        if ($options)
        {
            extract($options);
        }

        include_once( 'views/admin.php' );
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function add_action_links($links)
    {

        return array_merge(
                array(
            'settings' => '<a href="' . admin_url('options-general.php?page=' . $this->plugin_slug) . '">' . __('Settings', $this->plugin_slug) . '</a>'
                ), $links
        );
    }

    /**
     * Adds the meta box below the post content editor on the post edit dashboard.
     */
    function add_sa_metabox()
    {
        $options = get_option('sa_plugin_options');
        if ($options)
        {
            extract($options);
        }
        $available_post_types_array = array_keys($sa_source_posttypes);

        if (!empty($available_post_types_array))
        {
            foreach ($available_post_types_array as $ptype)
            {
                add_meta_box(
                        'sa_source', __('Sources', $this->plugin_slug), array($this, 'sa_source_display'), $ptype, 'normal', 'high'
                );
            }
        }
    }

    /**
     * Renders the nonce and the textarea for the notice.
     */
    function sa_source_display($post)
    {

        wp_nonce_field(plugin_basename(__FILE__), 'sa_source_nonce');

        $html = '<textarea id="sa-source" name="sa_source" placeholder="' . __('Enter your sources here.', $this->plugin_slug) . '" style="height:100px; width:99%;">' . esc_textarea( get_post_meta($post->ID, 'sa_source', true) ) . '</textarea><p>' . __('Enter your sources here. Title and link separated by <strong>||</strong> . Each source in separate line.', $this->plugin_slug) .' '. __('For example : ', $this->plugin_slug) . '</p>';
        $html .= '<ul style="list-style-type:none; list-style-position: inside;font-style: italic;">';
        $html .= '<li>' . __('Website Example||http://www.example.com', $this->plugin_slug) . '</li>';
        $html .= '<li>' . __('Another website||http://www.another.com', $this->plugin_slug) . '</li>';
        $html .= '</ul>';

        echo $html;
    }

    /**
     * Saves the source for the given post.
     *
     * @params	$post_id	The ID of the post that we're serializing
     */
    function save_sa_source($post_id)
    {
        if (isset($_POST['sa_source_nonce']) && isset($_POST['post_type']))
        {

            // Don't save if the user hasn't submitted the changes
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            {
                return;
            } // end if
            // Verify that the input is coming from the proper form
            if (!wp_verify_nonce($_POST['sa_source_nonce'], plugin_basename(__FILE__)))
            {
                return;
            } // end if
            // Make sure the user has permissions to post
            // Read the post message
            $sa_source_message = isset($_POST['sa_source']) ? esc_textarea( $_POST['sa_source'])  : '';


            // If the value for the source message exists, delete it first.
            if (0 == count(get_post_meta($post_id, 'sa_source')))
            {
                delete_post_meta($post_id, 'sa_source');
            } // end if
            // Update it for this post.
            update_post_meta($post_id, 'sa_source', $sa_source_message);
        } // end if
    }

    /**
     * Register plugin settings
     */
    public function sa_plugin_register_settings()
    {
        register_setting('sa-plugin-options-group', 'sa_plugin_options');
    }

}
