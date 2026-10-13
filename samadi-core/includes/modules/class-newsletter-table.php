<?php
/**
 * Admin list of newsletter subscribers.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Subscribers table.
 */
class Samadi_Core_Newsletter_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'subscriber',
				'plural'   => 'subscribers',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox">',
			'mobile'     => __( 'شماره موبایل', 'samadi-core' ),
			'source'     => __( 'محل فرم', 'samadi-core' ),
			'user'       => __( 'کاربر', 'samadi-core' ),
			'created_at' => __( 'تاریخ عضویت', 'samadi-core' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'mobile'     => array( 'mobile', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array( 'delete' => __( 'حذف', 'samadi-core' ) );
	}

	/**
	 * Load rows.
	 */
	public function prepare_items() {
		global $wpdb;

		$table    = Samadi_Core_Newsletter::table();
		$per_page = 30;
		$paged    = $this->get_pagenum();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only list filters.
		$search  = isset( $_REQUEST['s'] ) ? samadi_core_en_digits( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '';
		$orderby = isset( $_REQUEST['orderby'] ) && 'mobile' === $_REQUEST['orderby'] ? 'mobile' : 'id';
		$order   = isset( $_REQUEST['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_REQUEST['order'] ) ) ) ? 'ASC' : 'DESC';
		// phpcs:enable

		$where = '1=1';
		$args  = array();
		if ( '' !== $search ) {
			$where .= ' AND mobile LIKE %s';
			$args[] = '%' . $wpdb->esc_like( preg_replace( '/\D+/', '', $search ) ) . '%';
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared -- Custom table; identifiers are whitelisted above.
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
		$total     = (int) ( $args ? $wpdb->get_var( $wpdb->prepare( $count_sql, $args ) ) : $wpdb->get_var( $count_sql ) );

		$list_sql    = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$this->items = $wpdb->get_results( $wpdb->prepare( $list_sql, array_merge( $args, array( $per_page, ( $paged - 1 ) * $per_page ) ) ), ARRAY_A );
		// phpcs:enable

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns(), 'mobile' );
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="subscriber[]" value="%d">', absint( $item['id'] ) );
	}

	/**
	 * Mobile column with row actions.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_mobile( $item ) {
		$delete = wp_nonce_url(
			add_query_arg(
				array(
					'page'       => Samadi_Core_Newsletter::PAGE,
					'action'     => 'delete',
					'subscriber' => absint( $item['id'] ),
				),
				admin_url( 'admin.php' )
			),
			'samadi_core_delete_subscriber_' . absint( $item['id'] )
		);

		return sprintf(
			'<strong dir="ltr">%1$s</strong>%2$s',
			esc_html( $item['mobile'] ),
			$this->row_actions( array( 'delete' => '<a class="submitdelete" href="' . esc_url( $delete ) . '">' . esc_html__( 'حذف', 'samadi-core' ) . '</a>' ) )
		);
	}

	/**
	 * Other columns.
	 *
	 * @param array  $item        Row.
	 * @param string $column_name Column.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'source':
				return esc_html( $item['source'] ? $item['source'] : '—' );
			case 'user':
				$user = $item['user_id'] ? get_userdata( (int) $item['user_id'] ) : null;
				return $user ? '<a href="' . esc_url( get_edit_user_link( $user->ID ) ) . '">' . esc_html( $user->display_name ) . '</a>' : '—';
			case 'created_at':
				$time = strtotime( $item['created_at'] . ' UTC' );
				return esc_html( wp_date( get_option( 'date_format' ) . ' H:i', $time ) );
		}

		return '';
	}

	/**
	 * Empty state.
	 */
	public function no_items() {
		esc_html_e( 'هنوز کسی در خبرنامه عضو نشده است.', 'samadi-core' );
	}
}
