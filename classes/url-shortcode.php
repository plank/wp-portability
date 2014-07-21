<?php

class LocalURLShortcode{
	private static $instance = null;
	protected $name = 'url';
	public $autoinsert = true;

	public function _render($attr,$content){
		$uploads_url = wp_upload_dir();
		$location = count($attr)>0?$attr[0]:(!empty($content)?$content:null);
		if(empty($location)){
			return '';
		}
		switch ($location) {
			case 'theme':
				$path = get_template_directory_uri();
				break;
			case 'stylesheet':
				$path = get_stylesheet_directory_uri();
				break;
			case 'uploads':
				$path = $uploads_url['baseurl'];
				break;
			case 'plugins':
				$path = plugins_url();
				break;
			case 'site':
				$path = site_url();
				break;
			default:
				$path = '';
				break;
		}
		return $path;
	}
	public function _autoParse($content,$post_id){
		return $this->parse($content);
	}

	public function _autoInsert($data,$postarr){
		if(!$this->autoinsert){
			return $data;
		}
		$uploads_url = wp_upload_dir();
		$data['post_content'] = $this->insert($data['post_content']);
		return $data;
	}

	public function parse($content){
		if ( false === strpos( $content, '[' ) ) {
			return $content;
		}
		$pattern = $this->getLimitedShortcodeRegex(array($this->name));
		$result = preg_replace_callback( "/$pattern/s", 'do_shortcode_tag', $content );
		return $result;
	}

	public function insert($content){
		$uploads_url = wp_upload_dir();
		$paths = array(
			'theme' => get_template_directory_uri(),
			'stylesheet' => get_stylesheet_directory_uri(),
			'uploads' => $uploads_url['baseurl'],
			'plugins' => plugins_url(),
			'site' => site_url(),
		);

		foreach ($paths as $key => $location) {
			$content = str_replace($location, "[{$this->name} {$key}]", $content);
		}
		return $content;
	}

	public function getLimitedShortcodeRegex($shortcodes= array()){
		global $shortcode_tags;
		//preserve full shortcode list
		$hold = $shortcode_tags;
		//temporarily change to limited shortcode list
		$shortcode_tags = array_combine($shortcodes,$shortcodes);
		//get results for limited list
		$regex = get_shortcode_regex();
		//restore old values;
		$shortcode_tags = $hold;
		//return value
		return $regex;
	}

	private function __construct(){
		add_shortcode('url', array($this,'_render') );
		add_filter('wp_insert_post_data',array($this,'_autoInsert'),10,2);
		add_filter('content_edit_pre',array($this,'_autoParse'),10,2);
	}

	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}
}
