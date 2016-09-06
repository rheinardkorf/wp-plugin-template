<?php
/**
 * This file handles core functionality of PluginName.
 *
 * @package PluginPackage
 */

namespace PluginTemplateNS;

/**
 * Class Plugin
 */
class Plugin extends Base {

	const SETTINGS_KEY = 'plugin-settings-key';
	const HOOK_PREFIX = 'plugin_hook_prefix';
	const MENU_PARENT = 'plugin_menu_slug';
	const MENU_PARENT_TITLE = 'PluginMenu';
	const PAGE_HOOK_BASE = 'plugin-page-slug';
	const PRIMARY_JS_SLUG = 'plugin-js-slug';
	const PRIMARY_JS_OBJECT = 'plugin-js-object';
	const PRIMARY_CSS_SLUG = 'plugin-css-slug';

	/**
	 * Array containing all the important plugin information.
	 *
	 * @var array|bool
	 */
	public $info = array();

	/**
	 * Get an instance of the plugin.
	 *
	 * @return \PluginTemplateNS\Plugin
	 */
	public static function instance() {
		global $plugin_name_object;

		return $plugin_name_object;
	}

	/**
	 * Plugin constructor.
	 *
	 * @param bool $info Plugin information.
	 */
	public function __construct( $info ) {
		/**
		 * Call parent constructor for reflection of code.
		 */
		parent::__construct();

		/**
		 * Plugin information array.
		 */
		$this->info = $info;
	}

	/**
	 * Add the Plugin menus.
	 *
	 * @action admin_menu
	 */
	public function admin_menu() {

		add_menu_page( static::MENU_PARENT_TITLE,
			static::MENU_PARENT_TITLE,
			'manage_options',
			static::MENU_PARENT,
			array( $this, 'render_main_plugin_page' ),
			$this->info['assets_url'] . 'images/admin-menu-icon.svg'
		);

		do_action( static::HOOK_PREFIX . '_submenu', static::MENU_PARENT );
	}

	/**
	 * Render the primary admin menu.
	 */
	public function render_main_plugin_page() {
		/** @todo: Render the primary plugin page */
		echo 'Hello World!'; // WPCS: xss ok.
	}

	/**
	 * Enqueue Plugin admin scripts.
	 *
	 * @action admin_enqueue_scripts
	 *
	 * @param String $hook The page hook to enqueue scripts conditionally.
	 */
	public function admin_scripts( $hook ) {

		// Other plugins can allow these scripts to be loaded.
		$load_plugin_admin_scripts = apply_filters( static::HOOK_PREFIX . '_load_admin_scripts', false );

		if ( preg_match( '/' . static::PAGE_HOOK_BASE . '/i', $hook ) || $load_plugin_admin_scripts ) {

			// Attempt to enqueue Hooks.js.
			if ( ! wp_script_is( 'hooks', $list = 'enqueued' ) ) {
				wp_enqueue_script( 'hooks', $this->info['assets_url'] . 'js/hooks.js', array(), $this->info['version'], false );
			}

			wp_enqueue_script( static::PRIMARY_JS_SLUG, $this->info['assets_url'] . 'js/plugin.js', array(
				'jquery',
				'backbone',
				'hooks',
			), $this->info['version'], false );

			wp_localize_script( static::PRIMARY_JS_SLUG, static::PRIMARY_JS_OBJECT, array() );

			do_action( static::HOOK_PREFIX . '_enqueue_scripts' );

			wp_enqueue_style( static::PRIMARY_CSS_SLUG, $this->info['assets_url'] . 'css/plugin.css', array(), $this->info['version'] );

			do_action( static::HOOK_PREFIX . '_enqueue_style' );
		}
	}

	/**
	 * Convenience method to get the plugin settings.
	 *
	 * Will get settings from site options if on a network, or regular options for single site install.
	 *
	 * @param mixed $key     Key value.
	 * @param mixed $default Value if not found.
	 *
	 * @return mixed|void
	 */
	public function get_setting( $key = false, $default = null ) {

		if ( is_multisite() ) {
			$settings = get_site_option( static::SETTINGS_KEY, array() );
		} else {
			$settings = get_option( static::SETTINGS_KEY, array() );
		}

		if ( empty( $key ) ) {

			return apply_filters( static::HOOK_PREFIX . '_options_all', $settings );
		} else {

			$option = isset( $settings[ $key ] ) ? $settings[ $key ] : $default;

			// Deal with empty arrays.
			$option = is_array( $option ) && empty( $option ) && ! empty( $default ) ? $default : $option;

			return apply_filters( static::HOOK_PREFIX . '_options_' . $key, $option );
		}
	}

	/**
	 * Update a plugin setting.
	 *
	 * Uses site options if on a network or regular options for single site install.
	 *
	 * @param mixed $key   Key value.
	 * @param mixed $value New value for the setting.
	 */
	public function update_settings( $key = false, $value ) {

		$settings = $value;
		if ( is_multisite() ) {
			if ( false !== $key ) {
				$settings         = get_site_option( static::SETTINGS_KEY, array() );
				$settings[ $key ] = $value;
			}
			update_site_option( static::SETTINGS_KEY, $settings );
		} else {
			if ( false !== $key ) {
				$settings         = get_option( static::SETTINGS_KEY, array() );
				$settings[ $key ] = $value;
			}
			update_option( static::SETTINGS_KEY, $settings );
		}
	}

	/**
	 * Convenience method to get site settings.
	 *
	 * @param int   $blog_id ID for specific blog.
	 * @param mixed $key     Key value for setting.
	 * @param mixed $default Default value if not found.
	 *
	 * @return mixed|void
	 */
	public function get_site_setting( $blog_id, $key, $default = null ) {

		$settings = maybe_unserialize( get_blog_option( $blog_id, static::SETTINGS_KEY, array() ) );

		if ( empty( $key ) ) {

			return apply_filters( static::HOOK_PREFIX . '_site_settings_all', $settings, $blog_id );
		} else {

			$option = isset( $settings[ $key ] ) ? $settings[ $key ] : $default;

			return apply_filters( static::HOOK_PREFIX . '_site_settings_' . $key, $option, $blog_id );
		}
	}

	/**
	 * Update a site's setting.
	 *
	 * @param int   $blog_id ID for specific blog.
	 * @param mixed $key     Key value for setting.
	 * @param mixed $value   New value.
	 * @param mixed $reason  Optional reason for updating the settings.
	 */
	public function update_site_settings( $blog_id, $key, $value, $reason = false ) {

		$settings = $value;

		if ( false !== $key ) {
			$settings         = maybe_unserialize( get_blog_option( $blog_id, static::SETTINGS_KEY, array() ) );
			$old_value        = isset( $settings[ $key ] ) ? $settings[ $key ] : null;
			$settings[ $key ] = $value;
		} else {
			$old_value = maybe_unserialize( get_blog_option( $blog_id, static::SETTINGS_KEY, array() ) );
		}

		update_blog_option( $blog_id, static::SETTINGS_KEY, maybe_serialize( $settings ) );

		do_action( static::HOOK_PREFIX . '_site_settings_updated_' . $key, $value, $old_value, $blog_id, $reason );
		do_action( static::HOOK_PREFIX . '_site_settings_updated', $key, $value, $old_value, $blog_id, $reason );
	}
}
