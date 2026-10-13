<?php
/**
 * One order row in the account order lists (dashboard and orders endpoint).
 *
 * Every column from wc_get_account_orders_columns() is still rendered, and a column with a
 * woocommerce_my_account_my_orders_column_{id} callback is printed by that callback.
 *
 * @package Samadi_Electric
 *
 * @var array $args { order: WC_Order }
 */

defined( 'ABSPATH' ) || exit;

$samadi_order = isset( $args['order'] ) ? $args['order'] : null;

if ( ! $samadi_order instanceof WC_Order ) {
	return;
}

$samadi_columns = wc_get_account_orders_columns();
$samadi_number  = $samadi_order->get_order_number();
$samadi_date    = $samadi_order->get_date_created();
$samadi_core    = array( 'order-number', 'order-date', 'order-status', 'order-total', 'order-actions' );
$samadi_thumb   = '';

foreach ( $samadi_order->get_items() as $samadi_item ) {
	$samadi_product = is_callable( array( $samadi_item, 'get_product' ) ) ? $samadi_item->get_product() : null;
	if ( $samadi_product ) {
		$samadi_thumb = $samadi_product->get_image( 'woocommerce_gallery_thumbnail', array( 'loading' => 'lazy' ) );
		break;
	}
}

/**
 * Print a column through its plugin callback when one is registered.
 *
 * @param string   $column_id Column id.
 * @param WC_Order $order     Order.
 * @return bool Whether a callback printed the column.
 */
$samadi_hooked = static function ( $column_id, $order ) {
	if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) {
		do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order );
		return true;
	}
	return false;
};
?>
<li class="se-order-row woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $samadi_order->get_status() ); ?> order">
	<span class="se-order-row__thumb" aria-hidden="true">
		<?php echo $samadi_thumb ? $samadi_thumb : samadi_get_icon( 'package', array( 'size' => 22 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce image markup / static icon. ?>
	</span>

	<?php if ( isset( $samadi_columns['order-number'] ) ) : ?>
		<span class="se-order-row__number woocommerce-orders-table__cell-order-number">
			<?php if ( ! $samadi_hooked( 'order-number', $samadi_order ) ) : ?>
				<a href="<?php echo esc_url( $samadi_order->get_view_order_url() ); ?>">
					<?php echo esc_html( '#' . ( samadi_option( 'persian_digits' ) ? samadi_fa_digits( $samadi_number ) : $samadi_number ) ); ?>
				</a>
			<?php endif; ?>
		</span>
	<?php endif; ?>

	<?php if ( isset( $samadi_columns['order-date'] ) ) : ?>
		<span class="se-order-row__date woocommerce-orders-table__cell-order-date">
			<?php if ( ! $samadi_hooked( 'order-date', $samadi_order ) && $samadi_date ) : ?>
				<time datetime="<?php echo esc_attr( $samadi_date->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $samadi_date ) ); ?></time>
			<?php endif; ?>
		</span>
	<?php endif; ?>

	<?php if ( isset( $samadi_columns['order-status'] ) ) : ?>
		<span class="se-order-row__status woocommerce-orders-table__cell-order-status">
			<?php
			if ( ! $samadi_hooked( 'order-status', $samadi_order ) ) {
				echo samadi_account_status_badge( $samadi_order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper.
			}
			?>
		</span>
	<?php endif; ?>

	<?php if ( isset( $samadi_columns['order-total'] ) ) : ?>
		<span class="se-order-row__total woocommerce-orders-table__cell-order-total">
			<?php
			if ( ! $samadi_hooked( 'order-total', $samadi_order ) ) {
				echo wp_kses_post( $samadi_order->get_formatted_order_total() );
			}
			?>
		</span>
	<?php endif; ?>

	<?php foreach ( $samadi_columns as $samadi_column_id => $samadi_column_name ) : ?>
		<?php
		if ( in_array( $samadi_column_id, $samadi_core, true ) ) {
			continue;
		}
		?>
		<span class="se-order-row__extra woocommerce-orders-table__cell-<?php echo esc_attr( $samadi_column_id ); ?>" data-title="<?php echo esc_attr( $samadi_column_name ); ?>">
			<?php $samadi_hooked( $samadi_column_id, $samadi_order ); ?>
		</span>
	<?php endforeach; ?>

	<?php if ( isset( $samadi_columns['order-actions'] ) ) : ?>
		<span class="se-order-row__actions woocommerce-orders-table__cell-order-actions">
			<?php
			if ( ! $samadi_hooked( 'order-actions', $samadi_order ) ) {
				foreach ( wc_get_account_orders_actions( $samadi_order ) as $samadi_key => $samadi_action ) {
					$samadi_label = empty( $samadi_action['aria-label'] )
						/* translators: 1: action name, 2: order number. */
						? sprintf( __( '%1$s سفارش %2$s', 'samadi-electric' ), $samadi_action['name'], $samadi_number )
						: $samadi_action['aria-label'];
					printf(
						'<a href="%1$s" class="woocommerce-button button se-order-row__btn %2$s" aria-label="%3$s">%4$s</a>',
						esc_url( $samadi_action['url'] ),
						sanitize_html_class( $samadi_key ),
						esc_attr( $samadi_label ),
						esc_html( 'view' === $samadi_key ? __( 'مشاهده جزئیات', 'samadi-electric' ) : $samadi_action['name'] )
					);
				}
			}
			?>
		</span>
	<?php endif; ?>

	<a class="se-order-row__go" href="<?php echo esc_url( $samadi_order->get_view_order_url() ); ?>" tabindex="-1" aria-hidden="true">
		<?php samadi_icon( 'chevron-left', array( 'size' => 18 ) ); ?>
	</a>
</li>
