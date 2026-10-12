<?php
/**
 * Store benefits bar.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_items = samadi_benefit_items();

if ( ! $samadi_items ) {
	return;
}
?>
<ul class="se-benefits" style="--se-benefits-count: <?php echo esc_attr( count( $samadi_items ) ); ?>">
	<?php foreach ( $samadi_items as $samadi_item ) : ?>
		<li class="se-benefits__item">
			<span class="se-benefits__icon"><?php samadi_icon( $samadi_item['icon'], array( 'size' => 26 ) ); ?></span>
			<span class="se-benefits__body">
				<strong class="se-benefits__title"><?php echo esc_html( $samadi_item['title'] ); ?></strong>
				<?php if ( $samadi_item['text'] ) : ?>
					<span class="se-benefits__text"><?php echo esc_html( $samadi_item['text'] ); ?></span>
				<?php endif; ?>
			</span>
		</li>
	<?php endforeach; ?>
</ul>
