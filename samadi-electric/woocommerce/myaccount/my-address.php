<?php
/**
 * My Addresses: one card per address type.
 *
 * Based on WooCommerce's myaccount/my-address.php; filters, classes and hooks are unchanged.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Same variable WooCommerce passes to the filter.

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters( // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'آدرس صورتحساب', 'samadi-electric' ),
			'shipping' => __( 'آدرس ارسال', 'samadi-electric' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters( // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'آدرس صورتحساب', 'samadi-electric' ),
		),
		$customer_id
	);
}

$oldcol = 1;
$col    = 1;
?>

<p class="se-account-intro">
	<?php echo apply_filters( 'woocommerce_my_account_my_address_description', esc_html__( 'این آدرس‌ها به‌صورت پیش‌فرض در صفحه تسویه‌حساب استفاده می‌شوند.', 'samadi-electric' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WooCommerce.Commenting.CommentHooks.MissingHookComment ?>
</p>

<div class="u-columns woocommerce-Addresses col2-set addresses se-addresses">

<?php foreach ( $get_addresses as $name => $address_title ) : ?>
	<?php
		$address = wc_get_account_formatted_address( $name );
		$col     = $col * -1;
		$oldcol  = $oldcol * -1;
	?>

	<div class="u-column<?php echo $col < 0 ? 1 : 2; ?> col-<?php echo $oldcol < 0 ? 1 : 2; ?> woocommerce-Address se-address<?php echo $address ? '' : ' se-address--empty'; ?>">
		<header class="woocommerce-Address-title title se-address__head">
			<span class="se-address__icon"><?php samadi_icon( 'shipping' === $name ? 'truck' : 'receipt', array( 'size' => 20 ) ); ?></span>
			<h2 class="se-address__title"><?php echo esc_html( $address_title ); ?></h2>
		</header>
		<address class="se-address__body">
			<?php
				echo $address ? wp_kses_post( $address ) : esc_html__( 'هنوز این آدرس را ثبت نکرده‌اید.', 'samadi-electric' );

				/**
				 * Used to output content after core address fields.
				 *
				 * @param string $name Address type.
				 * @since 8.7.0
				 */
				do_action( 'woocommerce_my_account_after_my_address', $name );
			?>
		</address>
		<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="edit se-btn se-btn--outline se-address__edit">
			<?php samadi_icon( $address ? 'pencil' : 'plus', array( 'size' => 18 ) ); ?>
			<span>
				<?php
				/* translators: %s: address title. */
				echo esc_html( sprintf( $address ? __( 'ویرایش %s', 'samadi-electric' ) : __( 'افزودن %s', 'samadi-electric' ), $address_title ) );
				?>
			</span>
		</a>
	</div>

<?php endforeach; ?>

</div>
