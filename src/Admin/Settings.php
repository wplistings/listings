<?php

namespace Listings\Admin;

class Settings {

	public $settings = array();

	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * init_settings function.
	 *
	 * @access protected
	 * @return void
	 */
	protected function init_settings() {
		$this->settings = apply_filters( 'listings_settings', array(
			'listings_settings' => array(
				__( 'Settings', 'listings' ),
				array(
					array(
						'name'       => 'listings_enable_default_post_type',
						'std'        => '1',
						'label'      => __( 'Enable generic listings', 'listings' ),
						'cb_label'   => __( 'Enable', 'listings' ),
						'desc'       => __( 'This enables the generic listings type to create a generic listings site.', 'listings' ),
						'type'       => 'checkbox',
						'attributes' => array()
					),
					array(
						'name'       => 'listings_enable_default_categories',
						'std'        => '1',
						'label'      => __( 'Enable categories', 'listings' ),
						'cb_label'   => __( 'Enable', 'listings' ),
						'desc'       => __( 'This enables categories for the generic listings type.', 'listings' ),
						'type'       => 'checkbox',
						'attributes' => array()
					),
					array(
						'name'       => 'listings_use_template_archive',
						'std'        => '1',
						'label'      => __( 'Enable listings template', 'listings' ),
						'cb_label'   => __( 'Enable', 'listings' ),
						'desc'       => __( 'This enables the archive page for default listings. You can alternatively use the shortcode page as an archive.', 'listings' ),
						'type'       => 'checkbox',
						'attributes' => array()
					),
				)
			),
			'listings_pages' => array(
				__( 'Pages', 'listings' ),
				array(
					array(
						'name' => 'listings_overview_page_id',
						'std' => '',
						'label' => __('Listings Overview Page', 'listings'),
						'desc' => __('Select the page where you have placed the <code>[listings]</code> shortcode. This lets the plugin know where the main listings overview is located if using the shortcode page as an archive.', 'listings'),
						'type' => 'page',
					),
				),
			)
		) );
	}

	/**
	 * register_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_settings() {
		$this->init_settings();

		foreach ( $this->settings as $key => $section ) {
			foreach ( $section[1] as $option ) {
				if ( isset( $option['std'] ) )
					add_option( $option['name'], $option['std'] );
				register_setting( $key, $option['name'] );
			}
		}
	}

	/**
	 * output function.
	 *
	 * @access public
	 * @return void
	 */
	public function output() {
		$this->init_settings();
		?>
		<div class="wrap listings-settings-wrap">
			<form method="post" action="options.php">

				<?php

				// Hide all empty settings pages
				foreach ( $this->settings as $key => $setting ) {
					if ( ! isset($setting[1] ) || empty( $setting[1] ) ) {
						unset($this->settings[$key] );
					}
				}

				$settings_keys = array_keys($this->settings);
				$first_tab = array_shift($settings_keys);

				if ( !isset($_GET['tab'] ) || ! isset($this->settings[ $_GET['tab']])) {
					$active_tab = $first_tab;
				} else {
					$active_tab = $_GET['tab'];
				}

				settings_fields( $active_tab );
				?>

			    <h2 class="nav-tab-wrapper">
			    	<?php
			    		foreach ( $this->settings as $key => $section ) {
							$tab_url = remove_query_arg('settings-updated', add_query_arg('tab', sanitize_title( $key ) ) );
			    			echo '<a href="' . $tab_url . '" class="nav-tab';
			    			 if ( sanitize_title( $key ) == $active_tab ) {
								 echo ' nav-tab-active';
							 }
							echo '">' . esc_html( $section[0] ) . '</a>';
			    		}
			    	?>
			    </h2>

				<?php
					if ( ! empty( $_GET['settings-updated'] ) ) {
						flush_rewrite_rules();
						echo '<div class="updated fade listings-updated"><p>' . __( 'Settings successfully saved', 'listings' ) . '</p></div>';
					}

					if (isset($_GET['tab'] ) && isset( $this->settings[ $_GET['tab'] ] ) ) {
						$this->settings = array( $this->settings[ $_GET['tab'] ] );
					} else {
						$this->settings = array(array_shift($this->settings));
					}

					foreach ( $this->settings as $key => $section ) {

						echo '<div id="settings-' . sanitize_title( $key ) . '" class="settings_panel">';

						echo '<table class="form-table">';

						foreach ( $section[1] as $option ) {

							$placeholder    = ( ! empty( $option['placeholder'] ) ) ? 'placeholder="' . $option['placeholder'] . '"' : '';
							$class          = ! empty( $option['class'] ) ? $option['class'] : '';
							$value          = get_option( $option['name'] );
							$option['type'] = ! empty( $option['type'] ) ? $option['type'] : '';
							$attributes     = array();

							if ( ! empty( $option['attributes'] ) && is_array( $option['attributes'] ) )
								foreach ( $option['attributes'] as $attribute_name => $attribute_value )
									$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';

							echo '<tr valign="top" class="' . $class . '"><th scope="row"><label for="setting-' . $option['name'] . '">' . $option['label'] . '</a></th><td>';

							switch ( $option['type'] ) {

								case "checkbox" :

									?><label><input id="setting-<?php echo $option['name']; ?>" name="<?php echo $option['name']; ?>" type="checkbox" value="1" <?php echo implode( ' ', $attributes ); ?> <?php checked( '1', $value ); ?> /> <?php echo $option['cb_label']; ?></label><?php

									if ( $option['desc'] )
										echo ' <p class="description">' . $option['desc'] . '</p>';

								break;
								case "textarea" :

									?><textarea id="setting-<?php echo $option['name']; ?>" class="large-text" cols="50" rows="3" name="<?php echo $option['name']; ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea><?php

									if ( $option['desc'] )
										echo ' <p class="description">' . $option['desc'] . '</p>';

								break;
								case "select" :

									?><select id="setting-<?php echo $option['name']; ?>" class="regular-text" name="<?php echo $option['name']; ?>" <?php echo implode( ' ', $attributes ); ?>><?php
										foreach( $option['options'] as $key => $name )
											echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $name ) . '</option>';
									?></select><?php

									if ( $option['desc'] ) {
										echo ' <p class="description">' . $option['desc'] . '</p>';
									}

								break;
								case "page" :

									$args = array(
										'name'             => $option['name'],
										'id'               => $option['name'],
										'sort_column'      => 'menu_order',
										'sort_order'       => 'ASC',
										'show_option_none' => __( '--no page--', 'listings' ),
										'echo'             => false,
										'selected'         => absint( $value )
									);

									echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'listings' ) .  "' id=", wp_dropdown_pages( $args ) );

									if ( $option['desc'] ) {
										echo ' <p class="description">' . $option['desc'] . '</p>';
									}

								break;
								case "password" :

									?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="password" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php

									if ( $option['desc'] ) {
										echo ' <p class="description">' . $option['desc'] . '</p>';
									}

								break;
								case "number" :
									?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="number" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php

									if ( $option['desc'] ) {
										echo ' <p class="description">' . $option['desc'] . '</p>';
									}
								break;
								case "" :
								case "input" :
								case "text" :
									?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php

									if ( $option['desc'] ) {
										echo ' <p class="description">' . $option['desc'] . '</p>';
									}
								break;
								default :
									do_action( 'listings_admin_field_' . $option['type'], $option, $attributes, $value, $placeholder );
								break;

							}

							echo '</td></tr>';
						}

						echo '</table></div>';

					}
				?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'listings' ); ?>" />
				</p>
		    </form>
		</div>
		<?php
		do_action( 'listings_after_settings');
	}
}
