<?php
/**
 *
 * Template Scheduling Fields.
 *
 * @package ELEX Bulk Edit Products, Prices & Attributes for Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class='wrap postbox table-box table-box-main' id="elex_schedule_field" style='padding:5px 20px;'>
	<h2>
		<?php esc_html_e( 'Schedule', 'eh_bulk_edit' ); ?>
	</h2>
	<hr>
	<table class='eh-content-table' id='schedule'>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Actions ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose when to perform the bulk edit operation. If you are choosing to bulk edit now, you can enable the checkbox to optionally save it as a job. For a later schedule, the job will be saved automatically', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-right'>
				<select id="elex_schedule_options">
					<option value="bulk_update_now"><?php esc_html_e( 'Perform the Bulk Edit Now ', 'eh_bulk_edit' ); ?></option>
					<option value="schedule_later"><?php esc_html_e( 'Schedule it for later ', 'eh_bulk_edit' ); ?></option>
				</select>
				<input type="checkbox" id="elex_save_job_checkbox" checked="checked"> <output id="elex_save_job_text"><?php esc_html_e( 'Save it as a Job', 'eh_bulk_edit' ); ?></output>
			</td>
		</tr>
		<tr id="elex_undo_enable_field">
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Revert Last Update ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Enable this Undo option to revert back your current update. But it will override your previous update.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-right'>
				<input type="checkbox" id="add_undo_now" checked value="ok"><?php esc_html_e( 'Enable Undo Update', 'eh_bulk_edit' ); ?>
			</td>
		</tr>
		<tr id="elex_schedule_date_and_time">
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Schedule the Bulk Update on ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose the time and date to perform the bulk edit operation.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-right'>
				<input type="date" id="schedule_date">
				<select id="schedule_hr">
					<option value="">Hr</option>
					<?php
					for ( $i = 0;$i <= 23;$i++ ) {
						echo filter_var( '<option value=' . $i . '>' . $i . '</option>' );
					}
					?>
				</select>
				<select id="schedule_min">
					<option value="">Min</option>
					<?php
					for ( $i = 0;$i <= 59;$i++ ) {
						echo filter_var( '<option value=' . $i . '>' . $i . '</option>' );
					}
					?>
				</select>
			</td>
		</tr>
		<tr id="elex_revert_date_and_time">
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Revert the Values on (Optional) ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose a time and date to revert the edited values. Leave it as it is to not revert the values.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-right'>
				<input type="date" id="revert_date">
				<select id="revert_hr">
					<option value="">Hr</option>
					<?php
					for ( $i = 0;$i <= 23;$i++ ) {
						echo filter_var( '<option value=' . $i . '>' . $i . '</option>' );
					}
					?>
				</select>
				<select id="revert_min">
					<option value="">Min</option>
					<?php
					for ( $i = 0;$i <= 59;$i++ ) {
						echo filter_var( '<option value=' . $i . '>' . $i . '</option>' );
					}
					?>
				</select>
			</td>
		</tr>
		<tr id="schedule_frequency_options">
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Schedule Frequency ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select the frequency with which you want to make the updates.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-right'>
			<select id="schedule_frequency">
				<option value=""> <?php esc_html_e( 'No', 'eh_bulk_edit' ); ?> </option>
				<option value="daily"> <?php esc_html_e( 'Daily', 'eh_bulk_edit' ); ?> </option>
				<option value="weekly"> <?php esc_html_e( 'Weekly', 'eh_bulk_edit' ); ?> </option>
				<option value="monthly"> <?php esc_html_e( 'Monthly', 'eh_bulk_edit' ); ?> </option>
			</select>
			</td>
		</tr>
		<tr id="select_days_weekly">
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Select Days ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select the day(s) which you want to perform the updates.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td>
				<span class='select-eh'><select data-placeholder='<?php esc_html_e( 'Select days', 'eh_bulk_edit' ); ?>' id='schedule_days_weekly' multiple class='category-chosen weekly-schedule-days' >
							<?php
							{
								echo "<option value='0'>Sunday</option>";
								echo "<option value='1'>Monday</option>";
								echo "<option value='2'>Tuesday</option>";
								echo "<option value='3'>Wednesday</option>";
								echo "<option value='4'>Thursday</option>";
								echo "<option value='5'>Friday</option>";
								echo "<option value='6'>Saturday</option>";
							}
							?>
						</select></span>
			</td>
		</tr>
		<tr id="select_days_monthly">
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Select Dates ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select the date(s) which you want to perform the updates.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td>
				<span class='select-eh'><select data-placeholder='<?php esc_html_e( 'Select days', 'eh_bulk_edit' ); ?>' id='schedule_days_monthly' multiple class='category-chosen monthly-schedule-days' >
							<?php
							for ( $flag = 1;$flag < 32;$flag++ ) {
								echo filter_var( "<option value='$flag'>$flag</option>" );
							}
							?>
						</select></span>
			</td>
		</tr>
		<tr id="stop_schedule_field">
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Stop Schedule ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select the date and time to stop the update schedule.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td>
				<input type="date" id="stop_schedule_date">
				<select id="stop_hr">
					<option value="">Hr</option>
					<?php
					for ( $i = 0;$i <= 23;$i++ ) {
						echo filter_var( '<option value=' . $i . '>' . $i . '</option>' );
					}
					?>
				</select>
				<select id="stop_min">
					<option value="">Min</option>
					<?php
					for ( $i = 0;$i <= 59;$i++ ) {
						echo filter_var( '<option value=' . $i . '>' . $i . '</option>' );
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Enter Name (Optional) ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Enter a name for the bulk edit job', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-right'>
				<input type="text" id="schedule_name"> 
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Create a Log File ', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Enable this field to create a log file for the bulk edit job', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-right'>
				<input type="checkbox" id ="create_log_file"><?php esc_html_e( 'Enable', 'eh_bulk_edit' ); ?>
			</td>
		</tr>
	</table>
	<button id="elex_schedule_back" style='margin:5px 2px 2px 2px; color: white; width:10%; background-color: gray;' class='button button-large'><?php esc_html_e( 'Back', 'eh_bulk_edit' ); ?></button>
	<button id="elex_schedule_cancel" style='margin:5px 2px 2px 2px; color: white; width:10%; background-color: gray;' class='button button-large'><?php esc_html_e( 'Cancel', 'eh_bulk_edit' ); ?></button>
	<button id='schedule_update_button'  style='margin:5px 2px 2px 2px; width:15%;  float: right; ' class='button button-primary button-large'><?php esc_html_e( 'Finish', 'eh_bulk_edit' ); ?></button>        
</div>
