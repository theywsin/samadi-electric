<?php
/**
 * Address cards (dashboard panel and the addresses endpoint).
 *
 * Types and titles come from the woocommerce_my_account_get_addresses filter; a missing
 * address is offered as an "add address" card.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_types   = samadi_account_address_types();
$samadi_missing = '';
$samadi_first   = true;
?>
<div class="se-addresses u-columns woocommerce-Addresses col2-set addresses">
	<?php foreach ( $samadi_types as $samadi_type => $samadi_title ) : ?>
		<?php
		$samadi_address = samadi_account_address( $samadi_type );
		if ( ! $samadi_address ) {
			$samadi_missing = $samadi_missing ? $samadi_missing : $samadi_type;
			continue;
		}
		$samadi_default = $samadi_first;
		$samadi_first   = false;
		?>
		<div class="se-address woocommerce-Address<?php echo $samadi_default ? ' is-default' : ''; ?>">
			<header class="se-address__head woocommerce-Address-title title">
				<span class="se-address__icon"><?php samadi_icon( 'shipping' === $samadi_type ? 'truck' : 'house', array( 'size' => 20 ) ); ?></span>
				<h3 class="se-address__title"><?php echo esc_html( $samadi_title ); ?></h3>
				<?php if ( $samadi_default ) : ?>
					<span class="se-address__badge"><?php esc_html_e( 'پیش‌فرض', 'samadi-electric' ); ?></span>
				<?php endif; ?>
				<a class="se-address__edit edit" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $samadi_type, wc_get_page_permalink( 'myaccount' ) ) ); ?>">
					<?php samadi_icon( 'pencil', array( 'size' => 16 ) ); ?>
					<span class="screen-reader-text">
						<?php
						/* translators: %s: address title. */
						echo esc_html( sprintf( __( 'ویرایش %s', 'samadi-electric' ), $samadi_title ) );
						?>
					</span>
				</a>
			</header>
			<address class="se-address__lines">
				<?php
				echo wp_kses_post( $samadi_address['lines'] );

				/**
				 * Used to output content after core address fields.
				 *
				 * @param string $name Address type.
				 * @since 8.7.0
				 */
				do_action( 'woocommerce_my_account_after_my_address', $samadi_type );
				?>
			</address>
			<?php if ( $samadi_address['name'] || $samadi_address['phone'] ) : ?>
				<footer class="se-address__contact">
					<?php if ( $samadi_address['name'] ) : ?>
						<span><?php echo esc_html( $samadi_address['name'] ); ?></span>
					<?php endif; ?>
					<?php if ( $samadi_address['phone'] ) : ?>
						<span class="se-address__phone" dir="ltr"><?php echo esc_html( $samadi_address['phone'] ); ?></span>
					<?php endif; ?>
				</footer>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>

	<?php if ( $samadi_missing ) : ?>
		<div class="se-address se-address--add">
			<header class="se-address__head">
				<span class="se-address__icon se-address__icon--plus"><?php samadi_icon( 'plus', array( 'size' => 20 ) ); ?></span>
				<h3 class="se-address__title"><?php esc_html_e( 'آدرس جدید', 'samadi-electric' ); ?></h3>
			</header>
			<p class="se-address__lines"><?php esc_html_e( 'برای افزودن آدرس جدید روی دکمه زیر کلیک کنید.', 'samadi-electric' ); ?></p>
			<a class="se-btn se-btn--primary se-address__add" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $samadi_missing, wc_get_page_permalink( 'myaccount' ) ) ); ?>">
				<?php esc_html_e( 'افزودن آدرس جدید', 'samadi-electric' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
