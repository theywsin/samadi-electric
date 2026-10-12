<?php
/**
 * Swatch color field (hex) for product attribute terms, e.g. "رنگ" → "مشکی" = #1f1f1f.
 * The theme turns these into color swatches on variable products.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Attribute term colors.
 */
class Samadi_EW_Term_Colors {

	const META = 'se_color';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_term_hooks' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * Attach form hooks to every product attribute taxonomy.
	 */
	public static function register_term_hooks() {
		if ( ! function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
			return;
		}

		foreach ( wc_get_attribute_taxonomy_names() as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( __CLASS__, 'add_field' ) );
			add_action( $taxonomy . '_edit_form_fields', array( __CLASS__, 'edit_field' ) );
			add_action( 'created_' . $taxonomy, array( __CLASS__, 'save' ) );
			add_action( 'edited_' . $taxonomy, array( __CLASS__, 'save' ) );
			add_filter( 'manage_edit-' . $taxonomy . '_columns', array( __CLASS__, 'column' ) );
			add_filter( 'manage_' . $taxonomy . '_custom_column', array( __CLASS__, 'column_content' ), 10, 3 );
		}
	}

	/**
	 * Color picker on attribute term screens only.
	 *
	 * @param string $hook Hook.
	 */
	public static function assets( $hook ) {
		if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 0 !== strpos( (string) $screen->taxonomy, 'pa_' ) ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".se-color-field").wpColorPicker();});' );
	}

	/**
	 * Field on "add term".
	 */
	public static function add_field() {
		wp_nonce_field( 'se_term_color', 'se_term_color_nonce' );
		?>
		<div class="form-field">
			<label for="se-color"><?php esc_html_e( 'رنگ نمونه (سواچ)', 'samadi-elementor-widgets' ); ?></label>
			<input type="text" id="se-color" name="se_color" class="se-color-field" value="">
			<p><?php esc_html_e( 'برای ویژگی رنگ: کد رنگ این گزینه. در صفحه محصول به‌صورت دایره رنگی نمایش داده می‌شود.', 'samadi-elementor-widgets' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Field on "edit term".
	 *
	 * @param WP_Term $term Term.
	 */
	public static function edit_field( $term ) {
		$value = sanitize_hex_color( (string) get_term_meta( $term->term_id, self::META, true ) );
		?>
		<tr class="form-field">
			<th scope="row"><label for="se-color"><?php esc_html_e( 'رنگ نمونه (سواچ)', 'samadi-elementor-widgets' ); ?></label></th>
			<td>
				<?php wp_nonce_field( 'se_term_color', 'se_term_color_nonce' ); ?>
				<input type="text" id="se-color" name="se_color" class="se-color-field" value="<?php echo esc_attr( (string) $value ); ?>">
				<p class="description"><?php esc_html_e( 'خالی بگذارید تا گزینه به‌صورت دکمه متنی نمایش داده شود.', 'samadi-elementor-widgets' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save meta.
	 *
	 * @param int $term_id Term ID.
	 */
	public static function save( $term_id ) {
		if ( ! isset( $_POST['se_term_color_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['se_term_color_nonce'] ) ), 'se_term_color' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}

		$color = isset( $_POST['se_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['se_color'] ) ) : '';

		if ( $color ) {
			update_term_meta( $term_id, self::META, $color );
		} else {
			delete_term_meta( $term_id, self::META );
		}
	}

	/**
	 * Admin list column.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function column( $columns ) {
		$columns['se_color'] = __( 'رنگ', 'samadi-elementor-widgets' );
		return $columns;
	}

	/**
	 * Column content.
	 *
	 * @param string $content Content.
	 * @param string $column  Column.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public static function column_content( $content, $column, $term_id ) {
		if ( 'se_color' !== $column ) {
			return $content;
		}
		$color = sanitize_hex_color( (string) get_term_meta( $term_id, self::META, true ) );

		return $color ? sprintf( '<span style="display:inline-block;width:22px;height:22px;border-radius:6px;border:1px solid #ccc;background:%s"></span>', esc_attr( $color ) ) : '—';
	}
}
