<?php
/**
 * Empty state for account lists (no orders yet).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_shop_url = apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
?>
<div class="se-account-empty woocommerce-info--no-orders">
	<span class="se-account-empty__icon"><?php samadi_icon( 'package', array( 'size' => 32 ) ); ?></span>
	<h3 class="se-account-empty__title"><?php esc_html_e( 'هنوز سفارشی ثبت نکرده‌اید', 'samadi-electric' ); ?></h3>
	<p class="se-account-empty__text"><?php esc_html_e( 'سفارش‌های شما پس از خرید اینجا نمایش داده می‌شوند و می‌توانید وضعیت آن‌ها را پیگیری کنید.', 'samadi-electric' ); ?></p>
	<a class="se-btn se-btn--primary" href="<?php echo esc_url( $samadi_shop_url ); ?>">
		<?php samadi_icon( 'shopping-cart', array( 'size' => 18 ) ); ?>
		<span><?php esc_html_e( 'مشاهده محصولات', 'samadi-electric' ); ?></span>
	</a>
</div>
