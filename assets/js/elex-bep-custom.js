/// Custom js

jQuery(document).ready(function () {
    //Variation change attributes
    jQuery("#vari_attribu_add_variation input[type='checkbox']").click(function () {
        var display = jQuery("#vari_attribu_add_variation input[type=checkbox]:checked").length;
        if (!jQuery(this).is(":checked")) {
            remove_variation_attributes(jQuery(this).val());
        } else {
            jQuery(".loader").css("display", "block");
            append_variation_attributes(jQuery(this).val());
        }
    });

    function remove_variation_attributes(attrib_name) {
        var id = "#vari_attr_change" + attrib_name;
        jQuery(id).remove();
    }

    function append_variation_attributes(attrib_name) {
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
                action: "elex_bep_get_attribute_terms",
                attrib: attrib_name
            },
            success: function (data) {
                jQuery(".loader").css("display", "none");
                data = JSON.parse(data)
                var list_dom = "";
                for (i = 0; i < data.length; i++) {
                    // list_dom += "<li class='active-result' data-option-array-index='1' style=''>"+ data[i] + "</li>"
                    list_dom += "<option class='active-result' value='" + i + "'>" + data[i] + "</option>"
                }
                alert(list_dom)
                // jQuery('#ullist_'+ attrib_name> ul).append( list_dom );
                // jQuery('#ullist_'+ attrib_name> ul).html( list_dom );
                jQuery("#multiselect_" + attrib_name).append(list_dom)

            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }



    jQuery("#vari_attribu_add_variation1 input[type='checkbox']").click(function () {
        var display = jQuery("#vari_attribu_add_variation1 input[type=checkbox]:checked").length;
        if (display == 0) {
            jQuery("#attribute_value_select_and1_" + jQuery(this).val()).remove();
        }
        else {
            if (!jQuery("#attribute_value_select_and1_" + jQuery(this).val()).length) {
                alert("Hello")
                var dom = "<tr id='attribute_value_select_and1_" + jQuery(this).val() +"'><td>" + js_obj.label_for_attribute_name + "_(" + jQuery(this).val() + ") </td><td class='eh-content-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + js_obj.filter_attribute_value_tooltip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_attributes_and1'></select></span></td></tr>";
                alert(dom)
                jQuery("#attribute_types_add_variation").after(dom);
                jQuery(".attribute-chosen").chosen();
                jQuery(".tooltip").darkTooltip();
            }
            if (!jQuery(this).is(":checked")) {
                remove_attribute_value_and(jQuery(this).val());
            } else {
                append_attribute_value_and(jQuery(this).val());
            }
        }
    });

    function remove_attribute_value_and(attrib_name) {
        var id = "#grp_and_" + attrib_name;
        jQuery(id).remove();
        jQuery(".attribute-chosen").trigger("chosen:updated");
    }

    function append_attribute_value_and(attrib_name) {
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
                action: "eh_bep_get_attributes_action",
                attrib: attrib_name,
                attr_and: true
            },
            success: function (data) {
                alert(JSON.stringify(data))
                jQuery("#select_input_attributes_and1").append(data);
                jQuery(".attribute-chosen").trigger("chosen:updated");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
})


