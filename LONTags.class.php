<?php

/**
 * Load via GBS Add-On API
 */
class Group_Buying_LON_Addon extends Group_Buying_Controller {

	public static function init() {
		// Hook this plugin into the GBS add-ons controller
		add_filter( 'gb_addons', array( get_class(), 'gb_addon' ), 10, 1 );
	}

	public static function gb_addon( $addons ) {
		$addons['g_analytics'] = array(
			'label' => self::__( 'Local Offer Network Tags' ),
			'description' => self::__( 'Adds transactional and registration tags.' ),
			'files' => array(
				__FILE__,
			),
			'callbacks' => array(
				array( 'Group_Buying_LON', 'init' ),
			),
		);
		return $addons;
	}

}

class Group_Buying_LON extends Group_Buying_Controller {

	const KEY = 'gb_lon_key';
	const CC = 'gb_lon_cc_code';
	private static $key;
	private static $cc;

	public static function init() {
		self::$key = get_option( self::KEY );
		self::$cc = get_option( self::CC, 'US' );

		add_filter( 'wp_footer', array( get_class(), 'tracker_code' ) );

		// Filter the registration redirect
		add_filter( 'gb_registration_redirect', array( get_class(), 'registration_redirect' ) );

		// Options
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 0 );
	}

	public static function registration_redirect( $url ) {
		return add_query_arg( array( 'welcome' => 1 ), $url );
	}

	public function tracker_code() {
		/**
		 * Registration tracking
		 */
		if ( isset( $_REQUEST['welcome'] ) && $_REQUEST['welcome'] ) {
			$account = Group_Buying_Account::get_instance();
			$address = $account->get_address();
			?>
				<script type="text/javascript">console.log('lon registration')</script>
				<iframe src="https://www.lontrk.com/confirm?type=registration&aid=<?php echo self::$key ?>&ref=<?php echo get_current_user_id() ?>&market=<?php echo $address['city'] ?>" scrolling="no" frameborder="0" width="1" height="1"></iframe>

			<?php
		}
		if ( get_query_var( Group_Buying_Checkouts::CHECKOUT_QUERY_VAR ) && gb_get_current_checkout_page() == 'confirmation' ) {
			global $gb_purchase_confirmation_id;
			$purchase = Group_Buying_Purchase::get_instance($gb_purchase_confirmation_id);
			$user_id = $purchase->get_user();
			$account = Group_Buying_Account::get_instance($user_id);
			$address = $account->get_address();
			$item_names = array();
			$item_ids = array();
			$pending = TRUE;
			foreach ( $purchase->get_products() as $product ) {
				$item_ids[] = $product['deal_id'];
				$item_names[] = get_the_title( $product['deal_id'] );
				if ( gb_has_deal_tipped( $product['deal_id'] ) ) {
					$pending = FALSE;
				}
			}
			$status = ( $pending ) ? 'pending' : 'confirmed' ;
			?>
				<script type="text/javascript">console.log('lon confirmation')</script>
				<iframe_src="https://lontrk.com/confirm?type=sale&aid=<?php echo self::$key ?>&ref=<?php echo $purchase->get_id() ?>&qty=<?php echo count( $item_ids ) ?>&price=<?php echo $purchase->get_total() ?>&currency=<?php echo self::$cc ?>&item_id=<?php echo implode( ', ', $item_ids ) ?>&item_name=<?php echo implode( ', ', $item_names ) ?>&market=<?php echo $address['city'] ?>&status=<?php echo $status ?>" scrolling="no" frameborder="no" width="1" height="1"></frame>


			<?php
		}
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_lon_ga_settings';
		add_settings_section( $section, self::__( 'Local Offer Network' ), array( get_class(), 'display_settings_section' ), $page );
		// Settings
		register_setting( $page, self::KEY );
		register_setting( $page, self::CC );

		// Fields
		add_settings_field( self::KEY, self::__( 'Advertiser ID' ), array( get_class(), 'display_option' ), $page, $section );
		add_settings_field( self::CC, self::__( 'Currency Code' ), array( get_class(), 'display_cc_option' ), $page, $section );
	}

	public function display_settings_section() {
		gb_e( 'Provided by LON.' );
	}

	public static function display_option() {
		echo '<input name="'.self::KEY.'" id="'.self::KEY.'" type="text" maxlength="30" value="'.self::$key.'">';
	}

	public static function display_cc_option() {
		echo '<input name="'.self::CC.'" id="'.self::CC.'" type="text" maxlength="30" value="'.self::$cc.'">';
	}
}
