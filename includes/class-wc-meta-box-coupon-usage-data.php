<?php
/**
 * Coupon Usage Restriction Data.
 *
 * Display the coupon usage restriction data meta box.
 *
 * @category Admin
 * @author   AxisThemes
 * @package  WooCommerce/Admin/Meta Boxes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Meta_Box_Coupon_Usage_Data Class.
 */
class WC_Meta_Box_Coupon_Usage_Data {

	/**
	 * Coupon message code.
	 * @var integer
	 */
	const E_WC_COUPON_INVALID_COUNTRY = 99;

	/**
	 * Hooks in methods.
	 */
	public static function init() {
		add_action( 'woocommerce_coupon_options_usage_restriction', array( __CLASS__, 'coupon_options_data' ) );
		add_action( 'woocommerce_coupon_options_save', array( __CLASS__, 'coupon_options_save' ) );
		add_action( 'woocommerce_coupon_loaded', array( __CLASS__, 'coupon_loaded' ) );
		add_filter( 'woocommerce_coupon_is_valid', array( __CLASS__, 'is_valid_for_country' ), 10, 2 );
		add_filter( 'woocommerce_coupon_error', array( __CLASS__, 'get_country_coupon_error' ), 10, 3 );
	}

	/**
	 * Output coupons usage restriction meta box data.
	 */
	public static function coupon_options_data() {
		global $post;

		echo '<div class="options_group">';

		// Billing Countries.
		?>
		<p class="form-field"><label for="billing_countries"><?php _e( 'Billing countries', 'wc-coupons-by-country' ); ?></label>
		<select id="billing_countries" name="billing_countries[]" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Any countries', 'wc-coupons-by-country' ); ?>">
			<?php
				$locations = (array) get_post_meta( $post->ID, 'billing_countries', true );
				$countries = WC()->countries->countries;

				if ( $countries ) foreach ( $countries as $key => $val ) {
					echo '<option value="' . esc_attr( $key ) . '"' . selected( in_array( $key, $locations ), true, false ) . '>' . esc_html( $val ) . '</option>';
				}
			?>
		</select> <?php echo wc_help_tip( __( 'List of allowed countries to check against the customer\'s billing country for the coupon to remain valid.', 'wc-coupons-by-country' ) ); ?></p>
		<?php

		// Shipping Countries.
		?>
		<p class="form-field"><label for="shipping_countries"><?php _e( 'Shipping countries', 'wc-coupons-by-country' ); ?></label>
		<select id="shipping_countries" name="shipping_countries[]" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Any countries', 'wc-coupons-by-country' ); ?>">
			<?php
				$locations = (array) get_post_meta( $post->ID, 'shipping_countries', true );
				$countries = WC()->countries->countries;

				if ( $countries ) foreach ( $countries as $key => $val ) {
					echo '<option value="' . esc_attr( $key ) . '"' . selected( in_array( $key, $locations ), true, false ) . '>' . esc_html( $val ) . '</option>';
				}
			?>
		</select> <?php echo wc_help_tip( __( 'List of allowed countries to check against the customer\'s shipping country for the coupon to remain valid.', 'wc-coupons-by-country' ) ); ?></p>
		<?php

		echo '</div>';
	}

	/**
	 * Save coupons usage restriction meta box data.
	 */
	public static function coupon_options_save( $post_id ) {
		$billing_countries  = isset( $_POST['billing_countries'] ) ? wc_clean( $_POST['billing_countries'] ) : array();
		$shipping_countries = isset( $_POST['shipping_countries'] ) ? wc_clean( $_POST['shipping_countries'] ) : array();

		// Save billing and shipping countries.
		update_post_meta( $post_id, 'billing_countries', $billing_countries );
		update_post_meta( $post_id, 'shipping_countries', $shipping_countries );
	}

	/**
	 * Populates an order from the loaded post data.
	 * @param WC_Coupon $coupon
	 */
	public static function coupon_loaded( $coupon ) {
		$coupon->billing_countries  = get_post_meta( $coupon->id, 'billing_countries', true );
		$coupon->shipping_countries = get_post_meta( $coupon->id, 'shipping_countries', true );
	}

	/**
	 * Check if coupon is valid for country.
	 * @return bool
	 */
	public static function is_valid_for_country( $valid_for_cart, $coupon ) {
		if ( sizeof( $coupon->billing_countries ) > 0 || sizeof( $coupon->shipping_countries ) > 0 ) {
			$valid_for_cart = false;
			if ( ! WC()->cart->is_empty() ) {
				if ( in_array( WC()->customer->country, $coupon->billing_countries ) || in_array( WC()->customer->shipping_country, $coupon->shipping_countries ) ) {
					$valid_for_cart = true;
				}
			}
			if ( ! $valid_for_cart ) {
				throw new Exception( self::E_WC_COUPON_INVALID_COUNTRY );
			}
		}

		return $valid_for_cart;
	}

	/**
	 * Map one of the WC_Coupon error codes to an error string.
	 * @param  string $err Error message.
	 * @param  int $err_code Error code
	 * @return string| Error string
	 */
	public static function get_country_coupon_error( $err, $err_code, $coupon ) {
		if ( self::E_WC_COUPON_INVALID_COUNTRY == $err_code ) {
			$err = sprintf( __( 'Sorry, coupon "%s" is not applicable to your country.', 'wc-coupons-by-country' ), $coupon->code );
		}

		return $err;
	}
}

WC_Meta_Box_Coupon_Usage_Data::init();
