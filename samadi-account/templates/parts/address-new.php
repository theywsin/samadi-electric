<?php
/**
 * "New address" card.
 *
 * @package Samadi_Account
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sad-address sad-address--new">
	<div class="sad-address__top">
		<span class="sad-address__plus"><?php echo sad_icon( 'plus', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<p class="sad-address__label">آدرس جدید</p>
	</div>
	<p class="sad-address__line">برای افزودن آدرس جدید روی دکمه زیر کلیک کنید.</p>
	<a class="sad-btn sad-btn--primary sad-btn--block" href="<?php echo esc_url( Samadi_Account_Addresses::url( 'new' ) ); ?>">افزودن آدرس جدید</a>
</div>
