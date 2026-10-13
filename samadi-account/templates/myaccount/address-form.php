<?php
/**
 * Add / edit address form.
 *
 * @package Samadi_Account
 *
 * @var string $id      Address ID ('' = new).
 * @var array  $address Address values.
 * @var bool   $default Is the default address.
 */

defined( 'ABSPATH' ) || exit;

$sad_user = wp_get_current_user();
// Repopulate after a validation error, then saved values, then sensible defaults for a new address.
$sad_value = static function ( $key ) use ( $address, $sad_user ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Repopulating the submitted form.
	if ( isset( $_POST[ 'sad_' . $key ] ) ) {
		return sanitize_text_field( wp_unslash( $_POST[ 'sad_' . $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
	if ( isset( $address[ $key ] ) ) {
		return $address[ $key ];
	}
	$fallback = array(
		'first_name' => $sad_user->first_name,
		'last_name'  => $sad_user->last_name,
		'phone'      => (string) get_user_meta( $sad_user->ID, 'billing_phone', true ),
	);
	return isset( $fallback[ $key ] ) ? $fallback[ $key ] : '';
};
$sad_states = WC()->countries->get_states( Samadi_Account_Addresses::country() );
$sad_type   = $sad_value( 'type' ) ? $sad_value( 'type' ) : 'home';
?>
<section class="sad-card">
	<?php sad_template( 'parts/head', array( 'title' => $id ? 'ویرایش آدرس' : 'افزودن آدرس جدید', 'tag' => 'h1', 'link' => Samadi_Account_Addresses::url(), 'label' => 'بازگشت به آدرس‌ها' ) ); ?>
	<form method="post" class="sad-form" novalidate>
		<fieldset class="sad-types">
			<legend>نوع آدرس</legend>
			<?php foreach ( Samadi_Account_Addresses::types() as $sad_key => $sad_meta ) : ?>
				<label class="sad-type">
					<input type="radio" name="sad_type" value="<?php echo esc_attr( $sad_key ); ?>" <?php checked( $sad_type, $sad_key ); ?>>
					<span><?php echo sad_icon( $sad_meta[1], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $sad_meta[0] ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>

		<div class="sad-grid">
			<?php foreach ( Samadi_Account_Addresses::fields() as $sad_key => $sad_field ) : ?>
				<?php
				$sad_wide = in_array( $sad_key, array( 'address_1' ), true );
				$sad_id   = 'sad_' . $sad_key;
				?>
				<p class="sad-field<?php echo $sad_wide ? ' sad-field--wide' : ''; ?>">
					<label for="<?php echo esc_attr( $sad_id ); ?>"><?php echo esc_html( $sad_field[0] ); ?><?php echo $sad_field[1] ? ' <span class="required" aria-hidden="true">*</span>' : ''; ?></label>
					<?php if ( 'state' === $sad_key && $sad_states ) : ?>
						<select id="<?php echo esc_attr( $sad_id ); ?>" name="<?php echo esc_attr( $sad_id ); ?>" <?php echo $sad_field[1] ? 'required' : ''; ?>>
							<option value="">انتخاب استان</option>
							<?php foreach ( $sad_states as $sad_code => $sad_name ) : ?>
								<option value="<?php echo esc_attr( $sad_code ); ?>" <?php selected( $sad_value( 'state' ), $sad_code ); ?>><?php echo esc_html( $sad_name ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'address_1' === $sad_key ) : ?>
						<textarea id="<?php echo esc_attr( $sad_id ); ?>" name="<?php echo esc_attr( $sad_id ); ?>" rows="2" required placeholder="خیابان، کوچه، پلاک، واحد"><?php echo esc_textarea( $sad_value( $sad_key ) ); ?></textarea>
					<?php else : ?>
						<input
							type="<?php echo 'phone' === $sad_key ? 'tel' : 'text'; ?>"
							id="<?php echo esc_attr( $sad_id ); ?>"
							name="<?php echo esc_attr( $sad_id ); ?>"
							value="<?php echo esc_attr( $sad_value( $sad_key ) ); ?>"
							<?php echo in_array( $sad_key, array( 'phone', 'postcode' ), true ) ? 'dir="ltr" inputmode="numeric"' : ''; ?>
							<?php echo 'label' === $sad_key ? 'placeholder="مثلاً خانه، محل کار، منزل پدری"' : ''; ?>
							<?php echo $sad_field[1] ? 'required' : ''; ?>>
					<?php endif; ?>
				</p>
			<?php endforeach; ?>
		</div>

		<?php if ( ! $default ) : ?>
			<label class="sad-check"><input type="checkbox" name="sad_default" value="1"> این آدرس پیش‌فرض باشد</label>
		<?php endif; ?>

		<div class="sad-form__actions">
			<button type="submit" class="sad-btn sad-btn--primary"><?php echo $id ? 'ذخیره تغییرات' : 'ذخیره آدرس'; ?></button>
			<a class="sad-btn sad-btn--soft" href="<?php echo esc_url( Samadi_Account_Addresses::url() ); ?>">انصراف</a>
		</div>
		<input type="hidden" name="sad_id" value="<?php echo esc_attr( $id ); ?>">
		<?php wp_nonce_field( Samadi_Account_Addresses::ACTION . '_save', 'sad_address_nonce' ); ?>
	</form>
</section>
