<?php
/**
 * Nonce Helpers
 *
 * @author Sean Fraser <sean@plankdesign.com>
 */

/**
 * Nonce Helper
 *
 * A persistent object-oriented wrapper of Wordpress's nonce API. Values are retained to facilitate validation
 * @see  http://codex.wordpress.org/WordPress_Nonces WP Nonce API
 */
class Nonce{
	/**
	 * The name HTML attribute of the nonce field
	 * This is the name returned in $_REQUEST
	 * If empty, the nonce will not render and will automatically validate
	 * @var string
	 */
	protected $name = '';

	/**
	 * A value used to seed the hash used for the value HTML attribute
	 * @var string
	 */
	protected $value = '';

	/**
	 * Default constructor
	 * @param string $name  Value to use as the field name
	 * @param string $value Value to use for the hash
	 */
	public function __construct($name='',$value=''){
		$this->setName($name);
		$this->setValue($value);
	}

	/**
	 * Retrieve the name of the nonce
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Retrieve the seed value of the nonce
	 * @return string
	 */
	public function getValue(){
		return $this->value;
	}

	/**
	 * Update the field name of the nonce
	 * Be careful when using this between rendering the nonce and validating it
	 * @param string $name The new name
	 */
	public function setName($name){
		$this->name = (string)$name;
	}

	/**
	 * Update the hash seed of the nonce
	 * Be careful when using this between rendering the nonce and validating it
	 * @param string $name The new seed
	 */
	public function setValue($value){
		$this->value = (string)$value;
	}

	/**
	 * Output the nonce HTML input field(s)
	 * @return string HTML
	 */
	public function render(){
		ob_start();
		$nonce_name = $this->getName();
		if(!empty($nonce_name)){
			wp_nonce_field($this->getValue(),$nonce_name);
		}
		return ob_get_clean();
	}

	/**
	 * Create the nonce and return its hash value
	 * @return string
	 */
	public function getHash(){
		return wp_create_nonce( $this->getValue() );
	}

	/**
	 * Check the value of $_REQUEST to see if the nonce checks out
	 * @return boolean true is the nonce validates correctly, false on error.
	 */
	public function validate(){
		$nonce_name = $this->getName();
		if(empty($nonce_name)){
			return true;
		}
		return isset($_REQUEST[$nonce_name]) && wp_verify_nonce($_REQUEST[$nonce_name], $this->getValue());
	}

	public function validateAjax($key=null){
		if(empty($key)){
			$key = $this->getName();
		}
		return check_ajax_referer( $this->getValue(), $key, false );
	}
}
