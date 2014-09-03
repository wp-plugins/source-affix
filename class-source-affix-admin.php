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

	protected $options = array();


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
		$this->options = $plugin->source_affix_get_options_array();


        // Load admin style sheet and JavaScript.
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Add the options page and menu item.
        add_action('admin_menu', array($this, 'source_affix_add_plugin_admin_menu'));

        /*
         * Add an action link pointing to the options page.
         */
        $plugin_basename = plugin_basename(plugin_dir_path(__FILE__) . 'source-affix.php');
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'source_affix_add_action_links'));

        /*
         * Define custom functionality.
         */

        // Add the post meta box to the post editor
        add_action('add_meta_boxes', array($this, 'source_affix_add_sa_metabox'));
        add_action('save_post', array($this, 'source_affix_save_sa_source'));

        add_action('admin_init', array($this, 'source_affix_plugin_register_settings'));
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
        $screen = get_current_screen();
        $options = $this -> options ;
        if ($options)
        {
            extract($options);
        }
        $available_post_types_array = array_keys($sa_source_posttypes);

        if ( in_array( $screen->id, $available_post_types_array ) ) {
            wp_enqueue_style('source-affix-admin-styles', plugins_url('css/admin.css', __FILE__), array(), Source_Affix::VERSION);
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
        $screen = get_current_screen();
        $options = $this -> options ;
        if ($options)
        {
            extract($options);
        }
        $available_post_types_array = array_keys($sa_source_posttypes);

        if ( in_array( $screen->id, $available_post_types_array ) ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'source-affix-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'), Source_Affix::VERSION);
        }

    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function source_affix_add_plugin_admin_menu()
    {

        /*
         * Add a settings page for this plugin to the Settings menu.
         */
        $this->plugin_screen_hook_suffix = add_options_page(
                __('Source Affix Settings Page', 'source-affix'), __('Source Affix', 'source-affix'), 'manage_options', 'source-affix', array($this, 'display_plugin_admin_page')
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

        include_once( 'views/admin.php' );

    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function source_affix_add_action_links($links)
    {

        return array_merge(
                array(
            'settings' => '<a href="' . admin_url('options-general.php?page=' . $this->plugin_slug) . '">' . __('Settings', 'source-affix' ) . '</a>'
                ), $links
        );
    }

    /**
     * Adds the meta box below the post content editor on the post edit dashboard.
     */
    function source_affix_add_sa_metabox()
    {
        $options = $this -> options ;
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
                        'sa_source', __('Sources', 'source-affix' ), array($this, 'source_affix_sa_source_display'), $ptype, 'normal', 'high'
                );
            }
        }
    }

    /**
     * Renders the nonce and the textarea for the notice.
     */
    function source_affix_sa_source_display($post)
    {

        wp_nonce_field(plugin_basename(__FILE__), 'sa_source_nonce');

        $source_meta = get_post_meta($post->ID, 'sa_source', true);

        $links_array = source_affix_convert_meta_to_array( $source_meta );

        echo '<ul id="list-source-link">';
        if (!empty($links_array) && is_array($links_array)) {
            foreach ($links_array as $key => $link) {
                echo '<li>';
                echo '<span class="btn-move-source-link"><i class="dashicons dashicons-sort"></i></span>';
                echo '<input type="text" name="link_title[]" value="'.esc_attr($link['title']).'"  class="regular-text code" placeholder="Enter title" />';
                echo '<input type="text" name="link_url[]" value="'.esc_url($link['url']).'"  class="regular-text code" placeholder="Enter full URL" />';
                echo '<span class="btn-remove-source-link"><i class="dashicons dashicons-no-alt"></i></span>';
                echo '</li>';
            }
        }
        else{
            // show empty first field
            echo '<li>';
            echo '<span class="btn-move-source-link"><i class="dashicons dashicons-sort"></i></span>';
            echo '<input type="text" name="link_title[]" value=""  class="regular-text code" placeholder="Enter title" />';
            echo '<input type="text" name="link_url[]" value=""  class="regular-text code" placeholder="Enter full URL" />';
            echo '<span class="btn-remove-source-link"><i class="dashicons dashicons-no-alt"></i></span>';
            echo '</li>';
        }
        echo '</ul>';
        echo '<a href="" class="button button-primary" id="btn-add-source-link">Add New</a>';
        return;

    }

    /**
     * Saves the source for the given post.
     *
     * @params	$post_id	The ID of the post that we're serializing
     */
    function source_affix_save_sa_source($post_id)
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


            $links_array = array();
            if ( isset($_POST['link_title']) && !empty($_POST['link_title']) ) {
                $cnt=0;
                foreach ($_POST['link_title'] as $key => $lnk) {
                    $links_array[$cnt]['title'] = $lnk;
                    $links_array[$cnt]['url'] = $_POST['link_url'][$key];
                    $cnt++;
                }
            }

            $sa_source_message = source_affix_convert_array_to_meta($links_array);

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
    public function source_affix_plugin_register_settings()
    {
        register_setting('sa-plugin-options-group', 'sa_plugin_options', array( $this, 'source_affix_plugin_options_validate') );

		add_settings_section('main_settings', __( 'Source Affix Settings', 'source-affix' ) , array($this, 'source_affix_plugin_section_text_callback'), 'source-affix-main');

		add_settings_field('sa_source_posttypes', __( 'Select Source Affix for', 'source-affix' ), array($this, 'sa_source_posttypes_callback'), 'source-affix-main', 'main_settings');
		add_settings_field('sa_source_title', __( 'Source Title', 'source-affix' ), array($this, 'sa_source_title_callback'), 'source-affix-main', 'main_settings');
		add_settings_field('sa_source_style', __( 'Source Style', 'source-affix' ), array($this, 'sa_source_style_callback'), 'source-affix-main', 'main_settings');
		add_settings_field('sa_source_open_style', __( 'Source Open Style', 'source-affix' ), array($this, 'sa_source_open_style_callback'), 'source-affix-main', 'main_settings');
		add_settings_field('sa_source_position', __( 'Source Position', 'source-affix' ), array($this, 'sa_source_position_callback'), 'source-affix-main', 'main_settings');


    }
	// validate our options
	function source_affix_plugin_options_validate($input) {

		$input['sa_source_title'] = sanitize_text_field($input['sa_source_title']);

		return $input;
	}

	function source_affix_plugin_section_text_callback() {
    	echo '<p>'.__('Change your Source Affix settings.', 'source-affix' ).'</p>';
	}

	function sa_source_posttypes_callback() {
		?>
		<p>
			<input type="checkbox" name="sa_plugin_options[sa_source_posttypes][post]" value="1"
			<?php checked(isset($this -> options['sa_source_posttypes']['post']) && 1 == $this -> options['sa_source_posttypes']['post']); ?> />&nbsp;<?php _e("Post",  'source-affix' ); ?>
		</p>
		<p>
			<input type="checkbox" name="sa_plugin_options[sa_source_posttypes][page]" value="1"
			<?php checked(isset($this -> options['sa_source_posttypes']['page']) && 1 == $this -> options['sa_source_posttypes']['page']); ?> />&nbsp;<?php _e("Page",  'source-affix' ); ?>
		</p>
		<?php
		$args = array(
			'public' => true,
			'_builtin' => false
		);
		$post_types_custom = get_post_types($args);

		if (!empty($post_types_custom)){
			foreach ($post_types_custom as $ptype){
			?>
			<p>
				<input type="checkbox" name="sa_plugin_options[sa_source_posttypes][<?php echo $ptype ?>]" value="1"
				<?php checked( isset($this -> options['sa_source_posttypes'][$ptype]) && 1 == $this -> options['sa_source_posttypes'][$ptype]); ?> />&nbsp;<?php echo ucfirst($ptype); ?>
			</p>
			<?php
			}
		}


	} // end function sa_source_posttypes_callback
	function sa_source_title_callback() {
		?>
		<input type="text" id="sa_source_title" name="sa_plugin_options[sa_source_title]" value="<?php echo $this -> options['sa_source_title'] ; ?>" />
		<p class="description"><?php _e("Enter source title",  'source-affix' ); ?></p>
		<?php
	}
	function sa_source_style_callback() {
		?>
		<select id="sa_source_style" name="sa_plugin_options[sa_source_style]">
            <option value="COMMA" <?php selected($this -> options['sa_source_style'], 'COMMA'); ?>><?php _e("Comma Separated", 'source-affix' ); ?></option>
            <option value="LIST" <?php selected($this -> options['sa_source_style'], 'LIST'); ?>><?php _e("List",  'source-affix'); ?></option>
        </select>

        <p class="description"><?php _e("Select source display style",  'source-affix'); ?></p>
		<?php
	}
	function sa_source_open_style_callback() {
		?>
		<select id="sa_source_open_style" name="sa_plugin_options[sa_source_open_style]">
            <option value="SELF" <?php selected($this -> options['sa_source_open_style'], 'SELF'); ?>><?php _e("Current", 'source-affix'); ?></option>
            <option value="BLANK" <?php selected($this -> options['sa_source_open_style'], 'BLANK'); ?>>
                <?php _e("New Page", 'source-affix'); ?></option>
        </select>
        <p class="description"><?php _e("Select how to open source page", 'source-affix' ); ?></p>
		<?php
	}
	function sa_source_position_callback() {
		?>
		<select id="sa_source_position" name="sa_plugin_options[sa_source_position]">
            <option value="APPEND" <?php selected($this -> options['sa_source_position'], 'APPEND'); ?>>
                <?php _e("End of the content", 'source-affix' ); ?></option>
            <option value="PREPEND" <?php selected($this -> options['sa_source_position'], 'PREPEND'); ?>>
                <?php _e("Beginning of the content", 'source-affix' ); ?></option>
        </select>
        <p class="description"><?php _e("Select position of the source", 'source-affix' ); ?></p>
		<?php
	}



}
