<?php
defined( 'ABSPATH' ) || exit;

/**
 * Compatibility with plugin: Sendinblue - WooCommerce Email Marketing (by Sendinblue).
 */
class FluidCheckout_WooCommerceSendinblueNewsletterSubscription extends FluidCheckout {

	/**
	 * __construct function.
	 */
	public function __construct() {
		$this->hooks();
	}



	/**
	 * Initialize hooks.
	 */
	public function hooks() {
		// Late hooks
		add_action( 'init', array( $this, 'late_hooks' ), 100 );

		// Settings
		add_filter( 'fc_integrations_settings_add', array( $this, 'add_settings' ), 10 );
	}

	/**
	 * Add or remove late hooks.
	 */
	public function late_hooks() {
		// Bail if SendInBlue class is not available
		if ( ! class_exists( 'WC_Sendinblue_Integration' ) || ! array_key_exists( 'WC_Sendinblue_Integration', $GLOBALS ) ) { return; }

		// Bail if should not move field to contact step
		if ( 'yes' !== get_option( 'fc_compat_plugin_woocommerce_sendinblue_newsletter_subscription_move_checkbox_contact_step', 'yes' ) ) { return; }

		$sendinblue_woocommerce = $GLOBALS['WC_Sendinblue_Integration'];

		// Terms and conditions position
		$ws_opt_field     = isset( $sendinblue_woocommerce->customizations['ws_opt_field'] ) ? $sendinblue_woocommerce->customizations['ws_opt_field'] : 'no';
		$display_location = isset( $sendinblue_woocommerce->customizations['ws_opt_checkbox_location'] ) ? $sendinblue_woocommerce->customizations['ws_opt_checkbox_location'] : '';
		if ( 'yes' == $ws_opt_field && 'terms_condition' == $display_location ) {
			remove_action( 'woocommerce_checkout_after_terms_and_conditions', array( $sendinblue_woocommerce, 'maybe_add_checkout_fields_terms' ) );
			add_action( 'fc_checkout_after_contact_fields', array( $sendinblue_woocommerce, 'maybe_add_checkout_fields_terms' ) );
		}

		// Billing or order fields position
		if ( 'yes' == $ws_opt_field && ( empty( $display_location ) || 'billing' == $display_location || 'order' == $display_location ) ) {
			remove_filter( 'woocommerce_checkout_fields', array( $sendinblue_woocommerce, 'maybe_add_checkout_fields' ) );
			add_filter( 'woocommerce_checkout_fields', array( $this, 'maybe_add_checkout_fields_to_billing' ) );
			add_filter( 'fc_checkout_contact_step_field_ids', array( $this, 'move_signup_field_to_contact_substep' ) );
		}
	}



	/**
	 * Add new settings to the Fluid Checkout admin settings sections.
	 *
	 * @param   array   $settings         Array with all settings for the current section.
	 * @param   string  $current_section  Current section name.
	 */
	public function add_settings( $settings ) {
		// Define positions for new settings
		$index = count( $settings ) - 1;

		// Define setting to insert
		$insert_settings = array(
			array(
				'title'           => __( 'Sendinblue - WooCommerce Email Marketing', 'fluid-checkout' ),
				'desc'            => __( 'Move the sign up checkbox to the contact step', 'fluid-checkout' ),
				'desc_tip'        => __( 'When enabled, the checkbox will be moved to the contact step independently of the display location defined in the Sendinblue plugin settings.', 'fluid-checkout' ),
				'id'              => 'fc_compat_plugin_woocommerce_sendinblue_newsletter_subscription_move_checkbox_contact_step',
				'type'            => 'checkbox',
				'default'         => 'yes',
				'autoload'        => false,
			),
		);

		// Get token position
		$position_index = count( $settings ) - 1;

		// Insert at token position
		$new_settings  = array_slice( $settings, 0, $position_index );
		$new_settings = array_merge( $new_settings, $insert_settings );
		$new_settings = array_merge( $new_settings, array_slice( $settings, $position_index, count( $settings ) ) );

		return $new_settings;
	}



	/**
	 * Add the sign up field always in the billing section, to then be moed to the contact step via other hooks.
	 *
	 * @param   array  $checkout_fields  The checkout fields args.
	 */
	public function maybe_add_checkout_fields_to_billing( $checkout_fields ) {
		// CHANGE: Get SendInBlue class object
		$sendinblue_woocommerce = $GLOBALS['WC_Sendinblue_Integration'];

		// CHANGE: Removed unnecessary checks for display location

		$ws_opt_field = isset( $sendinblue_woocommerce->customizations['ws_opt_field'] ) ? $sendinblue_woocommerce->customizations['ws_opt_field'] : 'no';
		if ( 'yes' == $ws_opt_field ) {
			// CHANGE: Always add field to billing section, then it will be moved to contact step
			$checkout_fields[ 'billing' ]['ws_opt_in'] = array(
				'type'    => 'checkbox',
				'label'   => esc_attr( $sendinblue_woocommerce->customizations['ws_opt_field_label'] ),
				'default' => 'checked' == $sendinblue_woocommerce->customizations['ws_opt_default_status'] ? 1 : 0,
			);
		}
		if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) === 'GET' ) {
			?>
			<input type="hidden" class="ws_opt_in_nonce" name="ws_opt_in_nonce" value="<?php echo wp_create_nonce( 'order_checkout_nonce' ); ?>">
			<?php
		}
		return $checkout_fields;
	}



	/**
	 * Move sign up field to the contact substep.
	 */
	public function move_signup_field_to_contact_substep( $contact_field_ids ) {
		// Fields after existing fields
		$contact_field_ids = array_merge( $contact_field_ids, array( 'ws_opt_in' ) );

		return $contact_field_ids;
	}

}

FluidCheckout_WooCommerceSendinblueNewsletterSubscription::instance();