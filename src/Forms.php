<?php

namespace Listings;

use Listings\Forms\Form;

class Forms {

	/** @var array */
	public $forms = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_posted_form' ) );
	}

	public function register_form( Form $form ) {
		$this->forms[ $form->form_name ] = get_class( $form );
	}

	/**
	 * If a form was posted, load its class so that it can be processed before display.
	 */
	public function load_posted_form() {
		if ( ! empty( $_POST['listings_form'] ) ) {
			$this->load_form_class( sanitize_title( $_POST['listings_form'] ) );
		}
	}

	/**
	 * Load a form's class
	 *
	 * @param  string $form_name
	 * @return string class name on success, false on failure
	 */
	private function load_form_class( $form_name ) {
		if ( ! isset( $this->forms[ $form_name ] ) ) {
			return false;
		}

		$form_class = $this->forms[ $form_name ];

		if ( class_exists( $form_class ) ) {
			return call_user_func( array( $form_class, 'instance' ) );
		}

		return false;
	}

	/**
	 * get_form function.
	 *
	 * @param string $form_name
	 * @param  array $atts Optional passed attributes
	 * @return string
	 */
	public function get_form( $form_name, $atts = array() ) {
		if ( $form = $this->load_form_class( $form_name ) ) {
			ob_start();
			$form->output( $atts );
			return ob_get_clean();
		}
	}
}