<?php
/**
 * Wishlist endpoint.
 *
 * @package Samadi_Account
 *
 * @var int[] $ids Product IDs.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="sad-panel">
	<?php sad_template( 'parts/head', array( 'title' => 'علاقه‌مندی‌های من', 'tag' => 'h1' ) ); ?>
	<?php if ( $ids ) : ?>
		<div class="sad-products sad-products--full">
			<?php
			foreach ( $ids as $sad_pid ) {
				$sad_product = wc_get_product( $sad_pid );
				if ( $sad_product ) {
					sad_template( 'parts/product-card', array( 'product' => $sad_product ) );
				}
			}
			?>
		</div>
	<?php else : ?>
		<div class="sad-empty sad-empty--flat">
			<?php echo sad_icon( 'heart', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>لیست علاقه‌مندی‌های شما خالی است. با زدن آیکون قلب روی محصولات، آن‌ها را این‌جا ذخیره کنید.</p>
			<a class="sad-btn sad-btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">مشاهده محصولات</a>
		</div>
	<?php endif; ?>
</section>
