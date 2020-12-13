<?php
/**
 *
 * AJAX API Functions.
 *
 * @package ELEX Bulk Edit Products, Prices & Attributes for Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once 'class-bulk-edit-log.php';
global $hook_suffix;
add_action( 'wp_ajax_eh_bep_get_attributes_action', 'eh_bep_get_attributes_action_callback' );
add_action( 'wp_ajax_eh_bep_get_attributes_action_edit', 'eh_bep_get_attributes_action_edit_callback' );
add_action( 'wp_ajax_eh_bep_all_products', 'eh_bep_list_table_all_callback' );
add_action( 'wp_ajax_eh_bep_count_products', 'eh_bep_count_products_callback' );
add_action( 'wp_ajax_eh_bep_clear_products', 'eh_clear_all_callback' );
add_action( 'wp_ajax_eh_bep_update_products', 'eh_bep_update_product_callback' );
add_action( 'wp_ajax_eh_bep_filter_products', 'eh_bep_search_filter_callback' );
add_action( 'wp_ajax_eh_bep_undo_html', 'eh_bep_undo_html_maker' );
add_action( 'wp_ajax_eh_bulk_edit_display_count', 'eh_bulk_edit_display_count_callback' );
add_action( 'wp_ajax_eh_bep_undo_update', 'eh_bep_undo_update_callback' );
add_action( 'wp_ajax_eh_bulk_edit_save_filter_setting_tab', 'eh_bulk_edit_save_filter_setting_tab_callback' );
add_action( 'wp_ajax_elex_bep_edit_job', 'elex_bep_edit_job_callback' );
add_action( 'wp_ajax_elex_bep_run_job', 'elex_bep_run_job_callback' );
add_action( 'wp_ajax_elex_bep_revert_job', 'eh_bep_undo_html_maker' );
add_action( 'wp_ajax_elex_bep_delete_job', 'elex_bep_delete_job_callback' );
add_action( 'wp_ajax_elex_bep_cancel_schedule', 'elex_bep_cancel_schedule_callback' );
add_action( 'wp_ajax_elex_variations_attribute_change', 'elex_variations_attribute_change_callback' );
add_action( 'wp_ajax_elex_bep_get_attribute_terms', 'elex_bep_get_attribute_terms');
add_action( 'wp_ajax_elex_bep_update_checked_status', 'elex_bep_update_checked_status_callback' );

/** Filter Checkbox Handler. */
function elex_bep_update_checked_status_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$received_data = ! empty( $_POST ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) : array();
	if ( 'update' === $received_data['operation'] ) {
		$filter_checkbox_data = ! empty( get_option( 'elex_bep_filter_checkbox_data' ) ) ? get_option( 'elex_bep_filter_checkbox_data' ) : array();
		if ( 'false' === $received_data['checkbox_status'] ) { // unchecked.
			if ( ! in_array( intval( $received_data['checkbox_id'] ), array_map( 'intval', $filter_checkbox_data ), true ) ) { // don't update if already exists.
				array_push( $filter_checkbox_data, $received_data['checkbox_id'] );
				update_option( 'elex_bep_filter_checkbox_data', $filter_checkbox_data );
			}
		} else { // checked.
			$filter_checkbox_data = array_diff( $filter_checkbox_data, array( $received_data['checkbox_id'] ) );
			update_option( 'elex_bep_filter_checkbox_data', array_values( $filter_checkbox_data ) );
		}
		wp_die();
	} elseif ( 'delete' === $received_data['operation'] ) { // reset.
		delete_option( 'elex_bep_filter_checkbox_data' );
		wp_die();
	} elseif ( 'count' === $received_data['operation'] ) { // return count.
		$filter_checkbox_data = ! empty( get_option( 'elex_bep_filter_checkbox_data' ) ) ? get_option( 'elex_bep_filter_checkbox_data' ) : array();
		$size                 = count( $filter_checkbox_data );
		wp_die( wp_json_encode( $size ) );
	} elseif ( 'unselect_all' === $received_data['operation'] ) { // reset.
		update_option( 'elex_bep_filter_checkbox_data', array_values( get_option( 'bulk_edit_filtered_product_ids_for_select_unselect' ) ) );
		wp_die();
	}
}
function elex_variations_attribute_change_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$attribute_name     = isset( $_POST['attrib'] ) ? sanitize_text_field( $_POST['attrib'] ) : '';
	$selected_from_attr = '';
	$selected_to_attr   = '';
	if ( isset( $_POST['attr_edit'] ) ) {
		$attr_detail_arr    = explode( ',', $attribute_name );
		$from_attr          = $attr_detail_arr[0];
		$to_attr            = $attr_detail_arr[1];
		$from_attr_arr      = explode( ':', $from_attr );
		$to_attr_arr        = explode( ':', $to_attr );
		$attribute_name     = $to_attr_arr[0];
		$selected_from_attr = $from_attr_arr[1];
		$selected_to_attr   = $to_attr_arr[1];
	}

	$cat_args   = array(
		'hide_empty' => false,
		'order'      => 'ASC',
	);
	$attributes = wc_get_attribute_taxonomies();
	foreach ( $attributes as $key => $value ) {
		if ( $attribute_name == $value->attribute_name ) {
			$attribute_name  = $value->attribute_name;
			$attribute_label = $value->attribute_label;
		}
	}
	$attribute_value = get_terms( 'pa_' . $attribute_name, $cat_args );
	$return          = "<tr id='vari_attr_change" . $attribute_name . "'><td>$attribute_label</td><td></td>";
	$return         .= "<td style='width:30%;'><select style='width:50%;' id='vari_attr_change_" . $attribute_name . "'>";
	$return         .= "<option value='" . $attribute_name . ":any'> Any " . $attribute_label . '</option>';
	$selected        = '';
	foreach ( $attribute_value as $key => $value ) {
		if ( $selected_from_attr == $value->slug ) {
			$selected = 'selected';
		} else {
			$selected = '';
		}
		$return .= '<option value=' . $attribute_name . ':' . $value->slug . ' $selected>' . $value->name . '</option>';
	}
	$return .= '</select></td> <td>Change to</td> ';
	$return .= "<td style='width:34%;'><select style='width:50%;' id='vari_attr_to_change_" . $attribute_name . "'>";
	$return .= "<option value='" . $attribute_name . ":any'>Any " . $attribute_label . '</option>';
	foreach ( $attribute_value as $key => $value ) {
		if ( $selected_to_attr == $value->slug ) {
			$selected = 'selected';
		} else {
			$selected = '';
		}
		$return .= '<option value=' . $attribute_name . ':' . $value->slug . ' $selected>' . $value->name . '</option>';
	}
	$return .= '</select></td>';
	$return .= '</tr>';
	if ( isset( $_POST['attr_edit'] ) ) {
		$return_array = array(
			'attribute' => $attribute_name,
			'return'    => $return,
		);
		die( wp_json_encode( $return_array ) );
	}
	echo filter_var( $return );
	exit;
}

function eh_bep_get_attributes_action_callback() {
	global $wpdb;
	$custom_attribute_values = array();
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$attribute_name = isset( $_POST['attrib'] ) ? sanitize_text_field( $_POST['attrib'] ) : '';
	// Get custom attributes.
	$products = $wpdb->get_results(
		"
		SELECT
			postmeta.post_id,
			postmeta.meta_value
		FROM
			{$wpdb->postmeta} AS postmeta
		WHERE
			postmeta.meta_key = '_product_attributes'
			AND COALESCE(postmeta.meta_value, '') != ''
	"
	);
	foreach ( $products as $product ) {
		$product_attributes = maybe_unserialize( $product->meta_value );
		if ( is_array( $product_attributes ) || is_object( $product_attributes ) ) {
			foreach ( $product_attributes as $attribute_slug => $product_attribute ) {
				if ( isset( $product_attribute['is_taxonomy'] ) && $product_attribute['is_taxonomy'] == '0' && $attribute_slug != 'product_shipping_class' ) {
					$values = array_map( 'trim', explode( ' ' . WC_DELIMITER . ' ', $product_attribute['value'] ) );
					foreach ( $values as $value ) {
						$value_slug = $value;
						$custom_attribute_values[ $attribute_slug ][ $value_slug ] = $value;
					}
				}
			}
		}
	}
	if ( count( $custom_attribute_values ) > 0 ) {
		foreach ( $custom_attribute_values as $key => $value ) {
			// In order to differentiate global and custom attributes.
			if ( 'custom_' . $key == $attribute_name ) {
				if ( isset( $_POST['attr_and'] ) ) {
					$return = "<optgroup label='" . ucfirst( $key ) . "' id='grp_and_" . $attribute_name . "'>";
				} else {
					$return = "<optgroup label='" . ucfirst( $key ) . "' id='grp_" . $attribute_name . "'>";
				}
				foreach ( $value as $k => $v ) {
					$return .= "<option value=\"'" . $attribute_name . ':custom_' . strtolower( $v ) . "'\">" . $v . '</option>';
				}
				$return .= '</optgroup>';
				echo filter_var( $return );
				exit;
			}
		}
	}
	$cat_args   = array(
		'hide_empty' => false,
		'order'      => 'ASC',
	);
	$attributes = wc_get_attribute_taxonomies();
	foreach ( $attributes as $key => $value ) {
		if ( $attribute_name == $value->attribute_name ) {
			$attribute_name  = $value->attribute_name;
			$attribute_label = $value->attribute_label;
		}
	}
	$attribute_value = get_terms( 'pa_' . $attribute_name, $cat_args );
	if ( isset( $_POST['attr_and'] ) ) {
		$return = "<optgroup label='" . $attribute_label . "' id='grp_and_" . $attribute_name . "'>";
	} else {
		$return = "<optgroup label='" . $attribute_label . "' id='grp_" . $attribute_name . "'>";
	}
	foreach ( $attribute_value as $key => $value ) {
		$return .= "<option value=\"'pa_" . $attribute_name . ':' . $value->slug . "'\">" . $value->name . '</option>';
	}
	$return .= '</optgroup>';
	error_log( print_r($return, TRUE ));
	echo filter_var( $return );
	exit;
}

function eh_bep_get_attributes_action_edit_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$attribute        = '';
	$attributes_array = array();
	$temp_array       = array();
	$array_attr       = isset( $_POST['attributes'] ) ? array_map( 'sanitize_text_field', ( $_POST['attributes'] ) ) : array();
	foreach ( $array_attr as $index => $attributes_val ) {
		$attributes_val = str_replace( "'", '', $attributes_val );
		$attributes_val = stripslashes( stripslashes( $attributes_val ) );
		$attr_val_arr   = explode( ':', $attributes_val );
		$flag_arr       = explode( 'pa_', $attr_val_arr[0] );
		if ( '' == $attribute ) {
			array_push( $temp_array, $attr_val_arr[1] );
			$attribute = $attr_val_arr[0];
		} elseif ( $attr_val_arr[0] != $attribute ) {
			$temp_flag_arr                         = explode( 'pa_', $attribute );
			$attributes_array[ $temp_flag_arr[1] ] = $temp_array;
			$temp_array                            = array();
			array_push( $temp_array, $attr_val_arr[1] );
			$attribute = $attr_val_arr[0];
		} else {
			array_push( $temp_array, $attr_val_arr[1] );
		}
	}
	$attributes_array[ $flag_arr[1] ] = $temp_array;
	$return                           = '';
	foreach ( $attributes_array as $arr_key => $attr_val ) {
		$attribute_name = $arr_key;
		$cat_args       = array(
			'hide_empty' => false,
			'order'      => 'ASC',
		);
		$attributes     = wc_get_attribute_taxonomies();
		foreach ( $attributes as $key => $value ) {
			if ( $attribute_name == $value->attribute_name ) {
				$attribute_name  = $value->attribute_name;
				$attribute_label = $value->attribute_label;
			}
		}
		$attribute_value = get_terms( 'pa_' . $attribute_name, $cat_args );
		if ( isset( $_POST['attr_action'] ) && sanitize_text_field( $_POST['attr_action'] ) == 'or' ) {
			$return .= "<optgroup label='" . $attribute_label . "' id='grp_" . $attribute_name . "'>";
		} else {
			$return .= "<optgroup label='" . $attribute_label . "' id='grp_and_" . $attribute_name . "'>";
		}
		foreach ( $attribute_value as $key => $value ) {
			if ( in_array( $value->slug, $attr_val, true ) ) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			$return .= "<option value=\"'pa_" . $attribute_name . ':' . $value->slug . "'\" $selected>" . $value->name . '</option>';
		}
		$return .= '</optgroup>';
	}
	$return_array = array(
		'attributes'    => array_keys( $attributes_array ),
		'return_select' => $return,
	);
	die( wp_json_encode( $return_array ) );
}

function elex_bep_edit_job_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
	foreach ( $scheduled_jobs as $key => $val ) {
		if ( isset( $_POST['file'] ) && sanitize_text_field( $_POST['file'] ) == $val['job_name'] ) {
			die( wp_json_encode( $val ) );
		}
	}
}

function elex_bep_run_job_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
	foreach ( $scheduled_jobs as $key => $val ) {
		if ( isset( $_POST['file'] ) && sanitize_text_field( $_POST['file'] ) == $val['job_name'] ) {
			$id = xa_bep_filter_products( $val['param_to_save'] );
			// Exclude ids / unchecked ids.
			$val['param_to_save']['pid'] = array_diff( $id, $val['param_to_save']['exclude_ids'] );
			eh_bep_update_product_callback( $val );
			break;
		}
	}
}

function elex_bep_delete_job_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$upload_dir     = wp_upload_dir();
	$base           = $upload_dir['basedir'];
	$path           = $base . '/elex-bulk-edit-products/';
	$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
	foreach ( $scheduled_jobs as $key => $val ) {
		if ( isset( $_POST['file'] ) && sanitize_text_field( $_POST['file'] ) == $val['job_name'] ) {
			unset( $scheduled_jobs[ $key ] );
			$file_to_delete = str_replace( ' ', '_', $val['job_name'] ) . '.txt';
			if ( file_exists( $path . $file_to_delete ) ) {
				unlink( $path . $file_to_delete );
			}
			update_option( 'elex_bep_scheduled_jobs', $scheduled_jobs );
			break;
		}
	}
}

function elex_bep_cancel_schedule_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
	foreach ( $scheduled_jobs as $key => $val ) {
		if ( isset( $_POST['file'] ) && sanitize_text_field( $_POST['file'] ) == $val['job_name'] ) {
			if ( isset( $scheduled_jobs[ $key ]['schedule_opn'] ) ) {
				$scheduled_jobs[ $key ]['schedule_opn'] = false;
				if ( isset( $scheduled_jobs[ $key ]['revert_opn'] ) ) {
					$scheduled_jobs[ $key ]['revert_opn'] = false;
				}
				update_option( 'elex_bep_scheduled_jobs', $scheduled_jobs );
				break;
			}
		}
	}
}
$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
if ( '' != $scheduled_jobs && is_array( $scheduled_jobs ) ) {
	foreach ( $scheduled_jobs as $key => $val ) {
		$date_time               = current_time( 'mysql' );
		$number_of_days_in_month = current_time( 't' );
		$date_time_array         = explode( ' ', $date_time );
		$y_m_d                   = explode( '-', $date_time_array[0] );
		$h_m_s                   = explode( ':', $date_time_array[1] );
		$scheduled               = false;
		$revert                  = false;
		if ( isset( $val['scheduled_action'] ) && 'schedule_later' == $val['scheduled_action'] && $val['schedule_opn'] ) {
			$scheduled = true;
			if ( isset( $val['param_to_save']['stop_schedule_date'] ) && '' != $val['param_to_save']['stop_schedule_date'] ) {
				$stop_hr   = $val['param_to_save']['stop_hr'] ? $val['param_to_save']['stop_hr'] : 0;
				$stop_min  = $val['param_to_save']['stop_min'] ? $val['param_to_save']['stop_min'] : 0;
				$stop_date = strtotime( $val['param_to_save']['stop_schedule_date'] . ' ' . $stop_hr . ':' . $stop_min . ':0' );
				if ( strtotime( $date_time ) >= $stop_date ) {
					$scheduled = false;
				}
			}
			$schedule_y_m_d = explode( '-', $val['schedule_date'] );
			$hour           = $val['scheduled_hour'];
			$min            = $val['scheduled_min'];
		} elseif ( isset( $val['revert_opn'] ) && $val['revert_opn'] && isset( $val['revert_data'] ) ) {
			$revert         = true;
			$hour           = $val['revert_hour'];
			$min            = $val['revert_min'];
			$schedule_y_m_d = explode( '-', $val['revert_date'] );
		}
		if ( $scheduled || $revert ) {
			$run = false;
			if ( $y_m_d[0] > $schedule_y_m_d[0] ) {
				$run = true;
			} elseif ( $y_m_d[0] == $schedule_y_m_d[0] ) {
				if ( $y_m_d[1] > $schedule_y_m_d[1] ) {
					$run = true;
				} elseif ( $y_m_d[1] == $schedule_y_m_d[1] ) {
					if ( $y_m_d[2] > $schedule_y_m_d[2] ) {
						$run = true;
					} elseif ( $y_m_d[2] == $schedule_y_m_d[2] ) {
						if ( $h_m_s[0] > $hour ) {
							$run = true;
						} elseif ( $h_m_s[0] == $hour ) {
							if ( $h_m_s[1] >= $min ) {
								$run = true;
							}
						}
					}
				}
			}
			if ( $run ) {
				if ( $scheduled ) {
					$pr_id                       = xa_bep_filter_products( $val['param_to_save'] );
					$val['param_to_save']['pid'] = $pr_id;
					$callback                    = eh_bep_update_product_callback( $val );
					if ( '' != $callback ) {
						$scheduled_jobs[ $key ]['revert_data'] = $callback['undo_products'];
						$scheduled_jobs[ $key ]['edit_data']   = $callback['edit_data'];
					}
					if ( isset( $val['param_to_save']['schedule_frequency_action'] ) && '' !== $val['param_to_save']['schedule_frequency_action'] ) {
						if ( 'weekly' === $val['param_to_save']['schedule_frequency_action'] ) {
							$sort_days = $val['param_to_save']['schedule_weekly_days'];
							$today     = current_time( 'w' );
							$next_day  = '';
							foreach ( $sort_days as $index => $day_num ) {
								if ( $day_num > $today ) {
									$next_day = $day_num;
									break;
								}
							}
							if ( '' == $next_day ) {
								$next_day = $sort_days[0];
							}
							$day           = '';
							$next_schedule = '';
							switch ( $next_day ) {
								case 0:
									$day = 'sunday';
									break;
								case 1:
									$day = 'monday';
									break;
								case 2:
									$day = 'tuesday';
									break;
								case 3:
									$day = 'wednesday';
									break;
								case 4:
									$day = 'thursday';
									break;
								case 5:
									$day = 'friday';
									break;
								case 6:
									$day = 'saturday';
									break;
							}
							$next_schedule                           = gmdate( 'Y-m-d', strtotime( 'next ' . $day, strtotime( current_time( 'mysql' ) ) ) );
							$scheduled_jobs[ $key ]['schedule_date'] = $next_schedule;
						}
						if ( 'daily' == $val['param_to_save']['schedule_frequency_action'] ) {
							$next_schedule                           = gmdate( 'Y-m-d', strtotime( 'tomorrow', strtotime( current_time( 'mysql' ) ) ) );
							$scheduled_jobs[ $key ]['schedule_date'] = $next_schedule;
						}
						if ( 'monthly' == $val['param_to_save']['schedule_frequency_action'] ) {
							$sort_days  = $val['param_to_save']['schedule_monthly_days'];
							$today      = current_time( 'j' );
							$next_day   = '';
							$next_month = $y_m_d[1];
							$next_year  = $y_m_d[0];
							foreach ( $sort_days as $index => $day_num ) {
								if ( $day_num > $today ) {
									$next_day = $day_num;
									break;
								}
							}
							if ( '' == $next_day ) {
								$next_day = $sort_days[0];
								$next_month++;
								if ( $next_month > 12 ) {
									$next_month = 1;
									$next_year++;
								}
							} elseif ( $next_day > $number_of_days_in_month ) {
								$next_day = $number_of_days_in_month;
							}
							$scheduled_jobs[ $key ]['schedule_date'] = $next_year . '-' . $next_month . '-' . $next_day;
						}
					} else {
						$scheduled_jobs[ $key ]['schedule_opn'] = false;
					}
				} else {
					eh_bep_undo_update_callback( $val['revert_data'] );
					$scheduled_jobs[ $key ]['revert_opn'] = false;
				}
				update_option( 'elex_bep_scheduled_jobs', $scheduled_jobs );
			}
		}
	}
}

function eh_bulk_edit_display_count_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$value = isset( $_POST['row_count'] ) ? sanitize_text_field( $_POST['row_count'] ) : '';
	update_option( 'eh_bulk_edit_table_row', $value );
	die( 'success' );
}

function eh_bulk_edit_save_filter_setting_tab_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$metas_to_save = isset( $_POST['metas_to_save'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['metas_to_save'] ) ) : '';
	update_option( 'eh_bulk_edit_meta_values_to_update', $metas_to_save );
	die();
}

function eh_bep_count_products_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$filtered_products = xa_bep_get_selected_products();
	die( wp_json_encode( $filtered_products ) );
}

function eh_bep_undo_update_callback( $sch_jobs = '' ) {
	set_time_limit( 300 );
	$product_count = 0;
	if ( '' == $sch_jobs ) {
		check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
		if ( isset( $_POST['undo_sch_job'] ) && sanitize_text_field( $_POST['undo_sch_job'] ) ) {
			$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
			foreach ( $scheduled_jobs as $key => $val ) {
				if ( isset( $_POST['file'] ) && sanitize_text_field( $_POST['file'] ) == $val['job_name'] ) {
					$undo_product_id = $val['revert_data'];
					break;
				}
			}
		} else {
			$undo_product_id = get_option( 'eh_bulk_edit_undo_product_id', array() );
		}
		$product_chunk          = array_chunk( $undo_product_id, 100 );
		$undo_values_for_fields = isset( $_POST['undo_values'] ) ? sanitize_text_field( $_POST['undo_values'] ) : '';
		$undo_fields            = explode( ',', $undo_values_for_fields );
		$product_data           = isset( $_POST['index'] ) ? $product_chunk[ sanitize_text_field( $_POST['index'] ) ] : array();
	} else {
		$product_data = $sch_jobs;
		$undo_fields  = array();
		foreach ( $sch_jobs as $key => $val ) {
			$undo_fields = array_keys( $val );
			if ( 'cat_none' == $val['category_opn'] ) {
				unset( $undo_fields['category_opn'] );
				unset( $undo_fields['categories'] );
			}
		}
	}
	foreach ( $product_data as $pid => $current_product ) {

		$product = wc_get_product( $current_product['id'] );
		if ( ! empty( $product ) && $product->is_type( 'variation' ) ) {
			$parent_id = ( WC()->version < '2.7.0' ) ? $product->parent->id : $product->get_parent_id();
			$product   = wc_get_product( $current_product['id'] );
		}
		apply_filters( 'http_request_timeout', 30 );
		if ( eh_bep_in_array_fields_check( 'delete_product', $undo_fields ) && isset( $current_product['delete_product'] ) ) {
			wp_untrash_post( $current_product['delete_product'] );
		}
		if ( eh_bep_in_array_fields_check( 'title', $undo_fields ) && isset( $current_product['title'] ) ) {
			$my_post = array(
				'ID'         => $current_product['id'],
				'post_title' => $current_product['title'],
			);
			wp_update_post( $my_post );
		}
		if ( eh_bep_in_array_fields_check( 'sku', $undo_fields ) && isset( $current_product['sku'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_sku', $current_product['sku'] );
		}
		if ( eh_bep_in_array_fields_check( 'catalog', $undo_fields ) && isset( $current_product['catalog'] ) ) {
			if ( WC()->version < '3.0.0' ) {
				eh_bep_update_meta_fn( $current_product['id'], '_visibility', $current_product['catalog'] );
			} else {
				$options = array_keys( wc_get_product_visibility_options() );
				if ( in_array( $current_product['catalog'], $options, true ) ) {
					$product->set_catalog_visibility( wc_clean( $current_product['catalog'] ) );
					$product->save();
				}
			}
		}
		if ( eh_bep_in_array_fields_check( 'featured', $undo_fields ) && isset( $current_product['featured'] ) ) {
			if ( WC()->version < '3.0.0' ) {
				eh_bep_update_meta_fn( $current_product['id'], '_featured', $current_product['featured'] );
			} else {
					$product->set_featured( wc_clean( $current_product['featured'] ) );
					$product->save();
			}
		}
		if ( eh_bep_in_array_fields_check( 'vari_attribute', $undo_fields ) && isset( $current_product['vari_attribute'] ) ) {
			$product->set_attributes( $current_product['vari_attribute'] );
			$product->save();
		}
		if ( eh_bep_in_array_fields_check( 'main_image', $undo_fields ) && isset( $current_product['main_image'] ) ) {
			$product->set_image_id( $current_product['main_image'] );
			$product->save();
		}
		if ( eh_bep_in_array_fields_check( 'gallery_images', $undo_fields ) && isset( $current_product['gallery_images'] ) ) {
			$product->set_gallery_image_ids( $current_product['gallery_images'] );
			$product->save();
		}
		if ( eh_bep_in_array_fields_check( 'description', $undo_fields ) && isset( $current_product['description'] ) ) {
			$product->set_description( $current_product['description'] );
			$product->save();
		}
		if ( eh_bep_in_array_fields_check( 'short_description', $undo_fields ) && isset( $current_product['short_description'] ) ) {
			$product->set_short_description( $current_product['short_description'] );
			$product->save();
		}
		if ( eh_bep_in_array_fields_check( 'shipping', $undo_fields ) && isset( $current_product['shipping'] ) ) {
			wp_set_object_terms( (int) $current_product['id'], (int) $current_product['shipping'], 'product_shipping_class' );
		}
		if ( eh_bep_in_array_fields_check( 'sale', $undo_fields ) && isset( $current_product['sale'] ) ) {
			$undo_sale_val = $current_product['sale'];
			if ( 0 == $current_product['sale'] ) {
				$undo_sale_val = '';
			}
			eh_bep_update_meta_fn( $current_product['id'], '_sale_price', $undo_sale_val );
		}
		if ( eh_bep_in_array_fields_check( 'regular', $undo_fields ) && isset( $current_product['regular'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_regular_price', $current_product['regular'] );
		}
		if ( get_post_meta( $current_product['id'], '_sale_price', true ) !== '' && get_post_meta( $current_product['id'], '_regular_price', true ) !== '' ) {
			eh_bep_update_meta_fn( $current_product['id'], '_price', get_post_meta( $current_product['id'], '_sale_price', true ) );
		} elseif ( get_post_meta( $current_product['id'], '_sale_price', true ) === '' && get_post_meta( $current_product['id'], '_regular_price', true ) !== '' ) {
			eh_bep_update_meta_fn( $current_product['id'], '_price', get_post_meta( $current_product['id'], '_regular_price', true ) );
		} elseif ( get_post_meta( $current_product['id'], '_sale_price', true ) !== '' && get_post_meta( $current_product['id'], '_regular_price', true ) === '' ) {
			eh_bep_update_meta_fn( $current_product['id'], '_price', get_post_meta( $current_product['id'], '_sale_price', true ) );
		} elseif ( get_post_meta( $current_product['id'], '_sale_price', true ) === '' && get_post_meta( $current_product['id'], '_regular_price', true ) === '' ) {
			eh_bep_update_meta_fn( $current_product['id'], '_price', '' );
		}
		if ( eh_bep_in_array_fields_check( 'manage_stock', $undo_fields ) && isset( $current_product['stock_manage'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_manage_stock', $current_product['stock_manage'] );
		}
		if ( eh_bep_in_array_fields_check( 'quantity', $undo_fields ) && isset( $current_product['stock_quantity'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_stock', $current_product['stock_quantity'] );
		}
		if ( eh_bep_in_array_fields_check( 'backorders', $undo_fields ) && isset( $current_product['backorder'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_backorders', $current_product['backorder'] );
		}
		if ( eh_bep_in_array_fields_check( 'stock_status', $undo_fields ) && isset( $current_product['stock_status'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_stock_status', $current_product['stock_status'] );
		}
		if ( eh_bep_in_array_fields_check( 'length', $undo_fields ) && isset( $current_product['length'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_length', $current_product['length'] );
		}
		if ( eh_bep_in_array_fields_check( 'width', $undo_fields ) && isset( $current_product['width'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_width', $current_product['width'] );
		}
		if ( eh_bep_in_array_fields_check( 'height', $undo_fields ) && isset( $current_product['height'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_height', $current_product['height'] );
		}
		if ( eh_bep_in_array_fields_check( 'weight', $undo_fields ) && isset( $current_product['weight'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_weight', $current_product['weight'] );
		}
		if ( eh_bep_in_array_fields_check( 'tax_class_action', $undo_fields ) && isset( $current_product['tax_class_action'] ) ) {
			update_post_meta( $current_product['id'], '_tax_class', $current_product['tax_class_action'] );
		}
		if ( eh_bep_in_array_fields_check( 'tax_status_action', $undo_fields ) && isset( $current_product['tax_status_action'] ) ) {
			update_post_meta( $current_product['id'], '_tax_status', $current_product['tax_status_action'] );
		}
		if ( eh_bep_in_array_fields_check( 'hide_price', $undo_fields ) && isset( $current_product['product_adjustment_hide_price_unregistered'] ) ) {
			update_post_meta( $current_product['id'], 'product_adjustment_hide_price_unregistered', $current_product['product_adjustment_hide_price_unregistered'] );
		}
		if ( eh_bep_in_array_fields_check( 'hide_price_role', $undo_fields ) && isset( $current_product['eh_pricing_adjustment_product_price_user_role'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], 'eh_pricing_adjustment_product_price_user_role', $current_product['eh_pricing_adjustment_product_price_user_role'] );
		}
		if ( eh_bep_in_array_fields_check( 'price_adjustment', $undo_fields ) && isset( $current_product['product_based_price_adjustment'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], 'product_based_price_adjustment', $current_product['product_based_price_adjustment'] );
		}
		if ( eh_bep_in_array_fields_check( 'wf_shipping_unit', $undo_fields ) && isset( $current_product['wf_shipping_unit'] ) ) {
			eh_bep_update_meta_fn( $current_product['id'], '_wf_shipping_unit', $current_product['wf_shipping_unit'] );
		}
		if ( isset( $current_product['custom_meta'] ) ) {
			if ( isset( $_POST['file'] ) && '' != $_POST['file'] ) {
				$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
				foreach ( $scheduled_jobs as $key => $val ) {
					if ( sanitize_text_field( $_POST['file'] ) == $val['job_name'] ) {
						if ( isset( $val['param_to_save']['meta_fields'] ) ) {
							$keys = $val['param_to_save']['meta_fields'];
						}
						break;
					}
				}
			} else {
				$keys = get_option( 'eh_bulk_edit_meta_values_to_update' );
			}

			if ( ! empty( $keys ) ) {
				$key_size = count( $keys );
				for ( $i = 0; $i < $key_size; $i++ ) {
					if ( eh_bep_in_array_fields_check( $keys[ $i ], $undo_fields ) ) {
						update_post_meta( $current_product['id'], $keys[ $i ], $current_product['custom_meta'][ $i ] );
					}
				}
			}
		}
		if ( eh_bep_in_array_fields_check( 'categories', $undo_fields ) ) {
			wp_set_object_terms( $current_product['id'], $current_product['categories'], 'product_cat' );
		}
		if ( eh_bep_in_array_fields_check( 'attributes', $undo_fields ) && isset( $current_product['attributes'] ) ) {
			$_product_attributes = get_post_meta( $current_product['id'], '_product_attributes', true );
			foreach ( $_product_attributes as $key => $val ) {
				$_product_attributes[ $key ]['value'] = wc_get_product_terms( $current_product['id'], $key );
			}
			foreach ( $_product_attributes as $key2 => $val2 ) {
				foreach ( $_product_attributes[ $key ]['value'] as $k => $v ) {
					wp_remove_object_terms( $current_product['id'], $v, $key2 );
				}
			}

			$is_vari = 0;
			$i       = 0;
			foreach ( $current_product['attributes'] as $attr_name => $attr_details ) {
				$is_vari = $current_product['attributes'][ $attr_name ]['is_variation'];
				$is_visi = $current_product['attributes'][ $attr_name ]['is_visible'];
				foreach ( $current_product['attributes'][ $attr_name ]['value'] as $att_ind => $attr_value ) {
					wp_set_object_terms( $current_product['id'], $attr_value, $attr_name, true );
					$thedata = array(
						$attr_name => array(
							'name'         => $attr_name,
							'value'        => $attr_value,
							'is_visible'   => $is_visi,
							'is_taxonomy'  => '1',
							'is_variation' => $is_vari,
						),
					);
					if ( 0 == $i ) {
						update_post_meta( $current_product['id'], '_product_attributes', $thedata );
						$i++;
					} else {
						$_product_attr = get_post_meta( $current_product['id'], '_product_attributes', true );
						if ( ! empty( $_product_attr ) ) {
							update_post_meta( $current_product['id'], '_product_attributes', array_merge( $_product_attr, $thedata ) );
						} else {
							update_post_meta( $current_product['id'], '_product_attributes', $thedata );
						}
					}
				}
			}
		}

		$product_count++;
		wc_delete_product_transients( $current_product['id'] );
	}
	$index_count = isset( $_POST['index'] ) ? sanitize_text_field( $_POST['index'] ) : 0;
	if ( '' == $sch_jobs && count( $product_chunk ) - 1 == $index_count ) {
		delete_option( 'eh_bulk_edit_undo_product_id' );
		delete_option( 'eh_bulk_edit_undo_variation_id' );
		delete_option( 'eh_bulk_edit_undo_edit_data' );
		die( 'done' );
	}
	if ( '' == $sch_jobs ) {
		die( wp_json_encode( count( $undo_product_id ) ) );
	}
}

function eh_bep_in_array_fields_check( $key, $array ) {
	if ( empty( $array ) ) {
		return;
	}
	if ( in_array( $key, $array, true ) ) {
		return true;
	} else {
		return false;
	}
}

// custom rounding

function eh_bep_round_ceiling( $number, $significance = 1 ) {
	return ( is_numeric( $number ) && is_numeric( $significance ) ) ? ( ceil( $number / $significance ) * $significance ) : false;
}

function eh_bep_update_product_callback( $sch_jobs = '' ) {
	set_time_limit( 300 );
	// HTML tags and attributes allowed in description and short description.
	$allowed_html = wp_kses_allowed_html( 'post' );
	if ( '' == $sch_jobs ) {
		check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
		$fields_and_values         = array();
		$fields_and_values['type'] = '';
		if ( isset( $_POST['type'] ) && is_array( $_POST['type'] ) ) {
			$fields_and_values['type'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['type'] ) );
		}

		$fields_and_values['custom_attribute'] = '';
		if ( isset( $_POST['custom_attribute'] ) && is_array( $_POST['custom_attribute'] ) ) {
			$fields_and_values['custom_attribute'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['custom_attribute'] ) );
		}

		$fields_and_values['pid'] = '';
		// Exclude unchecked products for update.
		$unchecked_product_ids = ! empty( get_option( 'elex_bep_filter_checkbox_data' ) ) ? array_map( 'sanitize_text_field', ( get_option( 'elex_bep_filter_checkbox_data' ) ) ) : array();
		if ( isset( $_POST['pid'] ) && is_array( $_POST['pid'] ) ) {
			$filtered_product_ids     = array_map( 'sanitize_text_field', wp_unslash( $_POST['pid'] ) );
			$fields_and_values['pid'] = array_diff( $filtered_product_ids, $unchecked_product_ids );
		}
		if ( isset( $_POST['index_val'] ) ) {
			$fields_and_values['index_val'] = sanitize_text_field( $_POST['index_val'] );
		}
		if ( isset( $_POST['chunk_length'] ) ) {
			$fields_and_values['chunk_length'] = sanitize_text_field( $_POST['chunk_length'] );
		}
		$fields_and_values['attribute_value'] = '';
		if ( isset( $_POST['attribute_value'] ) && is_array( $_POST['attribute_value'] ) ) {
			$fields_and_values['attribute_value'] = array_map( 'sanitize_text_field', ( $_POST['attribute_value'] ) );
		}
		if ( isset( $_POST['attribute_action'] ) ) {
			$fields_and_values['attribute_action'] = sanitize_text_field( $_POST['attribute_action'] );
		}
		$fields_and_values['new_attribute_values'] = '';
		if ( isset( $_POST['new_attribute_values'] ) && is_array( $_POST['new_attribute_values'] ) ) {
			$fields_and_values['new_attribute_values'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['new_attribute_values'] ) );
		}
		if ( isset( $_POST['attribute_variation'] ) ) {
			$fields_and_values['attribute_variation'] = sanitize_text_field( $_POST['attribute_variation'] );
		}
		$fields_and_values['categories_to_update'] = '';
		if ( isset( $_POST['categories_to_update'] ) && is_array( $_POST['categories_to_update'] ) ) {
			$fields_and_values['categories_to_update'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['categories_to_update'] ) );
		}
		if ( isset( $_POST['category_update_option'] ) ) {
			$fields_and_values['category_update_option'] = sanitize_text_field( $_POST['category_update_option'] );
		}
		if ( isset( $_POST['undo_update_op'] ) ) {
			$fields_and_values['undo_update_op'] = sanitize_text_field( $_POST['undo_update_op'] );
		}
		if ( isset( $_POST['shipping_unit'] ) ) {
			$fields_and_values['shipping_unit'] = sanitize_text_field( $_POST['shipping_unit'] );
		}
		if ( isset( $_POST['shipping_unit_select'] ) ) {
			$fields_and_values['shipping_unit_select'] = sanitize_text_field( $_POST['shipping_unit_select'] );
		}
		if ( isset( $_POST['title_select'] ) ) {
			$fields_and_values['title_select'] = sanitize_text_field( $_POST['title_select'] );
		}
		if ( isset( $_POST['sku_select'] ) ) {
			$fields_and_values['sku_select'] = sanitize_text_field( $_POST['sku_select'] );
		}
		if ( isset( $_POST['catalog_select'] ) ) {
			$fields_and_values['catalog_select'] = sanitize_text_field( $_POST['catalog_select'] );
		}
		if ( isset( $_POST['is_featured'] ) ) {
			$fields_and_values['is_featured'] = sanitize_text_field( $_POST['is_featured'] );
		}
		if ( isset( $_POST['shipping_select'] ) ) {
			$fields_and_values['shipping_select'] = sanitize_text_field( $_POST['shipping_select'] );
		}
		if ( isset( $_POST['sale_select'] ) ) {
			$fields_and_values['sale_select'] = sanitize_text_field( $_POST['sale_select'] );
		}
		if ( isset( $_POST['sale_round_select'] ) ) {
			$fields_and_values['sale_round_select'] = sanitize_text_field( $_POST['sale_round_select'] );
		}
		if ( isset( $_POST['regular_check_val'] ) ) {
			$fields_and_values['regular_check_val'] = sanitize_text_field( $_POST['regular_check_val'] );
		}
		if ( isset( $_POST['regular_round_select'] ) ) {
			$fields_and_values['regular_round_select'] = sanitize_text_field( $_POST['regular_round_select'] );
		}
		if ( isset( $_POST['regular_select'] ) ) {
			$fields_and_values['regular_select'] = sanitize_text_field( $_POST['regular_select'] );
		}
		if ( isset( $_POST['stock_manage_select'] ) ) {
			$fields_and_values['stock_manage_select'] = sanitize_text_field( $_POST['stock_manage_select'] );
		}
		if ( isset( $_POST['quantity_select'] ) ) {
			$fields_and_values['quantity_select'] = sanitize_text_field( $_POST['quantity_select'] );
		}
		if ( isset( $_POST['backorder_select'] ) ) {
			$fields_and_values['backorder_select'] = sanitize_text_field( $_POST['backorder_select'] );
		}
		if ( isset( $_POST['stock_status_select'] ) ) {
			$fields_and_values['stock_status_select'] = sanitize_text_field( $_POST['stock_status_select'] );
		}
		if ( isset( $_POST['length_select'] ) ) {
			$fields_and_values['length_select'] = sanitize_text_field( $_POST['length_select'] );
		}
		if ( isset( $_POST['width_select'] ) ) {
			$fields_and_values['width_select'] = sanitize_text_field( $_POST['width_select'] );
		}
		if ( isset( $_POST['height_select'] ) ) {
			$fields_and_values['height_select'] = sanitize_text_field( $_POST['height_select'] );
		}
		if ( isset( $_POST['weight_select'] ) ) {
			$fields_and_values['weight_select'] = sanitize_text_field( $_POST['weight_select'] );
		}
		if ( isset( $_POST['title_text'] ) ) {
			$fields_and_values['title_text'] = sanitize_text_field( $_POST['title_text'] );
		}
		if ( isset( $_POST['replace_title_text'] ) ) {
			$fields_and_values['replace_title_text'] = sanitize_text_field( $_POST['replace_title_text'] );
		}
		if ( isset( $_POST['regex_replace_title_text'] ) ) {
			$fields_and_values['regex_replace_title_text'] = sanitize_text_field( $_POST['regex_replace_title_text'] );
		}
		if ( isset( $_POST['sku_text'] ) ) {
			$fields_and_values['sku_text'] = sanitize_text_field( $_POST['sku_text'] );
		}
		if ( isset( $_POST['sku_replace_text'] ) ) {
			$fields_and_values['sku_replace_text'] = sanitize_text_field( $_POST['sku_replace_text'] );
		}
		if ( isset( $_POST['regex_sku_replace_text'] ) ) {
			$fields_and_values['regex_sku_replace_text'] = sanitize_text_field( $_POST['regex_sku_replace_text'] );
		}
		if ( isset( $_POST['sale_text'] ) ) {
			$fields_and_values['sale_text'] = sanitize_text_field( $_POST['sale_text'] );
		}
		if ( isset( $_POST['sale_round_text'] ) ) {
			$fields_and_values['sale_round_text'] = sanitize_text_field( $_POST['sale_round_text'] );
		}
		if ( isset( $_POST['regular_round_text'] ) ) {
			$fields_and_values['regular_round_text'] = sanitize_text_field( $_POST['regular_round_text'] );
		}
		if ( isset( $_POST['regular_text'] ) ) {
			$fields_and_values['regular_text'] = sanitize_text_field( $_POST['regular_text'] );
		}
		if ( isset( $_POST['quantity_text'] ) ) {
			$fields_and_values['quantity_text'] = sanitize_text_field( $_POST['quantity_text'] );
		}
		if ( isset( $_POST['length_text'] ) ) {
			$fields_and_values['length_text'] = sanitize_text_field( $_POST['length_text'] );
		}
		if ( isset( $_POST['width_text'] ) ) {
			$fields_and_values['width_text'] = sanitize_text_field( $_POST['width_text'] );
		}
		if ( isset( $_POST['height_text'] ) ) {
			$fields_and_values['height_text'] = sanitize_text_field( $_POST['height_text'] );
		}
		if ( isset( $_POST['weight_text'] ) ) {
			$fields_and_values['weight_text'] = sanitize_text_field( $_POST['weight_text'] );
		}
		if ( isset( $_POST['hide_price'] ) ) {
			$fields_and_values['hide_price'] = sanitize_text_field( $_POST['hide_price'] );
		}
		if ( isset( $_POST['hide_price_role'] ) ) {
			$fields_and_values['hide_price_role'] = sanitize_text_field( $_POST['hide_price_role'] );
		}
		if ( isset( $_POST['price_adjustment'] ) ) {
			$fields_and_values['price_adjustment'] = sanitize_text_field( $_POST['price_adjustment'] );
		}
		$fields_and_values['regex_flag_sele_title'] = '';
		if ( isset( $_POST['regex_flag_sele_title'] ) && is_array( $_POST['regex_flag_sele_title'] ) ) {
			$fields_and_values['regex_flag_sele_title'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['regex_flag_sele_title'] ) );
		}
		$fields_and_values['regex_flag_sele_sku'] = '';
		if ( isset( $_POST['regex_flag_sele_sku'] ) && is_array( $_POST['regex_flag_sele_sku'] ) ) {
			$fields_and_values['regex_flag_sele_sku'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['regex_flag_sele_sku'] ) );
		}
		if ( isset( $_POST['scheduled_action'] ) ) {
			$fields_and_values['scheduled_action'] = sanitize_text_field( $_POST['scheduled_action'] );
		}
		if ( isset( $_POST['save_job'] ) ) {
			$fields_and_values['save_job'] = sanitize_text_field( $_POST['save_job'] );
		}
		if ( isset( $_POST['schedule_date'] ) ) {
			$fields_and_values['schedule_date'] = sanitize_text_field( $_POST['schedule_date'] );
		}
		if ( isset( $_POST['revert_date'] ) ) {
			$fields_and_values['revert_date'] = sanitize_text_field( $_POST['revert_date'] );
		}
		if ( isset( $_POST['scheduled_hour'] ) ) {
			$fields_and_values['scheduled_hour'] = sanitize_text_field( $_POST['scheduled_hour'] );
		}
		if ( isset( $_POST['scheduled_min'] ) ) {
			$fields_and_values['scheduled_min'] = sanitize_text_field( $_POST['scheduled_min'] );
		}
		if ( isset( $_POST['revert_hour'] ) ) {
			$fields_and_values['revert_hour'] = sanitize_text_field( $_POST['revert_hour'] );
		}
		if ( isset( $_POST['revert_min'] ) ) {
			$fields_and_values['revert_min'] = sanitize_text_field( $_POST['revert_min'] );
		}
		if ( isset( $_POST['schedule_frequency_action'] ) ) {
			$fields_and_values['schedule_frequency_action'] = sanitize_text_field( $_POST['schedule_frequency_action'] );
		}
		$fields_and_values['schedule_weekly_days'] = '';
		if ( isset( $_POST['schedule_weekly_days'] ) && is_array( $_POST['schedule_weekly_days'] ) ) {
			$fields_and_values['schedule_weekly_days'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['schedule_weekly_days'] ) );
		}
		$fields_and_values['schedule_monthly_days'] = '';
		if ( isset( $_POST['schedule_monthly_days'] ) && is_array( $_POST['schedule_monthly_days'] ) ) {
			$fields_and_values['schedule_monthly_days'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['schedule_monthly_days'] ) );
		}
		if ( isset( $_POST['stop_schedule_date'] ) ) {
			$fields_and_values['stop_schedule_date'] = sanitize_text_field( $_POST['stop_schedule_date'] );
		}
		if ( isset( $_POST['stop_hr'] ) ) {
			$fields_and_values['stop_hr'] = sanitize_text_field( $_POST['stop_hr'] );
		}
		if ( isset( $_POST['stop_min'] ) ) {
			$fields_and_values['stop_min'] = sanitize_text_field( $_POST['stop_min'] );
		}
		if ( isset( $_POST['job_name'] ) ) {
			$fields_and_values['job_name'] = sanitize_text_field( $_POST['job_name'] );
		}
		if ( isset( $_POST['create_log_file'] ) ) {
			$fields_and_values['create_log_file'] = sanitize_text_field( $_POST['create_log_file'] );
		}
		if ( isset( $_POST['is_edit_job'] ) ) {
			$fields_and_values['is_edit_job'] = sanitize_text_field( $_POST['is_edit_job'] );
		}
		$fields_and_values['category_filter'] = '';
		if ( isset( $_POST['category_filter'] ) && is_array( $_POST['category_filter'] ) ) {
			$fields_and_values['category_filter'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['category_filter'] ) );
		}
		$fields_and_values['custom_meta'] = '';
		if ( isset( $_POST['custom_meta'] ) && is_array( $_POST['custom_meta'] ) ) {
			$fields_and_values['custom_meta'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['custom_meta'] ) );
		}
		$fields_and_values['meta_fields'] = '';
		if ( isset( $_POST['meta_fields'] ) && is_array( $_POST['meta_fields'] ) ) {
			$fields_and_values['meta_fields'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['meta_fields'] ) );
		}
		if ( isset( $_POST['sub_category_filter'] ) ) {
			$fields_and_values['sub_category_filter'] = sanitize_text_field( $_POST['sub_category_filter'] );
		}
		if ( isset( $_POST['attribute'] ) ) {
			$fields_and_values['attribute'] = sanitize_text_field( $_POST['attribute'] );
		}
		if ( isset( $_POST['attribute_variation'] ) ) {	
			$fields_and_values['attribute_variation'] = sanitize_text_field( $_POST['attribute_variation'] );	
		}	
		if ( isset( $_POST['attr_visible_action'] ) ) {	
			$fields_and_values['attr_visible_action'] = sanitize_text_field( $_POST['attr_visible_action'] );	
		}
		if ( isset( $_POST['product_title_select'] ) ) {
			$fields_and_values['product_title_select'] = sanitize_text_field( $_POST['product_title_select'] );
		}
		if ( isset( $_POST['product_title_text'] ) ) {
			$fields_and_values['product_title_text'] = sanitize_text_field( $_POST['product_title_text'] );
		}
		$fields_and_values['regex_flags'] = '';
		if ( isset( $_POST['regex_flags'] ) && is_array( $_POST['regex_flags'] ) ) {
			$fields_and_values['regex_flags'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['regex_flags'] ) );
		}

		if ( isset( $_POST['product_description_select'] ) ) {
			$fields_and_values['product_description_select'] = sanitize_text_field( $_POST['product_description_select'] );
		}
		if ( isset( $_POST['product_description_text'] ) ) {
			$fields_and_values['product_description_text'] = sanitize_text_field( $_POST['product_description_text'] );
		}
		$fields_and_values['regex_flags_description'] = '';
		if ( isset( $_POST['regex_flags_description'] ) && is_array( $_POST['regex_flags_description'] ) ) {
			$fields_and_values['regex_flags_description'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['regex_flags_description'] ) );
		}

		if ( isset( $_POST['product_short_description_select'] ) ) {
			$fields_and_values['product_short_description_select'] = sanitize_text_field( $_POST['product_short_description_select'] );
		}
		if ( isset( $_POST['product_short_description_text'] ) ) {
			$fields_and_values['product_short_description_text'] = sanitize_text_field( $_POST['product_short_description_text'] );
		}
		$fields_and_values['regex_flags_short_description'] = '';
		if ( isset( $_POST['regex_flags_short_description'] ) && is_array( $_POST['regex_flags_short_description'] ) ) {
			$fields_and_values['regex_flags_short_description'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['regex_flags_short_description'] ) );
		}

		$fields_and_values['attribute_value_filter'] = '';
		if ( isset( $_POST['attribute_value_filter'] ) && is_array( $_POST['attribute_value_filter'] ) ) {
			$fields_and_values['attribute_value_filter'] = array_map( 'sanitize_text_field', ( $_POST['attribute_value_filter'] ) );
		}
		if ( isset( $_POST['attribute_and'] ) ) {
			$fields_and_values['attribute_and'] = sanitize_text_field( $_POST['attribute_and'] );
		}
		$fields_and_values['attribute_value_and_filter'] = '';
		if ( isset( $_POST['attribute_value_and_filter'] ) && is_array( $_POST['attribute_value_and_filter'] ) ) {
			$fields_and_values['attribute_value_and_filter'] = array_map( 'sanitize_text_field', ( $_POST['attribute_value_and_filter'] ) );
		}
		if ( isset( $_POST['range'] ) ) {
			$fields_and_values['range'] = sanitize_text_field( $_POST['range'] );
		}
		if ( isset( $_POST['desired_price'] ) ) {
			$fields_and_values['desired_price'] = sanitize_text_field( $_POST['desired_price'] );
		}
		if ( isset( $_POST['minimum_price'] ) ) {
			$fields_and_values['minimum_price'] = sanitize_text_field( $_POST['minimum_price'] );
		}
		if ( isset( $_POST['maximum_price'] ) ) {
			$fields_and_values['maximum_price'] = sanitize_text_field( $_POST['maximum_price'] );
		}
		if ( isset( $_POST['attr_visible_action'] ) ) {
			$fields_and_values['attr_visible_action'] = sanitize_text_field( $_POST['attr_visible_action'] );
		}
		$fields_and_values['exclude_ids'] = '';
		if ( isset( $_POST['exclude_ids'] ) && is_array( $_POST['exclude_ids'] ) ) {
			$fields_and_values['exclude_ids'] = array_merge( array_map( 'sanitize_text_field', wp_unslash( $_POST['exclude_ids'] ) ), $unchecked_product_ids );
		} else { // unchecked ids.
			$fields_and_values['exclude_ids'] = $unchecked_product_ids;
		}
		$fields_and_values['exclude_categories'] = '';
		if ( isset( $_POST['exclude_categories'] ) && is_array( $_POST['exclude_categories'] ) ) {
			$fields_and_values['exclude_categories'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['exclude_categories'] ) );
		}
		if ( isset( $_POST['exclude_subcat_check'] ) ) {
			$fields_and_values['exclude_subcat_check'] = sanitize_text_field( $_POST['exclude_subcat_check'] );
		}
		if ( isset( $_POST['enable_exclude_prods'] ) ) {
			$fields_and_values['enable_exclude_prods'] = sanitize_text_field( $_POST['enable_exclude_prods'] );
		}
		if ( isset( $_POST['undo_sch_job'] ) ) {
			$fields_and_values['undo_sch_job'] = sanitize_text_field( $_POST['undo_sch_job'] );
		}
		if ( isset( $_POST['file'] ) ) {
			$fields_and_values['file'] = sanitize_text_field( $_POST['file'] );
		}
		$fields_and_values['prod_tags'] = '';
		if ( isset( $_POST['prod_tags'] ) && is_array( $_POST['prod_tags'] ) ) {
			$fields_and_values['prod_tags'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['prod_tags'] ) );
		}
		$fields_and_values['vari_attribute'] = '';
		if ( isset( $_POST['vari_attribute'] ) && is_array( $_POST['vari_attribute'] ) ) {
			$fields_and_values['vari_attribute'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['vari_attribute'] ) );
		}
		if ( isset( $_POST['description_action'] ) ) {
			$fields_and_values['description_action'] = sanitize_text_field( $_POST['description_action'] );
		}
		if ( isset( $_POST['short_description_action'] ) ) {
			$fields_and_values['short_description_action'] = sanitize_text_field( $_POST['short_description_action'] );
		}
		if ( isset( $_POST['description'] ) ) {
			$fields_and_values['description'] = wp_kses( $_POST['description'], $allowed_html );
		}
		if ( isset( $_POST['short_description'] ) ) {
			$fields_and_values['short_description'] = wp_kses( $_POST['short_description'], $allowed_html );
		}
		if ( isset( $_POST['delete_product_action'] ) ) {
			$fields_and_values['delete_product_action'] = sanitize_text_field( $_POST['delete_product_action'] );
		}
		if ( isset( $_POST['main_image'] ) ) {
			$fields_and_values['main_image'] = sanitize_text_field( $_POST['main_image'] );
		}
		if ( isset( $_POST['gallery_images_action'] ) ) {
			$fields_and_values['gallery_images_action'] = sanitize_text_field( $_POST['gallery_images_action'] );
		}
		$fields_and_values['gallery_images'] = '';
		if ( isset( $_POST['gallery_images'] ) && is_array( $_POST['gallery_images'] ) ) {
			$fields_and_values['gallery_images'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['gallery_images'] ) );
		}
		if ( isset( $_POST['tax_status_action'] ) ) {
			$fields_and_values['tax_status_action'] = sanitize_text_field( $_POST['tax_status_action'] );
		}
		if ( isset( $_POST['tax_class_action'] ) ) {
			$fields_and_values['tax_class_action'] = sanitize_text_field( $_POST['tax_class_action'] );
		}
	} else {
		$fields_and_values = $sch_jobs['param_to_save'];
	}
	$job_name   = $fields_and_values['job_name'];
	$saved_jobs = get_option( 'elex_bep_scheduled_jobs' );
	if ( '' == $sch_jobs ) {
		if ( '' != $fields_and_values['job_name'] && ! empty( $saved_jobs ) ) {
			foreach ( $saved_jobs as $key => $val ) {
				if ( ( $val['param_to_save']['job_name'] == $fields_and_values['job_name'] ) && ( ! ( '' == $sch_jobs && isset( $fields_and_values['is_edit_job'] ) && 'true' == $fields_and_values['is_edit_job'] ) ) ) {
						$job_name = $fields_and_values['job_name'] . '_1';
					break;
				}
			}
		} else {
			$job_count = get_option( 'elex_bep_job_count' );
			if ( '' == $job_count ) {
				$job_name = 'job_1';
				update_option( 'elex_bep_job_count', 1 );
			} else {
				$job_count++;
				$job_name = 'job_' . $job_count;
				update_option( 'elex_bep_job_count', $job_count );
			}
		}
	}
	if ( '' == $sch_jobs && 'schedule_later' == $fields_and_values['scheduled_action'] ) {
		$pids       = array();
		$param      = array();
		$merged_ids = array();
		if ( $fields_and_values['index_val'] == $fields_and_values['chunk_length'] - 1 ) {
			$saved_jobs             = get_option( 'elex_bep_scheduled_jobs' );
			$param['param_to_save'] = $fields_and_values;
			if ( 0 != $fields_and_values['index_val'] ) {
				$prev_ids                      = get_option( 'elex_bep_product_ids_to_schedule' );
				$current_ids                   = $fields_and_values['pid'];
				$res_id                        = array_merge( $prev_ids, $current_ids );
				$param['param_to_save']['pid'] = $res_id;
				delete_option( 'elex_bep_product_ids_to_schedule' );
			}
			$param['scheduled_action'] = $fields_and_values['scheduled_action'];
			$param['save_job']         = $fields_and_values['save_job'];
			$param['schedule_date']    = $fields_and_values['schedule_date'];
			$param['revert_date']      = $fields_and_values['revert_date'];
			$param['scheduled_hour']   = $fields_and_values['scheduled_hour'];
			$param['scheduled_min']    = $fields_and_values['scheduled_min'];
			$param['revert_hour']      = $fields_and_values['revert_hour'];
			$param['revert_min']       = $fields_and_values['revert_min'];
			$param['job_name']         = $job_name;
			$param['create_log_file']  = $fields_and_values['create_log_file'];
			$param['schedule_opn']     = true;

			if ( '' != $fields_and_values['revert_date'] ) {
				$param['revert_opn'] = true;
			}
			if ( '' != $saved_jobs ) {
				if ( ( isset( $fields_and_values['is_edit_job'] ) && 'true' == $fields_and_values['is_edit_job'] ) ) {
					foreach ( $saved_jobs as $index => $jobs ) {
						if ( $jobs['param_to_save']['job_name'] == $fields_and_values['job_name'] ) {
							$saved_jobs[ $index ] = $param;
							break;
						}
					}
					update_option( 'elex_bep_scheduled_jobs', $saved_jobs );
				} else {
					array_push( $saved_jobs, $param );
					update_option( 'elex_bep_scheduled_jobs', $saved_jobs );
				}
			} else {
				$te_arr = array();
				array_push( $te_arr, $param );
				update_option( 'elex_bep_scheduled_jobs', $te_arr );
			}
			die( 'scheduled' );
		} else {
			$saved_pids_ = get_option( 'elex_bep_product_ids_to_schedule' );
			if ( '' == $saved_pids_ ) {
				update_option( 'elex_bep_product_ids_to_schedule', $fields_and_values['pid'] );
			} else {
				$result_ids = array_merge( $saved_pids_, $fields_and_values['pid'] );
				update_option( 'elex_bep_product_ids_to_schedule', $result_ids );
			}
			die( 'part_scheduled' );
		}
	}

	$selected_products                     = $fields_and_values['pid'];
	$undo_product_data                     = array();
	$undo_variation_data                   = array();
	$product_data                          = array();
	$edit_data                             = array();
	$undo_update                           = $fields_and_values['undo_update_op'];
	$edit_data['undo_update']              = $undo_update;
	$title_select                          = $fields_and_values['title_select'];
	$edit_data['title_select']             = $title_select;
	$sku_select                            = $fields_and_values['sku_select'];
	$edit_data['sku_select']               = $sku_select;
	$catalog_select                        = $fields_and_values['catalog_select'];
	$edit_data['catalog_select']           = $catalog_select;
	$featured                              = $fields_and_values['is_featured'];
	$edit_data['featured']                 = $featured;
	$shipping_select                       = $fields_and_values['shipping_select'];
	$edit_data['shipping_select']          = $shipping_select;
	$sale_select                           = $fields_and_values['sale_select'];
	$edit_data['sale_select']              = $sale_select;
	$sale_round_select                     = $fields_and_values['sale_round_select'];
	$edit_data['sale_round_select']        = $sale_round_select;
	$regular_select                        = $fields_and_values['regular_select'];
	$edit_data['regular_select']           = $regular_select;
	$regular_round_select                  = $fields_and_values['regular_round_select'];
	$edit_data['regular_round_select']     = $regular_round_select;
	$stock_manage_select                   = $fields_and_values['stock_manage_select'];
	$edit_data['stock_manage_select']      = $stock_manage_select;
	$quantity_select                       = $fields_and_values['quantity_select'];
	$edit_data['quantity_select']          = $quantity_select;
	$backorder_select                      = $fields_and_values['backorder_select'];
	$edit_data['backorder_select']         = $backorder_select;
	$stock_status_select                   = $fields_and_values['stock_status_select'];
	$edit_data['stock_status_select']      = $stock_status_select;
	$attribute_action                      = $fields_and_values['attribute_action'];
	$edit_data['attribute_action']         = $attribute_action;
	$tax_status_action                     = $fields_and_values['tax_status_action'];
	$edit_data['tax_status_action']        = $tax_status_action;
	$tax_class_action                      = $fields_and_values['tax_class_action'];
	$edit_data['tax_class_action']         = $tax_class_action;
	$length_select                         = $fields_and_values['length_select'];
	$edit_data['length_select']            = $length_select;
	$width_select                          = $fields_and_values['width_select'];
	$edit_data['width_select']             = $width_select;
	$height_select                         = $fields_and_values['height_select'];
	$edit_data['height_select']            = $height_select;
	$weight_select                         = $fields_and_values['weight_select'];
	$edit_data['weight_select']            = $weight_select;
	$title_text                            = $fields_and_values['title_text'];
	$edit_data['title_text']               = $title_text;
	$replace_title_text                    = sanitize_text_field( $fields_and_values['replace_title_text'] );
	$edit_data['replace_title_text']       = $replace_title_text;
	$regex_replace_title_text              = sanitize_text_field( $fields_and_values['regex_replace_title_text'] );
	$edit_data['regex_replace_title_text'] = $regex_replace_title_text;
	$sku_text                              = $fields_and_values['sku_text'];
	$edit_data['sku_text']                 = $sku_text;
	$sku_replace_text                      = sanitize_text_field( $fields_and_values['sku_replace_text'] );
	$edit_data['sku_replace_text']         = $sku_replace_text;
	$regex_sku_replace_text                = sanitize_text_field( $fields_and_values['regex_sku_replace_text'] );
	$edit_data['regex_sku_replace_text']   = $regex_sku_replace_text;
	$sale_text                             = $fields_and_values['sale_text'];
	$edit_data['sale_text']                = $sale_text;
	$sale_round_text                       = isset( $fields_and_values['sale_round_text'] ) ? $fields_and_values['sale_round_text'] : '';
	$edit_data['sale_round_text']          = $sale_round_text;
	$regular_text                          = $fields_and_values['regular_text'];
	$edit_data['regular_text']             = $regular_text;
	$regular_round_text                    = isset( $fields_and_values['regular_round_text'] ) ? $fields_and_values['regular_round_text'] : '';
	$edit_data['regular_round_text']       = $regular_round_text;
	$quantity_text                         = $fields_and_values['quantity_text'];
	$edit_data['quantity_text']            = $quantity_text;
	$length_text                           = $fields_and_values['length_text'];
	$edit_data['length_text']              = $length_text;
	$width_text                            = $fields_and_values['width_text'];
	$edit_data['width_text']               = $width_text;
	$height_text                           = $fields_and_values['height_text'];
	$edit_data['height_text']              = $height_text;
	$weight_text                           = $fields_and_values['weight_text'];
	$edit_data['weight_text']              = $weight_text;
	$hide_price                            = $fields_and_values['hide_price'];
	$edit_data['hide_price']               = $hide_price;
	$hide_price_role                       = ( '' !== $fields_and_values['hide_price_role'] ) ? $fields_and_values['hide_price_role'] : '';
	$edit_data['hide_price_role']          = $hide_price_role;
	$price_adjustment                      = $fields_and_values['price_adjustment'];
	$edit_data['price_adjustment']         = $price_adjustment;
	$shipping_unit                         = sanitize_text_field( $fields_and_values['shipping_unit'] );
	$edit_data['shipping_unit']            = $shipping_unit;
	$shipping_unit_select                  = $fields_and_values['shipping_unit_select'];
	$edit_data['shipping_unit_select']     = $shipping_unit_select;
	$edit_data['categories']               = '';
	$edit_data['category_opn']             = $fields_and_values['category_update_option'];
	$edit_data['vari_attribute']           = '';
	$edit_data['gallery_images']           = '';

	$sale_warning = array();
	foreach ( $selected_products as $pid => $temp ) {
		
		$pid                        = $temp;
		$collect_product_data       = array();
		$collect_product_data['id'] = $pid;

		$collect_product_data['categories']   = '';
		$collect_product_data['category_opn'] = $fields_and_values['category_update_option'];
		apply_filters( 'http_request_timeout', 30 );
		switch ( $hide_price ) {
			case 'yes':
				$collect_product_data['product_adjustment_hide_price_unregistered'] = get_post_meta( $pid, 'product_adjustment_hide_price_unregistered', true );
				eh_bep_update_meta_fn( $pid, 'product_adjustment_hide_price_unregistered', 'yes' );
				break;
			case 'no':
				$collect_product_data['product_adjustment_hide_price_unregistered'] = get_post_meta( $pid, 'product_adjustment_hide_price_unregistered', true );
				eh_bep_update_meta_fn( $pid, 'product_adjustment_hide_price_unregistered', 'no' );
				break;
		}
		switch ( $price_adjustment ) {
			case 'yes':
				$collect_product_data['product_based_price_adjustment'] = get_post_meta( $pid, 'product_based_price_adjustment', true );
				eh_bep_update_meta_fn( $pid, 'product_based_price_adjustment', 'yes' );
				break;
			case 'no':
				$collect_product_data['product_based_price_adjustment'] = get_post_meta( $pid, 'product_based_price_adjustment', true );
				eh_bep_update_meta_fn( $pid, 'product_based_price_adjustment', 'no' );
				break;
		}
		if ( '' !== $hide_price_role ) {
			$collect_product_data['eh_pricing_adjustment_product_price_user_role'] = get_post_meta( $pid, 'eh_pricing_adjustment_product_price_user_role', true );
			eh_bep_update_meta_fn( $pid, 'eh_pricing_adjustment_product_price_user_role', $hide_price_role );
		}
		switch ( $shipping_unit_select ) {
			case 'add':
				$unit                                     = get_post_meta( $pid, '_wf_shipping_unit', true );
				$collect_product_data['wf_shipping_unit'] = $unit;
				$unit_val                                 = number_format( $unit + $shipping_unit, 6, '.', '' );
				eh_bep_update_meta_fn( $pid, '_wf_shipping_unit', $unit_val );
				break;
			case 'sub':
				$unit                                     = get_post_meta( $pid, '_wf_shipping_unit', true );
				$collect_product_data['wf_shipping_unit'] = $unit;
				$unit_val                                 = number_format( $unit - $shipping_unit, 6, '.', '' );
				eh_bep_update_meta_fn( $pid, '_wf_shipping_unit', $unit_val );
				break;
			case 'replace':
				$unit                                     = get_post_meta( $pid, '_wf_shipping_unit', true );
				$collect_product_data['wf_shipping_unit'] = $unit;
				eh_bep_update_meta_fn( $pid, '_wf_shipping_unit', $shipping_unit );
				break;
			default:
				break;
		}
		$temp      = wc_get_product( $pid );
		$parent    = $temp;
		$parent_id = $pid;
		#############
		if ( $temp->is_type( 'variable' ) ){

			$variation_data =  array(
				'attributes' => array(
					'size1'  => array("M", "L"),
					'color1' => array("G", "Y"),
				),
				'sku'           => '',
				'regular_price' => '22.00',
				'sale_price'    => '',
				'stock_qty'     => 10,
			);
			
			include_once 'class-eh-bulk-edit-create-variation.php';
			// elex_bep_create_product_variation($pid, $variation_data );
			include_once "class-bulk-edit-crate-variation-2.php";
			$variation_id = elex_bep_create_variation( $pid, array());
			error_log( "&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&");
		}
		############
		if ( ! empty( $temp ) && $temp->is_type( 'variation' ) ) {
			$parent_id = ( WC()->version < '2.7.0' ) ? $temp->parent->id : $temp->get_parent_id();
			$parent    = wc_get_product( $parent_id );
		}

		$temp_type  = ( WC()->version < '2.7.0' ) ? $temp->product_type : $temp->get_type();
		$temp_title = ( WC()->version < '2.7.0' ) ? $temp->post->post_title : $temp->get_title();

		if ( 'simple' === $temp_type || 'variation' === $temp_type || 'variable' === $temp_type || 'external' == $temp_type ) {
			$product_data                      = array();
			$product_data['type']              = 'simple';
			$product_data['title']             = $temp_title;
			$product_data['sku']               = get_post_meta( $pid, '_sku', true );
			$product_data['catalog']           = ( WC()->version < '3.0.0' ) ? get_post_meta( $pid, '_visibility', true ) : $temp->get_catalog_visibility();
			$product_data['featured']          = ( WC()->version < '3.0.0' ) ? get_post_meta( $pid, '_featured', true ) : $temp->get_featured();
			$ship_args                         = array( 'fields' => 'ids' );
			$product_data['shipping']          = current( wp_get_object_terms( $pid, 'product_shipping_class', $ship_args ) );
			$product_data['sale']              = (float) get_post_meta( $pid, '_sale_price', true );
			$product_data['regular']           = (float) get_post_meta( $pid, '_regular_price', true );
			$product_data['stock_manage']      = get_post_meta( $pid, '_manage_stock', true );
			$product_data['tax_status_action'] = get_post_meta( $pid, '_tax_status', true );
			$product_data['tax_class_action']  = get_post_meta( $pid, '_tax_class', true );
			$product_data['stock_quantity']    = (float) get_post_meta( $pid, '_stock', true );
			$product_data['backorder']         = get_post_meta( $pid, '_backorders', true );
			$product_data['stock_status']      = get_post_meta( $pid, '_stock_status', true );
			$product_data['length']            = (float) get_post_meta( $pid, '_length', true );
			$product_data['width']             = (float) get_post_meta( $pid, '_width', true );
			$product_data['height']            = (float) get_post_meta( $pid, '_height', true );
			$product_data['weight']            = (float) get_post_meta( $pid, '_weight', true );
			$collect_product_data['id']        = $pid;
			$collect_product_data['type']      = $product_data['type'];
			switch ( $title_select ) {
				case 'set_new':
					$my_post                       = array(
						'ID'         => $pid,
						'post_title' => $title_text,
					);
					$collect_product_data['title'] = $product_data['title'];
					wp_update_post( $my_post );
					break;
				case 'append':
					$my_post                       = array(
						'ID'         => $pid,
						'post_title' => $product_data['title'] . $title_text,
					);
					$collect_product_data['title'] = $product_data['title'];
					wp_update_post( $my_post );
					break;
				case 'prepand':
					$my_post                       = array(
						'ID'         => $pid,
						'post_title' => $title_text . $product_data['title'],
					);
					$collect_product_data['title'] = $product_data['title'];
					wp_update_post( $my_post );
					break;
				case 'replace':
					$my_post                       = array(
						'ID'         => $pid,
						'post_title' => str_replace( $replace_title_text, $title_text, $product_data['title'] ),
					);
					$collect_product_data['title'] = $product_data['title'];
					wp_update_post( $my_post );
					break;
				case 'regex_replace':
					if ( @preg_replace( '/' . $regex_replace_title_text . '/', $title_text, $product_data['title'] ) != false ) {
						$regex_flags = '';
						if ( ! empty( $_REQUEST['regex_flag_sele_title'] ) ) {
							foreach ( array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['regex_flag_sele_title'] ) ) as $reg_val ) {
								$regex_flags .= $reg_val;
							}
						}
						$my_post                       = array(
							'ID'         => $pid,
							'post_title' => preg_replace( '/' . $regex_replace_title_text . '/' . $regex_flags, $title_text, $product_data['title'] ),
						);
						$collect_product_data['title'] = $product_data['title'];
						wp_update_post( $my_post );
					}
					break;
			}
			switch ( $sku_select ) {
				case 'set_new':
					$collect_product_data['sku'] = $product_data['sku'];
					eh_bep_update_meta_fn( $pid, '_sku', $sku_text );
					break;
				case 'append':
					$collect_product_data['sku'] = $product_data['sku'];
					$sku_val                     = $product_data['sku'] . $sku_text;
					eh_bep_update_meta_fn( $pid, '_sku', $sku_val );
					break;
				case 'prepand':
					$collect_product_data['sku'] = $product_data['sku'];
					$sku_val                     = $sku_text . $product_data['sku'];
					eh_bep_update_meta_fn( $pid, '_sku', $sku_val );
					break;
				case 'replace':
					$collect_product_data['sku'] = $product_data['sku'];
					$sku_val                     = str_replace( $sku_replace_text, $sku_text, $product_data['sku'] );
					eh_bep_update_meta_fn( $pid, '_sku', $sku_val );
					break;
				case 'regex_replace':
					if ( @preg_replace( '/' . $regex_sku_replace_text . '/', $sku_text, $product_data['sku'] ) !== false ) {
						$regex_flags = '';
						if ( ! empty( $_REQUEST['regex_flag_sele_sku'] ) ) {
							foreach ( array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['regex_flag_sele_sku'] ) ) as $reg_val ) {
								$regex_flags .= $reg_val;
							}
						}
						$sku_val = preg_replace( '/' . $regex_sku_replace_text . '/' . $regex_flags, $sku_text, $product_data['sku'] );
						eh_bep_update_meta_fn( $pid, '_sku', $sku_val );
						$collect_product_data['sku'] = $product_data['sku'];
					}
					break;
			}
			$edit_data['main_image'] = '';
			if ( isset( $fields_and_values['main_image'] ) && $fields_and_values['main_image'] ) {
				$edit_data['main_image']            = $temp->get_image_id();
				$collect_product_data['main_image'] = $edit_data['main_image'];
				$image_id                           = attachment_url_to_postid( $fields_and_values['main_image'] );
				$temp->set_image_id( $image_id );
				$temp->save();
			}
			// Product description(Can be set for variations also).
			if ( isset( $fields_and_values['description'] ) && '' !== $fields_and_values['description'] && '' != $fields_and_values['description_action'] ) {
				$product_details = $temp->get_data();
				$edit_data['description']            = $fields_and_values['description'];
				$collect_product_data['description'] = $product_details['description'];
				if ( 'append' === $fields_and_values['description_action'] ) {
					$desc = $product_details['description'] . $fields_and_values['description'];
				} elseif ( 'prepend' === $fields_and_values['description_action'] ) {
					$desc = $fields_and_values['description'] . $product_details['description'];
				} else {
					$desc = $fields_and_values['description'];
				}
				$temp->set_description( $desc );
				$temp->save();
			}
			if ( 'variation' !== $temp_type ) {
				$collect_product_data['catalog'] = $product_data['catalog'];
				if ( WC()->version < '3.0.0' ) {
					eh_bep_update_meta_fn( $pid, '_visibility', $catalog_select );
				} else {
					$options        = array_keys( wc_get_product_visibility_options() );
					$catalog_select = wc_clean( $catalog_select );
					if ( in_array( $catalog_select, $options, true ) ) {
						$parent->set_catalog_visibility( $catalog_select );
						$parent->save();
					}
				}
				// Set featured.
				if ( isset( $_REQUEST['is_featured'] ) && ! empty( $_REQUEST['is_featured'] ) ) {
					$collect_product_data['featured'] = $product_data['featured'];
					$parent->set_featured( $featured );
					$parent->save();
				}
				$product_details                = $temp->get_data();
				$edit_data['short_description'] = '';
				$edit_data['description']       = '';
				// Product short description.
				if ( isset( $fields_and_values['short_description'] ) && '' != $fields_and_values['short_description'] && '' != $fields_and_values['short_description_action'] ) {
					$edit_data['short_description']            = $fields_and_values['short_description'];
					$collect_product_data['short_description'] = $product_details['short_description'];
					if ( 'append' == $fields_and_values['short_description_action'] ) {
						$short_desc = $product_details['short_description'] . $fields_and_values['short_description'];
					} elseif ( 'prepend' == $fields_and_values['short_description_action'] ) {
						$short_desc = $fields_and_values['short_description'] . $product_details['short_description'];
					} else {
						$short_desc = $fields_and_values['short_description'];
					}
					$temp->set_short_description( $short_desc );
					$temp->save();
				}
				if ( isset( $fields_and_values['gallery_images'] ) && $fields_and_values['gallery_images'] && '' != $fields_and_values['gallery_images_action'] ) {
					$edit_data['gallery_images']            = $fields_and_values['gallery_images_action'];
					$collect_product_data['gallery_images'] = $temp->get_gallery_image_ids();
					$gallery_image_ids                      = array();
					foreach ( $fields_and_values['gallery_images'] as $image_index => $image_url ) {
						$gallery_image_id = attachment_url_to_postid( $image_url );
						array_push( $gallery_image_ids, $gallery_image_id );
					}
					if ( 'add' == $fields_and_values['gallery_images_action'] ) {
						$gallery_image_ids = array_merge( $gallery_image_ids, $collect_product_data['gallery_images'] );
					} elseif ( 'remove' == $fields_and_values['gallery_images_action'] ) {
						$flag_array = array();
						if ( ! empty( $collect_product_data['gallery_images'] ) ) {
							foreach ( $collect_product_data['gallery_images'] as $i_ids ) {
								if ( ! in_array( $i_ids, $gallery_image_ids, true ) ) {
									array_push( $flag_array, $i_ids );
								}
							}
						}
						if ( ! empty( $flag_array ) ) {
							$gallery_image_ids = $flag_array;
						}
					}
					$temp->set_gallery_image_ids( $gallery_image_ids );
					$temp->save();
				}
			} else {
				if ( isset( $fields_and_values['vari_attribute'] ) && is_array( $fields_and_values['vari_attribute'] ) ) {
					$existing_attr     = $temp->get_attributes();
					$change_attributes = array();
					foreach ( $fields_and_values['vari_attribute'] as $index => $attribute_details ) {
						$attr_detail_arr = explode( ',', $attribute_details );
						$from_attr       = $attr_detail_arr[0];
						$to_attr         = $attr_detail_arr[1];
						$from_attr_arr   = explode( ':', $from_attr );
						if ( 'any' == $from_attr_arr[1] ) {
							$from_attr_arr[1] = '';
						}
						if ( array_key_exists( 'pa_' . $from_attr_arr[0], $existing_attr ) && $existing_attr[ 'pa_' . $from_attr_arr[0] ] == $from_attr_arr[1] ) {
							$to_attr_arr = explode( ':', $to_attr );
							if ( 'any' == $to_attr_arr[1] ) {
								$to_attr_arr[1] = '';
							}
							$change_attributes[ 'pa_' . $to_attr_arr[0] ] = $to_attr_arr[1];
						}
					}
					$result_attri_update = array();
					if ( ! empty( $change_attributes ) ) {
						$edit_data['vari_attribute']            = $existing_attr;
						$collect_product_data['vari_attribute'] = $existing_attr;
						$result_attri_update                    = array_merge( $existing_attr, $change_attributes );
						$temp->set_attributes( $result_attri_update );
						$temp->save();
					}
				}
			}


			if ( '' != $shipping_select ) {
				$collect_product_data['shipping'] = $product_data['shipping'];
				wp_set_object_terms( (int) $pid, (int) $shipping_select, 'product_shipping_class' );
			}
			switch ( $regular_select ) {
				case 'up_percentage':
					if ( '' !== $product_data['regular'] ) {
						$collect_product_data['regular'] = $product_data['regular'];
						$per_val                         = $product_data['regular'] * ( $regular_text / 100 );
						$cal_val                         = $product_data['regular'] + $per_val;
						if ( '' != $regular_round_select ) {
							if ( '' == $regular_round_text ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );

						$sal_val = get_post_meta( $pid, '_sale_price', true );
						if ( 'variable' != $temp_type && $sal_val < $regular_val ) {
							eh_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
				case 'down_percentage':
					if ( '' !== $product_data['regular'] ) {
						$collect_product_data['regular'] = $product_data['regular'];
						$per_val                         = $product_data['regular'] * ( $regular_text / 100 );
						$cal_val                         = $product_data['regular'] - $per_val;
						if ( '' != $regular_round_select ) {
							if ( '' == $regular_round_text ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );
						$sal_val     = get_post_meta( $pid, '_sale_price', true );
						if ( 'variable' != $temp_type && $sal_val < $regular_val ) {
							eh_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
				case 'up_price':
					if ( '' !== $product_data['regular'] ) {
						$collect_product_data['regular'] = $product_data['regular'];
						$cal_val                         = $product_data['regular'] + $regular_text;
						if ( '' != $regular_round_select ) {
							if ( '' == $regular_round_text ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );
						$sal_val     = get_post_meta( $pid, '_sale_price', true );
						if ( 'variable' != $temp_type && $sal_val < $regular_val ) {
							eh_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
				case 'down_price':
					if ( '' !== $product_data['regular'] ) {
						$collect_product_data['regular'] = $product_data['regular'];
						$cal_val                         = $product_data['regular'] - $regular_text;
						if ( '' != $regular_round_select ) {
							if ( '' == $regular_round_text ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );
						$sal_val     = get_post_meta( $pid, '_sale_price', true );
						if ( 'variable' != $temp_type && $sal_val < $regular_val ) {
							eh_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
				case 'flat_all':
					$collect_product_data['regular'] = $product_data['regular'];
					$regular_val                     = wc_format_decimal( $regular_text, '', true );
					$sal_val                         = get_post_meta( $pid, '_sale_price', true );
					if ( 'variable' != $temp_type && $sal_val < $regular_val ) {
						eh_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
					} else {
						array_push( $sale_warning, $pid, $parent_id );
						array_push( $sale_warning, 'Regular' );
						array_push( $sale_warning, $temp_type );
					}
					break;
			}
			switch ( $sale_select ) {
				case 'up_percentage':
					if ( '' !== $product_data['sale'] ) {
						$collect_product_data['sale'] = $product_data['sale'];
						$per_val                      = $product_data['sale'] * ( $sale_text / 100 );
						$cal_val                      = $product_data['sale'] + $per_val;
						if ( '' != $sale_round_select ) {
							if ( '' == $sale_round_text ) {
								$sale_round_text = 1;
							}
							$got_sale = $cal_val;
							switch ( $sale_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_sale, $sale_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_sale, -$sale_round_text );
									break;
							}
						}
						$sale_val = wc_format_decimal( $cal_val, '', true );
						// leave sale price blank if sale price increased by -100%.
						if ( 0 == $sale_val ) {
							$sale_val = '';
						}
						$reg_val = get_post_meta( $pid, '_regular_price', true );
						if ( 'variable' != $temp_type && $sale_val < $reg_val ) {
							eh_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
							if ( isset( $fields_and_values['regular_select'] ) ) {
								eh_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
							}
						}
					}
					break;
				case 'down_percentage':
					if ( '' !== $product_data['sale'] || $fields_and_values['regular_check_val'] ) {
						$collect_product_data['sale'] = $product_data['sale'];
						if ( $fields_and_values['regular_check_val'] ) {
							if ( '' == $product_data['regular'] ) {
								break;
							}
							$per_val = $product_data['regular'] * ( $sale_text / 100 );
							$cal_val = $product_data['regular'] - $per_val;
						} else {
							$per_val = $product_data['sale'] * ( $sale_text / 100 );
							$cal_val = $product_data['sale'] - $per_val;
						}
						if ( '' != $sale_round_select ) {
							if ( '' == $sale_round_text ) {
								$sale_round_text = 1;
							}
							$got_sale = $cal_val;
							switch ( $sale_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_sale, $sale_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_sale, -$sale_round_text );
									break;
							}
						}
						$sale_val = wc_format_decimal( $cal_val, '', true );
						// leave sale price blank if sale price decreased by 100%.
						if ( 0 == $sale_val  || $sale_val < 0 ) {
							$sale_val = '';
						}

						$reg_val = get_post_meta( $pid, '_regular_price', true );
						if ( 'variable' != $temp_type && $sale_val < $reg_val ) {
							eh_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
							if ( isset( $fields_and_values['regular_select'] ) ) {
								eh_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
							}
						}
					}
					break;
				case 'up_price':
					if ( '' !== $product_data['sale'] ) {
						$collect_product_data['sale'] = $product_data['sale'];
						$cal_val                      = $product_data['sale'] + $sale_text;
						if ( '' != $sale_round_select ) {
							if ( '' == $sale_round_text ) {
								$sale_round_text = 1;
							}
							$got_sale = $cal_val;
							switch ( $sale_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_sale, $sale_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_sale, -$sale_round_text );
									break;
							}
						}
						$sale_val = wc_format_decimal( $cal_val, '', true );
						if ( $sale_val < 0 || $sale_val == 0) {
							$sale_val = '';
						}
						$reg_val  = get_post_meta( $pid, '_regular_price', true );
						if ( 'variable' != $temp_type && $sale_val < $reg_val ) {
							eh_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
							if ( isset( $fields_and_values['regular_select'] ) ) {
								eh_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
							}
						}
					}
					break;
				case 'down_price':
					if ( '' !== $product_data['sale'] || $fields_and_values['regular_check_val'] ) {
						$collect_product_data['sale'] = $product_data['sale'];
						if ( $fields_and_values['regular_check_val'] ) {
							if ( '' == $product_data['regular'] ) {
								break;
							}
							$cal_val = $product_data['regular'] - $sale_text;
						} else {
							$cal_val = $product_data['sale'] - $sale_text;
						}

						if ( '' != $sale_round_select ) {
							if ( '' == $sale_round_text ) {
								$sale_round_text = 1;
							}
							$got_sale = $cal_val;
							switch ( $sale_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_sale, $sale_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_sale, -$sale_round_text );
									break;
							}
						}
						$sale_val = wc_format_decimal( $cal_val, '', true );
						$reg_val  = get_post_meta( $pid, '_regular_price', true );
						if ( 'variable' != $temp_type && $sale_val < $reg_val ) {
							eh_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
							if ( isset( $fields_and_values['regular_select'] ) ) {
								eh_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
							}
						}
					}
					break;
				case 'flat_all':
					$collect_product_data['sale'] = $product_data['sale'];
					$sale_val                     = wc_format_decimal( $sale_text, '', true );
					$reg_val                      = get_post_meta( $pid, '_regular_price', true );
					if ( 'variable' != $temp_type && $sale_val < $reg_val ) {
						eh_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
					} else {
						array_push( $sale_warning, $pid, $parent_id );
						array_push( $sale_warning, 'Sales' );
						array_push( $sale_warning, $temp_type );
						if ( isset( $fields_and_values['regular_select'] ) ) {
							eh_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
						}
					}
					break;
			}
			if ( get_post_meta( $pid, '_sale_price', true ) !== '' && get_post_meta( $pid, '_regular_price', true ) !== '' ) {
				eh_bep_update_meta_fn( $pid, '_price', get_post_meta( $pid, '_sale_price', true ) );
			} elseif ( get_post_meta( $pid, '_sale_price', true ) === '' && get_post_meta( $pid, '_regular_price', true ) !== '' ) {
				eh_bep_update_meta_fn( $pid, '_price', get_post_meta( $pid, '_regular_price', true ) );
			} elseif ( get_post_meta( $pid, '_sale_price', true ) !== '' && get_post_meta( $pid, '_regular_price', true ) === '' ) {
				eh_bep_update_meta_fn( $pid, '_price', get_post_meta( $pid, '_sale_price', true ) );
			} elseif ( get_post_meta( $pid, '_sale_price', true ) === '' && get_post_meta( $pid, '_regular_price', true ) === '' ) {
				eh_bep_update_meta_fn( $pid, '_price', '' );
			}
			switch ( $stock_manage_select ) {
				case 'yes':
					$collect_product_data['stock_manage'] = $product_data['stock_manage'];
					eh_bep_update_meta_fn( $pid, '_manage_stock', 'yes' );
					break;
				case 'no':
					$collect_product_data['stock_manage'] = $product_data['stock_manage'];
					eh_bep_update_meta_fn( $pid, '_manage_stock', 'no' );
					break;
			}
			switch ( $tax_status_action ) {
				case 'taxable':
					$collect_product_data['tax_status_action'] = $product_data['tax_status_action'];
					eh_bep_update_meta_fn( $pid, '_tax_status', $tax_status_action );
					break;
				case 'shipping':
					$collect_product_data['tax_status_action'] = $product_data['tax_status_action'];
					eh_bep_update_meta_fn( $pid, '_tax_status', $tax_status_action );
					break;
				case 'none':
					$collect_product_data['tax_status_action'] = $product_data['tax_status_action'];
					eh_bep_update_meta_fn( $pid, '_tax_status', $tax_status_action );
					break;
			}
			if ( 'default' == $fields_and_values['tax_class_action'] ) {
				$collect_product_data['tax_class_action'] = $product_data['tax_class_action'];
				eh_bep_update_meta_fn( $pid, '_tax_class', '' );
			} else {
				$collect_product_data['tax_class_action'] = $product_data['tax_class_action'];
				eh_bep_update_meta_fn( $pid, '_tax_class', $fields_and_values['tax_class_action'] );
			}
			switch ( $quantity_select ) {
				case 'add':
					$collect_product_data['stock_quantity'] = $product_data['stock_quantity'];
					$quantity_val                           = number_format( $product_data['stock_quantity'] + $quantity_text, 6, '.', '' );
					eh_bep_update_meta_fn( $pid, '_stock', $quantity_val );
					break;
				case 'sub':
					$collect_product_data['stock_quantity'] = $product_data['stock_quantity'];
					$quantity_val                           = number_format( $product_data['stock_quantity'] - $quantity_text, 6, '.', '' );
					eh_bep_update_meta_fn( $pid, '_stock', $quantity_val );
					break;
				case 'replace':
					$collect_product_data['stock_quantity'] = $product_data['stock_quantity'];
					$quantity_val                           = number_format( $quantity_text, 6, '.', '' );
					eh_bep_update_meta_fn( $pid, '_stock', $quantity_val );
					break;
			}
			switch ( $backorder_select ) {
				case 'no':
					$collect_product_data['backorder'] = $product_data['backorder'];
					eh_bep_update_meta_fn( $pid, '_backorders', 'no' );
					break;
				case 'notify':
					$collect_product_data['backorder'] = $product_data['backorder'];
					eh_bep_update_meta_fn( $pid, '_backorders', 'notify' );
					break;
				case 'yes':
					$collect_product_data['backorder'] = $product_data['backorder'];
					eh_bep_update_meta_fn( $pid, '_backorders', 'yes' );
					break;
			}
			switch ( $stock_status_select ) {
				case 'instock':
					$collect_product_data['stock_status'] = $product_data['stock_status'];
					eh_bep_update_meta_fn( $pid, '_stock_status', 'instock' );
					break;
				case 'outofstock':
					$collect_product_data['stock_status'] = $product_data['stock_status'];
					eh_bep_update_meta_fn( $pid, '_stock_status', 'outofstock' );
					break;
				case 'onbackorder':
					$collect_product_data['stock_status'] = $product_data['stock_status'];
					eh_bep_update_meta_fn( $pid, '_stock_status', 'onbackorder' );
					break;
			}
			switch ( $length_select ) {
				case 'add':
					$collect_product_data['length'] = $product_data['length'];
					$length_val                     = $product_data['length'] + $length_text;
					eh_bep_update_meta_fn( $pid, '_length', $length_val );
					break;
				case 'sub':
					$collect_product_data['length'] = $product_data['length'];
					$length_val                     = $product_data['length'] - $length_text;
					eh_bep_update_meta_fn( $pid, '_length', $length_val );
					break;
				case 'replace':
					$collect_product_data['length'] = $product_data['length'];
					$length_val                     = $length_text;
					eh_bep_update_meta_fn( $pid, '_length', $length_val );
					break;
			}
			switch ( $width_select ) {
				case 'add':
					$collect_product_data['width'] = $product_data['width'];
					$width_val                     = $product_data['width'] + $width_text;
					eh_bep_update_meta_fn( $pid, '_width', $width_val );
					break;
				case 'sub':
					$collect_product_data['width'] = $product_data['width'];
					$width_val                     = $product_data['width'] - $width_text;
					eh_bep_update_meta_fn( $pid, '_width', $width_val );
					break;
				case 'replace':
					$collect_product_data['width'] = $product_data['width'];
					$width_val                     = $width_text;
					eh_bep_update_meta_fn( $pid, '_width', $width_val );
					break;
			}
			switch ( $height_select ) {
				case 'add':
					$collect_product_data['height'] = $product_data['height'];
					$height_val                     = $product_data['height'] + $height_text;
					eh_bep_update_meta_fn( $pid, '_height', $height_val );
					break;
				case 'sub':
					$collect_product_data['height'] = $product_data['height'];
					$height_val                     = $product_data['height'] - $height_text;
					eh_bep_update_meta_fn( $pid, '_height', $height_val );
					break;
				case 'replace':
					$collect_product_data['height'] = $product_data['height'];
					$height_val                     = $height_text;
					eh_bep_update_meta_fn( $pid, '_height', $height_val );
					break;
			}
			switch ( $weight_select ) {
				case 'add':
					$collect_product_data['weight'] = $product_data['weight'];
					$weight_val                     = $product_data['weight'] + $weight_text;
					eh_bep_update_meta_fn( $pid, '_weight', $weight_val );
					break;
				case 'sub':
					$collect_product_data['weight'] = $product_data['weight'];
					$weight_val                     = $product_data['weight'] - $weight_text;
					eh_bep_update_meta_fn( $pid, '_weight', $weight_val );
					break;
				case 'replace':
					$collect_product_data['weight'] = $product_data['weight'];
					$weight_val                     = $weight_text;
					eh_bep_update_meta_fn( $pid, '_weight', $weight_val );
					break;
			}
			wc_delete_product_transients( $pid );
		}

		// Edit Attributes.
		if ( 'variation' != $temp_type && ! empty( $fields_and_values['attribute'] ) ) {
			$i                   = 0;
			$is_variation        = 0;
			$is_visible          = 0;
			$prev_value          = '';
			$_product_attributes = get_post_meta( $pid, '_product_attributes', true );
			$attr_undo           = $_product_attributes;
			foreach ( $attr_undo as $key => $val ) {
				$attr_undo[ $key ]['value'] = wc_get_product_terms( $pid, $key );
			}
			$collect_product_data['attributes'] = $attr_undo;
			if ( 'add' == $fields_and_values['attribute_variation'] ) {
				$is_variation = 1;
			}
			if ( 'remove' == $fields_and_values['attribute_variation'] ) {
				$is_variation = 0;
			}
			if ( 'add' == $fields_and_values['attr_visible_action'] ) {
				$is_visible = 1;
			}
			if ( 'remove' == $fields_and_values['attr_visible_action'] ) {
				$is_visible = 0;
			}

			if ( ! empty( $fields_and_values['attribute_value'] ) ) {
				foreach ( $fields_and_values['attribute_value'] as $key => $value ) {
					$value     = stripslashes( $value );
					$value     = preg_replace( '/\'/', '', $value );
					$att_slugs = explode( ':', $value );
					if ( '' == $fields_and_values['attribute_variation'] && isset( $_product_attributes[ $att_slugs[0] ] ) ) {
						$is_variation = $_product_attributes[ $att_slugs[0] ]['is_variation'];
					}
					if ( $prev_value != $att_slugs[0] ) {
						$i = 0;
					}
					if ( '' == $fields_and_values['attr_visible_action'] && isset( $_product_attributes[ $att_slugs[0] ] ) ) {
						$is_visible = $_product_attributes[ $att_slugs[0] ]['is_visible'];
					}
					if ( $prev_value != $att_slugs[0] ) {
						$i = 0;
					}	
					$prev_value = $att_slugs[0];
					if ( 'replace' == $fields_and_values['attribute_action'] && 0 == $i ) {
						wp_set_object_terms( $pid, $att_slugs[1], $att_slugs[0] );
						$i++;
					} else {
						wp_set_object_terms( $pid, $att_slugs[1], $att_slugs[0], true );
					}
					$thedata = array(
						$att_slugs[0] => array(
							'name'         => $att_slugs[0],
							'value'        => $att_slugs[1],
							'is_visible'   => $is_visible,
							'is_taxonomy'  => '1',
							'is_variation' => $is_variation,
						),
					);
					if ( 'add' == $fields_and_values['attribute_action'] || 'replace' == $fields_and_values['attribute_action'] ) {
						$_product_attr = get_post_meta( $pid, '_product_attributes', true );
						if ( ! empty( $_product_attr ) ) {
							update_post_meta( $pid, '_product_attributes', array_merge( $_product_attr, $thedata ) );
						} else {
							update_post_meta( $pid, '_product_attributes', $thedata );
						}
					}
					if ( 'remove' == $fields_and_values['attribute_action'] ) {
						wp_remove_object_terms( $pid, $att_slugs[1], $att_slugs[0] );
					}
				}
			}
			if ( ! empty( $fields_and_values['new_attribute_values'] ) || '' != $fields_and_values['new_attribute_values'] ) {
				$ar1 = explode( ',', $fields_and_values['attribute'] );
				foreach ( $ar1 as $key => $value ) {
					foreach ( $fields_and_values['new_attribute_values'] as $key_index => $value_slug ) {
						$att_s = 'pa_' . $value;
						if ( $prev_value != $att_s ) {
							$i = 0;
						}
						if ( '' == $fields_and_values['attribute_variation'] && isset( $_product_attributes[ $att_s ] ) ) {
							$is_variation = $_product_attributes[ $att_s ]['is_variation'];
						}
						$prev_value = $att_s;
						if ( 'replace' == $fields_and_values['attribute_action'] && 0 == $i ) {
							wp_set_object_terms( $pid, $value_slug, $att_s );
							$i++;
						} else {
							wp_set_object_terms( $pid, $value_slug, $att_s, true );
						}
						$thedata = array(
							$att_s => array(
								'name'         => $att_s,
								'value'        => $value_slug,
								'is_visible'   => '1',
								'is_taxonomy'  => '1',
								'is_variation' => $is_variation,
							),
						);
						if ( 'add' == $fields_and_values['attribute_action'] || 'replace' == $fields_and_values['attribute_action'] ) {
							$_product_attr = get_post_meta( $pid, '_product_attributes', true );
							if ( ! empty( $_product_attr ) ) {
								update_post_meta( $pid, '_product_attributes', array_merge( $_product_attr, $thedata ) );
							} else {
								update_post_meta( $pid, '_product_attributes', $thedata );
							}
						}
					}
				}
			}
		}
		
		// category feature.
		if ( 'cat_none' != $fields_and_values['category_update_option'] && isset( $fields_and_values['categories_to_update'] ) ) {
			$temparr      = array();
			$existing_cat = wp_get_object_terms( $pid, 'product_cat' );
			// undo data.
			$undo_cat_data = array();
			foreach ( $existing_cat as $key => $val ) {
				array_push( $undo_cat_data, $val->term_id );
			}
			$collect_product_data['categories']   = $undo_cat_data;
			$edit_data['categories']              = $undo_cat_data;
			$collect_product_data['category_opn'] = $fields_and_values['category_update_option'];
			$edit_data['category_opn']            = $fields_and_values['category_update_option'];

			
			if ( $fields_and_values['category_update_option']  == 'cat_add' ) {
				$temparr = array();
				foreach ( $existing_cat as $cat_key => $cat_val ) {
					array_push( $temparr, (int) $cat_val->term_id );
				}
				foreach ( $fields_and_values['categories_to_update'] as $key => $value ){
					if ( ! in_array(  (int) $value, $temparr, true )) {
						array_push( $temparr, (int) $value);
					}
				}
				wp_set_object_terms( $pid, $temparr, 'product_cat' );
			}
			elseif (  $fields_and_values['category_update_option']  == 'cat_replace' ) {
				$temparr = array();
				foreach ( $fields_and_values['categories_to_update'] as $key => $val ) {
					array_push( $temparr, (int) $val );
					}
					wp_set_object_terms( $pid, $temparr, 'product_cat' );
			}
			elseif ( $fields_and_values['category_update_option']  == 'cat_remove' ) {
				$temparr_remove = array();
				foreach ( $existing_cat as $cat_rem_key => $cat_rem_val ) {
					
					if ( ! in_array( (int) $cat_rem_val->term_id, $fields_and_values['categories_to_update'] ) ) {
						array_push( $temparr_remove, (int) $cat_rem_val->term_id );
					}
				}
				wp_set_object_terms( $pid, $temparr_remove, 'product_cat' );
			}
		}

		// update custom meta with help of code snippet.
		if ( isset( $fields_and_values['custom_meta'] ) && '' != $fields_and_values['custom_meta'] ) {
			$current_val                         = array();
			$current_val                         = eh_bep_update_custom_meta( $pid, $fields_and_values['custom_meta'] );
			$edit_data['custom_meta']            = $fields_and_values['custom_meta'];
			$collect_product_data['custom_meta'] = $current_val;
		}
		$edit_data['delete_product']            = '';
		$collect_product_data['delete_product'] = '';
		if ( isset( $fields_and_values['delete_product_action'] ) && '' != $fields_and_values['delete_product_action'] ) {
			if ( 'move_to_trash' == $fields_and_values['delete_product_action'] ) {
				$edit_data['delete_product']            = $pid;
				$collect_product_data['delete_product'] = $pid;
				$temp->delete( false );
			} else {
				$temp->delete( true );
				$undo_update = 'no';
				delete_option( 'eh_bulk_edit_undo_edit_data' );
			}
		}
		$undo_product_data[ $pid ] = $collect_product_data;
	}
	if ( 0 == $fields_and_values['index_val'] ) {
		update_option( 'eh_temp_product_id', $undo_product_data );
	} else {
		$update_pid = array();
		$update_pid = get_option( 'eh_temp_product_id' );
		$update_pid = array_merge( $update_pid, $undo_product_data );
		update_option( 'eh_temp_product_id', $update_pid );
	}
	$prod_id = get_option( 'eh_temp_product_id' );
	if ( 'true' == $fields_and_values['create_log_file'] ) {
		$upload_dir = wp_upload_dir();
		$base       = $upload_dir['basedir'];
		$log_path   = $base . '/elex-bulk-edit-products/';
		if ( ! file_exists( $log_path ) ) {
			wp_mkdir_p( $log_path );
		}
		$file_name = $job_name;
		if ( '' != $sch_jobs ) {
			$file_name = $sch_jobs['job_name'];
		}
		$file_name = str_replace( ' ', '_', $file_name );
		$file      = fopen( $log_path . '/' . $file_name . '.txt', 'w' );
		fwrite( $file, print_r( $prod_id, true ) );
		fclose( $file );
	}
	if ( 'yes' === $undo_update ) {
		update_option( 'eh_bulk_edit_undo_product_id', $prod_id );
		update_option( 'eh_bulk_edit_undo_variation_id', $undo_variation_data );
		update_option( 'eh_bulk_edit_undo_edit_data', $edit_data );
		if ( '' != $sch_jobs ) {
			$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
			foreach ( $scheduled_jobs as $key => $val ) {
				if ( $sch_jobs['job_name'] == $val['job_name'] ) {
					$scheduled_jobs[ $key ]['revert_data'] = $prod_id;
					$scheduled_jobs[ $key ]['edit_data']   = $edit_data;
					update_option( 'elex_bep_scheduled_jobs', $scheduled_jobs );
					break;
				}
			}
		}
	}
	if ( $fields_and_values['index_val'] == $fields_and_values['chunk_length'] - 1 ) {
		if ( '' == $sch_jobs && 'bulk_update_now' == sanitize_text_field( $_POST['scheduled_action'] ) && 'true' == sanitize_text_field( $_POST['save_job'] ) ) {
			$param                  = array();
			$param['param_to_save'] = $fields_and_values;
			if ( sanitize_text_field( $_POST['index_val'] ) != 0 ) {
				$prev_ids                      = get_option( 'elex_bep_product_ids_to_schedule' );
				$current_ids                   = array_map( 'sanitize_text_field', wp_unslash( $_POST['pid'] ) );
				$res_id                        = array_merge( $prev_ids, $current_ids );
				$param['param_to_save']['pid'] = $res_id;
				delete_option( 'elex_bep_product_ids_to_schedule' );
			}
			$param['job_name']        = $job_name;
			$param['create_log_file'] = sanitize_text_field( $_POST['create_log_file'] );
			if ( 'yes' === $undo_update ) {
				$param['revert_data'] = $prod_id;
				$param['edit_data']   = $edit_data;
			}
			$saved_jobs = get_option( 'elex_bep_scheduled_jobs' );
			if ( '' != $saved_jobs ) {
				if ( ( isset( $_POST['is_edit_job'] ) && sanitize_text_field( $_POST['is_edit_job'] ) == 'true' ) ) {
					foreach ( $saved_jobs as $index => $jobs ) {
						if ( $jobs['job_name'] == $job_name ) {
							$saved_jobs[ $index ] = $param;
							break;
						}
					}
					update_option( 'elex_bep_scheduled_jobs', $saved_jobs );
				} else {
					array_push( $saved_jobs, $param );
					update_option( 'elex_bep_scheduled_jobs', $saved_jobs );
				}
			} else {
				$te_arr = array();
				array_push( $te_arr, $param );
				update_option( 'elex_bep_scheduled_jobs', $te_arr );
			}
		}
		if ( '' == $sch_jobs ) {
			array_push( $sale_warning, 'done' );
			die( wp_json_encode( $sale_warning ) );
		}
	} else {
		if ( '' == $sch_jobs && 'bulk_update_now' == sanitize_text_field( $_POST['scheduled_action'] ) && 'true' == sanitize_text_field( $_POST['save_job'] ) ) {
			$saved_pids_ = get_option( 'elex_bep_product_ids_to_schedule' );
			if ( '' == $saved_pids_ ) {
				update_option( 'elex_bep_product_ids_to_schedule', array_map( 'sanitize_text_field', wp_unslash( $_POST['pid'] ) ) );
			} else {
				$result_ids = array_merge( $saved_pids_, $fields_and_values['pid'] );
				update_option( 'elex_bep_product_ids_to_schedule', $result_ids );
			}
		}
	}
	if ( '' != $sch_jobs ) {
		return array(
			'edit_data'     => $edit_data,
			'undo_products' => $prod_id,
		);
	}
	if ( '' == $sch_jobs ) {
		die( wp_json_encode( $sale_warning ) );
	}



}

function eh_bep_update_meta_fn( $id, $key, $value ) {
	update_post_meta( $id, $key, $value );
}

function eh_bep_list_table_all_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$obj = new Eh_DataTables();
	$obj->input();
	$obj->ajax_response( '1' );
}

function eh_clear_all_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	update_option( 'eh_bulk_edit_choosed_product_id', eh_bep_get_first_products() );
	$obj = new Eh_DataTables();
	$obj->input();
	$obj->ajax_response();
}

function eh_bep_search_filter_callback() {
	set_time_limit( 300 );
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$obj_fil = new Eh_DataTables();
	$obj_fil->input();
	$obj_fil->ajax_response( '1' );
}

function eh_bep_undo_html_maker() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	if ( isset( $_POST['file'] ) ) {
		$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
		foreach ( $scheduled_jobs as $key => $val ) {
			if ( sanitize_text_field( $_POST['file'] ) == $val['job_name'] ) {
				$undo_data = $val['edit_data'];
				break;
			}
		}
	} else {
		$undo_data = get_option( 'eh_bulk_edit_undo_edit_data', array() );
	}
	ob_start();
	if ( ! empty( $undo_data ) ) {
		?>
		<div class='wrap postbox table-box table-box-main' id="undo_update" style='padding:0px 20px;'>
			<h2>
				<?php esc_html_e( 'Undo the Update - Overview', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id='update_general_table'>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['title_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='title'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Title', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition to edit the title, and enter the relevant text', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['title_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'set_new':
								?>
								<span><?php esc_html_e( 'Set New [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'append':
								?>
								<span><?php esc_html_e( 'Append [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'prepand':
								?>
								<span><?php esc_html_e( 'Prepend [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replace [ ', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;
							case 'regex_replace':
								?>
								<span><?php esc_html_e( 'RegEx Replace [ ', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;
							default:
								break;
						}
						?>
						<span id='title_text'>
							<?php
							switch ( $undo_data['title_select'] ) {
								case '':
									break;
								case 'replace':
									?>
									<span style="background: whitesmoke">Text to be replaced : <b><?php $undo_data['replace_title_text']; ?></b> -> Replace Text : <b><?php $undo_data['title_text']; ?></b></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'regex_replace':
									?>
									<span style="background: whitesmoke">Pattern : <b><?php $undo_data['regex_replace_title_text']; ?></b> -> Replacement : <b><?php $undo_data['title_text']; ?></b></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								default:
									?>
									<span style="background: whitesmoke"><b><?php $undo_data['title_text']; ?></b></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['sku_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='sku'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'SKU', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition to edit the SKU, and enter the relevant text', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['sku_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'set_new':
								?>
								<span><?php esc_html_e( 'Set New [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'append':
								?>
								<span><?php esc_html_e( 'Append [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'prepend':
								?>
								<span><?php esc_html_e( 'Prepend [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replace [ ', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;
							case 'regex_replace':
								?>
								<span><?php esc_html_e( 'RegEx_Replace [ ', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;
							default:
								break;
						}
						?>
						<span id='sku_text'>
							<?php
							switch ( $undo_data['sku_select'] ) {
								case '':
									break;
								case 'replace':
									?>
									<span style="background: whitesmoke">Text to be replaced : <b><?php $undo_data['sku_replace_text']; ?></b> -> Replace Text : <b><?php $undo_data['sku_text']; ?></b></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'regex_replace':
									?>
									<span style="background: whitesmoke">Pattern : <b><?php $undo_data['regex_sku_replace_text']; ?></b> -> Replacement : <b><?php $undo_data['sku_text']; ?></b></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								default:
									?>
									<span style="background: whitesmoke"><b><?php $undo_data['sku_text']; ?></b></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['catalog_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='catalog'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Product Visiblity', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose which all shop pages the product will be listed on', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['catalog_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'visible':
								?>
								<span><?php esc_html_e( 'Shop and Search', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'catalog':
								?>
								<span><?php esc_html_e( 'Shop', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'search':
								?>
								<span><?php esc_html_e( 'Search', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'hidden':
								?>
								<span><?php esc_html_e( 'Hidden', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;
							default:
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['featured'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='featured'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Featured Product', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select an option to make the product(s) Featured or not.', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['featured'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'yes':
								?>
								<span><?php esc_html_e( 'Yes', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'no':
								?>
								<span><?php esc_html_e( 'No', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['shipping_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='shipping'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Shipping Class', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a shipping class that will be added to all the filtered products', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['shipping_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case '-1':
								?>
								<span><?php esc_html_e( 'Shipping Class : No Shipping Class', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								?>
								<span><?php esc_html_e( 'Shipping Class : ', 'eh_bulk_edit' ) . get_term( $undo_data['shipping_select'] )->name; ?></span>
								<?php
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['description'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='description'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Description', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='
						<?php esc_html_e( 'Select a condition to edit or add the description, and enter the relevant text.', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['description'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								?>
								<span><?php esc_html_e( 'Description updated', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['short_description'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='short_description'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Short description', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Short description', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['short_description'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								?>
								<span><?php esc_html_e( 'Short description updated', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['main_image'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='main_image'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Product image', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Specify an image url to add or replace the product image.', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['main_image'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								?>
								<span><?php esc_html_e( 'Updated product image', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['gallery_images'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='gallery_images'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Product gallery images Actio ', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition to modify product gallery images.', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['gallery_images'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'add':
								?>
								<span><?php esc_html_e( 'Added', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'remove':
								?>
								<span><?php esc_html_e( 'Removed', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replaced', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
						}
						?>
					</td>
				</tr>
			</table>
			<h2>
				<?php esc_html_e( 'Price', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id="update_price_table">
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['regular_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='regular'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Regular Price', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition to adjust the price and enter the value. You can also choose an option to round it to the nearest value', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['regular_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'up_percentage':
								?>
								<span><?php esc_html_e( 'Increased by Percentage ( + %) [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'down_percentage':
								?>
								<span><?php esc_html_e( 'Decreased by Percentage ( - %) [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'up_price':
								?>
								<span><?php esc_html_e( 'Increased by Price ( + $) [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'down_price':
								?>
								<span><?php esc_html_e( 'Decreased by Price ( - $) [ ', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;
							case 'flat_all':
								?>
								<span><?php esc_html_e( 'Flat Price for all [ ', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;

							default:
								break;
						}
						?>
						<span id='regular_price_text'>
							<?php
							switch ( $undo_data['regular_select'] ) {
								case '':
									break;
								case 'up_percentage':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Percentage : ', 'eh_bulk_edit' ) . $undo_data['regular_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'down_percentage':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Percentage : ', 'eh_bulk_edit' ) . $undo_data['regular_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'up_price':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Amount : ', 'eh_bulk_edit' ) . $undo_data['regular_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'down_price':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Amount : ', 'eh_bulk_edit' ) . $undo_data['regular_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'flat_all':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Amount : ', 'eh_bulk_edit' ) . $undo_data['regular_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								default:
									break;
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['sale_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='sale'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Sale Price', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition to adjust the price and enter the value. You can also choose an option to round it to the nearest value', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['sale_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'up_percentage':
								?>
								<span><?php esc_html_e( 'Increased by Percentage ( + %) [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'down_percentage':
								?>
								<span><?php esc_html_e( 'Decreased by Percentage ( - %) [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'up_price':
								?>
								<span><?php esc_html_e( 'Increased by Price ( + $) [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'down_price':
								?>
								<span><?php esc_html_e( 'Decreased by Price ( - $) [ ', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;
							case 'flat_all':
								?>
								<span><?php esc_html_e( 'Flat Price for all [ ', 'eh_bulk_edit' ); ?></Span>
								<?php
								break;

							default:
								break;
						}
						?>
						<span id='sale_price_text'>
							<?php
							switch ( $undo_data['sale_select'] ) {
								case '':
									break;
								case 'up_percentage':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Percentage : ', 'eh_bulk_edit' ) . $undo_data['sale_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'down_percentage':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Percentage : ', 'eh_bulk_edit' ) . $undo_data['sale_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'up_price':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Amount : ', 'eh_bulk_edit' ) . $undo_data['sale_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'down_price':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Amount : ', 'eh_bulk_edit' ) . $undo_data['sale_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								case 'flat_all':
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Amount : ', 'eh_bulk_edit' ) . $undo_data['sale_text'] . ' %'; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
								default:
									break;
							}
							?>
						</span>
					</td>
				</tr>

			</table>
			<h2>
				<?php esc_html_e( 'Stock', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id='update_stock_table'>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['stock_manage_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='manage_stock'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Manage Stock', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Enable or Disable manage stock for products or variations', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['stock_manage_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'yes':
								?>
								<span><?php esc_html_e( 'Enabled', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'no':
								?>
								<span><?php esc_html_e( 'Disabled', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['quantity_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='quantity'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Stock Quantity', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose an option to update stock quantity and enter the value', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['quantity_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'add':
								?>
								<span><?php esc_html_e( 'Increased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'sub':
								?>
								<span><?php esc_html_e( 'Decreased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replaced [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
						<span id='stock_quantity_text'>
							<?php
							switch ( $undo_data['quantity_select'] ) {
								case '':
									break;
								default:
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Quantity : ', 'eh_bulk_edit' ) . $undo_data['quantity_text']; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['backorder_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='backorders'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Allow Backorders', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose how you want to handle backorders', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['backorder_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'no':
								?>
								<span><?php esc_html_e( 'Do not Allow', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'notify':
								?>
								<span><?php esc_html_e( 'Allow, but Notify the Customer', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'yes':
								?>
								<span><?php esc_html_e( 'Allowed', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['stock_status_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='stock_status'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Stock Status', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose an option to update  the stock status', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['stock_status_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'instock':
								?>
								<span><?php esc_html_e( 'In Stock', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'outofstock':
								?>
								<span><?php esc_html_e( 'Out of Stock', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'onbackorder':
								?>
								<span><?php esc_html_e( 'On Backorder', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
					</td>
				</tr>
			</table>
			<h2>
				<?php esc_html_e( 'Weight & Dimensions', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id='update_properties_table'>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['length_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='length'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Length', 'eh_bulk_edit' ); ?>
						<span style="float:right;"><?php echo filter_var( strtolower( get_option( 'woocommerce_dimension_unit' ) ) ); ?></span>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose an option to update length and enter the value', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['length_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'add':
								?>
								<span><?php esc_html_e( 'Increased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'sub':
								?>
								<span><?php esc_html_e( 'Decreased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replaced [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
						<span id='length_text'>
							<?php
							switch ( $undo_data['length_select'] ) {
								case '':
									break;
								default:
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Dimension : ', 'eh_bulk_edit' ) . $undo_data['length_text']; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['width_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='width'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Width', 'eh_bulk_edit' ); ?>
						<span style="float:right;"><?php echo filter_var( strtolower( get_option( 'woocommerce_dimension_unit' ) ) ); ?></span>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose an option to update width and enter the value', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['width_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'add':
								?>
								<span><?php esc_html_e( 'Increased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'sub':
								?>
								<span><?php esc_html_e( 'Decreased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replaced [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
						<span id='width_text'>
							<?php
							switch ( $undo_data['width_select'] ) {
								case '':
									break;
								default:
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Dimension : ', 'eh_bulk_edit' ) . $undo_data['width_text']; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['height_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='height'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Height', 'eh_bulk_edit' ); ?>
						<span style="float:right;"><?php echo filter_var( strtolower( get_option( 'woocommerce_dimension_unit' ) ) ); ?></span>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose an option to update height and enter the value', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['height_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'add':
								?>
								<span><?php esc_html_e( 'Increased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'sub':
								?>
								<span><?php esc_html_e( 'Decreased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replaced [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
						<span id='height_text'>
							<?php
							switch ( $undo_data['height_select'] ) {
								case '':
									break;
								default:
									?>
									<span style="background: whitesmoke"><span><?php esc_html_e( 'Dimension : ', 'eh_bulk_edit' ) . $undo_data['height_text']; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
							}
							?>
							</span>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['weight_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='weight'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Weight', 'eh_bulk_edit' ); ?>
						<span style="float:right;"><?php echo filter_var( strtolower( get_option( 'woocommerce_weight_unit' ) ) ); ?></span>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Choose an option to update weight and enter the value', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['weight_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'add':
								?>
								<span><?php esc_html_e( 'Increased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'sub':
								?>
								<span><?php esc_html_e( 'Decreased [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replaced [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
						<span id='weight_text'>
							<?php
							switch ( $undo_data['weight_select'] ) {
								case '':
									break;
								default:
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Dimension : ', 'eh_bulk_edit' ) . $undo_data['weight_text']; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
							}
							?>
						</span>
					</td>
				</tr>
			</table>


		</table>
		<h2>
			<?php esc_html_e( 'Attributes', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='update_properties_table'>
			<tr>
				<td class='eh-edit-tab-table-undo-check'>
					<?php
					switch ( $undo_data['attribute_action'] ) {
						case '':
							break;
						default:
							?>
							<input type="checkbox" name='undo_checkbox_values' checked value='attributes'>
							<?php
							break;
					}
					?>
				</td>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Attribute Actions', 'eh_bulk_edit' ); ?>

				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select an option to make changes to your attribute values', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<?php
					switch ( $undo_data['attribute_action'] ) {
						case '':
							?>
							<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'add':
							?>
							<span><?php esc_html_e( 'Added ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'remove':
							?>
							<span><?php esc_html_e( 'Removed ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'replace':
							?>
							<span><?php esc_html_e( 'Replaced ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						default:
							break;
					}
					?>

				</td>
			</tr>
			<tr>
		</table>

		<h2>
			<?php esc_html_e( 'Tax', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>


		<table class='eh-edit-table' id='update_properties_table'>
			<tr>
				<td class='eh-edit-tab-table-undo-check'>
					<?php
					switch ( $undo_data['tax_status_action'] ) {
						case '':
							break;
						default:
							?>
							<input type="checkbox" name='undo_checkbox_values' checked value='tax_status_action'>
							<?php
							break;
					}
					?>
				</td>


				<td class='eh-edit-tab-table-left'>

				<?php esc_html_e( 'Tax Status', 'eh_bulk_edit' ); ?>

				</td>

				<td class='eh-edit-tab-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select an option to make changes to your Tax Status', 'eh_bulk_edit' ); ?>'></span>
				</td>

				<td class='eh-edit-tab-table-input-td' >
					<?php
					switch ( $undo_data['tax_status_action'] ) {
						case '':
							?>
							<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'taxable':
							?>
							<span><?php esc_html_e( 'Taxable ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'shipping':
							?>
							<span><?php esc_html_e( 'Shipping ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'none':
							?>
							<span><?php esc_html_e( 'None ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						default:
							break;
					}
					?>
				</td>
			</tr>

			<tr>
				<td class='eh-edit-tab-table-undo-check'>
					<?php
					switch ( $undo_data['tax_class_action'] ) {
						case '':
							break;
						default:
							?>
							<input type="checkbox" name='undo_checkbox_values' checked value='tax_class_action'>
							<?php
							break;
					}
					?>
				</td>

				<td class='eh-edit-tab-table-left'>

				<?php esc_html_e( 'Tax Class', 'eh_bulk_edit' ); ?>

				</td>

				<td class='eh-edit-tab-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select an option to make changes to your Tax Class', 'eh_bulk_edit' ); ?>'></span>
				</td>

				<td class='eh-edit-tab-table-input-td' >
					<?php
					switch ( $undo_data['tax_class_action'] ) {
						case '':
							?>
							<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'default':
							?>
							<span><?php esc_html_e( 'Standard ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						default:
							?>
							<span><?php echo( filter_var( $undo_data['tax_class_action'] ) ); ?></span>
							<?php
							break;
					}
					?>
				</td>
			</tr>
	</table>

		<h2>
			<?php esc_html_e( 'Variations', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>


		<table class='eh-edit-table'>
			<tr>
				<td class='eh-edit-tab-table-undo-check'>
					<?php
					switch ( $undo_data['vari_attribute'] ) {
						case '':
							break;
						default:
							?>
							<input type="checkbox" name='undo_checkbox_values' checked value='vari_attribute'>
							<?php
							break;
					}
					?>
				</td>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Interchange Attribute Values', 'eh_bulk_edit' ); ?>

				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select the attribute and specify the attribute values you want to change.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<?php
					switch ( $undo_data['vari_attribute'] ) {
						case '':
							?>
							<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						default:
							?>
							<span><?php esc_html_e( 'Changed', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
					}
					?>
				</td>
			</tr>
			<tr>
		</table>
		</table>
		<h2>
			<?php esc_html_e( 'Categories', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='update_properties_table'>
			<tr>
				<td class='eh-edit-tab-table-undo-check'>
					<?php
					switch ( $undo_data['categories'] ) {
						case '':
							break;
						default:
							?>
							<input type="checkbox" name='undo_checkbox_values' checked value='categories'>
							<?php
							break;
					}
					?>
				</td>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Categories', 'eh_bulk_edit' ); ?>

				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Category process', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<?php
					switch ( $undo_data['category_opn'] ) {
						case 'cat_none':
							?>
							<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'cat_add':
							?>
							<span><?php esc_html_e( 'Added ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'cat_remove':
							?>
							<span><?php esc_html_e( 'Removed ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						case 'cat_replace':
							?>
							<span><?php esc_html_e( 'Replaced ', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						default:
							break;
					}
					?>

				</td>
			</tr>
			<tr>
		</table>

		<?php
		if ( in_array( 'pricing-discounts-by-user-role-woocommerce/pricing-discounts-by-user-role-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			?>
			<h2>
				<?php esc_html_e( 'Role Based Pricing', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id='update_general_table'>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['hide_price'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='hide_price'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Hide price', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select option to hide price for unregistered users.', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['hide_price'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'no':
								?>
								<span><?php esc_html_e( 'Show Price', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'yes':
								?>
								<span><?php esc_html_e( 'Hide Price', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						$selected_roles = $undo_data['hide_price_role'];
						switch ( $selected_roles ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='hide_price_role'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Hide product price based on user role', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'For selected user role, hide the product price', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<span class='select-eh'>
							<?php
							global $wp_roles;
							$roles = $wp_roles->role_names;
							$r     = 0;
							foreach ( $roles as $key => $value ) {
								if ( in_array( $key, $selected_roles, true ) ) {
									echo filter_var( $value );
									$r++;
								}
								if ( $r > 0 ) {
									echo ',';
								}
							}
							?>
						</span>
					</td>
				</tr>
				<?php
				$enabled_roles = get_option( 'eh_pricing_discount_product_price_user_role' );
				if ( is_array( $enabled_roles ) ) {
					if ( ! in_array( 'none', $enabled_roles, true ) ) {
						?>
						<tr>
							<td class='eh-edit-tab-table-undo-check'>
								<?php
								switch ( $undo_data['price_adjustment'] ) {
									case '':
										break;
									default:
										?>
										<input type="checkbox" name='undo_checkbox_values' checked value='price_adjustment'>
										<?php
										break;
								}
								?>
							</td>
							<td class='eh-edit-tab-table-left'>
								<?php esc_html_e( 'Enforce product price adjustment', 'eh_bulk_edit' ); ?>
							</td>
							<td class='eh-edit-tab-table-middle'>
								<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select option to enforce indvidual price adjustment', 'eh_bulk_edit' ); ?>'></span>
							</td>
							<td class='eh-edit-tab-table-input-td'>
								<?php
								switch ( $undo_data['price_adjustment'] ) {
									case '':
										?>
										<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
										<?php
										break;
									case 'no':
										?>
										<span><?php esc_html_e( 'Disabled', 'eh_bulk_edit' ); ?></span>
										<?php
										break;
									case 'yes':
										?>
										<span><?php esc_html_e( 'Enabled', 'eh_bulk_edit' ); ?></span>
										<?php
										break;
									default:
										break;
								}
								?>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</table>
			<?php
		}
		if ( in_array( 'per-product-addon-for-woocommerce-shipping-pro/woocommerce-per-product-shipping-addon-for-shipping-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			?>
			<h2>
				<?php esc_html_e( 'Shipping Pro', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id='update_general_table'>
				<tr>
					<td class='eh-edit-tab-table-undo-check'>
						<?php
						switch ( $undo_data['shipping_unit_select'] ) {
							case '':
								break;
							default:
								?>
								<input type="checkbox" name='undo_checkbox_values' checked value='wf_shipping_unit'>
								<?php
								break;
						}
						?>
					</td>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Shipping Unit', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Update Shipping Unit', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<?php
						switch ( $undo_data['shipping_unit_select'] ) {
							case '':
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'add':
								?>
								<span><?php esc_html_e( 'Added [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'sub':
								?>
								<span><?php esc_html_e( 'Subtracted [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							case 'replace':
								?>
								<span><?php esc_html_e( 'Replaced [ ', 'eh_bulk_edit' ); ?></span>
								<?php
								break;
							default:
								break;
						}
						?>
						<span id='weight_text'>
							<?php
							switch ( $undo_data['shipping_unit_select'] ) {
								case '':
									break;
								default:
									?>
									<span style="background: whitesmoke"><?php esc_html_e( 'Unit : ', 'eh_bulk_edit' ) . $undo_data['shipping_unit']; ?></span>
									<?php
									esc_html_e( ' ] ', 'eh_bulk_edit' );
									break;
							}
							?>
						</span>
					</td>
				</tr>
			</table>
			<?php
		}
		$keys = array();
		if ( isset( $_POST['file'] ) ) {
			$scheduled_jobs = get_option( 'elex_bep_scheduled_jobs' );
			foreach ( $scheduled_jobs as $key => $val ) {
				if ( sanitize_text_field( $_POST['file'] ) == $val['job_name'] ) {
					if ( isset( $val['param_to_save']['meta_fields'] ) ) {
						$keys = $val['param_to_save']['meta_fields'];
					}
					break;
				}
			}
		} else {
			$keys = get_option( 'eh_bulk_edit_meta_values_to_update' );
		}
		if ( ! empty( $keys ) ) {
			?>
			<h2>
				<?php esc_html_e( 'Update meta values', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id='update_meta_table'>
				<?php
				$key_size = count( $keys );
				for ( $i = 0; $i < $key_size; $i++ ) {
					?>
					<tr>
						<td class='eh-edit-tab-table-undo-check'>
							<?php
							if ( '' != $undo_data['custom_meta'][ $i ] ) {
								?>

								<input type="checkbox" name='undo_checkbox_values' checked value=<?php echo filter_var( $keys[ $i ] ); ?>>
								<?php
							}
							?>
						</td>
						<td class='eh-edit-tab-table-left'>
							<?php echo filter_var( $keys[ $i ] ); ?>
						</td>
						<td class='eh-edit-tab-table-middle'>
							<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Update meta', 'eh_bulk_edit' ); ?>'></span>
						</td>
						<td class='eh-edit-tab-table-input-td'>
							<?php
							if ( isset( $undo_data['custom_meta'] ) && '' != $undo_data['custom_meta'][ $i ] ) {
								?>
								<span><?php echo filter_var( $undo_data['custom_meta'][ $i ] ); ?></span>
								<?php
							} else {
								?>
								<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
								<?php
							}
							?>
						</td>
					</tr>
					<?php
				}
		}
		?>
		</table>
			<h2>
			<?php esc_html_e( 'Delete Products', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>


		<table class='eh-edit-table'>
			<tr>
				<td class='eh-edit-tab-table-undo-check'>
					<?php
					switch ( $undo_data['delete_product'] ) {
						case '':
							break;
						default:
							?>
							<input type="checkbox" name='undo_checkbox_values' checked value='delete_product'>
							<?php
							break;
					}
					?>
				</td>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Delete Action', 'eh_bulk_edit' ); ?>

				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select how you want to delete products.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<?php
					switch ( $undo_data['delete_product'] ) {
						case '':
							?>
							<span><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
						default:
							?>
							<span><?php esc_html_e( 'Moved to Trash', 'eh_bulk_edit' ); ?></span>
							<?php
							break;
					}
					?>

				</td>
			</tr>
			<tr>
		</table>

		<button id='undo_cancel_button' style="margin-bottom: 1%; background-color: gray; color: white; width: 10%;" class='button button-large'><span class="update-text"><?php esc_html_e( 'Cancel', 'eh_bulk_edit' ); ?></span></button>
		<button id='undo_update_button' style="margin-bottom: 1%; float: right; color: white; width: 10%;" class='button button-primary button-large'><span class="update-text"><?php esc_html_e( 'Continue', 'eh_bulk_edit' ); ?></span></button>
		</div>
		<?php
	} else {
		?>
		<div class='wrap postbox table-box table-box-main' id="undo_update" style='padding:0px 20px;'>
			<h2>
				<?php esc_html_e( 'Undo the update - Overview', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<div class='eh-edit-table'>
				<?php esc_html_e( 'Oops! No previous update found.', 'eh_bulk_edit' ); ?>
			</div>
			<button id='undo_cancel_button' style="margin-bottom: 1%;  background-color: gray; color: white; width: 10%;" class='button button-large'><span class="update-text"><?php esc_html_e( 'Back', 'eh_bulk_edit' ); ?></span></button>
		</div>
		<?php
	}
	$value = ob_get_clean();
	die( filter_var( $value ) );
}


function xa_bep_get_selected_products( $table_obj = null ) {
	$sel_ids = array();
	if ( isset( $_REQUEST['count_products'] ) ) {
		$sel_ids = get_option( 'xa_bulk_selected_ids' );
		// Get unchecked ids.
		$uc_ids = ! empty( get_option( 'elex_bep_filter_checkbox_data' ) ) ? get_option( 'elex_bep_filter_checkbox_data' ):array();
		// Get the difference and reindex.
		$final_ids = array_values(array_diff($sel_ids, $uc_ids));
		return $final_ids;
	}
	delete_option( 'xa_bulk_selected_ids' );
	$page_no           = ! empty( $_REQUEST['paged'] ) ? sanitize_text_field( $_REQUEST['paged'] ) : 1;
	$selected_products = array();
	$per_page          = ( get_option( 'eh_bulk_edit_table_row' ) ) ? get_option( 'eh_bulk_edit_table_row' ) : 20;
	$pid_to_include    = xa_bep_filter_products();

	update_option( 'xa_bulk_selected_ids', $pid_to_include );
	$sel_chunk = array_chunk( $pid_to_include, $per_page, true );
	if ( ! empty( $sel_chunk ) ) {
		$ids_per_page = $sel_chunk[ $page_no - 1 ];
		foreach ( $ids_per_page as $ids ) {
			$selected_products[ $ids ] = wc_get_product( $ids );
		}
	}

	$total_pages = count( $sel_chunk );
	if ( isset( $_REQUEST['page'] ) && ! empty( $table_obj ) && ( 1 == $total_pages ) ) {
		$total_pages++;
	}
	$ele_on_page = count( $pid_to_include );
	if ( ! empty( $table_obj ) ) {
		$table_obj->set_pagination_args(
			array(
				'total_items' => $ele_on_page,
				'per_page'    => $ele_on_page,
				'total_pages' => $total_pages,
			)
		);
	}

	if ( ! empty( $selected_products ) ) {
		return $selected_products;
	}
}

function elex_get_categories( $categories, $subcat ) {
	$filter_categories   = array();
	$selected_categories = $categories;
	$t_arr               = array();
	if ( $subcat ) {
		while ( ! empty( $selected_categories ) ) {
			$slug_name = $selected_categories[0];
			$slug_name = trim( $slug_name, "\'" );
			array_push( $filter_categories, $slug_name );
			unset( $selected_categories[0] );
			$t_arr               = xa_subcats_from_parentcat_by_slug( $slug_name );
			$selected_categories = array_merge( $selected_categories, $t_arr );
		}
	} else {
		foreach ( $categories as $category ) {
			array_push( $filter_categories, $category );
		}
	}
	return $filter_categories;
}

function xa_bep_filter_products( $data = '' ) {
	global $wpdb;
	$prefix = $wpdb->prefix;
	if ( empty( $data ) ) {
		$data_to_filter         = array();
		$data_to_filter['type'] = '';
		if ( isset( $_REQUEST['type'] ) && is_array( $_REQUEST['type'] ) ) {
			$data_to_filter['type'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['type'] ) );
		}

		if ( isset( $_REQUEST['stock_status'] ) && is_array( $_REQUEST['stock_status'] ) ){
			$data_to_filter['stock_status'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['stock_status'] ) );
		}
		$data_to_filter['custom_attribute'] = '';
		if ( isset( $_REQUEST['custom_attribute'] ) && is_array( $_REQUEST['custom_attribute'] ) ) {
			$data_to_filter['custom_attribute'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['custom_attribute'] ) );
		}
		$data_to_filter['category_filter'] = '';
		if ( isset( $_REQUEST['category_filter'] ) && is_array( $_REQUEST['category_filter'] ) ) {
			$data_to_filter['category_filter'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['category_filter'] ) );
		}
		if ( isset( $_REQUEST['sub_category_filter'] ) ) {
			$data_to_filter['sub_category_filter'] = sanitize_text_field( $_REQUEST['sub_category_filter'] );
		}
		if ( isset( $_REQUEST['attribute'] ) ) {
			$data_to_filter['attribute'] = sanitize_text_field( $_REQUEST['attribute'] );
		}
		if ( isset( $_REQUEST['product_title_select'] ) ) {
			$data_to_filter['product_title_select'] = sanitize_text_field( $_REQUEST['product_title_select'] );
		}
		if ( isset( $_REQUEST['product_title_text'] ) ) {
			$data_to_filter['product_title_text'] = sanitize_text_field( $_REQUEST['product_title_text'] );
		}
		$data_to_filter['regex_flags'] = '';
		if ( isset( $_REQUEST['regex_flags'] ) && is_array( $_REQUEST['regex_flags'] ) ) {
			$data_to_filter['regex_flags'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['regex_flags'] ) );
		}
		if ( isset( $_REQUEST['product_description_select'] ) ) {
			$data_to_filter['product_description_select'] = sanitize_text_field( $_REQUEST['product_description_select'] );
		}
		if ( isset( $_REQUEST['product_description_text'] ) ) {
			$data_to_filter['product_description_text'] = sanitize_text_field( $_REQUEST['product_description_text'] );
		}
		$data_to_filter['regex_flags_description'] = '';
		if ( isset( $_REQUEST['regex_flags_description'] ) && is_array( $_REQUEST['regex_flags_description'] ) ) {
			$data_to_filter['regex_flags_description'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['regex_flags_description'] ) );
		}
		if ( isset( $_REQUEST['product_short_description_select'] ) ) {
			$data_to_filter['product_short_description_select'] = sanitize_text_field( $_REQUEST['product_short_description_select'] );
		}
		if ( isset( $_REQUEST['product_short_description_text'] ) ) {
			$data_to_filter['product_short_description_text'] = sanitize_text_field( $_REQUEST['product_short_description_text'] );
		}
		$data_to_filter['regex_flags_short_description'] = '';
		if ( isset( $_REQUEST['regex_flags_short_description'] ) && is_array( $_REQUEST['regex_flags_short_description'] ) ) {
			$data_to_filter['regex_flags_short_description'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['regex_flags_short_description'] ) );
		}
		$data_to_filter['attribute_value_filter'] = '';
		if ( isset( $_REQUEST['attribute_value_filter'] ) && is_array( $_REQUEST['attribute_value_filter'] ) ) {
			$data_to_filter['attribute_value_filter'] = array_map( 'sanitize_text_field', ( $_REQUEST['attribute_value_filter'] ) );
		}
		if ( isset( $_REQUEST['attribute_and'] ) ) {
			$data_to_filter['attribute_and'] = sanitize_text_field( $_REQUEST['attribute_and'] );
		}
		$data_to_filter['attribute_value_and_filter'] = '';
		if ( isset( $_REQUEST['attribute_value_and_filter'] ) && is_array( $_REQUEST['attribute_value_and_filter'] ) ) {
			$data_to_filter['attribute_value_and_filter'] = array_map( 'sanitize_text_field', ( $_REQUEST['attribute_value_and_filter'] ) );
		}
		if ( isset( $_REQUEST['range'] ) ) {
			$data_to_filter['range'] = sanitize_text_field( $_REQUEST['range'] );
			$data_to_filter['range'] = str_replace( '&lt;', '<', $data_to_filter['range'] );
		}
		if ( isset( $_REQUEST['desired_price'] ) ) {
			$data_to_filter['desired_price'] = sanitize_text_field( $_REQUEST['desired_price'] );
		}
		if ( isset( $_REQUEST['minimum_price'] ) ) {
			$data_to_filter['minimum_price'] = sanitize_text_field( $_REQUEST['minimum_price'] );
		}
		if ( isset( $_REQUEST['maximum_price'] ) ) {
			$data_to_filter['maximum_price'] = sanitize_text_field( $_REQUEST['maximum_price'] );
		}
		$data_to_filter['exclude_ids'] = '';
		if ( isset( $_REQUEST['exclude_ids'] ) && is_array( $_REQUEST['exclude_ids'] ) ) {
			$data_to_filter['exclude_ids'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['exclude_ids'] ) );
		}
		$data_to_filter['exclude_categories'] = '';
		if ( isset( $_REQUEST['exclude_categories'] ) && is_array( $_REQUEST['exclude_categories'] ) ) {
			$data_to_filter['exclude_categories'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['exclude_categories'] ) );
		}
		if ( isset( $_REQUEST['exclude_subcat_check'] ) ) {
			$data_to_filter['exclude_subcat_check'] = sanitize_text_field( $_REQUEST['exclude_subcat_check'] );
		}
		if ( isset( $_REQUEST['enable_exclude_prods'] ) ) {
			$data_to_filter['enable_exclude_prods'] = sanitize_text_field( $_REQUEST['enable_exclude_prods'] );
		}
		if ( isset( $_REQUEST['undo_sch_job'] ) ) {
			$data_to_filter['undo_sch_job'] = sanitize_text_field( $_REQUEST['undo_sch_job'] );
		}
		if ( isset( $_REQUEST['file'] ) ) {
			$data_to_filter['file'] = sanitize_text_field( $_REQUEST['file'] );
		}
		$data_to_filter['prod_tags'] = '';
		if ( isset( $_REQUEST['prod_tags'] ) && is_array( $_REQUEST['prod_tags'] ) ) {
			$data_to_filter['prod_tags'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['prod_tags'] ) );
		}
		if ( isset( $_REQUEST['paged'] ) ) {
			$data_to_filter['paged'] = sanitize_text_field( $_REQUEST['paged'] );
		}
	} else {
		$data_to_filter = $data;
	}
	$sql         = "SELECT DISTINCT ID FROM {$prefix}posts LEFT JOIN {$prefix}term_relationships on {$prefix}term_relationships.object_id={$prefix}posts.ID LEFT JOIN {$prefix}term_taxonomy on {$prefix}term_taxonomy.term_taxonomy_id  = {$prefix}term_relationships.term_taxonomy_id LEFT JOIN {$prefix}terms on {$prefix}terms.term_id  ={$prefix}term_taxonomy.term_id LEFT JOIN {$prefix}postmeta on {$prefix}postmeta.post_id  ={$prefix}posts.ID WHERE  post_type = 'product' AND post_status='publish'";
	
	#stock filter
	$stock_sql_query = '';
	$ids_stock_filtered = array();
	if ( isset( $data_to_filter['stock_status'] ) ){
		$results = array();
		foreach($data_to_filter['stock_status'] as $item)
		{
			$results[] = $item;
		}
		
		$ids = join("','",$results);
		$stock_query_for_ids = "SELECT  DISTINCT(ID) from wp_posts LEFT JOIN wp_postmeta on  wp_posts.ID = wp_postmeta.post_id where wp_postmeta.meta_value IN ('$ids')";
		$resulted_ids            = $wpdb->get_results( ( $wpdb->prepare( '%1s', $stock_query_for_ids ) ? stripslashes( $wpdb->prepare( '%1s', $stock_query_for_ids ) ) : $wpdb->prepare( '%s', '' ) ), ARRAY_A );
		$ids_stock_filtered  = wp_list_pluck( $resulted_ids, 'ID' );
		
		$ids_to_query_for_stock = join("','", $ids_stock_filtered );
		$stock_query_by_ids = " AND {$prefix}posts.ID IN ('$ids_to_query_for_stock')";
		$stock_sql_query  = " AND {$prefix}postmeta.meta_key LIKE '_stock_status' AND {$prefix}postmeta.meta_value IN ('$ids')";
	}
	
	
	$title_query = '';
	if ( isset( $data_to_filter['product_title_select'] ) && 'all' != $data_to_filter['product_title_select'] && '' != $data_to_filter['product_title_text'] ) {
		switch ( $data_to_filter['product_title_select'] ) {
			case 'starts_with':
				$title_query = " AND post_title LIKE '{$data_to_filter['product_title_text']}%' ";
				break;
			case 'ends_with':
				$title_query = " AND post_title LIKE '%{$data_to_filter['product_title_text']}' ";
				break;
			case 'contains':
				$title_query = " AND post_title LIKE '%{$data_to_filter['product_title_text']}%' ";
				break;
			case 'title_regex':
				$title_query = " AND (post_title REGEXP '{$data_to_filter['product_title_text']}') ";
				break;
		}
	}
	// Description filter.
	$description_query = '';
	if ( isset( $data_to_filter['product_description_select'] ) && 'all' != $data_to_filter['product_description_select'] && '' != $data_to_filter['product_description_text'] ) {
		$query_string = '';
		if ( 'starts_with' === $data_to_filter['product_description_select'] ) {
			$query_string = "LIKE '{$data_to_filter['product_description_text']}%'";
		} elseif ( 'ends_with' === $data_to_filter['product_description_select'] ) {
			$query_string = "LIKE '%{$data_to_filter['product_description_text']}'";
		} elseif ( 'contains' === $data_to_filter['product_description_select'] ) {
			$query_string = "LIKE '%{$data_to_filter['product_description_text']}%'";
		} elseif ( 'description_regex' === $data_to_filter['product_description_select'] ) {
			$query_string = "REGEXP '{$data_to_filter['product_description_text']}'";
		}
		if ( ! empty( $query_string ) ) {
			$description_ids       = $wpdb->get_results(
				"
				SELECT 
					id
				FROM
					{$prefix}posts
				WHERE
					post_type IN ('product', 'product_variation')
				AND
					post_content " . $query_string
			); // WPCS: unprepared SQL OK.
			$description_ids_array = array();
			if ( ! empty( $description_ids ) ) {
				foreach ( $description_ids as $k => $v ) {
					array_push( $description_ids_array, $v->id );
				}
				$description_query_ids           = ! empty( $description_ids_array ) ? implode( ',', $description_ids_array ) : array();
				$description_variation_ids       = $wpdb->get_results(
					"
					SELECT 
						id
					FROM
						{$prefix}posts
					WHERE post_type = 'product_variation' AND post_parent in ({$description_query_ids})
				"
				); // WPCS: unprepared SQL OK.
				$description_variation_ids_array = array();
				if ( ! empty( $description_variation_ids ) ) {
					foreach ( $description_variation_ids as $k => $v ) {
						array_push( $description_variation_ids_array, $v->id );
					}
				}
				$description_query = ' AND ID IN (' . implode( ',', array_values( array_merge( $description_variation_ids_array, $description_ids_array ) ) ) . ')';
			} else {
				$description_query = ' AND ID NOT IN (ID)';
			}
		}
	}
	// Short description filter.
	$short_description_query = '';
	if ( isset( $data_to_filter['product_short_description_select'] ) && 'all' != $data_to_filter['product_short_description_select'] && '' != $data_to_filter['product_short_description_text'] ) {
		$query_string = '';
		if ( 'starts_with' === $data_to_filter['product_short_description_select'] ) {
			$query_string = "LIKE '{$data_to_filter['product_short_description_text']}%'";
		} elseif ( 'ends_with' === $data_to_filter['product_short_description_select'] ) {
			$query_string = "LIKE '%{$data_to_filter['product_short_description_text']}'";
		} elseif ( 'contains' === $data_to_filter['product_short_description_select'] ) {
			$query_string = "LIKE '%{$data_to_filter['product_short_description_text']}%'";
		} elseif ( 'short_description_regex' === $data_to_filter['product_short_description_select'] ) {
			$query_string = "REGEXP '{$data_to_filter['product_short_description_text']}'";
		}
		if ( ! empty( $query_string ) ) {
			$short_description_ids       = $wpdb->get_results(
				"
				SELECT 
					id
				FROM
					{$prefix}posts
				WHERE
					post_type IN ('product', 'product_variation')
				AND
					post_excerpt " . $query_string
			); // WPCS: unprepared SQL OK.
			$short_description_ids_array = array();
			if ( ! empty( $short_description_ids ) ) {
				foreach ( $short_description_ids as $k => $v ) {
					array_push( $short_description_ids_array, $v->id );
				}
				$short_description_query_ids           = ! empty( $short_description_ids_array ) ? implode( ',', $short_description_ids_array ) : array();
				$short_description_variation_ids       = $wpdb->get_results(
					"
					SELECT 
						id
					FROM
						{$prefix}posts
					WHERE post_type = 'product_variation' AND post_parent in ({$short_description_query_ids})
				"
				); // WPCS: unprepared SQL OK.
				$short_description_variation_ids_array = array();
				if ( ! empty( $short_description_variation_ids ) ) {
					foreach ( $short_description_variation_ids as $k => $v ) {
						array_push( $short_description_variation_ids_array, $v->id );
					}
				}
				$short_description_query = ' AND ID IN (' . implode( ',', array_values( array_merge( $short_description_variation_ids_array, $short_description_ids_array ) ) ) . ')';
			} else {
				$short_description_query = ' AND ID NOT IN (ID)';
			}
		}
	}
	$price_query  = '';
	$filter_range = ! empty( $data_to_filter['range'] ) ? $data_to_filter['range'] : '';
	if ( 'all' != $filter_range && ! empty( $filter_range ) ) {
		if ( '|' != $filter_range ) {
			$price_query = " AND meta_key='_regular_price' AND meta_value {$filter_range} {$data_to_filter['desired_price']} ";
		} else {
			$price_query = " AND meta_key='_regular_price' AND (meta_value >= {$data_to_filter['minimum_price']} AND meta_value <= {$data_to_filter['maximum_price']}) ";
		}
	}

	$attr_condition  = '';
	$attribute_value = '';
	if ( ! empty( $data_to_filter['attribute_value_filter'] ) && is_array( $data_to_filter['attribute_value_filter'] ) ) {
		$attribute_value = implode( ',', $data_to_filter['attribute_value_filter'] );
		$attribute_value = stripslashes( $attribute_value );
	}
	$and_attribute_condition = '';

	if ( ! empty( $data_to_filter['attribute_value_and_filter'] ) && is_array( $data_to_filter['attribute_value_and_filter'] ) ) {
		$attribute_and_value = implode( ',', $data_to_filter['attribute_value_and_filter'] );
		$attribute_and_value = stripslashes( $attribute_and_value );
		if ( empty( $attribute_value ) ) {
			$attribute_value = $attribute_and_value;
		} else {
			$attribute_value .= ',' . $attribute_and_value;
		}
	}
	$test_attr =  explode( ',', $attribute_value);

	if ( ! empty( $attribute_value ) ) {
		$attr_condition = " CONCAT(taxonomy, ':', slug) in ({$attribute_value})";
	}

	// Custom attribute filter.
	$custom_attribute_query = '';
	if ( ! empty( $data_to_filter['custom_attribute'] ) && is_array( $data_to_filter['custom_attribute'] ) ) {
		global $wpdb;
		// Get custom attributes.
		$products = $wpdb->get_results(
			"
			SELECT
				postmeta.post_id,
				postmeta.meta_value
			FROM
				{$prefix}postmeta AS postmeta
			WHERE
				postmeta.meta_key = '_product_attributes'
				AND COALESCE(postmeta.meta_value, '') != ''
		"
		); // WPCS: unprepared SQL OK.
		// Get selected custom attributes.
		$custom_attribute_ids = array();
		foreach ( $products as $product ) {
			$product_attributes = maybe_unserialize( $product->meta_value );
			if ( is_array( $product_attributes ) || is_object( $product_attributes ) ) {
				foreach ( $product_attributes as $attribute_slug => $product_attribute ) {
					if ( isset( $product_attribute['is_taxonomy'] ) && $product_attribute['is_taxonomy'] == '0' && $attribute_slug != 'product_shipping_class' ) {
						if ( in_array( $attribute_slug, $data_to_filter['custom_attribute'], true ) ) {
							array_push( $custom_attribute_ids, $product->post_id );
						}
					}
				}
			}
		}
		$custom_attribute_query_ids = implode( ',', $custom_attribute_ids );
		// Get variation ids.
		$custom_attribute_variation_ids       = $wpdb->get_results(
			"
			SELECT 
				DISTINCT ID 
			FROM 
				{$prefix}posts 
			LEFT JOIN 
				{$prefix}term_relationships on 
				{$prefix}term_relationships.object_id={$prefix}posts.ID 
			LEFT JOIN 
				{$prefix}term_taxonomy on 
				{$prefix}term_taxonomy.term_taxonomy_id  = {$prefix}term_relationships.term_taxonomy_id 
			LEFT JOIN 
				{$prefix}terms on 
				{$prefix}terms.term_id  ={$prefix}term_taxonomy.term_id 
			LEFT JOIN 
				{$prefix}postmeta on 
				{$prefix}postmeta.post_id  ={$prefix}posts.ID 
			WHERE  
				post_type = 'product_variation' AND 
				post_status='publish' AND 
				post_parent IN ({$custom_attribute_query_ids})
		"
		); // WPCS: unprepared SQL OK.
		$custom_attribute_variation_ids_array = array();
		if ( ! empty( $custom_attribute_variation_ids ) ) {
			foreach ( $custom_attribute_variation_ids as $k => $v ) {
				array_push( $custom_attribute_variation_ids_array, $v->ID );
			}
		}
		$final_variation_ids = array_merge( $custom_attribute_variation_ids_array, $custom_attribute_ids );
		if ( ! empty( $final_variation_ids ) ) {
			$custom_attribute_ids_in = implode( ',', $final_variation_ids );
			$custom_attribute_query  = " AND ID IN ({$custom_attribute_ids_in})";
		}
	}

	// Tags filter.
	$tags_query = '';
	if ( isset( $data_to_filter['prod_tags'] ) && ! empty( $data_to_filter['prod_tags'] && is_array( $data_to_filter['prod_tags'] ) ) ) {
		$tag_cond = '';
		foreach ( $data_to_filter['prod_tags'] as $key => $tag_slug ) {
			if ( empty( $tag_cond ) ) {
				$tag_cond = "'" . $tag_slug . "'";
			} else {
				$tag_cond .= ",'" . $tag_slug . "'";
			}
		}
		$tags_query = " taxonomy='product_tag' AND slug  in ({$tag_cond})";
	}

	$category_condition = '';
	$filter_categories  = array();
	if ( ! empty( $data_to_filter['category_filter'] ) && is_array( $data_to_filter['category_filter'] ) ) {
		$filter_categories = elex_get_categories( $data_to_filter['category_filter'], $data_to_filter['sub_category_filter'] );
		$cat_cond          = '';
		foreach ( $filter_categories as $cats ) {
			if ( empty( $cat_cond ) ) {
				$cat_cond = "'" . $cats . "'";
			} else {
				$cat_cond .= ",'" . $cats . "'";
			}
		}
		$category_condition = " taxonomy='product_cat' AND slug  in ({$cat_cond}) ";
	}
	if ( ! empty( $tags_query ) ) {
		if ( ! empty( $category_condition ) ) {
			$category_condition .= " AND ID IN ( SELECT DISTINCT ID FROM {$prefix}posts LEFT JOIN {$prefix}term_relationships on {$prefix}term_relationships.object_id={$prefix}posts.ID LEFT JOIN {$prefix}term_taxonomy on {$prefix}term_taxonomy.term_taxonomy_id  = {$prefix}term_relationships.term_taxonomy_id LEFT JOIN {$prefix}terms on {$prefix}terms.term_id  ={$prefix}term_taxonomy.term_id LEFT JOIN {$prefix}postmeta on {$prefix}postmeta.post_id  ={$prefix}posts.ID WHERE  post_type = 'product' AND post_status='publish' AND " . $tags_query . ')';
		} else {
			$category_condition = $tags_query;
		}
	}
	$exclude_categories = array();
	if ( ! empty( $data_to_filter['exclude_categories'] ) && is_array( $data_to_filter['exclude_categories'] ) ) {
		$exclude_categories = elex_get_categories( $data_to_filter['exclude_categories'], $data_to_filter['exclude_categories'] );
		$cat_cond           = '';
		foreach ( $exclude_categories as $cats ) {
			if ( empty( $cat_cond ) ) {
				$cat_cond = "'" . $cats . "'";
			} else {
				$cat_cond .= ",'" . $cats . "'";
			}
		}
		if ( empty( $category_condition ) ) {
			$category_condition = " taxonomy='product_cat' AND slug NOT in ({$cat_cond}) ";
		} else {
			$category_condition .= " AND taxonomy='product_cat' AND slug NOT in ({$cat_cond}) ";
		}
	}
	if ( ! empty( $title_query ) ) {
		$sql .= $title_query;
	}

	if ( ! empty( $description_query ) ) {
		$sql .= $description_query;
	}

	if ( ! empty( $short_description_query ) ) {
		$sql .= $short_description_query;
	}

	if ( ! empty( $custom_attribute_query ) ) {
		$sql .= $custom_attribute_query;
	}
	$ids_simple_external = array();
	if ( empty( $data_to_filter['type'] ) || in_array( 'simple', $data_to_filter['type'], true ) || in_array( 'external', $data_to_filter['type'], true ) ) {
		$sql_simple_ext = $sql;
		if ( ! empty( $price_query ) ) {
			$sql_simple_ext .= $price_query;
		}

		if ( empty( $data_to_filter['type'] ) || ( in_array( 'simple', $data_to_filter['type'], true ) && in_array( 'external', $data_to_filter['type'], true ) ) ) {
			$product_type_condition = " taxonomy='product_type'  AND slug  in ('simple','external') ";
		} elseif ( in_array( 'simple', $data_to_filter['type'], true ) ) {
			$product_type_condition = " taxonomy='product_type'  AND slug  in ('simple') ";
		} elseif ( in_array( 'external', $data_to_filter['type'], true ) ) {
			$product_type_condition = " taxonomy='product_type'  AND slug  in ('external') ";
		}
		if ( ! empty( $attr_condition ) && ! empty( $category_condition ) ) {
			$main_query = $sql_simple_ext . ' AND ' . $attr_condition . ' AND ID IN (' . $sql_simple_ext . ' AND ' . $category_condition . ' AND ID IN (' . $sql_simple_ext . ' AND ' . $product_type_condition . '))';
		} elseif ( ! empty( $attr_condition ) && empty( $category_condition ) ) {
			$main_query = $sql_simple_ext . ' AND ' . $attr_condition . ' AND ID IN (' . $sql_simple_ext . ' AND ' . $product_type_condition . ')';
		} elseif ( ! empty( $category_condition ) && empty( $attr_condition ) ) {
			$main_query = $sql_simple_ext . ' AND ' . $category_condition . ' AND ID IN (' . $sql_simple_ext . ' AND ' . $product_type_condition . ')';
		} else {
			$main_query = $sql_simple_ext . ' AND ' . $product_type_condition;
		}
		$result              = $wpdb->get_results( ( $wpdb->prepare( '%1s', $main_query ) ? stripslashes( $wpdb->prepare( '%1s', $main_query ) ) : $wpdb->prepare( '%s', '' ) ), ARRAY_A );
		$ids_simple_external = wp_list_pluck( $result, 'ID' );
	}
	$ids_variable = array();
	if ( empty( $data_to_filter['type'] ) || in_array( 'variation', $data_to_filter['type'], true ) || in_array( 'variable', $data_to_filter['type'], true ) ) {
		$product_type_condition = " taxonomy='product_type'  AND slug  in ('variable') ";

		if ( ! empty( $attr_condition ) && ! empty( $category_condition ) ) {
			$main_query = $sql . ' AND ' . $attr_condition . ' AND ID IN (' . $sql . ' AND ' . $category_condition . ' AND ID IN (' . $sql . ' AND ' . $product_type_condition . '))';
		} elseif ( ! empty( $attr_condition ) && empty( $category_condition ) ) {
			$main_query = $sql . ' AND ' . $attr_condition . ' AND ID IN (' . $sql . ' AND ' . $product_type_condition . ')';
		} elseif ( ! empty( $category_condition ) && empty( $attr_condition ) ) {
			$main_query = $sql . ' AND ' . $category_condition . ' AND ID IN (' . $sql . ' AND ' . $product_type_condition . ')';
		} else {
			$main_query = $sql . ' AND ' . $product_type_condition;
		}
		$result       = $wpdb->get_results( ( $wpdb->prepare( '%1s', $main_query ) ? stripslashes( $wpdb->prepare( '%1s', $main_query ) ) : $wpdb->prepare( '%s', '' ) ), ARRAY_A );
		$ids_variable = wp_list_pluck( $result, 'ID' );
	}
	$ids_variations = array();
	if ( ! empty( $ids_variable ) && ( empty( $data_to_filter['type'] ) || in_array( 'variation', $data_to_filter['type'], true ) || in_array( 'variable', $data_to_filter['type'], true ) ) ) {
		$temp_ids   = implode( ',', $ids_variable );
		$sql        = "SELECT DISTINCT ID FROM {$prefix}posts LEFT JOIN {$prefix}term_relationships on {$prefix}term_relationships.object_id={$prefix}posts.ID LEFT JOIN {$prefix}term_taxonomy on {$prefix}term_taxonomy.term_taxonomy_id  = {$prefix}term_relationships.term_taxonomy_id LEFT JOIN {$prefix}terms on {$prefix}terms.term_id  ={$prefix}term_taxonomy.term_id LEFT JOIN {$prefix}postmeta on {$prefix}postmeta.post_id  ={$prefix}posts.ID WHERE  post_type = 'product_variation' AND post_status='publish' AND post_parent IN ({$temp_ids}) ";
		$attr_query = '';
		if ( ! empty( $attribute_value ) ) {
			$tt = explode( ',', $attribute_value );
			foreach ( $tt as $key => $val ) {
				$attr        = explode( ':', $val );
				$attr_key    = str_replace( "'", '', $attr[0] );
				$attr_val    = str_replace( "'", '', $attr[1] );
				$attr_query .= " (meta_key='attribute_{$attr_key}' AND meta_value = '{$attr_val}') OR";
			}
			$attr_query = substr( $attr_query, 0, -2 );
		}
		$attribute            = '';
		$sub_attribute_array  = array();
		$main_attribute_array = array();
		if ( isset( $data_to_filter['attribute_value_and_filter'] ) && is_array( $data_to_filter['attribute_value_and_filter'] ) ) {
			foreach ( $data_to_filter['attribute_value_and_filter'] as $index => $attr_pair ) {
				$attr_pair     = stripslashes( $attr_pair );
				$attr_pair_arr = explode( ':', $attr_pair );
				if ( $attribute != $attr_pair_arr[0] && '' != $attribute ) {
					$main_attribute_array[] = $sub_attribute_array;
					$sub_attribute_array    = array();
				}
				$attribute             = $attr_pair_arr[0];
				$sub_attribute_array[] = $attr_pair;
			}
		}
		if ( ! empty( $sub_attribute_array ) ) {
			$main_attribute_array[] = $sub_attribute_array;
		}
		if ( ! empty( $main_attribute_array ) ) {
			$attr_query = '';
			$counter    = 0;
			foreach ( $main_attribute_array as $key => $unique_attribute_values ) {
				if ( 0 != $counter ) {
					$attr_query .= ' AND ID IN (' . $sql . 'AND';
				}
				foreach ( $unique_attribute_values as $arr_index => $attribute_key_val ) {
					$attribute_key_val     = trim( $attribute_key_val, "'" );
					$attribute_key_val_arr = explode( ':', $attribute_key_val );
					$attr_query           .= " (meta_key='attribute_{$attribute_key_val_arr[0]}' AND meta_value = '{$attribute_key_val_arr[1]}') OR";
				}
				$attr_query = substr( $attr_query, 0, -2 );
				if ( 0 != $counter ) {
					$attr_query .= ')';
				}
				$counter++;
			}
		}
		$price_sql_query = '';
		if ( ! empty( $price_query ) ) {
			$price_sql_query = 'AND ID IN (' . $sql . $price_query . ')';
		}
		if ( ! empty( $attr_query ) ) {
			$sql .= "AND ({$attr_query})";
		}
		if ( ! empty( $price_sql_query ) ) {
			$sql .= $price_sql_query;
		}
		$result         = $wpdb->get_results( ( $wpdb->prepare( '%1s', $sql ) ? stripslashes( $wpdb->prepare( '%1s', $sql ) ) : $wpdb->prepare( '%s', '' ) ), ARRAY_A );
		$ids_variations = wp_list_pluck( $result, 'ID' );
	}
	if ( ! empty( $ids_variations ) && ( empty( $data_to_filter['type'] ) || in_array( 'variable', $data_to_filter['type'], true ) ) ) {
		$ids_togetparent = implode( ',', $ids_variations );
		$sql             = "SELECT DISTINCT post_parent FROM {$prefix}posts WHERE  post_type = 'product_variation' AND post_status='publish' AND ID IN ({$ids_togetparent}) ";
		#commented Line SQL query represents all variations which have no parent relation after type conversion:. $sql = "SELECT DISTINCT ID  FROM wp_posts INNER JOIN wp_term_relationships on wp_posts.ID = wp_term_relationships.object_id INNER JOIN wp_terms on wp_term_relationships.term_taxonomy_id = wp_terms.term_id where wp_terms.slug = 'variable'   and wp_posts.post_status = 'publish'";
		$result          = $wpdb->get_results( ( $wpdb->prepare( '%1s', $sql ) ? stripslashes( $wpdb->prepare( '%1s', $sql ) ) : $wpdb->prepare( '%s', '' ) ), ARRAY_A );
		$ids_variable    = wp_list_pluck( $result, 'post_parent' );
	} else {
		if ( ! empty( $data_to_filter['type'] ) ){
			if ( in_array( 'variation', $data_to_filter['type'], true ) ){
				$ids_variable = array();
			}
		}
	}
	if ( ! empty( $data_to_filter['type'] ) && ! in_array( 'variation', $data_to_filter['type'], true ) ) {
		$ids_variations = array();
	}
	$res_id = array_merge( $ids_simple_external, $ids_variable, $ids_variations );
	if ( isset( $data_to_filter['enable_exclude_prods'] ) && $data_to_filter['enable_exclude_prods'] && ! empty( $res_id ) && ! empty( $data_to_filter['exclude_ids'] ) ) {
		foreach ( $res_id as $key => $val ) {
			if ( in_array( $val, $data_to_filter['exclude_ids'], true ) ) {
				unset( $res_id[ $key ] );
			}
		}
		// To reindex array values after unsetting.
		$res_id = array_values( $res_id );
	}
	$revert_keys = array();
	if ( isset( $data_to_filter['undo_sch_job'] ) && $data_to_filter['undo_sch_job'] ) {
		$undo_product_id = array();
		$scheduled_jobs  = get_option( 'elex_bep_scheduled_jobs' );
		foreach ( $scheduled_jobs as $key => $val ) {
			if ( sanitize_text_field( $data_to_filter['file'] ) == $val['job_name'] ) {
				$undo_product_id = $val['revert_data'];
				break;
			}
		}
		$revert_keys = array_keys( $undo_product_id );
	}
	if ( ! empty( $revert_keys ) ) {
		return $revert_keys;
	}
	if ( !empty( $ids_stock_filtered ) ){
		$res_id = array_intersect( $ids_stock_filtered, $res_id );
	}

	update_option( 'bulk_edit_filtered_product_ids_for_select_unselect', $res_id );
	return $res_id;
}

// Get Subcategories.
function xa_subcats_from_parentcat_by_slug( $parent_cat_slug ) {
	$id_by_slug     = get_term_by( 'slug', $parent_cat_slug, 'product_cat' );
	$product_cat_id = $id_by_slug->term_id;
	$args           = array(
		'hierarchical'     => 1,
		'show_option_none' => '',
		'hide_empty'       => 0,
		'parent'           => $product_cat_id,
		'taxonomy'         => 'product_cat',
	);
	$subcats        = get_categories( $args );
	$temp_arr       = array();
	foreach ( $subcats as $sc ) {
		array_push( $temp_arr, $sc->slug );
	}
	return $temp_arr;
}

function eh_bep_update_custom_meta( $pid, $val ) {
	$meta_key      = get_option( 'eh_bulk_edit_meta_values_to_update' );
	$undo_val      = array();
	$meta_key_size = count( $meta_key );
	for ( $i = 0; $i < $meta_key_size; $i++ ) {
		$undo_data = get_post_meta( $pid, $meta_key[ $i ], true );
		array_push( $undo_val, $undo_data );
		if ( '' != $val[ $i ] ) {
			update_post_meta( $pid, $meta_key[ $i ], $val[ $i ] );
		}
	}
	return $undo_val;
}







function elex_bep_get_attribute_terms() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$attribute_name     = isset( $_POST['attrib'] ) ? sanitize_text_field( $_POST['attrib'] ) : '';
	$selected_from_attr = '';
	$selected_to_attr   = '';
	if ( isset( $_POST['attr_edit'] ) ) {
		$attr_detail_arr    = explode( ',', $attribute_name );
		$from_attr          = $attr_detail_arr[0];
		$to_attr            = $attr_detail_arr[1];
		$from_attr_arr      = explode( ':', $from_attr );
		$to_attr_arr        = explode( ':', $to_attr );
		$attribute_name     = $to_attr_arr[0];
		$selected_from_attr = $from_attr_arr[1];
		$selected_to_attr   = $to_attr_arr[1];
	}

	$cat_args   = array(
		'hide_empty' => false,
		'order'      => 'ASC',
	);
	$attributes = wc_get_attribute_taxonomies();
	foreach ( $attributes as $key => $value ) {
		if ( $attribute_name == $value->attribute_name ) {
			$attribute_name  = $value->attribute_name;
			$attribute_label = $value->attribute_label;
		}
	}
	$attribute_value = get_terms( 'pa_' . $attribute_name, $cat_args );
	$return_array = array();
	foreach ( $attribute_value as $key => $value ) {
		error_log( $value->name );
		array_push( $return_array, $value->name );
	}
	error_log( print_r( $return_array, TRUE ));
	die( wp_json_encode( $return_array ) );
}