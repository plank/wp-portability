<?php
/**
 * Plugin Name: WP-Portability
 * Plugin URI: http://plankdesign.com/wp-portability
 * Description: A plugin for facilitating deploying a site across different domains and servers without breaking the paths.
 * Version: 1.0
 * Author: Sean Fraser <sean@plankdesign.com> (Plank Design Inc.)
 * Author URI: http://plankdesign.com
 * License: GPL2
 */

if(!defined('WP_PORTABILITY')){
	define('WP_PORTABILITY',__FILE__);
}

require_once dirname(__FILE__) . '/classes/nonce.php';
require_once dirname(__FILE__) . '/classes/url-shortcode.php';

class PLKPortability{
	/**
	 * Current state of settings based on.
	 * @var array
	 */
	protected $settings = array();

	protected $nonce;

	/**
	 * Default settings for new installs.
	 * @var array
	 */
	protected function getDefaultSettings(){
		return array(
			'dynamic_siteurl' => !is_multisite() && get_option('home') === get_option('siteurl'),
			'dynamic_homeurl' => !is_multisite() && get_option('home') === get_option('siteurl'),
			'relative_htaccess' => !is_multisite(),
			'url_shortcode_inject' => true
		);
	}

	/**
	 * Set the `WP_SITEURL` and `WP_HOME` constants if they have not already been set.
	 *
	 * This function will not execute if on a multisite installation or if the wordpress and site installation differ, as this might cause issues.
	 */
	public function overrideSiteURLs(){
		if(is_multisite() || get_option('home') !== get_option('siteurl')){
			return;
		}
		if(is_ssl()){
			$protocol = 'https://';
		}else{
			$protocol = 'http://';
		}
		$path = substr(ABSPATH, strlen($_SERVER['DOCUMENT_ROOT']));
		$url = "{$protocol}{$_SERVER['HTTP_HOST']}{$path}";
		if(!defined('WP_SITEURL') && $this->settings['dynamic_siteurl']){
			define('WP_SITEURL', $url);
		}
		if(!defined('WP_HOME') && $this->settings['dynamic_homeurl']){
			define('WP_HOME', $url);
		}
	}

	/**
	 * HOOK: Remove `RewriteBase` and absolute paths from htaccess
	 * @param  string $rules current fiel contents
	 * @return string modified rules
	 */
	public function relativeRewriteRules($rules){
		if(is_multisite()){
			return $rules;
		}
		if(!$this->settings['relative_htaccess']){
			return $rules;
		}

		$split_rules = explode("\n",$rules);
		foreach ($split_rules as $key => $rule) {
			if(preg_match('/^RewriteBase/i', $rule)){
				unset($split_rules[$key]);
			}else{
				$split_rules[$key] = preg_replace('/^(RewriteRule\s+\.\s+)((?!index\.php).*)(index\.php)/i', '$1$3',$rule);
			}
		}
		return implode("\n", $split_rules);
	}

	/**
	 * Default constructor
	 */
	public function __construct(){
		$this->nonce = new Nonce('portability_nonce', __FILE__);
		$this->settings = wp_parse_args( get_option('plk_wp_portability'), $this->getDefaultSettings() );
		$this->overrideSiteURLs();
		add_filter('mod_rewrite_rules',	array($this,'relativeRewriteRules'), 10,1);
		add_action('admin_menu',array($this,'addAdminPage'));
		add_action('admin_init',array($this,'addAdminPageSettings'));

		add_action('admin_enqueue_scripts',array($this,'addAdminScripts'));
		add_action('wp_ajax_purge_shortcodes',array($this,'_purgeShortcodeChunk'));
		add_action('wp_ajax_insert_shortcodes',array($this,'_insertShortcodeChunk'));

		$shortcode = LocalURLShortcode::getInstance();
		$shortcode->autoinsert = $this->settings['url_shortcode_inject'];
	}

	/**
	 * Hook: Register admin options page
	 */
	public function addAdminPage(){
		add_options_page('WP-Portability Settings', 'Portability', 'manage_options', 'portability.php', array($this,'renderAdminPage'));
	}

	/**
	 * Hook: Register the options fields group
	 */
	public function addAdminPageSettings(){
		register_setting(
			'portability_group', //option group
			'plk_wp_portability',
			array($this,'sanitizeAdminPage')
		);
	}

	/**
	 * Callback: Render the form
	 */
	public function renderAdminPage(){
		include dirname(__FILE__) .'/views/admin-settings.php';
	}

	/**
	 * Callback: Modify the form data before saving
	 * @param  array $input The values returned by the form
	 * @return array modified values
	 */
	public function sanitizeAdminPage($input){
		foreach ($this->settings as $key => $value) {
			if(array_key_exists($key, $input) && $input[$key] == '1'){
				$input[$key] = true;
			}else{
				$input[$key] = false;
			}
		}
		return $input;
	}

	/**
	 * Hook: Register scripts needed for the settings page.
	 */
	public function addAdminScripts($hook){
		if($hook == 'portability.php'){
			wp_enqueue_script(
				'plk-wp-portability',
				plugins_url( '/assets/js/portability.js', __FILE__ ),
				array(),
				false,
				true
			);
			wp_localize_script(
				'plk-wp-portability',
				'wp_ajax',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php', $scheme = 'admin' ),
					'ajaxnonce' => $this->nonce->getHash()
			) );
		}
	}

	/**
	 * Ajax: Clear shortcode instances from the database
	 *
	 * In ordert to avoid PHP request timeout, this will process in chunks. Method should be called repeatedly until all clear message is received.
	 */
	public function _purgeShortcodeChunk(){
		$data = $_POST;
		if(!$this->nonce->validateAjax('nonce')){
			wp_send_json_error('Error: invalid nonce');
			return;
		}
		if(!current_user_can( 'manage_options' )){
			wp_send_json_error('Error: invalid permissions');
			return;
		}

		global $wpdb;
		$url_shortcode = LocalURLShortcode::getInstance();

		$posts = $wpdb->get_results($wpdb->prepare("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_content REGEXP %s AND post_status <> 'inherit' LIMIT %d",'\[url [[:alpha:]]+\]',$data['limit']));
		foreach ((array)$posts as $key => $post) {
			$post->post_content = $url_shortcode->parse($post->post_content);
			$wpdb->update(
				$wpdb->posts,
				array('post_content' => $post->post_content),
				array('ID' => $post->ID),
				array('%s'),
				array('%d')
			);
			// wp_update_post( $post );
		}
		wp_send_json_success(array('count'=>count($posts)));
	}

	/**
	 * Ajax: Inject shortcode into the database
	 *
	 * In ordert to avoid PHP request timeout, this will process in chunks. Method should be called repeatedly until all clear message is received.
	 */
	public function _insertShortcodeChunk(){
		$data = $_POST;
		if(!$this->nonce->validateAjax('nonce')){
			wp_send_json_error('Error: invalid nonce');
			return;
		}
		if(!current_user_can( 'manage_options' )){
			wp_send_json_error('Error: invalid permissions');
			return;
		}

		global $wpdb;
		$url_shortcode = LocalURLShortcode::getInstance();
		$siteurl = site_url();

		$posts = $wpdb->get_results($wpdb->prepare("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_content LIKE %s LIMIT %d","%{$siteurl}%",(int)$data['limit']));

		foreach ((array)$posts as $key => $post) {
			$post->post_content = $url_shortcode->insert($post->post_content);
			$wpdb->update(
				$wpdb->posts,
				array('post_content' => $post->post_content),
				array('ID' => $post->ID),
				array('%s'),
				array('%d')
			);
		}
		wp_send_json_success(array('count'=>count($posts)));
	}

}

$plk_wp_portability = new PLKPortability();
