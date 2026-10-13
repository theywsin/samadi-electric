<?php
/**
 * My Account dashboard: greeting, order stats, shortcuts and recent orders.
 *
 * Based on WooCommerce's myaccount/dashboard.php; the dashboard hooks still fire at the end.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$samadi_menu   = wc_get_account_menu_items();
$samadi_recent = isset( $samadi_menu['orders'] ) ? samadi_account_recent_orders( 3 ) : array();
$samadi_name   = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
$samadi_skip   = array( 'dashboard', 'customer-logout' );
?>

<section class="se-account-hero">
	<div class="se-account-hero__text">
		<h2 class="se-account-hero__title">
			<?php
			/* translators: %s: customer first name or display name. */
			echo esc_html( sprintf( __( 'سلام %s، خوش آمدید', 'samadi-electric' ), $samadi_name ) );
			?>
		</h2>
		<p class="se-account-hero__desc"><?php esc_html_e( 'از این بخش می‌توانید سفارش‌ها را پیگیری کنید، آدرس‌ها را مدیریت کنید و اطلاعات حساب خود را تغییر دهید.', 'samadi-electric' ); ?></p>
	</div>
	<a class="se-account-hero__logout" href="<?php echo esc_url( wc_logout_url() ); ?>">
		<?php samadi_icon( 'log-out', array( 'size' => 18 ) ); ?>
		<span>
			<?php
			/* translators: %s: customer display name. */
			echo esc_html( sprintf( __( '%s نیستید؟ خروج', 'samadi-electric' ), $current_user->display_name ) );
			?>
		</span>
	</a>
</section>

<?php if ( isset( $samadi_menu['orders'] ) ) : ?>
	<ul class="se-account-stats">
		<?php foreach ( samadi_account_stats() as $samadi_stat ) : ?>
			<li class="se-account-stat se-account-stat--<?php echo esc_attr( $samadi_stat['tone'] ); ?>">
				<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">
					<span class="se-account-stat__icon"><?php samadi_icon( $samadi_stat['icon'], array( 'size' => 22 ) ); ?></span>
					<span class="se-account-stat__value"><?php echo esc_html( samadi_number( $samadi_stat['value'] ) ); ?></span>
					<span class="se-account-stat__label"><?php echo esc_html( $samadi_stat['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<nav class="se-account-shortcuts" aria-label="<?php esc_attr_e( 'دسترسی سریع', 'samadi-electric' ); ?>">
	<?php foreach ( $samadi_menu as $samadi_endpoint => $samadi_label ) : ?>
		<?php
		if ( in_array( $samadi_endpoint, $samadi_skip, true ) ) {
			continue;
		}
		$samadi_desc = samadi_account_menu_description( $samadi_endpoint );
		?>
		<a class="se-account-shortcut" href="<?php echo esc_url( wc_get_account_endpoint_url( $samadi_endpoint ) ); ?>">
			<span class="se-account-shortcut__icon"><?php samadi_icon( samadi_account_menu_icon( $samadi_endpoint ), array( 'size' => 22 ) ); ?></span>
			<span class="se-account-shortcut__text">
				<strong><?php echo esc_html( $samadi_label ); ?></strong>
				<?php if ( $samadi_desc ) : ?>
					<span><?php echo esc_html( $samadi_desc ); ?></span>
				<?php endif; ?>
			</span>
		</a>
	<?php endforeach; ?>
</nav>

<?php if ( isset( $samadi_menu['orders'] ) ) : ?>
	<section class="se-account-section">
		<header class="se-account-section__head">
			<h2 class="se-account-section__title"><?php esc_html_e( 'آخرین سفارش‌ها', 'samadi-electric' ); ?></h2>
			<?php if ( $samadi_recent ) : ?>
				<a class="se-account-section__more" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">
					<span><?php esc_html_e( 'مشاهده همه', 'samadi-electric' ); ?></span>
					<?php samadi_icon( 'arrow-left', array( 'size' => 14 ) ); ?>
				</a>
			<?php endif; ?>
		</header>
		<?php if ( $samadi_recent ) : ?>
			<div class="se-orders">
				<?php
				foreach ( $samadi_recent as $samadi_order ) {
					get_template_part(
						'template-parts/account/order-card',
						null,
						array(
							'order'   => $samadi_order,
							'compact' => true,
						)
					);
				}
				?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/account/empty' ); ?>
		<?php endif; ?>
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
