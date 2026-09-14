<?php
/*
Plugin Name: Hide Editor For Page Templates
Plugin URI: https://github.com/Rupashdas/hide-editor-for-page-templates
Description: Hide classic editor or gutenberg editor support from specific page templates or all, optionally restricted to selected user roles.
Version: 1.1.0
Author: Devrupash
Author URI: https://devrupash.com
License: GPLv2 or later
Text Domain: hide-editor-for-page-templates
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HIDE_EDITOR_VERSION', '1.1.0' );

class HideEditor {
	private $hide_editor_options;
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'hide_editor_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'hide_editor_page_init' ) );
        add_action( 'admin_init', array($this, 'hide_editor_hide_editor') );
        add_action( 'admin_enqueue_scripts', array( $this, 'hide_editor_assets'));
        add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array($this, 'hide_editor_settings_link') );
        $this->hide_editor_options = get_option( 'hide_editor_page_templates' );
	}
    public function hide_editor_settings_link($links){
        $hide_editor_new_link = sprintf("<a href='%s'>%s</a>", "options-general.php?page=hide-editor", __("Settings", "hide-editor-for-page-templates" ));
        $links[] = $hide_editor_new_link;
        return $links;
    }
    public function hide_editor_assets($hook){
        if ( $hook !== 'settings_page_hide-editor' ) {
            return;
        }
        wp_enqueue_style( 'select2', plugin_dir_url(__FILE__ ).'css/select2.min.css', array(), '4.1.0', 'all');
        wp_enqueue_script( 'select2', plugin_dir_url(__FILE__ ).'js/select2.min.js', array('jquery'), '4.1.0', true );
        wp_enqueue_script( 'hide_editor', plugin_dir_url(__FILE__ ).'js/hide-editor.js', array('jquery', 'select2'), HIDE_EDITOR_VERSION, true );
    }
	public function hide_editor_add_plugin_page() {
        add_options_page(
            __("Hide Editor", 'hide-editor-for-page-templates'),
			__("Hide Editor", 'hide-editor-for-page-templates'),
			'manage_options',
			'hide-editor',
			array( $this, 'hide_editor_create_admin_page' )
		);
	}
	public function hide_editor_create_admin_page() {
		 ?>
		<div class="wrap">
			<form method="post" action="options.php">
				<?php
					settings_fields( 'hide_editor_option_group' );
					do_settings_sections( 'hide-editor-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }
	public function hide_editor_page_init() {
		register_setting(
			'hide_editor_option_group',
			'hide_editor_page_templates',
			array( $this, 'hide_editor_sanitize' )
		);
		add_settings_section(
			'hide_editor_setting_section',
			__("Settings", 'hide-editor-for-page-templates'),
			array( $this, 'hide_editor_section_info' ),
			'hide-editor-admin'
		);
        add_settings_field(
			'page_templates',
			__('Select page Template', 'hide-editor-for-page-templates'),
			array( $this, 'page_templates_select' ),
			'hide-editor-admin',
			'hide_editor_setting_section'
		);
        add_settings_field(
            'user_roles',
            __('Restrict to User Roles', 'hide-editor-for-page-templates'),
            array( $this, 'user_roles_select' ),
            'hide-editor-admin',
            'hide_editor_setting_section'
        );
	}
	public function hide_editor_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['page_templates'] ) ) {
			$sanitary_values['page_templates'] = array_map( 'sanitize_text_field', (array) $input['page_templates'] );
		}
        if ( isset( $input['roles'] ) ) {
            $editable_roles = array_keys( get_editable_roles() );
            $sanitary_values['roles'] = array_values( array_intersect( array_map( 'sanitize_text_field', (array) $input['roles'] ), $editable_roles ) );
        }

		return $sanitary_values;
	}
	public function hide_editor_section_info() {
	}
	public function page_templates_select() {
        $hide_editor_template_files = $this->hide_editor_page_templates();
        if( count($hide_editor_template_files)>0 ):
		?>
        <select class="page-templates" name="hide_editor_page_templates[page_templates][]" id="page_templates" multiple>
			<?php foreach($hide_editor_template_files as $template_file => $template_name):
                $selected = (isset( $this->hide_editor_options['page_templates'] ) && in_array($template_file, $this->hide_editor_options['page_templates'])) ? 'selected' : '' ;
                ?>
                <option value="<?php echo esc_attr($template_file ); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($template_name); ?></option>
            <?php endforeach; ?>
		</select> <?php
        else:
            esc_html_e( "There is no page template in the current theme", "hide-editor-for-page-templates" );
        endif;
	}

    public function user_roles_select() {
        $editable_roles = get_editable_roles();
        ?>
        <select class="user-roles" name="hide_editor_page_templates[roles][]" id="hide_editor_roles" multiple>
            <?php foreach ( $editable_roles as $role_slug => $role_info ):
                $selected = ( isset( $this->hide_editor_options['roles'] ) && in_array( $role_slug, $this->hide_editor_options['roles'] ) ) ? 'selected' : '';
                ?>
                <option value="<?php echo esc_attr( $role_slug ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( translate_user_role( $role_info['name'] ) ); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e( 'Leave empty to hide the editor for all users. Select one or more roles to only hide it for those roles.', 'hide-editor-for-page-templates' ); ?></p>
        <?php
    }

    public function hide_editor_page_templates(){
        return wp_get_theme()->get_page_templates( null, 'page');
    }
    function hide_editor_hide_editor() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only: only used to look up the current post ID for a display decision, no data is written.
        $page_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
        if ( $page_id ) {
            $disabled_IDs = array();
			if(isset($this->hide_editor_options['page_templates']) && count($this->hide_editor_options['page_templates'])>0){
				foreach($this->hide_editor_options['page_templates'] as $hide_editor_page_template){
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Admin-only lookup limited to the small number of templates selected in the plugin's own settings, not a user-facing hot path.
					$pages = get_pages( array(
						'meta_key' => '_wp_page_template',
						'meta_value' => $hide_editor_page_template,
					) );
					foreach($pages as $page){
						$pageId = $page->ID;
						array_push($disabled_IDs, $pageId);
					}
				}
			}
            if ( in_array( $page_id, $disabled_IDs ) && $this->hide_editor_current_user_matches_role() ) {
                remove_post_type_support('page', 'editor');
            }
        }
    }

    private function hide_editor_current_user_matches_role() {
        if ( empty( $this->hide_editor_options['roles'] ) ) {
            return true;
        }
        $current_user = wp_get_current_user();
        return (bool) array_intersect( (array) $current_user->roles, $this->hide_editor_options['roles'] );
    }
}
if ( is_admin() ){
	$hide_editor = new HideEditor();
}
