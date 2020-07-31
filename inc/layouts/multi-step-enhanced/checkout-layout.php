<?php
/**
 * Checkout steps layout: Multi Step Enhanced
 */
class FluidCheckoutLayout_MultiStepEnhanced extends FluidCheckout {

	/**
	 * __construct function.
	 */
	public function __construct() {
        // Load dependency
        require_once self::$directory_path . 'inc/layouts/multi-step/checkout-layout.php';
        FluidCheckoutLayout_MultiStep::instance();

		$this->hooks();
	}



	/**
	 * Initialize hooks.
	 */
	public function hooks() {

		// General
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 10 );
		
		// // Template loader
		// add_filter( 'woocommerce_locate_template', array( $this, 'locate_template' ), 20, 3 );

		// // Steps display order
		// add_action( 'wfc_checkout_before_steps', array( $this, 'output_checkout_progress_bar' ), 10 );
		// add_action( 'wfc_checkout_steps', array( $this, 'output_step_customer_contact' ), 10 );
		// add_action( 'wfc_checkout_steps', array( $this, 'output_step_shipping' ), 50 );
		// add_action( 'wfc_checkout_steps', array( $this, 'output_step_payment' ), 100 );

		// // Payment
		// remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
		// add_action( 'wfc_checkout_payment', 'woocommerce_checkout_payment', 20 );
		
		// Order Review
        add_action( 'wfc_checkout_after_steps', array( $this, 'output_checkout_order_review_wrapper' ), 10 );
        
		
	}



	/**
	 * Add page body class for feature detection
	 */
	public function add_body_class( $classes ) {
		return array_merge( $classes, array( 'has-wfc-checkout-layout--multi-step-enhanced' ) );
	}



	/**
	 * Enqueue scripts
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'wfc-checkout-layout--multi-step-enhanced', self::$directory_url . 'css/checkout-layout--multi-step-enhanced'. self::$asset_version . '.css', NULL, NULL );
	}



	/*
	 * Locate template files from this checkout layout.
	 * @since 1.1.0
	 */
	public function locate_template( $template, $template_name, $template_path ) {
	 
		global $woocommerce;
	 
		$_template = $template;

	 
		if ( ! $template_path ) $template_path = $woocommerce->template_url;
	 
		// Get plugin path
		$plugin_path  = self::$directory_path . 'inc/layouts/multi-step-enhanced/templates/';
	 
		// Look within passed path within the theme
		$template = locate_template(
			array(
				$template_path . $template_name,
				$template_name
			)
		);
	 
		// Get the template from this plugin, if it exists
		if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}
	 
		// Use default template
		if ( ! $template ){
			$template = $_template;
		}
	 
		// Return what we found
		return $template;
	}



	/**
	 * Output start tag for a checkout step.
	 */
	public function output_step_start_tag( $step_label ) {
		?>
		<section class="wfc-frame" data-label="<?php echo esc_attr( $step_label ) ?>">
		<?php
	}
	/**
	 * Output end tag for a checkout step.
	 */
	public function output_step_end_tag() {
		?>
		</section>
		<?php
	}



	/**
	 * Output the checkout progress bar
	 */
	public function output_checkout_progress_bar() {
		?>
		<div class="wfc-checkout-progress-bar wfc-row wfc-header">
			<div id="wfc-progressbar"><?php echo apply_filters( 'wfc_progressbar_steps_placeholder', '<div class="wfc-step current"></div><div class="wfc-step"></div><div class="wfc-step"></div>' ); ?></div>
		</div>
		<?php
	}



	/**
	 * Output step: Contact Details
	 */
	public function output_step_customer_contact() {
		$this->output_step_start_tag( apply_filters( 'wfc_billing_step_title', __( 'Billing', 'woocommerce-fluid-checkout' ) ) );
		do_action( 'woocommerce_checkout_before_customer_details' );
		do_action( 'woocommerce_checkout_billing' );
		echo $this->get_billing_step_actions_html();
		$this->output_step_end_tag();
	}



	/**
	 * Output step: Shipping
	 */
	public function output_step_shipping() {
		$this->output_step_start_tag( apply_filters( 'wfc_shipping_step_title', __( 'Shipping', 'woocommerce-fluid-checkout' ) ) );
		do_action( 'woocommerce_checkout_shipping' );
		do_action( 'woocommerce_checkout_after_customer_details' );
		echo $this->get_shipping_step_actions_html();
		$this->output_step_end_tag();
	}



	/**
	 * Output step: Payment
	 */
	public function output_step_payment() {
		$this->output_step_start_tag( apply_filters( 'wfc_payment_step_title', __( 'Payment', 'woocommerce-fluid-checkout' ) ) );
		do_action( 'wfc_checkout_payment' );
		$this->output_step_end_tag();
	}



	/**
	 * Output checkout place order button
	 */
	public function output_checkout_place_order() {
		wc_get_template(
			'checkout/place-order.php',
			array(
				'checkout'           => WC()->checkout(),
				'order_button_text'  => apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) ),
			)
		);
	}



	/**
	 * Output Order Review
	 */
	public function output_checkout_order_review() {
		wc_get_template(
			'checkout/order-review.php',
			array(
				'checkout'           => WC()->checkout(),
				'order_review_title' => apply_filters( 'wfc_order_review_title', __( 'Your order', 'woocommerce' ) ),
			)
		);
	}



	/**
	 * Add back button html to place order button on checkout.
	 */
	public function get_billing_step_actions_html() {
		$actions_html = '<div class="wfc-actions"><button class="wfc-next button alt">' . __( 'Proceed to Shipping', 'woocommerce-fluid-checkout' ) . '</button></div>';
		return apply_filters( 'wfc_billing_step_actions_html', $actions_html );
	}



	/**
	 * Add back button html to place order button on checkout.
	 */
	public function get_shipping_step_actions_html() {
		$actions_html = '<div class="wfc-actions"><button class="wfc-prev">' . _x( 'Back', 'Previous step button', 'woocommerce-fluid-checkout' ) . '</button> <button class="wfc-next button alt">' . __( 'Proceed to Payment', 'woocommerce-fluid-checkout' ) . '</button></div>';
		return apply_filters( 'wfc_shipping_step_actions_html', $actions_html );
	}



	/**
	 * Add back button html to place order button on checkout.
	 * @param [String] $button_html Place Order button html.
	 */
	public function get_payment_step_actions_html( $button_html ) {
		$actions_html = '<div class="wfc-actions"><button class="wfc-prev">' . _x( 'Back', 'Previous step button', 'woocommerce-fluid-checkout' ) . '</button> ' . $button_html . '</div>';
		return apply_filters( 'wfc_payment_step_actions_html', $actions_html, $button_html );
	}

}

FluidCheckoutLayout_MultiStepEnhanced::instance();