<?php
/**
 * Profile: photo upload and mobile number on the "account details" form.
 *
 * @package Samadi_Account
 */

defined( 'ABSPATH' ) || exit;

/**
 * Profile fields.
 */
class Samadi_Account_Profile {

	const AVATAR = '_sad_avatar';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'woocommerce_edit_account_form_tag', array( __CLASS__, 'form_tag' ) );
		add_action( 'woocommerce_edit_account_form_start', array( __CLASS__, 'fields' ) );
		add_action( 'woocommerce_save_account_details_errors', array( __CLASS__, 'validate' ), 10, 2 );
		add_action( 'woocommerce_save_account_details', array( __CLASS__, 'save' ) );
		add_filter( 'pre_get_avatar_data', array( __CLASS__, 'avatar_data' ), 10, 2 );
	}

	/**
	 * Allow file upload on the account form.
	 */
	public static function form_tag() {
		echo ' enctype="multipart/form-data"';
	}

	/**
	 * Photo and mobile fields.
	 */
	public static function fields() {
		$user_id = get_current_user_id();
		$phone   = (string) get_user_meta( $user_id, 'billing_phone', true );
		?>
		<div class="sad-profile-photo">
			<span class="sad-avatar sad-avatar--lg"><?php echo sad_avatar( $user_id, 96 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in sad_avatar(). ?></span>
			<div>
				<label for="sad_avatar" class="sad-profile-photo__label"><?php echo sad_icon( 'camera', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <span>تغییر عکس پروفایل</span></label>
				<input type="file" name="sad_avatar" id="sad_avatar" accept="image/jpeg,image/png,image/webp" class="sad-sr">
				<p class="sad-hint">JPG، PNG یا WebP — حداکثر ۲ مگابایت</p>
				<?php if ( get_user_meta( $user_id, self::AVATAR, true ) ) : ?>
					<label class="sad-check"><input type="checkbox" name="sad_avatar_remove" value="1"> حذف عکس</label>
				<?php endif; ?>
			</div>
		</div>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="sad_phone">شماره موبایل</label>
			<input type="tel" class="woocommerce-Input input-text" name="sad_phone" id="sad_phone" autocomplete="tel" dir="ltr" value="<?php echo esc_attr( $phone ); ?>">
		</p>
		<?php
		wp_nonce_field( 'sad_profile', 'sad_profile_nonce' );
	}

	/**
	 * Validate.
	 *
	 * @param WP_Error $errors Errors.
	 */
	public static function validate( $errors ) {
		if ( empty( $_POST['sad_profile_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sad_profile_nonce'] ) ), 'sad_profile' ) ) {
			return;
		}
		$phone = isset( $_POST['sad_phone'] ) ? wc_sanitize_phone_number( sanitize_text_field( wp_unslash( $_POST['sad_phone'] ) ) ) : '';
		if ( $phone && ! WC_Validation::is_phone( $phone ) ) {
			$errors->add( 'sad_phone', 'شماره موبایل معتبر نیست.' );
		}
		if ( ! empty( $_FILES['sad_avatar']['name'] ) ) {
			$size = isset( $_FILES['sad_avatar']['size'] ) ? (int) $_FILES['sad_avatar']['size'] : 0;
			$type = wp_check_filetype_and_ext( $_FILES['sad_avatar']['tmp_name'] ?? '', sanitize_file_name( wp_unslash( $_FILES['sad_avatar']['name'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( $size > 2 * MB_IN_BYTES ) {
				$errors->add( 'sad_avatar', 'حجم عکس بیشتر از ۲ مگابایت است.' );
			} elseif ( ! in_array( $type['type'], array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) {
				$errors->add( 'sad_avatar', 'فقط تصویر JPG، PNG یا WebP قابل بارگذاری است.' );
			}
		}
	}

	/**
	 * Save.
	 *
	 * @param int $user_id User.
	 */
	public static function save( $user_id ) {
		if ( empty( $_POST['sad_profile_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sad_profile_nonce'] ) ), 'sad_profile' ) ) {
			return;
		}
		if ( isset( $_POST['sad_phone'] ) ) {
			update_user_meta( $user_id, 'billing_phone', wc_sanitize_phone_number( sanitize_text_field( wp_unslash( $_POST['sad_phone'] ) ) ) );
		}

		$old = (int) get_user_meta( $user_id, self::AVATAR, true );
		if ( ! empty( $_POST['sad_avatar_remove'] ) && $old ) {
			wp_delete_attachment( $old, true );
			delete_user_meta( $user_id, self::AVATAR );
			$old = 0;
		}

		if ( ! empty( $_FILES['sad_avatar']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			$id = media_handle_upload( 'sad_avatar', 0, array(), array( 'test_form' => false, 'mimes' => array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) ) );
			if ( ! is_wp_error( $id ) ) {
				if ( $old ) {
					wp_delete_attachment( $old, true );
				}
				update_user_meta( $user_id, self::AVATAR, $id );
				update_post_meta( $id, '_sad_avatar_of', $user_id );
			}
		}
	}

	/**
	 * Use the uploaded photo everywhere WordPress shows an avatar.
	 *
	 * @param array $args        Avatar args.
	 * @param mixed $id_or_email User.
	 * @return array
	 */
	public static function avatar_data( $args, $id_or_email ) {
		$user_id = 0;
		if ( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
		} elseif ( $id_or_email instanceof WP_User ) {
			$user_id = $id_or_email->ID;
		} elseif ( $id_or_email instanceof WP_Comment ) {
			$user_id = (int) $id_or_email->user_id;
		}
		$attachment = $user_id ? (int) get_user_meta( $user_id, self::AVATAR, true ) : 0;
		if ( $attachment ) {
			$src = wp_get_attachment_image_url( $attachment, array( (int) $args['size'], (int) $args['size'] ) );
			if ( $src ) {
				$args['url'] = $src;
			}
		}
		return $args;
	}
}
