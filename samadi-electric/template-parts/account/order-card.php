<?php
/**
 * One order in the account order list (orders endpoint and dashboard).
 *
 * Every column from wc_get_account_orders_columns() is still rendered, and a column with a
 * woocommerce_my_account_my_orders_column_{id} callback is printed by that callback.
 *
 * @package Samadi_Electric
 *
 * @var array $args { order: WC_Order, compact: bool }
 */

defined( 'ABSPATH' ) || exit;

$samadi_order = isset( $args['order'] ) ? $args['order'] : null;

if ( ! $samadi_order instanceof WC_Order ) {
	return;
}

$samadi_compact    = ! empty( $args['compact'] );
$samadi_columns    = wc_get_account_orders_columns();
$samadi_item_count = $samadi_order->get_item_count() - $samadi_order->get_item_count_refunded();
$samadi_number     = $samadi_order->get_order_number();
$samadi_number_txt = samadi_option( 'persian_digits' ) ? samadi_fa_digits( $samadi_number ) : $samadi_number;
$samadi_core       = array( 'order-number', 'order-date', 'order-status', 'order-total', 'order-actions' );
$samadi_date       = $samadi_order->get_date_created();
$samadi_thumbs     = array();
$samadi_more       = 0;

foreach ( $samadi_order->get_items() as $samadi_item ) {
	if ( count( $samadi_thumbs ) >= 4 ) {
		++$samadi_more;
		continue;
	}
	$samadi_product = is_callable( array( $samadi_item, 'get_product' ) ) ? $samadi_item->get_product() : null;
	if ( $samadi_product ) {
		$samadi_thumbs[] = array(
			'image' => $samadi_product->get_image( 'woocommerce_gallery_thumbnail', array( 'loading' => 'lazy' ) ),
			'name'  => $samadi_item->get_name(),
		);
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
<article class="se-order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $samadi_order->get_status() ); ?> order<?php echo $samadi_compact ? ' se-order--compact' : ''; ?>">
	<header class="se-order__head">
		<div class="se-order__id">
			<span class="se-order__icon"><?php samadi_icon( 'package', array( 'size' => 20 ) ); ?></span>
			<div>
				<?php if ( isset( $samadi_columns['order-number'] ) ) : ?>
					<h3 class="se-order__number woocommerce-orders-table__cell-order-number">
						<?php if ( ! $samadi_hooked( 'order-number', $samadi_order ) ) : ?>
							<a href="<?php echo esc_url( $samadi_order->get_view_order_url() ); ?>">
								<?php
								/* translators: %s: order number. */
								echo esc_html( sprintf( __( 'سفارش #%s', 'samadi-electric' ), $samadi_number_txt ) );
								?>
							</a>
						<?php endif; ?>
					</h3>
				<?php endif; ?>
				<?php if ( isset( $samadi_columns['order-date'] ) ) : ?>
					<p class="se-order__date woocommerce-orders-table__cell-order-date">
						<?php if ( ! $samadi_hooked( 'order-date', $samadi_order ) && $samadi_date ) : ?>
							<time datetime="<?php echo esc_attr( $samadi_date->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $samadi_date ) ); ?></time>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( isset( $samadi_columns['order-status'] ) ) : ?>
			<div class="se-order__status woocommerce-orders-table__cell-order-status">
				<?php
				if ( ! $samadi_hooked( 'order-status', $samadi_order ) ) {
					echo samadi_account_status_badge( $samadi_order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper.
				}
				?>
			</div>
		<?php endif; ?>
	</header>

	<div class="se-order__body">
		<?php if ( $samadi_thumbs ) : ?>
			<ul class="se-order__items" aria-label="<?php esc_attr_e( 'کالاهای سفارش', 'samadi-electric' ); ?>">
				<?php foreach ( $samadi_thumbs as $samadi_thumb ) : ?>
					<li class="se-order__thumb" title="<?php echo esc_attr( $samadi_thumb['name'] ); ?>">
						<?php echo $samadi_thumb['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce image markup. ?>
						<span class="screen-reader-text"><?php echo esc_html( $samadi_thumb['name'] ); ?></span>
					</li>
				<?php endforeach; ?>
				<?php if ( $samadi_more ) : ?>
					<li class="se-order__thumb se-order__thumb--more">+<?php echo esc_html( samadi_number( $samadi_more ) ); ?></li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>

		<dl class="se-order__meta">
			<?php if ( isset( $samadi_columns['order-total'] ) ) : ?>
				<div class="se-order__meta-row woocommerce-orders-table__cell-order-total">
					<dt><?php echo esc_html( $samadi_columns['order-total'] ); ?></dt>
					<dd>
						<?php if ( ! $samadi_hooked( 'order-total', $samadi_order ) ) : ?>
							<strong class="se-order__total"><?php echo wp_kses_post( $samadi_order->get_formatted_order_total() ); ?></strong>
							<span class="se-order__count">
								<?php
								/* translators: %s: number of items. */
								echo esc_html( sprintf( __( '%s کالا', 'samadi-electric' ), samadi_number( $samadi_item_count ) ) );
								?>
							</span>
						<?php endif; ?>
					</dd>
				</div>
			<?php endif; ?>
			<?php foreach ( $samadi_columns as $samadi_column_id => $samadi_column_name ) : ?>
				<?php
				if ( in_array( $samadi_column_id, $samadi_core, true ) ) {
					continue;
				}
				?>
				<div class="se-order__meta-row woocommerce-orders-table__cell-<?php echo esc_attr( $samadi_column_id ); ?>">
					<dt><?php echo esc_html( $samadi_column_name ); ?></dt>
					<dd><?php $samadi_hooked( $samadi_column_id, $samadi_order ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>

	<?php if ( isset( $samadi_columns['order-actions'] ) ) : ?>
		<footer class="se-order__actions woocommerce-orders-table__cell-order-actions">
			<?php
			if ( ! $samadi_hooked( 'order-actions', $samadi_order ) ) {
				$samadi_actions = wc_get_account_orders_actions( $samadi_order );
				foreach ( $samadi_actions as $samadi_key => $samadi_action ) {
					$samadi_label = empty( $samadi_action['aria-label'] )
						/* translators: 1: action name, 2: order number. */
						? sprintf( __( '%1$s سفارش %2$s', 'samadi-electric' ), $samadi_action['name'], $samadi_number )
						: $samadi_action['aria-label'];
					$samadi_class = 'view' === $samadi_key ? 'se-btn se-btn--primary' : 'se-btn se-btn--outline';
					printf(
						'<a href="%1$s" class="woocommerce-button button %2$s %3$s" aria-label="%4$s">%5$s</a>',
						esc_url( $samadi_action['url'] ),
						esc_attr( $samadi_class ),
						sanitize_html_class( $samadi_key ),
						esc_attr( $samadi_label ),
						esc_html( $samadi_action['name'] )
					);
				}
			}
			?>
		</footer>
	<?php endif; ?>
</article>
