<?php
/**
 * Orders list.
 *
 * Replaces woocommerce/templates/myaccount/orders.php; the orders hooks, actions
 * (wc_get_account_orders_actions) and pagination are kept.
 *
 * @package Samadi_Account
 * @version 9.5.0
 *
 * @var bool   $has_orders      Has orders.
 * @var object $customer_orders Orders query result.
 * @var int    $current_page    Current page.
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>
<section class="sad-card">
	<?php sad_template( 'parts/head', array( 'title' => 'سفارش‌های من', 'tag' => 'h1' ) ); ?>

	<?php if ( $has_orders ) : ?>
		<ul class="sad-orders sad-orders--full">
			<?php
			foreach ( $customer_orders->orders as $sad_customer_order ) {
				$sad_order = wc_get_order( $sad_customer_order );
				if ( $sad_order ) {
					sad_template( 'parts/order-row', array( 'order' => $sad_order, 'actions' => true ) );
				}
			}
			?>
		</ul>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<div class="sad-pagination woocommerce-pagination woocommerce-Pagination">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="sad-btn sad-btn--soft woocommerce-button--previous" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">سفارش‌های جدیدتر</a>
				<?php endif; ?>
				<span class="sad-pagination__info"><?php echo esc_html( sad_fa( 'صفحه ' . $current_page . ' از ' . (int) $customer_orders->max_num_pages ) ); ?></span>
				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="sad-btn sad-btn--soft woocommerce-button--next" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">سفارش‌های قدیمی‌تر</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="sad-empty">
			<?php echo sad_icon( 'shopping-bag', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>هنوز سفارشی ثبت نکرده‌اید.</p>
			<a class="sad-btn sad-btn--primary" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>">مشاهده محصولات</a>
		</div>
	<?php endif; ?>
</section>
<?php
do_action( 'woocommerce_after_account_orders', $has_orders );
