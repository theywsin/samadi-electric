<?php
/**
 * My Account dashboard — greeting, shortcut tiles and recent orders (Samadi Electric design).
 *
 * Based on WooCommerce's myaccount/dashboard.php; all hooks are kept.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$samadi_orders = wc_get_orders(
	array(
		'customer_id' => get_current_user_id(),
		'limit'       => 3,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'status'      => array_keys( wc_get_order_statuses() ),
	)
);
$samadi_items  = wc_get_account_menu_items();
$samadi_tiles  = array(
	'orders'       => array( 'package', __( 'سفارش‌های من', 'samadi-electric' ), __( 'پیگیری وضعیت و جزئیات سفارش‌ها', 'samadi-electric' ) ),
	'edit-address' => array( 'map-pin', __( 'آدرس‌ها', 'samadi-electric' ), __( 'آدرس ارسال و صورتحساب', 'samadi-electric' ) ),
	'edit-account' => array( 'user', __( 'اطلاعات حساب', 'samadi-electric' ), __( 'نام، ایمیل و رمز عبور', 'samadi-electric' ) ),
);
?>

<div class="se-account-hello">
	<h2 class="se-account-hello__title">
		<?php
		/* translators: %s: customer name. */
		echo esc_html( sprintf( __( 'سلام %s، خوش آمدید', 'samadi-electric' ), $current_user->display_name ) );
		?>
	</h2>
	<p class="se-account-hello__text"><?php esc_html_e( 'از این بخش می‌توانید سفارش‌ها، آدرس‌ها و اطلاعات حساب خود را مدیریت کنید.', 'samadi-electric' ); ?></p>
</div>

<ul class="se-account-tiles">
	<?php foreach ( $samadi_tiles as $samadi_endpoint => $samadi_tile ) : ?>
		<?php
		if ( ! isset( $samadi_items[ $samadi_endpoint ] ) ) {
			continue;
		}
		?>
		<li>
			<a class="se-account-tile" href="<?php echo esc_url( wc_get_account_endpoint_url( $samadi_endpoint ) ); ?>">
				<span class="se-account-tile__icon"><?php samadi_icon( $samadi_tile[0], array( 'size' => 24 ) ); ?></span>
				<strong class="se-account-tile__title"><?php echo esc_html( $samadi_tile[1] ); ?></strong>
				<span class="se-account-tile__text"><?php echo esc_html( $samadi_tile[2] ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>

<?php if ( $samadi_orders ) : ?>
	<section class="se-account-recent">
		<div class="se-section-head">
			<h3 class="se-section-head__title"><?php esc_html_e( 'آخرین سفارش‌ها', 'samadi-electric' ); ?></h3>
			<a class="se-section-head__link" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'samadi-electric' ); ?><?php samadi_icon( 'arrow-left', array( 'size' => 14 ) ); ?></a>
		</div>
		<ul class="se-order-list">
			<?php foreach ( $samadi_orders as $samadi_order ) : ?>
				<li class="se-order-row">
					<a href="<?php echo esc_url( $samadi_order->get_view_order_url() ); ?>">
						<span class="se-order-row__id">
							<?php
							/* translators: %s: order number. */
							echo esc_html( sprintf( __( 'سفارش #%s', 'samadi-electric' ), samadi_option( 'persian_digits' ) ? samadi_fa_digits( $samadi_order->get_order_number() ) : $samadi_order->get_order_number() ) );
							?>
						</span>
						<span class="se-order-row__date"><?php echo esc_html( wc_format_datetime( $samadi_order->get_date_created() ) ); ?></span>
						<span class="se-status se-status--<?php echo esc_attr( $samadi_order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $samadi_order->get_status() ) ); ?></span>
						<span class="se-order-row__total"><?php echo wp_kses_post( $samadi_order->get_formatted_order_total() ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
