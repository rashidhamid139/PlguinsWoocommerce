<?php
/**
 *
 * Manage Scheduled Tasks.
 *
 * @package ELEX Bulk Edit Products, Prices & Attributes for Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$upload_dir  = wp_upload_dir();
$base        = $upload_dir['basedir'];
$folder_path = $base . '/elex-bulk-edit-products/';
$files       = array();
if ( file_exists( $folder_path ) ) {
	$files = array_diff( scandir( $folder_path ), array( '..', '.' ) );
}
$dir_url = $upload_dir['baseurl'] . '/elex-bulk-edit-products/';
?>
<div class='wrap postbox table-box table-box-main' id="manage_schedule_tasks" style='padding:5px 20px;'>
	<h2>
		<?php esc_html_e( 'Scheduled Tasks ', 'eh_bulk_edit' ); ?>
	</h2>
	<table class='elex-bep-manage-schedule'>
		<tr>
			<th class='elex-bep-manage-schedule-left'>
				<?php esc_html_e( 'Name', 'eh_bulk_edit' ); ?>
			</th>
			<th class="elex-bep-manage-schedule-middle">
				<?php esc_html_e( 'Chosen Fields', 'eh_bulk_edit' ); ?>
			</th>
			<th class="elex-bep-manage-schedule-sch">
				<?php esc_html_e( 'Schedule (y-m-d)', 'eh_bulk_edit' ); ?>
			</th>
			<th class='elex-bep-manage-schedule-right'>
				<?php esc_html_e( 'Actions', 'eh_bulk_edit' ); ?>
			</th>
		</tr>
		<tr></tr>
		<?php
		$saved_jobs = get_option( 'elex_bep_scheduled_jobs' );
		if ( ! empty( $saved_jobs ) ) {
			$saved_jobs = array_reverse( $saved_jobs );
			foreach ( $saved_jobs as $key => $val ) {
				$file_name = '';
				foreach ( $files as $file ) {
					if ( isset( $val['job_name'] ) && ( ( str_replace( ' ', '_', $val['job_name'] ) . '.txt' ) === $file ) ) {
						$file_name = $file;
						break;
					}
				}
				if ( isset( $val['revert_data'] ) ) {
					$revert_class = 'elex-bep-icon-revert';
					$rev_onclick  = $val['job_name'];
				} else {
					$revert_class = 'elex-bep-icon-revert-disable';
					$rev_onclick  = '';
				}

				if ( 'true' === $val['create_log_file'] && ( ( ! isset( $val['schedule_opn'] ) ) || false === $val['schedule_opn'] ) ) {
					$log_class = 'elex-bep-icon-log';
				} else {
					$log_class = 'elex-bep-icon-log-disable';
				}
				if ( ( isset( $val['schedule_opn'] ) && true === $val['schedule_opn'] ) || ( isset( $val['revert_opn'] ) && true === $val['revert_opn'] ) ) {
					$cancel_schedule = 'elex-bep-icon-cancel';
					$cancel_job_name = $val['job_name'];
				} else {
					$cancel_schedule = 'elex-bep-icon-cancel-disable';
					$cancel_job_name = '';
				}
				?>

		<tr id="<?php echo filter_var( $val['job_name'] ); ?>">
				<td>
					<?php echo filter_var( $val['job_name'] ); ?>
				</td>
				<td>
					<?php
					$chosen_fields = '';
					if ( '' !== $val['param_to_save']['title_select'] ) {
						$chosen_fields .= 'Title, ';
					}
					if ( '' !== $val['param_to_save']['sku_select'] ) {
						$chosen_fields .= 'SKU, ';
					}
					if ( '' !== $val['param_to_save']['catalog_select'] ) {
						$chosen_fields .= 'Product Visiblity, ';
					}
					if ( '' !== $val['param_to_save']['description_action'] ) {
						$chosen_fields .= 'Description, ';
					}
					if ( '' !== $val['param_to_save']['short_description_action'] ) {
						$chosen_fields .= 'Short Description, ';
					}
					if ( '' !== $val['param_to_save']['main_image'] ) {
						$chosen_fields .= 'Main Image, ';
					}
					if ( '' !== $val['param_to_save']['gallery_images_action'] ) {
						$chosen_fields .= 'Gallery Images, ';
					}
					if ( '' !== $val['param_to_save']['is_featured'] ) {
						$chosen_fields .= 'Featured, ';
					}
					if ( '' !== $val['param_to_save']['shipping_select'] ) {
						$chosen_fields .= 'Shipping Class, ';
					}
					if ( '' !== $val['param_to_save']['regular_select'] ) {
						$chosen_fields .= 'Regular Price, ';
					}
					if ( '' !== $val['param_to_save']['sale_select'] ) {
						$chosen_fields .= 'Sale Price, ';
					}
					if ( '' !== $val['param_to_save']['stock_manage_select'] ) {
						$chosen_fields .= 'Manage Stock, ';
					}
					if ( '' !== $val['param_to_save']['quantity_select'] ) {
						$chosen_fields .= 'Stock Quantity, ';
					}
					if ( '' !== $val['param_to_save']['backorder_select'] ) {
						$chosen_fields .= 'Allow Backorders, ';
					}
					if ( '' !== $val['param_to_save']['stock_status_select'] ) {
						$chosen_fields .= 'Stock Status, ';
					}
					if ( '' !== $val['param_to_save']['length_select'] ) {
						$chosen_fields .= 'Length, ';
					}
					if ( '' !== $val['param_to_save']['width_select'] ) {
						$chosen_fields .= 'Width, ';
					}
					if ( '' !== $val['param_to_save']['height_select'] ) {
						$chosen_fields .= 'Height, ';
					}
					if ( '' !== $val['param_to_save']['weight_select'] ) {
						$chosen_fields .= 'Weight, ';
					}
					if ( '' !== $val['param_to_save']['attribute_action'] ) {
						$chosen_fields .= 'Attribute Actions, ';
					}
					if ( 'cat_none' !== $val['param_to_save']['category_update_option'] ) {
						$chosen_fields .= 'Category Actions, ';
					}
					if ( isset( $val['param_to_save']['meta_fields'] ) && is_array( $val['param_to_save']['meta_fields'] ) ) {
						foreach ( $val['param_to_save']['meta_fields'] as $index => $meta ) {
							if ( '' !== $val['param_to_save']['custom_meta'][ $index ] ) {
								$chosen_fields .= $meta . ', ';
							}
						}
					}
					$chosen_fields = substr( $chosen_fields, 0, -2 );
					echo filter_var( $chosen_fields );
					?>
				</td>
				<td>
					<?php
					$schedule_details = '';
					if ( ! empty( $val['schedule_date'] ) ) {
						$schedule_details .= 'Scheduled time: ' . $val['schedule_date'];
						if ( ! empty( $val['scheduled_hour'] ) ) {
							$schedule_details .= ' At ' . $val['scheduled_hour'];
						} else {
							$schedule_details .= ' At 0';
						}
						if ( ! empty( $val['scheduled_min'] ) ) {
							$schedule_details .= ':' . $val['scheduled_min'];
						} else {
							$schedule_details .= ' : 0';
						}
						if ( ! empty( $val['revert_date'] ) ) {
							$schedule_details .= '<br>Revert time: ' . $val['revert_date'];
							if ( ! empty( $val['revert_hour'] ) ) {
								$schedule_details .= ' At ' . $val['revert_hour'];
							} else {
								$schedule_details .= ' At 0';
							}
							if ( ! empty( $val['revert_min'] ) ) {
								$schedule_details .= ':' . $val['revert_min'];
							} else {
								$schedule_details .= ' : 0';
							}
						}
						echo filter_var( $schedule_details );
					}
					?>
				</td>
				<td>
					<span class="elex-bep-icon-edit"  title="Edit" onclick="elex_bep_edit_copy_job('<?php echo filter_var( $val['job_name'] ); ?>','edit')"  style="display: inline-block;"></span>
					<span class="elex-bep-icon-copy"  title="Copy" onclick="elex_bep_edit_copy_job('<?php echo filter_var( $val['job_name'] ); ?>','copy')"  style="display: inline-block;"></span>
					<span class="elex-bep-icon-run"  title="Run Now"onclick="elex_bep_run_now('<?php echo filter_var( $val['job_name'] ); ?>')"  style="display: inline-block; margin: 0px 2px 1px;"></span>
					<span class="<?php echo filter_var( $revert_class ); ?>"  title="Revert Now" onclick="elex_bep_revert_now('<?php echo filter_var( $rev_onclick ); ?>')"  style="display: inline-block; margin: 0px 2px 1px;"></span>
					<span class="elex-bep-icon-delete"  title="Delete" onclick="elex_bep_delete_job('<?php echo filter_var( $val['job_name'] ); ?>')"  style="display: inline-block; margin: 0px 2px 1px;"></span>
					<span class="<?php echo filter_var( $cancel_schedule ); ?>"  title="Cancel Schedule" onclick="elex_bep_cancel_job('<?php echo filter_var( $cancel_job_name ); ?>')" style="display: inline-block; margin: 0px 2px 1px;"></span>
					<a href=<?php echo filter_var( $dir_url . $file_name ); ?> download="<?php echo filter_var( $file_name ); ?>" target="_blank" id="<?php echo filter_var( $file_name ); ?>"></a>
					<span class=" <?php echo filter_var( $log_class ); ?>"  title="Log File" onclick="document.getElementById('<?php echo filter_var( $file_name ); ?>').click();" download="<?php echo filter_var( $file_name ); ?>"style="display: inline-block; margin: 2px 3px 1px;"></span>
				</td>
			</tr>
				<?php
			}
		}
		?>
	</table>
</div>
