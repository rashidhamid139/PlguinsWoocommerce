/// Custom js
jQuery(document).ready(function() {
    jQuery("#vari_attribu_add_variation1 input[type='checkbox']").click(function () {
        var display = jQuery("#vari_attribu_add_variation1 input[type=checkbox]:checked").length;
        if (display == 0) {
            jQuery("#attribute_value_select_and_" + jQuery(this).val()).remove();
        }
        else {
            if (!jQuery("#attribute_value_select_and_" + jQuery(this).val()).length) {
                var dom = "<tr id='attribute_value_select_and_" + jQuery(this).val() +"'><td>" + js_obj.label_for_attribute_name + "_" + jQuery(this).val() + " </td><td class='eh-content-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + js_obj.filter_attribute_value_tooltip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_attributes_and"+ "_" + jQuery(this).val() +"'></select></span></td></tr><br>";
                jQuery("#attribute_types_add_variation").after(dom);
                jQuery(".attribute-chosen").chosen();
                jQuery(".tooltip").darkTooltip();
            }
            if (!jQuery(this).is(":checked")) {
                remove_attribute_value_and(jQuery(this).val());
            } else {
                append_attribute_value_categories(jQuery(this).val());
            }
        }
    });

    function remove_attribute_value_and(attrib_name) {
        var id = "#grp_and_" + attrib_name;
        jQuery(id).remove();
        jQuery(".attribute-chosen").trigger("chosen:updated");
    }

    function append_attribute_value_categories(attrib_name) {
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
                action: "eh_bep_get_attributes_terms_action_callback",
                attrib: attrib_name,
                attr_and: true
            },
            success: function (data) {
                alert("#select_input_attributes_and" + "_" + attrib_name  )
                jQuery().append( "<optgroup label='Size' id='grp_and_size'><option value='pa_size:large'>Large</option></optgroup>")
                jQuery("#select_input_attributes_and" + "_" + attrib_name).append(data);
                jQuery(".attribute-chosen").trigger("chosen:updated");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Hello")
                console.log(textStatus, errorThrown);
            }
        });
    }
})


