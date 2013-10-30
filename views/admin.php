<?php
/**
 * Represents the view for the administration dashboard.
 *
 * @package   Source_Affix
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://nilambar.net
 * @copyright 2013 Nilambar Sharma
 */
?>
<div class="wrap">

    <?php screen_icon(); ?>
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <form name="form1" method="post" action="options.php">
        <?php settings_fields('sa-plugin-options-group'); ?>

        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="sa_source_posttypes">
                            <?php _e("Select Source Affix for", $this->plugin_slug); ?>
                        </label></th>
                    <td>

                        <p>
                            <input type="checkbox" name="sa_plugin_options[sa_source_posttypes][post]" value="1"<?php checked(1 == $sa_source_posttypes['post']); ?> />&nbsp;<?php _e("Post", $this->plugin_slug); ?>
                        </p>
                        <p>
                            <input type="checkbox" name="sa_plugin_options[sa_source_posttypes][page]" value="1"<?php checked(1 == $sa_source_posttypes['page']); ?> />&nbsp;<?php _e("Page", $this->plugin_slug); ?>
                        </p>
                        <?php
                        $args = array(
                            'public' => true,
                            '_builtin' => false
                        );
                        $post_types_custom = get_post_types($args);
                        if (!empty($post_types_custom))
                        {
                            foreach ($post_types_custom as $ptype)
                            {
                                ?>
                                <p>
                                    <input type="checkbox" name="sa_plugin_options[sa_source_posttypes][<?php echo $ptype ?>]" value="1"<?php checked(1 == $sa_source_posttypes[$ptype]); ?> />&nbsp;<?php echo ucfirst($ptype); ?>
                                </p>
                                <?php
                            }//end foreach
                        }//end if
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="sa_source_title">
                            <?php _e("Source Title", $this->plugin_slug); ?>
                        </label></th>
                    <td><input type="text" class="regular-text code" value="<?php echo $sa_source_title; ?>" id="sa_source_title" name="sa_plugin_options[sa_source_title]">
                        <p class="description"><?php _e("Enter title", $this->plugin_slug); ?></p></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="sa_source_style">
                            <?php _e("Source Style", $this->plugin_slug); ?>
                        </label></th>
                    <td>
                        <select id="sa_source_style" name="sa_plugin_options[sa_source_style]">
                            <option value="COMMA" <?php selected($sa_source_style, 'COMMA'); ?>><?php _e("Comma Separated", $this->plugin_slug); ?></option>
                            <option value="LIST" <?php selected($sa_source_style, 'LIST'); ?>><?php _e("List", $this->plugin_slug); ?></option>
                        </select>

                        <p class="description"><?php _e("Select source display style", $this->plugin_slug); ?></p></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="sa_source_open_style">
                            <?php _e("Source Open Style", $this->plugin_slug); ?>
                        </label></th>
                    <td>
                        <select id="sa_source_open_style" name="sa_plugin_options[sa_source_open_style]">
                            <option value="SELF" <?php selected($sa_source_open_style, 'SELF'); ?>><?php _e("Current", $this->plugin_slug); ?></option>
                            <option value="BLANK" <?php selected($sa_source_open_style, 'BLANK'); ?>>
                                <?php _e("New Page", $this->plugin_slug); ?></option>
                        </select>
                        <p class="description"><?php _e("Select how to open source page", $this->plugin_slug); ?></p></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="sa_source_position">
                            <?php _e("Source Position", $this->plugin_slug); ?>
                        </label></th>
                    <td>
                        <select id="sa_source_position" name="sa_plugin_options[sa_source_position]">
                            <option value="APPEND" <?php selected($sa_source_position, 'APPEND'); ?>>
                                <?php _e("End of the content", $this->plugin_slug); ?></option>
                            <option value="PREPEND" <?php selected($sa_source_position, 'PREPEND'); ?>>
                                <?php _e("Beginning of the content", $this->plugin_slug); ?></option>
                        </select>
                        <p class="description"><?php _e("Select position of the source", $this->plugin_slug); ?></p></td>
                </tr>
            </tbody>
        </table>	
        <?php submit_button('Save Changes'); ?>
    </form>
</div>