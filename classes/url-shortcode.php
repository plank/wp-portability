<?php
/**
 * Local URL Shortcode
 *
 * @author Sean Fraser <sean@plankdesign.com>
 */
class LocalURLShortcode{
	/**
	 * Shared singleton instance.
	 * @var LocalURLShortcode
	 */
	private static $instance = null;

	/**
	 * The name of the shortcode.
	 * @var string
	 */
	protected $name = 'url';

	/**
	 * Should this shortcode inject itself into post content on save.
	 * @var boolean
	 */
	public $autoinsert = true;

	/**
	 * Parse the shortcode inline
	 * @param  array $attr    the attributes provided. Only $attr[0] will be read, which should correspond to the location of the link
	 * @param  string $content
	 * @return string
	 */
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

	/**
	 * Hook: Automatically parse the shortcode before sending to editor
	 * @param  string $content the content to review
	 * @param  int $post_id the id of the post being edited
	 * @return string
	 */
	public function _autoParse($content,$post_id){
		return $this->parse($content);
	}

	/**
	 * Hook: Automatically inject the shortcode before saving
	 * @param  array $data    new data
	 * @param  array $postarr old data
	 * @return [type]          [description]
	 */
	public function _autoInsert($data,$postarr){
		if(!$this->autoinsert){
			return $data;
		}
		$uploads_url = wp_upload_dir();
		$data['post_content'] = $this->insert($data['post_content']);
		return $data;
	}

	/**
	 * Parse only this shortcode in the provided content
	 *
	 * Will replace all instances of the shortcode with absolute links from the current install location
	 * @param  string $content
	 * @return string
	 */
	public function parse($content){
		if ( false === strpos( $content, '[' ) ) {
			return $content;
		}
		$pattern = $this->getLimitedShortcodeRegex(array($this->name));
		$result = preg_replace_callback( "/$pattern/s", 'do_shortcode_tag', $content );
		return $result;
	}

	/**
	 * Inject this shortcode into the provided content
	 *
	 * Will replace all links to the current install location with the shortcode.
	 * @param  string $content
	 * @return string
	 */
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

	/**
	 * Pull and modify the WordPress shortcode regex to only parse the specified shortcodes
	 * @param  array  $shortcodes Shortcode names to look for
	 * @return string Regex
	 */
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

	/**
	 * Private constructor
	 *
	 * registers hooks.
	 */
	private function __construct(){
		add_shortcode('url', array($this,'_render') );
		add_filter('wp_insert_post_data',array($this,'_autoInsert'),10,2);
		add_filter('content_edit_pre',array($this,'_autoParse'),10,2);
	}

	/**
	 * Get the shared instance of this class.
	 * @return LocalURLShortcode
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}
}
