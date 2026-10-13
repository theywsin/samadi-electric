<?php
/**
 * Address book (edit-address endpoint without a type): list, add and edit.
 *
 * Replaces woocommerce/templates/myaccount/my-address.php. The WooCommerce billing and
 * shipping forms (edit-address/billing, edit-address/shipping) are untouched.
 *
 * @package Samadi_Account
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$sad_user_id = get_current_user_id();
$sad_list    = Samadi_Account_Addresses::all( $sad_user_id );
$sad_default = Samadi_Account_Addresses::default_id( $sad_user_id );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Selects which form to show.
$sad_form = isset( $_GET['sad-address'] ) && sad_option( 'address_book' ) ? sanitize_key( wp_unslash( $_GET['sad-address'] ) ) : '';

if ( $sad_form && ( 'new' === $sad_form || isset( $sad_list[ $sad_form ] ) ) ) {
	sad_template(
		'myaccount/address-form',
		array(
			'id'      => 'new' === $sad_form ? '' : $sad_form,
			'address' => 'new' === $sad_form ? array() : $sad_list[ $sad_form ],
			'default' => $sad_form === $sad_default,
		)
	);
	return;
}

uksort(
	$sad_list,
	static function ( $a, $b ) use ( $sad_default ) {
		return ( $b === $sad_default ) - ( $a === $sad_default );
	}
);
?>
<section class="sad-panel">
	<?php sad_template( 'parts/head', array( 'title' => 'آدرس‌های من', 'tag' => 'h1' ) ); ?>
	<p class="sad-muted"><?php echo esc_html( sad_option( 'address_book' ) ? 'آدرس پیش‌فرض به‌صورت خودکار در صفحه تسویه‌حساب استفاده می‌شود.' : apply_filters( 'woocommerce_my_account_my_address_description', __( 'The following addresses will be used on the checkout page by default.', 'woocommerce' ) ) ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?></p>
	<div class="sad-addresses">
		<?php
		foreach ( $sad_list as $sad_id => $sad_address ) {
			sad_template( 'parts/address-card', array( 'id' => $sad_id, 'address' => $sad_address, 'default' => $sad_id === $sad_default, 'manage' => true ) );
		}
		if ( sad_option( 'address_book' ) ) {
			sad_template( 'parts/address-new' );
		} elseif ( ! $sad_list ) {
			printf( '<a class="sad-btn sad-btn--primary" href="%s">افزودن آدرس</a>', esc_url( wc_get_endpoint_url( 'edit-address', 'billing' ) ) );
		}
		?>
	</div>
</section>
