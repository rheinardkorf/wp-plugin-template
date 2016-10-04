<?php
/**
 * Plugin Name: PluginName
 * Plugin URI: http://pluginuri.com
 * Description: PluginDescription
 * Version: 0.0.0
 * Author: Rheinard Korf
 * Author URI: http://rheinardkorf.com
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: plugin-name-td
 * Domain Path: plugin-languages
 * Network: false
 *
 * @package PluginPackage
 *
 * Copyright (C) copyright_year Rheinard Korf
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Class PluginTemplateNS
 *
 * This class is responsible for setting up the autoloader of the plugin.
 *
 * NOTE: This class is outside of the PluginTemplateNS namespace.
 */
class PluginTemplateNS {

    /**
     * Plugin information.
     *
     * @var array|bool|mixed
     */
    private $info = array();

    /**
     * PluginTemplateNS constructor.
     */
    public function __construct() {

        /**
         * If not correct version of PHP, then no point in continuing.
         */
        if ( version_compare( phpversion(), '5.3', '<' ) ) {
            if ( defined( 'WP_CLI' ) ) {
                WP_CLI::warning( $this->version_fail_text() );
            } else {
                add_action( 'admin_notices', array( $this, 'version_fail' ) );
            }
            return;
        }

        $data = array(
            '__FILE__'       => __FILE__,
            'library_path'   => 'php',
            'assets_path'    => 'assets',
            'api_version'    => '1',
        );
        $data = array_merge( $data, $this->parse_header_information() );
        $this->info = $this->setup_paths( $data );

        /**
         * If paths are messed up we need to alert the admin.
         */
        if ( empty( $this->info['base_name'] ) ) {
            add_action( 'shutdown', array( $this, 'installation_fail' ) );
            return;
        }

        /**
         * Register the Autoloader.
         */
        $autoloader_path = $this->info['include_dir'] . 'class-autoloader.php';
        if ( is_readable( $autoloader_path ) ) {
            require_once $autoloader_path;
            $autoloader = 'PluginTemplateNS\Autoloader';
            $autoloader = new $autoloader();
            $autoloader->register( $this->info['include_dir'] );
        }

        /**
         * Load the plugin's text domain.
         */
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }

    /**
     * Admin notice for incorrect PHP version.
     */
    public function version_fail() {
        printf( '<div class="error"><p>%s</p></div>', esc_html( $this->version_fail_text() ) );
    }

    /**
     * Version failure error message
     *
     * @return string
     */
    private function version_fail_text() {
        return __( 'PluginName plugin error: Your version of PHP is too old to run this plugin. You must be running PHP 5.3 or higher.', 'plugin-name-td' );
    }

    /**
     * Paths not correctly setup.
     */
    public function installation_fail() {
        // Translators: This can't be translated if the plugin has an installation failure.
        $message      = esc_html( sprintf( '%s has not been properly installed. Please remove the plugin and try reinstalling.', 'PluginName' ) );
        $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );

        echo wp_kses_post( $html_message );
    }

    /* ---- Convenience Methods ---- */

    /**
     * Load the plugin's text domain.
     *
     * Look for plugin-name-td-<locale>.mo file and load it.
     *
     * e.g. plugin-name-td-en_US.mo
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'plugin-name-td', false, $this->info['languages_dir'] );
    }

    /**
     * Prevent cloning
     */
    private function __clone() {
        return;
    }

    /**
     * Prevent unserializing
     */
    private function __wakeup() {
        return;
    }

    /**
     * Parse file header information into plugin $info.
     */
    private function parse_header_information() {
        $default_headers = array(
            'name'        => 'Plugin Name',
            'plugin_uri'  => 'Plugin URI',
            'version'     => 'Version',
            'description' => 'Description',
            'author'      => 'Author',
            'author_uri'  => 'Author URI',
            'text_domain' => 'Text Domain',
            'domain_path' => 'Domain Path',
            'network'     => 'Network',
        );

        return get_file_data( __FILE__, $default_headers, 'plugin' );
    }

    /**
     * Get plugin locations and paths.
     *
     * @param array $data Plugin information.
     *
     * @return bool|mixed
     */
    private function setup_paths( $data ) {

        if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( plugin_dir_path( __FILE__ ) . basename( __FILE__ ) ) ) {
            /**
             * Normal Plugin Location
             */
            $data['location']   = 'plugins';
            $data['plugin_dir'] = plugin_dir_path( __FILE__ );
            $data['plugin_url'] = plugins_url( '/', __FILE__ );

            // Must use plugin location.
        } elseif ( defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . basename( __FILE__ ) ) ) {
            /**
             * "Must-Use" Plugin Location
             */
            $data['location']   = 'mu-plugins';
            $data['plugin_dir'] = WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR;
            $data['plugin_url'] = WPMU_PLUGIN_URL . '/';
        } else {
            return false;
        }

        $data['base_name']     = dirname( plugin_basename( __FILE__ ) );
        $data['include_dir']   = $data['plugin_dir'] . $data['library_path'] . DIRECTORY_SEPARATOR;
        $data['include_url']   = $data['plugin_url'] . $data['library_path'] . DIRECTORY_SEPARATOR;
        $data['assets_dir']    = $data['plugin_dir'] . $data['assets_path'] . DIRECTORY_SEPARATOR;
        $data['assets_url']    = $data['plugin_url'] . $data['assets_path'] . DIRECTORY_SEPARATOR;
        $data['languages_dir'] = $data['plugin_dir'] . trim( $data['domain_path'], '/' ) . DIRECTORY_SEPARATOR;
        $data['languages_url'] = $data['plugin_url'] . trim( $data['domain_path'], '/' ) . DIRECTORY_SEPARATOR;

        return $data;
    }

    /**
     * Create the primary plugin object.
     */
    public function launch_plugin() {
        /**
         * Create core plugin object.
         */
        global $plugin_name_object;
        $core = 'PluginTemplateNS\Plugin';
        $plugin_name_object = new $core( $this->info );
    }
}

/**
 * LAUNCH!
 */
$plugin_name_object_bootstrap = new PluginTemplateNS();
$plugin_name_object_bootstrap->launch_plugin();
