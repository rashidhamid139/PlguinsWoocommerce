<?php
/**
 *
 * Template Frontend Filters.
 *
 * @package ELEX Bulk Edit Products, Prices & Attributes for Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cat_args        = array(
	'hide_empty' => false,
	'order'      => 'ASC',
);
$attributes      = wc_get_attribute_taxonomies();
$attribute_value = get_terms( 'pa_size', $cat_args );
$plugin_name     = 'productbulkedit';
require EH_BEP_DIR . 'includes/wf_api_manager/html/html-wf-activation-window.php';
?>

<div class="loader"></div>

<div class='wrap postbox table-box table-box-main' id="top_filter_tag" style='padding:5px 20px;'>
	<h2>
		<?php esc_html_e( 'Filter the Products', 'eh_bulk_edit' ); ?>
		<span style="float: right;" id="remove_undo_update_button_top" ><span class='woocommerce-help-tip tooltip' id='add_undo_button_tooltip' style="padding:0px 15px" data-tooltip='<?php esc_html_e( 'Click to undo the last update you have done', 'eh_bulk_edit' ); ?>'></span><button id='undo_display_update_button' style="margin-bottom: 2%;" class='button button-primary button-large'><span class="update-text"><?php esc_html_e( 'Undo Last Update', 'eh_bulk_edit' ); ?></span></button></span>
	</h2>
	<hr>
	<table class='eh-content-table' id='data_table'>

		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Title', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition from the drop-down and enter a product title', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<select id='product_title_select' style="width: 45%;">
					<option value = 'all'><?php esc_html_e( 'All', 'eh_bulk_edit' ); ?></option>
					<option value = 'starts_with'><?php esc_html_e( 'Starts With', 'eh_bulk_edit' ); ?></option>
					<option value = 'ends_with'><?php esc_html_e( 'Ends With', 'eh_bulk_edit' ); ?></option>
					<option value = 'contains'><?php esc_html_e( 'Contains', 'eh_bulk_edit' ); ?></option>
					<option value = 'title_regex'><?php esc_html_e( 'Regex Match', 'eh_bulk_edit' ); ?></option>
				</select>
				<span id='product_title_text'></span>
			</td>
			<td class='eh-content-table-right' id='regex_flags_field'>
				<span class='select-eh'><select data-placeholder='<?php esc_html_e( 'Select Flags (Optional)', 'eh_bulk_edit' ); ?>' id='regex_flags_values' multiple class='category-chosen' >
						<?php
						{
							echo "<option value='A'>Anchored (A)</option>";
							echo "<option value='D'>Dollors End Only (D)</option>";
							echo "<option value='x'>Extended (x)</option>";
							echo "<option value='X'>Extra (X)</option>";
							echo "<option value='i'>Insensitive (i)</option>";
							echo "<option value='J'>Jchanged (J)</option>";
							echo "<option value='m'>Multi Line (m)</option>";
							echo "<option value='s'>Single Line (s)</option>";
							echo "<option value='u'>Unicode (u)</option>";
							echo "<option value='U'>Ungreedy (U)</option>";
						}
						?>
					</select></span>
			</td>
			<td class='eh-content-table-help_link' id='regex_help_link'>
				<a href="https://elextensions.com/understanding-regular-expression-regex-pattern-matching-bulk-edit-products-prices-attributes-woocommerce-plugin/" target="_blank">Help</a>
			</td>
		</tr>
		<tr>	
		</tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Tags', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip=' <?php esc_html_e( 'Select the product tag(s) for which the filter has to be applied', 'eh_bulk_edit' ); ?> '></span>
			</td>
			<td>

				<span class='select-eh'><select data-placeholder='<?php esc_html_e( 'Select Product Tags', 'eh_bulk_edit' ); ?>' id='elex_product_tags' multiple class='category-chosen' >
						<?php
						$terms = get_terms( 'product_tag' );
						foreach ( $terms as $index => $term_obj ) {
							echo filter_var( '<option value="' . $term_obj->slug . '">' . $term_obj->name . '</option>' );
						}
						?>
					</select></span>
			</td>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Types', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip=' <?php esc_html_e( 'Select the product type(s) for which the filter has to be applied', 'eh_bulk_edit' ); ?> '></span>
			</td>
			<td>

				<span class='select-eh'><select data-placeholder='<?php esc_html_e( 'Select Product Types', 'eh_bulk_edit' ); ?>' id='product_type' multiple class='category-chosen' >
						<?php
						{
							echo "<option value='simple'>Simple</option>";
							echo "<option value='variable'>Variable (Parent)</option>";
							echo "<option value='variation'>Variable (Variation)</option>";
							echo "<option value='external'>External</option>";
						}
						?>
					</select></span>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Stock Status', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip=' <?php esc_html_e( 'Select the stok status for which the filter has to be applied', 'eh_bulk_edit' ); ?> '></span>
			</td>
			<td>

				<span class='select-eh'><select data-placeholder='<?php esc_html_e( 'Select Stock Status', 'eh_bulk_edit' ); ?>' id='stock_status_id' multiple class='category-chosen' >
						<?php
						{
							echo "<option value='instock'>In Stock</option>";
							echo "<option value='outofstock'>Out of Stock</option>";
							echo "<option value='onbackorder'>On Backorder</option>";
						}
						?>
					</select></span>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Categories', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select the category(s) for which the filter has to be applied. Enable the checkbox to include subcategories', 'eh_bulk_edit' ); ?>'></span>
			</td>

			<td class='eh-edit-tab-table-input-td'>
				<?php
				$cat_args      = array(
					'hide_empty'   => 0,
					'taxonomy'     => 'product_cat',
					'hierarchical' => 1,
					'orderby'      => 'name',
					'order'        => 'ASC',
					'child_of'     => 0,
				);
				$cat_hierarchy = xa_filter_get_cat_hierarchy( 0, $cat_args );
				$cat_rows      = xa_filter_category_rows( $cat_hierarchy, 0, 'cat_filter' );
				?>
				<div id="product_cat-all" class="tabs-panel">
					<ul id="product_catchecklist">
						<?php
						echo filter_var( $cat_rows );
						?>
					</ul></div>
			</td>
			<td class='eh-content-table-right'>
				<input type="checkbox" id ="subcat_check">Include Subcategories
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Regular Price', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition and specify a price', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<select id='regular_price_range_select' style="width: 45%;">
					<option value='all'><?php esc_html_e( 'All', 'eh_bulk_edit' ); ?></option>
					<option value='>='>>=</option>
					<option value='<='><=</option>
					<option value='='>==</option>
					<option value='|'>|| <?php esc_html_e( 'Between', 'eh_bulk_edit' ); ?></option>
				</select>
				<span id='regular_price_range_text'></span>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Description', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition from the drop-down and enter a product description', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<select id='product_description_select' style="width: 45%;">
					<option value = 'all'><?php esc_html_e( 'All', 'eh_bulk_edit' ); ?></option>
					<option value = 'starts_with'><?php esc_html_e( 'Starts With', 'eh_bulk_edit' ); ?></option>
					<option value = 'ends_with'><?php esc_html_e( 'Ends With', 'eh_bulk_edit' ); ?></option>
					<option value = 'contains'><?php esc_html_e( 'Contains', 'eh_bulk_edit' ); ?></option>
					<option value = 'description_regex'><?php esc_html_e( 'Regex Match', 'eh_bulk_edit' ); ?></option>
				</select>
				<span id='product_description_text'></span>
			</td>
			<td class='eh-content-table-right' id='regex_flags_field_description'>
				<span class='select-eh'><select data-placeholder='<?php esc_html_e( 'Select Flags (Optional)', 'eh_bulk_edit' ); ?>' id='regex_flags_values_description' multiple class='category-chosen' >
						<?php
						{
							echo "<option value='A'>Anchored (A)</option>";
							echo "<option value='D'>Dollors End Only (D)</option>";
							echo "<option value='x'>Extended (x)</option>";
							echo "<option value='X'>Extra (X)</option>";
							echo "<option value='i'>Insensitive (i)</option>";
							echo "<option value='J'>Jchanged (J)</option>";
							echo "<option value='m'>Multi Line (m)</option>";
							echo "<option value='s'>Single Line (s)</option>";
							echo "<option value='u'>Unicode (u)</option>";
							echo "<option value='U'>Ungreedy (U)</option>";
						}
						?>
					</select></span>
			</td>
			<td class='eh-content-table-help_link' id='regex_help_link_description'>
				<a href="https://elextensions.com/understanding-regular-expression-regex-pattern-matching-bulk-edit-products-prices-attributes-woocommerce-plugin/" target="_blank">Help</a>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Short Description', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition from the drop-down and enter a product short description', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<select id='product_short_description_select' style="width: 45%;">
					<option value = 'all'><?php esc_html_e( 'All', 'eh_bulk_edit' ); ?></option>
					<option value = 'starts_with'><?php esc_html_e( 'Starts With', 'eh_bulk_edit' ); ?></option>
					<option value = 'ends_with'><?php esc_html_e( 'Ends With', 'eh_bulk_edit' ); ?></option>
					<option value = 'contains'><?php esc_html_e( 'Contains', 'eh_bulk_edit' ); ?></option>
					<option value = 'short_description_regex'><?php esc_html_e( 'Regex Match', 'eh_bulk_edit' ); ?></option>
				</select>
				<span id='product_short_description_text'></span>
			</td>
			<td class='eh-content-table-right' id='regex_flags_field_short_description'>
				<span class='select-eh'><select data-placeholder='<?php esc_html_e( 'Select Flags (Optional)', 'eh_bulk_edit' ); ?>' id='regex_flags_values_short_description' multiple class='category-chosen' >
						<?php
						{
							echo "<option value='A'>Anchored (A)</option>";
							echo "<option value='D'>Dollors End Only (D)</option>";
							echo "<option value='x'>Extended (x)</option>";
							echo "<option value='X'>Extra (X)</option>";
							echo "<option value='i'>Insensitive (i)</option>";
							echo "<option value='J'>Jchanged (J)</option>";
							echo "<option value='m'>Multi Line (m)</option>";
							echo "<option value='s'>Single Line (s)</option>";
							echo "<option value='u'>Unicode (u)</option>";
							echo "<option value='U'>Ungreedy (U)</option>";
						}
						?>
					</select></span>
			</td>
			<td class='eh-content-table-help_link' id='regex_help_link_short_description'>
				<a href="https://elextensions.com/understanding-regular-expression-regex-pattern-matching-bulk-edit-products-prices-attributes-woocommerce-plugin/" target="_blank">Help</a>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<h3><?php esc_html_e( 'Attributes', 'eh_bulk_edit' ); ?></h3>
				<hr>
			</td>
		</tr>
		<tr id='attribute_types'>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Attributes (Group with OR)', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class="woocommerce-help-tip tooltip" data-tooltip="<?php esc_html_e( "The products will be filtered when any one of the attributes and it's corresponding values are present", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<td>
				<?php
				global $wpdb;
				// Get custom attributes.
				if ( count( $attributes ) > 0 ) {
					foreach ( $attributes as $key => $value ) {
						echo filter_var( "<span id='attrib_name' class='checkbox-eh'><input type='checkbox' name='attrib_name' value='" . $value->attribute_name . "' id='" . $value->attribute_name . "'>" . $value->attribute_label . '</span>' );
					}
				} else {
					echo "<span id='attrib_name' class='checkbox-eh'>No attributes found.</span>";
				}
				?>
			</td>
		</tr>
		<tr id='attribute_types_and'>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Attributes (Group with AND)', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class="woocommerce-help-tip tooltip" data-tooltip="<?php esc_html_e( "The products will be filtered only when both attributes and it's corresponding values are present", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<td>
				<?php
				if ( count( $attributes ) > 0 ) {
					foreach ( $attributes as $key => $value ) {
						echo filter_var( "<span id='attrib_name_and' class='checkbox-eh'><input type='checkbox' name='attrib_name_and' value='" . $value->attribute_name . "' id='" . $value->attribute_name . "'>" . $value->attribute_label . '</span>' );
					}
				} else {
					echo "<span id='attrib_name_and' class='checkbox-eh'>No attributes found.</span>";
				}
				?>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Custom Attributes', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip=' <?php esc_html_e( 'Filter Products based on Custom Attributes.', 'eh_bulk_edit' ); ?> '></span>
			</td>
			<td class='eh-content-table-input-td'>
				<?php
				global $wpdb;
				// Get custom attributes.
				$products                = $wpdb->get_results(
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
				$custom_attribute_values = array();
				foreach ( $products as $product ) {
					$product_attributes = maybe_unserialize( $product->meta_value );
					if ( is_array( $product_attributes ) || is_object( $product_attributes ) ) {
						foreach ( $product_attributes as $attribute_slug => $product_attribute ) {
							if ( isset( $product_attribute['is_taxonomy'] ) && 0 === intval( $product_attribute['is_taxonomy'] ) && 'product_shipping_class' !== $attribute_slug ) {
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
					?>
					<select data-placeholder='<?php esc_html_e( 'Select Attributes', 'eh_bulk_edit' ); ?>' id='elex_select_custom_attribute' multiple class='category-chosen'>
					<?php
					foreach ( $custom_attribute_values as $key => $value ) {
						echo filter_var( "<option value='" . $key . "'>" . ucfirst( $key ) . '</option>' );
					}
					?>
					</select>
					<?php
				} else {
					echo filter_var( '<p>No Custom Attributes Found</p>' );
				}
				?>
			</td>
		</tr>
	</table>
	<h2 >
		<span style='padding-right:1em ; font-size:20px;'><?php esc_html_e( 'Exclusions', 'eh_bulk_edit' ); ?></span>
		<input type="checkbox" id ="enable_exclude_products"><span style="font-weight:normal;font-size: 14px;"><?php esc_html_e( 'Enable', 'eh_bulk_edit' ); ?></span>
	</h2>
	<hr align="left" width="20%" >
	<table class='eh-content-table' id="exclude_products">
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Exclude by IDs', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Enter the Product IDs to exclude from getting updated (separate IDs by comma).', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<textarea rows="4" cols="50" id="exclude_ids"></textarea>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Exclude by Categories', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip="<?php esc_html_e( "Select the categories to exclude products from getting updated. All the subcategories under a parent category will be excluded if you enable 'Include Subcategories' checkbox.", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<?php
			$cat_exclude_rows = xa_filter_category_rows( $cat_hierarchy, 0, 'cat_exclude' );
			?>
			<td class='eh-edit-tab-table-input-td'>
				<div id="product_cat-all" class="tabs-panel">
					<ul id="product_catchecklist">
						<?php
						echo filter_var( $cat_exclude_rows );
						?>
					</ul></div>
			</td>
			<td class='eh-content-table-right'>
				<input type="checkbox" id ="exclude_subcat_check">Include Subcategories
			</td>
		</tr>
	</table>
	<button id='clear_filter_button' value='clear_products' style='margin:5px 2px 2px 2px; color: white; width:15%; background-color: gray;' class='button button-large'><?php esc_html_e( 'Reset Filter', 'eh_bulk_edit' ); ?></button>
	<button id='filter_products_button' value='filter_products' style='margin:5px 2px 2px 2px; float: right; ' class='button button-primary button-large'><?php esc_html_e( 'Preview Filtered Products', 'eh_bulk_edit' ); ?></button>        
</div>
<?php
require_once EH_BEP_TEMPLATE_PATH . '/template-frontend-tables.php';
/** Get Category Hierarchy.
 *
 * @param var $parent parent.
 * @param var $args args.
 */
function xa_filter_get_cat_hierarchy( $parent, $args ) {
	$cats = get_categories( $args );
	$ret  = new stdClass();
	foreach ( $cats as $cat ) {
		if ( $cat->parent === $parent ) {
			$id                 = $cat->cat_ID;
			$ret->$id           = $cat;
			$ret->$id->children = xa_filter_get_cat_hierarchy( $id, $args );
		}
	}
	return $ret;
}
/** Get Category Hierarchy.
 *
 * @param var $categories categories.
 * @param var $level level.
 * @param var $name name.
 */
function xa_filter_category_rows( $categories, $level, $name ) {
	$html_code       = '';
	$level_indicator = '';
	for ( $i = 0; $i < $level; $i++ ) {
		$level_indicator .= '- ';
	}
	if ( $categories ) {
		foreach ( $categories as $category ) {
			$html_code .= '<li><label><input value=' . $category->slug . " type='checkbox' name=" . $name . '>' . $level_indicator . $category->name . '</label></li>';
			if ( $category->children && count( (array) $category->children ) > 0 ) {
				$html_code .= xa_filter_category_rows( $category->children, $level + 1, $name );
			}
		}
	} else {
		$html_code .= esc_html__( 'No categories found.', 'eh_bulk_edit' );
	}
	return $html_code;
}
