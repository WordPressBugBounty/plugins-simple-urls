<?php
use Elementor\Controls_Manager;
use Elementor\Plugin as Elementor_Plugin;
use Elementor\Widget_Base;

use LassoLite\Admin\Constant;

use LassoLite\Classes\Setting as Lasso_Setting;
use LassoLite\Classes\Helper as Lasso_Helper;
use LassoLite\Classes\Enum;
use LassoLite\Classes\Amazon_Api;

class Widget_Lasso_Shortcode extends Widget_Base {

	public function get_name() {
		return 'lasso_shortcode';
	}

	public function get_title() {
		return esc_html__( 'Lasso Lite', 'simple-urls' );
	}

	public function get_icon() {
		return 'eicon-lasso';
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'lasso', 'lasso lite', 'shortcode', 'code', 'affiliate', 'link' ];
	}

	/**
	 * Editor panel + preview iframe need widget assets. HTTP_REFERER-only checks miss the preview document.
	 *
	 * @return bool
	 */
	private static function load_elementor_builder_assets() {
		// ? elementor-preview request: get_script_depends() often runs before preview->is_preview_mode() is true.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['elementor-preview'] ) ) {
			return true;
		}
		$req_uri = Lasso_Helper::get_server_param( 'REQUEST_URI' );
		if ( is_string( $req_uri ) && false !== strpos( $req_uri, 'elementor-preview' ) ) {
			return true;
		}
		if ( ! class_exists( Elementor_Plugin::class ) ) {
			return false;
		}
		$elementor = Elementor_Plugin::$instance;
		if ( $elementor->editor && $elementor->editor->is_edit_mode() ) {
			return true;
		}
		if ( isset( $elementor->preview ) && $elementor->preview && $elementor->preview->is_preview_mode() ) {
			return true;
		}
		return self::is_editor();
	}

	public function get_custom_help_url() {
		return 'https://support.getlasso.co/en/articles/4575092-shortcode-reference-guide';
	}

	/**
	 * @link https://developers.elementor.com/docs/scripts-styles/widget-styles/
	 *
	 * @return array
	 */
	public function get_style_depends() {
		if ( self::load_elementor_builder_assets() ) {
			Lasso_Helper::enqueue_style( 'lasso-live', 'lasso-live.min.css' );
			Lasso_Helper::enqueue_style( 'bootstrap-grid-css', 'bootstrap-grid.min.css' );
			Lasso_Helper::enqueue_style( 'simple-panigation-css', 'simplePagination.css' );
			Lasso_Helper::enqueue_style( 'lasso-display-modal', 'lasso-display-modal.css' );
			Lasso_Helper::enqueue_style( 'lasso-quill', 'quill.snow.css' );
			Lasso_Helper::enqueue_style( 'lasso-table-frontend', 'lasso-table-frontend.min.css' );
			Lasso_Helper::enqueue_style( 'lasso-elementor', 'lasso-elementor.css' );
			Lasso_Helper::enqueue_style( 'lasso-modal-css', 'lasso-modal.css' );
		}

		return array();
	}

	/**
	 * @link https://developers.elementor.com/docs/scripts-styles/widget-scripts/
	 *
	 * @return array
	 */
	public function get_script_depends() {
		if ( self::load_elementor_builder_assets() ) {
			$setting           = new Lasso_Setting();
			$setting_data      = Lasso_Setting::get_settings();
			$support_enabled   = $setting_data[ Enum::SUPPORT_ENABLED ] ?? false;
			$data_passed_to_js = array(
				'registerNonce'              => wp_create_nonce( 'lasso_registration' ),
				'optionsNonce'               => wp_create_nonce( Constant::LASSO_LITE_NONCE . wp_salt() ),
				'ajax_url'                   => admin_url( 'admin-ajax.php' ),
				'site_url'                   => site_url(),
				'lasso_settings_general_url' => Lasso_Setting::get_lasso_page_url( $setting->settings_general_page ),
				'loading_image'              => SIMPLE_URLS_URL . '/admin/assets/images/lasso-icon.svg',
				'plugin_url'                 => SIMPLE_URLS_URL,
				'customizing_display'        => Constant::BLOCK_CUSTOMIZE,
				'segment_analytic_id'        => '',
				'display_type_single'        => Lasso_Setting::DISPLAY_TYPE_SINGLE,
				'display_type_grid'          => Lasso_Setting::DISPLAY_TYPE_GRID,
				'display_type_list'          => Lasso_Setting::DISPLAY_TYPE_LIST,
				'app_id'                     => Constant::LASSO_INTERCOM_APP_ID,
				'amazon_tracking_id_regex'   => Amazon_Api::TRACKING_ID_REGEX,
				'rewrite_slug_default'       => Enum::REWRITE_SLUG_DEFAULT,
				'simple_urls_slug'           => SIMPLE_URLS_SLUG,
				'page_url_details'           => SIMPLE_URLS_SLUG . '-' . Enum::PAGE_URL_DETAILS,
				'setup_progress'             => Lasso_Helper::get_setup_progress_information(),
				'should_open_support_modal'  => $support_enabled,
				'block_customize'            => Constant::BLOCK_CUSTOMIZE,
			);

			wp_enqueue_media();
			Lasso_Helper::enqueue_script( 'popper-js', 'popper.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-js', 'bootstrap.min.js', array( 'jquery', 'popper-js' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-select-js', 'bootstrap-select.min.js', array( 'jquery', 'bootstrap-js' ) );
			Lasso_Helper::enqueue_script( 'pagination-js', 'jquery.simplePagination.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'lasso-icons', 'fontawesome.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'lasso-icons-regular', 'regular.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'lasso-helper', 'lasso-helper.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( SIMPLE_URLS_SLUG . '-js', 'settings.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'lasso-post-edit-segment-analytic', 'lasso-post-edit-segment-analytic.js' );
			Lasso_Helper::enqueue_script( 'lasso-quill', 'quill.min.js' );
			Lasso_Helper::enqueue_script( 'lasso-elementor', 'lasso-elementor.js', array( 'jquery' ), true );
			Lasso_Helper::enqueue_script( 'lasso-modal-js', 'lasso-modal.js', array( 'jquery' ), true );
			Lasso_Helper::enqueue_script( 'lasso-lite-display-modal', 'lasso-lite-display-modal.js', array( 'jquery' ), true );
			Lasso_Helper::enqueue_script( 'display-add', 'display-add.js', array( 'jquery' ), true );
			Lasso_Helper::enqueue_script( 'url-add', 'url-add.js', array( 'jquery' ), true );

			wp_localize_script( 'jquery', 'lassoLiteOptionsData', $data_passed_to_js );
		}

		return array();
	}

	/**
	 * Register shortcode widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Lasso Shortcode' ),
				'tab' => Controls_Manager::TAB_CONTENT
			]
		);

		$this->add_control(
			'lasso_shortcode',
			[
				'label' => esc_html__( 'Enter your shortcode' ),
				'type' => Controls_Manager::TEXTAREA,
				'class' => 'lasso_shortcode',
				'placeholder' => '[lasso id="123" rel="lasso-rel"]',
				'default' => '',
			]
		);

		foreach ( Constant::BLOCK_CUSTOMIZE as $customizing_display ) {
			if ( isset ( $customizing_display['attributes'] ) ) {
				$type = $customizing_display['type'];

				foreach ( $customizing_display['attributes'] as $attribute ) {
					$input_type = in_array( $attribute['attr'], Constant::BLOCK_CUSTOMIZE['toogle_attributes'] )
						? \Elementor\Controls_Manager::SWITCHER
						: \Elementor\Controls_Manager::TEXT;
					$default = in_array( $attribute['attr'], Constant::BLOCK_CUSTOMIZE['toogle_attributes'] )
						? 'yes'
						: '';

					$this->add_control(
						$attribute['attr'] . '_' . $type,
						[
							'label'       => esc_html__( $attribute['name'] ),
							'type'        => $input_type,
							'class'       => 'lasso_shortcode_' . $type,
							'default'     => $default,
							'description' => $attribute['desc'] ?? '',
						]
					);
				}
			}
		}

		$this->end_controls_section();
	}

	protected function render() {
		// ? In the Elementor editor, content_template + JS own the chrome (.shortcode-html, buttons). Server render here replaces that after refresh.
		if ( $this->should_skip_frontend_render_in_elementor_builder() ) {
			return;
		}

		$shortcode = $this->get_settings_for_display( 'lasso_shortcode' );
		$shortcode = do_shortcode( shortcode_unautop( $shortcode ) );

		echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * True while editing in Elementor (avoid echoing shortcode HTML — it hides content_template chrome).
	 *
	 * @return bool
	 */
	private function should_skip_frontend_render_in_elementor_builder() {
		if ( class_exists( Elementor_Plugin::class ) && Elementor_Plugin::$instance->editor && Elementor_Plugin::$instance->editor->is_edit_mode() ) {
			return true;
		}
		if ( class_exists( Elementor_Plugin::class ) && isset( Elementor_Plugin::$instance->preview ) && Elementor_Plugin::$instance->preview && Elementor_Plugin::$instance->preview->is_preview_mode() ) {
			return true;
		}
		// ? Referer fallback when editor bootstrap hasn't set edit mode yet (refresh / iframe race).
		return self::is_editor();
	}

	/**
	 * Render shortcode widget as plain content.
	 *
	 * Override the default behavior by printing the shortcode instead of rendering it.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_plain_content() {
		// In plain mode, render without shortcode
		$this->print_unescaped_setting( 'lasso_shortcode' );
	}


	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {
		?>
		<div class="lasso-lite-elementor-shell" style="display: flex; flex-direction: column; align-items: stretch; text-align: center; background-color: rgb(94, 54, 202); border-radius: 10px; padding: 0px 0px 20px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif;">
			<div class="shortcode-html lasso-lite-elementor-preview" style="display: block; flex: 0 1 auto; margin: 0px auto; width: 100%; max-width: 100%; box-sizing: border-box; background: white; padding: 1px 0px; text-align: initial;">
			</div>
			<div class="lasso-lite-elementor-chrome" style="flex-shrink: 0; margin-top: 8px;">
			<div style="display: flex; align-items: center; padding: 10px 0px 0px; justify-content: center;">
				<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 500 500">
					<defs>
						<clipPath id="b">
							<rect width="500" height="500"></rect>
						</clipPath>
					</defs>
					<circle cx="249.5" cy="249.5" r="249.5" transform="translate(1 1)" fill="#5e36ca"></circle>
					<g id="a" clip-path="url(#b)">
						<g transform="translate(59.684 92.664)">
							<g transform="translate(90.918 0.437)">
								<path d="M177.568,52.494h0a25.365,25.365,0,0,0-25.84,25.613l.443,9.957c-.371,62.1-18.019,59.155-20.892,58.341V30.649C131.284,16.543,119.335,5,104.734,5h0C90.128,5,78.179,16.543,78.179,30.649V147.743C53.909,154.035,58.167,82.39,58.167,82.39V57.457c0-14.374-13.9-25.989-28.805-25.989h0c-14.874,0-24.29,11.759-24.29,26.133L5,82.673C12.208,193.8,78.179,183.648,78.179,183.648l.036,37.434H131.32l-.036-37.542C200.1,183.267,204.391,88.3,204.391,88.3v-9.89C204.385,64.155,192.318,52.494,177.568,52.494Z" transform="translate(-5 -5)" fill="#00ffd3"></path>
								<path d="M4.762,37.732c0,10.173,6.178,18.5,13.736,18.5h44.43c7.558,0,13.741-8.325,13.741-18.5L81.416,0H0Z" transform="translate(59.721 257.209)" fill="#cc4afc"></path>
							</g>
							<path d="M195.564,425.8H103.779c-4.193.017-7.588,4.181-7.6,9.321v14.692c.011,5.14,3.406,9.3,7.6,9.321h91.785c4.2-.014,7.6-4.178,7.609-9.321V435.121C203.159,429.978,199.76,425.814,195.564,425.8Z" transform="translate(41.681 -205.257)" fill="#cc4afc"></path>
						</g>
					</g>
				</svg>
				<span style="font-size: 26px; font-weight: 700;"></span>
			</div>
			<span style="display: <# if ( settings.lasso_shortcode ) { #>none<# } else { #>block<# } #>; margin-bottom: 20px; margin-top: 10px; font-size: 18px; color: rgb(255, 255, 255); font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif;">Choose a Lasso Link to display.</span>
			<input type="text" class="shortcode-input" value='{{{ settings.lasso_shortcode }}}' style="display: <# if ( settings.lasso_shortcode ) { #>block<# } else { #>none<# } #>; background-color: white; margin: 10px auto 20px; padding: 0.5rem 0.75rem; border-radius: 0.5rem; border: 1px solid rgb(206, 212, 218); width: 85%; height: auto; line-height: 2; font-size: 1rem; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif;">
			<button class="lasso-update-display" style="display: <# if ( settings.lasso_shortcode ) { #>inline-block<# } else { #>none<# } #>; background-color: rgb(34, 186, 160); color: rgb(255, 255, 255); padding: 0.75rem 2rem; border-radius: 100rem; font-size: 1rem; margin: 0.5rem; font-weight: 800; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; border: 0px; cursor: pointer;">Update Display</button>
			<button class="btn-modal-add-display" style="background-color: rgb(34, 186, 160); color: rgb(255, 255, 255); padding: 0.75rem 2rem; border-radius: 100rem; font-size: 1rem; margin: 0.5rem; font-weight: 800; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; border: 0px; cursor: pointer;"><# if ( settings.lasso_shortcode ) { #>Select a New Display<# } else { #>Add a Display<# } #></button>
			<button class="lasso-edit-display" style="display: <# if ( settings.lasso_shortcode ) { #>inline-block<# } else { #>none<# } #>; background-color: rgb(34, 186, 160); color: rgb(255, 255, 255); padding: 0.75rem 2rem; border-radius: 100rem; font-size: 1rem; margin: 0.5rem; font-weight: 800; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; border: 0px; cursor: pointer;">Edit Display</button>
			</div>
		</div>
		<div class="lasso-display-modal-wrapper"></div>
		<?php
	}

	/**
	 * Check is Elementor editor page
	 *
	 * @return bool
	 */
	public static function is_editor() {
		$http_referer = Lasso_Helper::get_server_param( 'HTTP_REFERER' );
		$query_str    = parse_url( $http_referer, PHP_URL_QUERY );
		parse_str( $query_str, $query_params );

		return isset( $query_params['action'] ) && 'elementor' === $query_params['action'];
	}
}
