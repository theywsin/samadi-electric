<?php
/**
 * Custom Customizer controls.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checklist of taxonomy terms stored as a comma-separated ID list.
 */
class Samadi_Customize_Term_Checklist_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'samadi_term_checklist';

	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	public $taxonomy = 'product_cat';

	/**
	 * Render the control.
	 */
	public function render_content() {
		$terms = get_terms(
			array(
				'taxonomy'   => $this->taxonomy,
				'hide_empty' => false,
				'number'     => 200,
				'orderby'    => 'name',
			)
		);

		if ( is_wp_error( $terms ) || ! $terms ) {
			echo '<p>' . esc_html__( 'هنوز دسته‌ای تعریف نشده است.', 'samadi-electric' ) . '</p>';
			return;
		}

		$selected = array_filter( array_map( 'absint', explode( ',', (string) $this->value() ) ) );
		$input_id = '_customize-input-' . $this->id;
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>
		<div class="samadi-term-checklist" style="max-height:240px;overflow:auto;border:1px solid #dcdcde;padding:8px;background:#fff">
			<?php foreach ( $terms as $term ) : ?>
				<label style="display:block;margin:4px 0">
					<input type="checkbox" value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( in_array( (int) $term->term_id, $selected, true ) ); ?>>
					<?php echo esc_html( $term->name ); ?>
				</label>
			<?php endforeach; ?>
		</div>
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $selected ) ); ?>">
		<script>
			( function () {
				var wrap = document.currentScript.previousElementSibling.previousElementSibling;
				var input = document.currentScript.previousElementSibling;
				wrap.addEventListener( 'change', function () {
					var ids = Array.prototype.map.call( wrap.querySelectorAll( 'input:checked' ), function ( el ) { return el.value; } );
					input.value = ids.join( ',' );
					input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );
			}() );
		</script>
		<?php
	}
}
