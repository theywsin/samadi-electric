<?php
/**
 * One order row.
 *
 * @package Samadi_Account
 *
 * @var WC_Order $order   Order.
 * @var bool     $actions Show all WooCommerce order actions (orders page).
 */

defined( 'ABSPATH' ) || exit;

$sad_status = sad_status( $order->get_status() );
$sad_count  = $order->get_item_count() - $order->get_item_count_refunded();
?>
<li class="sad-order">
	<span class="sad-order__thumb"><?php echo Samadi_Account::order_thumb( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in order_thumb(). ?></span>
	<a class="sad-order__number" href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php echo esc_html( sad_fa( '#' . $order->get_order_number() ) ); ?></a>
	<span class="sad-order__date">
		<?php if ( $order->get_date_created() ) : ?>
			<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( sad_date( $order->get_date_created() ) ); ?></time>
		<?php endif; ?>
		<small class="sad-order__items"><?php echo esc_html( sad_fa( $sad_count . ' کالا' ) ); ?></small>
	</span>
	<span class="sad-order__status"><span class="sad-pill sad-pill--<?php echo esc_attr( $sad_status['tone'] ); ?>"><?php echo esc_html( $sad_status['label'] ); ?></span></span>
	<span class="sad-order__total"><?php echo wp_kses_post( sad_fa( $order->get_formatted_order_total() ) ); ?></span>
	<span class="sad-order__actions">
		<?php
		if ( ! empty( $actions ) ) {
			foreach ( wc_get_account_orders_actions( $order ) as $sad_key => $sad_action ) {
				$sad_label = 'view' === $sad_key ? 'مشاهده جزئیات' : $sad_action['name'];
				printf(
					'<a href="%1$s" class="sad-btn sad-btn--soft sad-btn--sm %2$s" aria-label="%3$s">%4$s</a>',
					esc_url( $sad_action['url'] ),
					esc_attr( sanitize_html_class( $sad_key ) ),
					esc_attr( ! empty( $sad_action['aria-label'] ) ? $sad_action['aria-label'] : $sad_label ),
					esc_html( $sad_label )
				);
			}
		} else {
			printf( '<a href="%s" class="sad-btn sad-btn--soft sad-btn--sm">مشاهده جزئیات</a>', esc_url( $order->get_view_order_url() ) );
		}
		?>
	</span>
	<a class="sad-order__go" href="<?php echo esc_url( $order->get_view_order_url() ); ?>" aria-hidden="true" tabindex="-1"><?php echo sad_icon( 'chevron-left', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
</li>
