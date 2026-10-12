<?php
/**
 * Classic widget: "Samadi product filters" (for the theme's shop filters sidebar or any sidebar).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter widget.
 */
class Samadi_EW_Filter_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'samadi_product_filters',
			__( 'فیلتر محصولات صمدی', 'samadi-elementor-widgets' ),
			array(
				'description'                 => __( 'فیلتر برند، قیمت، موجودی و ویژگی‌های محصول — فقط فیلترهای مرتبط با دسته جاری نمایش داده می‌شوند.', 'samadi-elementor-widgets' ),
				'customize_selective_refresh' => true,
				'show_instance_in_rest'       => true,
			)
		);
	}

	/**
	 * Output.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$html = Samadi_EW_Filters::render_form(
			array(
				'price'      => ! empty( $instance['price'] ),
				'stock'      => ! empty( $instance['stock'] ),
				'brand'      => ! empty( $instance['brand'] ),
				'attributes' => isset( $instance['attributes'] ) ? (array) $instance['attributes'] : array(),
			)
		);

		if ( '' === $html ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render_form().
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Admin form.
	 *
	 * @param array $instance Instance.
	 * @return string
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			$instance,
			array(
				'title'      => '',
				'price'      => true,
				'stock'      => true,
				'brand'      => true,
				'attributes' => array(),
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'عنوان:', 'samadi-elementor-widgets' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>
		<?php foreach ( array( 'price' => __( 'فیلتر قیمت', 'samadi-elementor-widgets' ), 'stock' => __( 'فیلتر موجودی', 'samadi-elementor-widgets' ), 'brand' => __( 'فیلتر برند', 'samadi-elementor-widgets' ) ) as $key => $label ) : ?>
			<p>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" value="1" <?php checked( ! empty( $instance[ $key ] ) ); ?>>
				<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $label ); ?></label>
			</p>
		<?php endforeach; ?>
		<p><strong><?php esc_html_e( 'ویژگی‌ها', 'samadi-elementor-widgets' ); ?></strong><br>
			<small><?php esc_html_e( 'هیچ‌کدام انتخاب نشود = همه ویژگی‌هایی که در دسته جاری کاربرد دارند.', 'samadi-elementor-widgets' ); ?></small>
		</p>
		<?php
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
			$field_id = $this->get_field_id( 'attributes' ) . '-' . $attribute->attribute_name;
			printf(
				'<p><input type="checkbox" id="%1$s" name="%2$s[]" value="%3$s" %4$s> <label for="%1$s">%5$s</label></p>',
				esc_attr( $field_id ),
				esc_attr( $this->get_field_name( 'attributes' ) ),
				esc_attr( $taxonomy ),
				checked( in_array( $taxonomy, (array) $instance['attributes'], true ), true, false ),
				esc_html( $attribute->attribute_label )
			);
		}
		return '';
	}

	/**
	 * Save.
	 *
	 * @param array $new_instance New.
	 * @param array $old_instance Old.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$valid = wc_get_attribute_taxonomy_names();

		return array(
			'title'      => sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : '' ),
			'price'      => ! empty( $new_instance['price'] ),
			'stock'      => ! empty( $new_instance['stock'] ),
			'brand'      => ! empty( $new_instance['brand'] ),
			'attributes' => array_values( array_intersect( isset( $new_instance['attributes'] ) ? (array) $new_instance['attributes'] : array(), $valid ) ),
		);
	}
}
