<?php
/**
 * Plugin Name: DEPRECATED - Disciple.Tools - People Groups UI
 * Plugin URI: https://github.com/DiscipleTools/disciple-tools-people-groups
 * Description: Old plugin. Please Delete me.
 * Version:  0.3.0
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-people-groups
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$dt_people_groups_required_dt_theme_version = '1.32';

/**
 * Gets the instance of the `DT_People_Groups_Plugin` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
function dt_people_groups_plugin() {
    global $dt_people_groups_required_dt_theme_version;
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;
    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = class_exists( "Disciple_Tools" );
    if ( $is_theme_dt && version_compare( $version, $dt_people_groups_required_dt_theme_version, "<" ) ) {
        add_action( 'admin_notices', 'dt_people_groups_plugin_hook_admin_notice' );
        add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
        return false;
    }
    if ( !$is_theme_dt ){
        return false;
    }
    /**
     * Load useful function from the theme
     */
    if ( !defined( 'DT_FUNCTIONS_READY' ) ){
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }
    /*
     * Don't load the plugin on every rest request. Only those with the metrics namespace
     */
//    $is_rest = dt_is_rest();
//    if ( !$is_rest || strpos( dt_get_url_path(), 'sample' ) !== false ){
//    }
        return DT_People_Groups_Plugin::get_instance();
}
//add_action( 'after_setup_theme', 'dt_people_groups_plugin' );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class DT_People_Groups_Plugin {

    /**
     * Declares public variables
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public $token;
    public $version;
    public $dir_path = '';
    public $dir_uri = '';
    public $img_uri = '';
    public $includes_path;

    /**
     * Returns the instance.
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new dt_people_groups_plugin();
            $instance->setup();
            $instance->includes();
            $instance->setup_actions();
        }
        return $instance;
    }

    /**
     * Constructor method.
     *
     * @since  0.1
     * @access private
     * @return void
     */
    private function __construct() {
    }

    /**
     * Loads files needed by the plugin.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function includes() {
        if ( is_admin() ) {
            require_once( 'includes/admin/admin-menu-and-tabs.php' );
        }

        require_once( 'post-type/loader.php' );

//        require_once( 'includes/people-groups.php' );
    }

    /**
     * Sets up globals.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup() {

        // Main plugin directory path and URI.
        $this->dir_path     = trailingslashit( plugin_dir_path( __FILE__ ) );
        $this->dir_uri      = trailingslashit( plugin_dir_url( __FILE__ ) );

        // Plugin directory paths.
        $this->includes_path      = trailingslashit( $this->dir_path . 'includes' );

        // Admin and settings variables
        $this->token             = 'dt_people_groups_plugin';
        $this->version             = '0.1';

    }

    /**
     * Sets up main plugin actions and filters.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup_actions() {

        // Internationalize the text strings used.
        add_action( 'after_setup_theme', array( $this, 'i18n' ), 51 );
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {

        // Confirm 'Administrator' has 'manage_dt' privilege. This is key in 'remote' configuration when
        // Disciple.Tools theme is not installed, otherwise this will already have been installed by the Disciple.Tools Theme
        $role = get_role( 'administrator' );
        if ( !empty( $role ) ) {
            $role->add_cap( 'manage_dt' ); // gives access to dt plugin options
        }

    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
        delete_option( 'dismissed-dt-people-groups' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        //Take from loadTextDomain() in /disciple-tools-theme/dt-core/libraries/plugin-update-checker/Puc/v4p5/UpdateChecker.php
        // @link
        $domain = 'disciple-tools-plugin'; // this must be the same as the slug for the plugin
        $locale = apply_filters(
            'plugin_locale',
            ( is_admin() && function_exists( 'get_user_locale' ) ) ? get_user_locale() : get_locale(),
            $domain
        );

        $mo_file = $domain . '-' . $locale . '.mo';
        $path = realpath( dirname( __FILE__ ) . '/languages' );

        if ( $path && file_exists( $path ) ) {
            load_textdomain( $domain, $path . '/' . $mo_file );
        }
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'disciple-tools-plugin';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html( 'Whoah, partner!' ), '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html( 'Whoah, partner!' ), '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @since  0.1
     * @access public
     * @return null
     */
    public function __call( $method = '', $args = array() ) {
        // @codingStandardsIgnoreLine
        _doing_it_wrong( "dt_people_groups_plugin::{$method}", esc_html__( 'Method does not exist.', 'dt_people_groups_plugin' ), '0.1' );
        unset( $method, $args );
        return null;
    }
}
// end main plugin class

// Register activation hook.
register_activation_hook( __FILE__, [ 'DT_People_Groups_Plugin', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'DT_People_Groups_Plugin', 'deactivation' ] );

function dt_people_groups_plugin_hook_admin_notice() {
    global $dt_people_groups_required_dt_theme_version;
    $wp_theme = wp_get_theme();
    $current_version = $wp_theme->version;
    $message = __( "'Disciple.Tools - People Groups' plugin requires 'Disciple.Tools' theme to work. Please activate 'Disciple.Tools' theme or make sure it is latest version.", "dt_people_groups_plugin" );
    if ( $wp_theme->get_template() === "disciple-tools-theme" ){
        $message .= sprintf( esc_html__( 'Current Disciple.Tools version: %1$s, required version: %2$s', 'dt_people_groups_plugin' ), esc_html( $current_version ), esc_html( $dt_people_groups_required_dt_theme_version ) );
    }
    // Check if it's been dismissed...
    if ( ! get_option( 'dismissed-dt-people-groups', false ) ) { ?>
        <div class="notice notice-error notice-dt-people-groups is-dismissible" data-notice="dt-people-groups">
            <p><?php echo esc_html( $message );?></p>
        </div>
        <script>
            jQuery(function($) {
                $( document ).on( 'click', '.notice-dt-people-groups .notice-dismiss', function () {
                    $.ajax( ajaxurl, {
                        type: 'POST',
                        data: {
                            action: 'dismissed_notice_handler',
                            type: 'dt-people-groups',
                            security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                        }
                    })
                });
            });
        </script>
    <?php }
}


/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( !function_exists( "dt_hook_ajax_notice_handler" ) ){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST["type"] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST["type"] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}


/**
 * Check for plugin updates even when the active theme is not Disciple.Tools
 *
 * Below is the publicly hosted .json file that carries the version information. This file can be hosted
 * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
 * a template.
 * Also, see the instructions for version updating to understand the steps involved.
 * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
 */
add_action( 'plugins_loaded', function (){
    if ( is_admin() && !( is_multisite() && class_exists( "DT_Multisite" ) ) ){
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            // find the Disciple.Tools theme and load the plugin update checker.
            foreach ( wp_get_themes() as $theme ){
                if ( $theme->get( 'TextDomain' ) === "disciple_tools" && file_exists( $theme->get_stylesheet_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' ) ){
                    require( $theme->get_stylesheet_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
                }
            }
        }
        if ( class_exists( 'Puc_v4_Factory' ) ){
            $hosted_json = "https://raw.githubusercontent.com/DiscipleTools/disciple-tools-people-groups/master/version-control.json";
            Puc_v4_Factory::buildUpdateChecker(
                $hosted_json,
                __FILE__,
                'disciple-tools-people-groups'
            );

        }
    }
} );
