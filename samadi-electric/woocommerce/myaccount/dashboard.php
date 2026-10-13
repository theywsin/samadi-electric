<?php
/**
 * My Account dashboard — greeting, account stats, recent orders and quick links.
 *
 * Overrides woocommerce/templates/myaccount/dashboard.php; the dashboard hooks
 * (including the deprecated ones) are kept. Every figure is real WooCommerce data.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 4.4.0
 *
 * @var WP_User $current_user Current user (passed by WooCommerce).
 */

defined( 'ABSPATH' ) || exit;

$samadi_user_id = get_current_user_id();
$samadi_stats   = samadi_wc_account_stats( $samadi_user_id );
$samadi_recent  = wc_get_orders(
	array(
		'customer_id' => $samadi_user_id,
		'limit'       => 3,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'status'      => array_keys( wc_get_order_statuses() ),
	)
);
$samadi_links   = array_diff_key( wc_get_account_menu_items(), array_flip( array( 'dashboard', 'customer-logout' ) ) );
$samadi_since   = strtotime( $current_user->user_registered );
?>

<section class="se-account-hero">
	<div class="se-account-hero__text">
		<p class="se-account-hero__hello">
			<?php
			/* translators: %s: customer display name. */
			printf( esc_html__( 'سلام %s، خوش آمدید', 'samadi-electric' ), '<strong>' . esc_html( $current_user->display_name ) . '</strong>' );
			?>
		</p>
		<p class="se-account-hero__desc"><?php esc_html_e( 'از این‌جا سفارش‌ها را پیگیری کنید، آدرس‌ها و اطلاعات حساب را مدیریت کنید.', 'samadi-electric' ); ?></p>
		<?php if ( $samadi_since ) : ?>
			<p class="se-account-hero__since">
				<?php samadi_icon( 'clock', array( 'size' => 16 ) ); ?>
				<?php
				/* translators: %s: registration date. */
				echo esc_html( sprintf( __( 'عضو از %s', 'samadi-electric' ), samadi_wc_account_date( $samadi_since ) ) );
				?>
			</p>
		<?php endif; ?>
	</div>
	<a class="se-btn se-btn--accent se-account-hero__btn" href="<?php echo esc_url( samadi_shop_url() ); ?>">
		<?php samadi_icon( 'shopping-cart', array( 'size' => 18 ) ); ?>
		<span><?php esc_html_e( 'ادامه خرید', 'samadi-electric' ); ?></span>
	</a>
</section>

<ul class="se-account-stats">
	<li class="se-account-stat">
		<span class="se-account-stat__icon"><?php samadi_icon( 'package', array( 'size' => 22 ) ); ?></span>
		<span class="se-account-stat__value"><?php echo esc_html( samadi_number( $samadi_stats['orders'] ) ); ?></span>
		<span class="se-account-stat__label"><?php esc_html_e( 'کل سفارش‌ها', 'samadi-electric' ); ?></span>
	</li>
	<li class="se-account-stat se-account-stat--active">
		<span class="se-account-stat__icon"><?php samadi_icon( 'truck', array( 'size' => 22 ) ); ?></span>
		<span class="se-account-stat__value"><?php echo esc_html( samadi_number( $samadi_stats['active'] ) ); ?></span>
		<span class="se-account-stat__label"><?php esc_html_e( 'سفارش‌های جاری', 'samadi-electric' ); ?></span>
	</li>
	<li class="se-account-stat">
		<span class="se-account-stat__icon"><?php samadi_icon( 'receipt', array( 'size' => 22 ) ); ?></span>
		<span class="se-account-stat__value"><?php echo wp_kses_post( wc_price( $samadi_stats['spent'] ) ); ?></span>
		<span class="se-account-stat__label"><?php esc_html_e( 'مجموع خرید', 'samadi-electric' ); ?></span>
	</li>
</ul>

<section class="se-account-box">
	<header class="se-account-box__head">
		<h2 class="se-account-box__title"><?php esc_html_e( 'آخرین سفارش‌ها', 'samadi-electric' ); ?></h2>
		<?php if ( $samadi_recent ) : ?>
			<a class="se-account-box__more" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
				<?php esc_html_e( 'همه سفارش‌ها', 'samadi-electric' ); ?>
				<?php samadi_icon( 'chevron-left', array( 'size' => 16 ) ); ?>
			</a>
		<?php endif; ?>
	</header>

	<?php if ( $samadi_recent ) : ?>
		<ul class="se-account-orders">
			<?php foreach ( $samadi_recent as $samadi_order ) : ?>
				<?php
				if ( ! $samadi_order instanceof WC_Order ) {
					continue;
				}
				$samadi_count   = $samadi_order->get_item_count() - $samadi_order->get_item_count_refunded();
				$samadi_created = $samadi_order->get_date_created();
				?>
				<li class="se-account-order">
					<a class="se-account-order__link" href="<?php echo esc_url( $samadi_order->get_view_order_url() ); ?>">
						<span class="se-account-order__icon"><?php samadi_icon( 'package', array( 'size' => 20 ) ); ?></span>
						<span class="se-account-order__main">
							<span class="se-account-order__number">
								<?php
								/* translators: %s: order number. */
								echo esc_html( sprintf( __( 'سفارش %s', 'samadi-electric' ), samadi_number( _x( '#', 'hash before order number', 'woocommerce' ) . $samadi_order->get_order_number() ) ) );
								?>
							</span>
							<span class="se-account-order__meta">
								<?php if ( $samadi_created ) : ?>
									<time datetime="<?php echo esc_attr( $samadi_created->date( 'c' ) ); ?>"><?php echo esc_html( samadi_wc_account_date( $samadi_created->getTimestamp() ) ); ?></time>
									<span aria-hidden="true">·</span>
								<?php endif; ?>
								<?php
								/* translators: %s: number of items. */
								echo esc_html( sprintf( __( '%s کالا', 'samadi-electric' ), samadi_number( $samadi_count ) ) );
								?>
							</span>
						</span>
						<span class="se-account-order__end">
							<span class="se-status se-status--<?php echo esc_attr( $samadi_order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $samadi_order->get_status() ) ); ?></span>
							<span class="se-account-order__total"><?php echo wp_kses_post( $samadi_order->get_formatted_order_total() ); ?></span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<div class="se-account-empty">
			<span class="se-account-empty__icon"><?php samadi_icon( 'shopping-cart', array( 'size' => 28 ) ); ?></span>
			<p><?php esc_html_e( 'هنوز سفارشی ثبت نکرده‌اید.', 'samadi-electric' ); ?></p>
			<a class="se-btn se-btn--outline" href="<?php echo esc_url( samadi_shop_url() ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'samadi-electric' ); ?></a>
		</div>
	<?php endif; ?>
</section>

<?php if ( $samadi_links ) : ?>
	<section class="se-account-box se-account-box--plain">
		<header class="se-account-box__head">
			<h2 class="se-account-box__title"><?php esc_html_e( 'دسترسی سریع', 'samadi-electric' ); ?></h2>
		</header>
		<ul class="se-account-links">
			<?php foreach ( $samadi_links as $samadi_endpoint => $samadi_label ) : ?>
				<?php $samadi_meta = samadi_wc_account_item_meta( $samadi_endpoint ); ?>
				<li>
					<a class="se-account-link" href="<?php echo esc_url( wc_get_account_endpoint_url( $samadi_endpoint ) ); ?>">
						<span class="se-account-link__icon"><?php samadi_icon( $samadi_meta['icon'], array( 'size' => 22 ) ); ?></span>
						<span class="se-account-link__text">
							<span class="se-account-link__label"><?php echo esc_html( $samadi_label ); ?></span>
							<?php if ( $samadi_meta['desc'] ) : ?>
								<span class="se-account-link__desc"><?php echo esc_html( $samadi_meta['desc'] ); ?></span>
							<?php endif; ?>
						</span>
						<?php samadi_icon( 'chevron-left', array( 'size' => 18, 'class' => 'se-account-link__arrow' ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
			<?php if ( samadi_wishlist_url() ) : ?>
				<li>
					<a class="se-account-link" href="<?php echo esc_url( samadi_wishlist_url() ); ?>">
						<span class="se-account-link__icon"><?php samadi_icon( 'heart', array( 'size' => 22 ) ); ?></span>
						<span class="se-account-link__text">
							<span class="se-account-link__label"><?php esc_html_e( 'علاقه‌مندی‌ها', 'samadi-electric' ); ?></span>
							<span class="se-account-link__desc"><?php esc_html_e( 'محصولاتی که ذخیره کرده‌اید', 'samadi-electric' ); ?></span>
						</span>
						<?php samadi_icon( 'chevron-left', array( 'size' => 18, 'class' => 'se-account-link__arrow' ) ); ?>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( samadi_wholesale_url() ) : ?>
				<li>
					<a class="se-account-link" href="<?php echo esc_url( samadi_wholesale_url() ); ?>">
						<span class="se-account-link__icon"><?php samadi_icon( 'building-2', array( 'size' => 22 ) ); ?></span>
						<span class="se-account-link__text">
							<span class="se-account-link__label"><?php echo esc_html( (string) samadi_option( 'header_wholesale_label' ) ); ?></span>
							<span class="se-account-link__desc"><?php esc_html_e( 'استعلام قیمت و سفارش عمده', 'samadi-electric' ); ?></span>
						</span>
						<?php samadi_icon( 'chevron-left', array( 'size' => 18, 'class' => 'se-account-link__arrow' ) ); ?>
					</a>
				</li>
			<?php endif; ?>
			<li>
				<a class="se-account-link se-account-link--logout" href="<?php echo esc_url( wc_logout_url() ); ?>">
					<span class="se-account-link__icon"><?php samadi_icon( 'log-out', array( 'size' => 22 ) ); ?></span>
					<span class="se-account-link__text">
						<span class="se-account-link__label"><?php esc_html_e( 'خروج از حساب', 'samadi-electric' ); ?></span>
					</span>
				</a>
			</li>
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
