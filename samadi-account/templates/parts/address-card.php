<?php
/**
 * Address card.
 *
 * @package Samadi_Account
 *
 * @var string $id      Address ID.
 * @var array  $address Address.
 * @var bool   $default Default address.
 * @var bool   $manage  Show edit/delete/default actions.
 */

defined( 'ABSPATH' ) || exit;

$sad_types = Samadi_Account_Addresses::types();
$sad_type  = isset( $sad_types[ $address['type'] ] ) ? $sad_types[ $address['type'] ] : $sad_types['other'];
$sad_book  = (bool) sad_option( 'address_book' );
$sad_edit  = $sad_book ? Samadi_Account_Addresses::url( 'edit', $id ) : wc_get_endpoint_url( 'edit-address', $id, wc_get_page_permalink( 'myaccount' ) );
?>
<div class="sad-address<?php echo $default ? ' is-default' : ''; ?>">
	<div class="sad-address__top">
		<span class="sad-address__icon"><?php echo sad_icon( $sad_type[1], 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<p class="sad-address__label"><?php echo esc_html( $address['label'] ); ?></p>
		<?php if ( $default && $sad_book ) : ?>
			<span class="sad-badge">پیش‌فرض</span>
		<?php endif; ?>
	</div>
	<p class="sad-address__line"><?php echo esc_html( sad_fa( Samadi_Account_Addresses::line( $address ) ) ); ?></p>
	<p class="sad-address__name"><?php echo esc_html( trim( $address['first_name'] . ' ' . $address['last_name'] ) ); ?></p>
	<?php if ( $address['phone'] ) : ?>
		<p class="sad-address__phone" dir="ltr"><?php echo esc_html( sad_phone( $address['phone'] ) ); ?></p>
	<?php endif; ?>
	<?php if ( ! empty( $manage ) ) : ?>
		<div class="sad-address__actions">
			<a href="<?php echo esc_url( $sad_edit ); ?>"><?php echo sad_icon( 'pencil', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> ویرایش</a>
			<?php if ( $sad_book && ! $default ) : ?>
				<a href="<?php echo esc_url( Samadi_Account_Addresses::url( 'default', $id ) ); ?>"><?php echo sad_icon( 'check', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> پیش‌فرض شود</a>
			<?php endif; ?>
			<?php if ( $sad_book ) : ?>
				<a class="is-danger" href="<?php echo esc_url( Samadi_Account_Addresses::url( 'delete', $id ) ); ?>" data-sad-confirm><?php echo sad_icon( 'trash-2', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> حذف</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
