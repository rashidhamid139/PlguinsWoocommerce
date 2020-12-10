<?php
/**
 *
 * Frontend Settings Tab Fields.
 *
 * @package ELEX Bulk Edit Products, Prices & Attributes for Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class='wrap postbox table-box table-box-main' id="filter_settings_tab" style='padding:5px 20px;'>
	<h3>
		<?php esc_html_e( 'Settings ', 'eh_bulk_edit' ); ?>
	</h3>
	<hr>
	<table class='eh-content-table' id='data_table'>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Update Custom Meta', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Enter custom meta keys seperated by comma.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<?php
				$meta = get_option( 'eh_bulk_edit_meta_values_to_update' );
				if ( ! empty( $meta ) ) {
					$meta = implode( ',', $meta );
				}
				?>
				<textarea style="width: 65%;" rows="4" id="update_meta_values"><?php echo filter_var( $meta ); ?></textarea>
			</td>
		</tr>
	</table>
	<button id='save_filter_setting_fields' value='save_filter_setting_fields' style='margin:5px 2px 2px 90%;width: 10%; ' class='button button-primary button-large'><?php esc_html_e( 'Save', 'eh_bulk_edit' ); ?></button>
</div>
<?php
require_once EH_BEP_TEMPLATE_PATH . '/template-scheduling-fields.php';
