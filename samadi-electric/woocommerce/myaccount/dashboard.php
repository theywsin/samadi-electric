<?php
/**
 * My Account dashboard: greeting banner, stats, recent orders, addresses, wishlist and
 * account details.
 *
 * Based on WooCommerce's myaccount/dashboard.php; the dashboard hooks still fire at the end.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$samadi_menu   = wc_get_account_menu_items();
$samadi_recent = isset( $samadi_menu['orders'] ) ? samadi_account_recent_orders( 4 ) : array();
$samadi_name   = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
$samadi_banner = samadi_account_banner_image();
$samadi_phone  = (string) get_user_meta( $current_user->ID, 'billing_phone', true );
$samadi_full   = trim( $current_user->first_name . ' ' . $current_user->last_name );
?>

<section class="se-account-hero<?php echo $samadi_banner ? ' has-image' : ''; ?>"<?php echo $samadi_banner ? ' style="--se-hero-image: url(' . esc_url( $samadi_banner ) . ')"' : ''; ?>>
	<div class="se-account-hero__text">
		<h2 class="se-account-hero__title">
			<?php
			/* translators: %s: customer first name or display name. */
			echo esc_html( sprintf( __( 'سلام، %s جان', 'samadi-electric' ), $samadi_name ) );
			?>
			<span class="se-account-hero__wave" aria-hidden="true">👋</span>
		</h2>
		<p class="se-account-hero__desc">
			<?php
			/* translators: %s: site name. */
			echo esc_html( sprintf( __( 'به حساب کاربری خود در %s خوش آمدید.', 'samadi-electric' ), get_bloginfo( 'name' ) ) );
			?>
			<br>
			<?php esc_html_e( 'از اینجا می‌توانید سفارش‌ها، آدرس‌ها و اطلاعات حساب خود را مدیریت کنید.', 'samadi-electric' ); ?>
		</p>
	</div>
</section>

<?php $samadi_stats = samadi_account_stats(); ?>
<?php if ( $samadi_stats ) : ?>
	<ul class="se-account-stats">
		<?php foreach ( $samadi_stats as $samadi_stat ) : ?>
			<li class="se-account-stat se-account-stat--<?php echo esc_attr( $samadi_stat['key'] ); ?>">
				<div class="se-account-stat__head">
					<span class="se-account-stat__icon"><?php samadi_icon( $samadi_stat['icon'], array( 'size' => 20 ) ); ?></span>
					<span class="se-account-stat__label"><?php echo esc_html( $samadi_stat['label'] ); ?></span>
				</div>
				<strong class="se-account-stat__value"><?php echo wp_kses_post( $samadi_stat['value'] ); ?></strong>
				<a class="se-account-stat__link" href="<?php echo esc_url( $samadi_stat['url'] ); ?>">
					<span><?php echo esc_html( $samadi_stat['link'] ); ?></span>
					<?php samadi_icon( 'chevron-left', array( 'size' => 14 ) ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php if ( isset( $samadi_menu['orders'] ) ) : ?>
	<section class="se-panel">
		<header class="se-panel__head">
			<h2 class="se-panel__title"><?php esc_html_e( 'آخرین سفارش‌های من', 'samadi-electric' ); ?></h2>
			<?php if ( $samadi_recent ) : ?>
				<a class="se-panel__more" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
					<span><?php esc_html_e( 'مشاهده همه سفارش‌ها', 'samadi-electric' ); ?></span>
					<?php samadi_icon( 'chevron-left', array( 'size' => 16 ) ); ?>
				</a>
			<?php endif; ?>
		</header>
		<?php if ( $samadi_recent ) : ?>
			<ul class="se-order-list">
				<?php
				foreach ( $samadi_recent as $samadi_order ) {
					get_template_part( 'template-parts/account/order-row', null, array( 'order' => $samadi_order ) );
				}
				?>
			</ul>
		<?php else : ?>
			<?php get_template_part( 'template-parts/account/empty' ); ?>
		<?php endif; ?>
	</section>
<?php endif; ?>

<?php if ( isset( $samadi_menu['edit-address'] ) ) : ?>
	<section class="se-panel">
		<header class="se-panel__head">
			<h2 class="se-panel__title"><?php esc_html_e( 'آدرس‌های من', 'samadi-electric' ); ?></h2>
			<a class="se-panel__more" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>">
				<span><?php esc_html_e( 'مدیریت آدرس‌ها', 'samadi-electric' ); ?></span>
				<?php samadi_icon( 'chevron-left', array( 'size' => 16 ) ); ?>
			</a>
		</header>
		<?php get_template_part( 'template-parts/account/addresses' ); ?>
	</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/account/wishlist' ); ?>

<?php if ( isset( $samadi_menu['edit-account'] ) ) : ?>
	<section class="se-panel se-panel--plain">
		<header class="se-panel__head">
			<h2 class="se-panel__title"><?php esc_html_e( 'اطلاعات حساب کاربری', 'samadi-electric' ); ?></h2>
			<a class="se-panel__more" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>">
				<span><?php esc_html_e( 'ویرایش اطلاعات', 'samadi-electric' ); ?></span>
				<?php samadi_icon( 'chevron-left', array( 'size' => 16 ) ); ?>
			</a>
		</header>
		<dl class="se-account-info">
			<div class="se-account-info__item">
				<span class="se-account-info__icon"><?php samadi_icon( 'key-round', array( 'size' => 20 ) ); ?></span>
				<dt><?php esc_html_e( 'رمز عبور', 'samadi-electric' ); ?></dt>
				<dd aria-label="<?php esc_attr_e( 'پنهان', 'samadi-electric' ); ?>">••••••••</dd>
			</div>
			<div class="se-account-info__item">
				<span class="se-account-info__icon"><?php samadi_icon( 'user', array( 'size' => 20 ) ); ?></span>
				<dt><?php esc_html_e( 'نام و نام خانوادگی', 'samadi-electric' ); ?></dt>
				<dd><?php echo esc_html( $samadi_full ? $samadi_full : $current_user->display_name ); ?></dd>
			</div>
			<div class="se-account-info__item">
				<span class="se-account-info__icon"><?php samadi_icon( 'phone', array( 'size' => 20 ) ); ?></span>
				<dt><?php esc_html_e( 'شماره موبایل', 'samadi-electric' ); ?></dt>
				<dd dir="ltr"><?php echo $samadi_phone ? esc_html( samadi_option( 'persian_digits' ) ? samadi_fa_digits( $samadi_phone ) : $samadi_phone ) : esc_html__( 'ثبت نشده', 'samadi-electric' ); ?></dd>
			</div>
			<div class="se-account-info__item">
				<span class="se-account-info__icon"><?php samadi_icon( 'mail', array( 'size' => 20 ) ); ?></span>
				<dt><?php esc_html_e( 'ایمیل', 'samadi-electric' ); ?></dt>
				<dd dir="ltr"><?php echo esc_html( $current_user->user_email ); ?></dd>
			</div>
		</dl>
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
