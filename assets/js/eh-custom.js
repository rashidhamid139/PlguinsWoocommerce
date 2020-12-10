
var filtered_ids = [];
var chunk_data = [];
var update_index = 0;
var undo_index = 0;
var edit_job = false;
var undo_scheduled_job = 0;
var file_to_undo = "";
var prev_metas = [];
jQuery(document).ready(function () {
  jQuery.ajax({
    type: "post",
    url: ajaxurl,
    async: false,
    data: {
      action: "elex_bep_update_checked_status",
      _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
      operation: "delete"
    }
  });
  jQuery("input[id=bep_filter_select_unselect_all_products_checkbox]").attr("checked", "checked");
  // new checked update.
  jQuery('form[id=products-filter]').on("change", "input[id=bep_filter_select_unselect_all_products_checkbox]", function () {
    if (this.checked) {
      jQuery("input:checkbox[name=column-checkbox]").each(function () {
        this.checked = true;
      });
      jQuery.ajax({
        type: "post",
        url: ajaxurl,
        async: false,
        data: {
          action: "elex_bep_update_checked_status",
          _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
          operation: "delete"
        }
      });
    } else {
      jQuery("input:checkbox[name=column-checkbox]").each(function () {
        this.checked = false;
      });
      jQuery.ajax({
        type: "post",
        async: false,
        url: ajaxurl,
        data: {
          action: "elex_bep_update_checked_status",
          _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
          operation: "unselect_all",
        }
      });
    }
  });
});
jQuery(function () {
  jQuery("#attr_names").hide();
  jQuery(".category-chosen").chosen();
  jQuery("#regex_flags_field_sku").hide();
  jQuery("#regex_help_link_sku").hide();
  jQuery("#regex_flags_field_title").hide();
  jQuery("#regex_help_link_title").hide();
  jQuery("#regex_flags_field_description").hide();
  jQuery("#regex_help_link_description").hide();
  jQuery("#regex_flags_field_short_description").hide();
  jQuery("#regex_help_link_short_description").hide();
  jQuery(".hide-price-role-select-chosen").chosen();
  jQuery("#regex_flags_field").hide();
  jQuery("#regex_help_link").hide();
  jQuery(".tooltip").darkTooltip();
  //jQuery('#add_undo_button_tooltip').trigger('mouseover');
  jQuery(".attribute-update-chosen").chosen();
  jQuery("#cat_select").hide();
  jQuery("#exclude_products").hide();
  jQuery("#regular_checkbox").hide();
  jQuery("#elex_schedule_field").hide();
  jQuery("#elex_schedule_date_and_time").hide();
  jQuery("#elex_revert_date_and_time").hide();
  jQuery("#schedule_frequency_options").hide();
  jQuery("#select_days_weekly").hide();
  jQuery("#select_days_monthly").hide();
  jQuery("#stop_schedule_field").hide();
  jQuery("#description_tr").hide();
  jQuery("#short_description_tr").hide();
  jQuery("#gallery_images_tr").hide();
});
jQuery(function () {
  jQuery("#save_dislay_count_order").on("click", function () {
    row_count_txt = jQuery("#display_count_order").val();
    if (!row_count_txt || row_count_txt <= 0) {
      alert("Please enter a value greater than zero");
      return false;
    }
    if (row_count_txt > 9999) {
      alert("Enter value less than 10000");
      return false;
    }
  });
  jQuery("#cancel_update_button").on("click", function () {
    jQuery("#edit_product").find("select").prop("selectedIndex", 0);
    jQuery("#edit_product").find("select").trigger("change");
  });
  jQuery("#enable_exclude_products").click(function () {
    if (jQuery(this).is(":checked")) {
      jQuery("#exclude_products").show();
    } else {
      jQuery("#exclude_products").hide();
    }
  });
});
jQuery(function () {
  var sale_round_type = "";
  var sale_round_val = "";
  var regular_round_type = "";
  var regular_round_val = "";
  jQuery("#main_var_disp").on("click", "#pop_close", function () {
    jQuery("#main_var_disp").fadeOut(350);
  });
  jQuery("#wrap_table").on("click", "#preview_back", function () {
    jQuery.ajax({
      type: "post",
      async: false,
      url: ajaxurl,
      data: {
        action: "elex_bep_update_checked_status",
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        operation: "delete"
      }
    });
    jQuery("input[id=bep_filter_select_unselect_all_products_checkbox]").attr("checked", "checked");
    jQuery("#wrap_table").css("display", "none");
    document.getElementById("wrap_table").hidden = true;
    document.getElementById("top_filter_tag").hidden = false;
    jQuery("#top_filter_tag").css("display", "block");
    jQuery("#step2").removeClass("active");
    jQuery("#step1").addClass("active");
  });
  jQuery("#finish_cancel, #undo_cancel").click(function () {
    Swal.fire({
      title: "Do you want to cancel the ongoing update",
      showCancelButton: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      reverseButtons: false,
      confirmButtonColor: "#aaa",
      cancelButtonColor: "#0085ba",
      confirmButtonText: js_obj.process_edit_alert_confirm_button,
      cancelButtonText: js_obj.process_edit_alert_cancel_button
    }).then(function (result) {
      if (result.isConfirmed === true) {
        window.location.reload();
      }
    });
  });
  // new checked update.
  jQuery("table.wp-list-table").unbind().on("change", "input.filter_product_checkbox", function () {
    var isChecked = false;
    if (this.checked) {
      isChecked = "true";
    } else {
      isChecked = "false";
    }
    var productID = this.id;
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        action: "elex_bep_update_checked_status",
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        operation: "update",
        checkbox_status: isChecked,
        checkbox_id: productID
      }
    });
  });
  jQuery("#wrap_table").on("click", "#process_edit", function () {
    var type_data = "";
    var category_data = "";
    var attribute_data = "";
    var attribute_value_data = "";
    var range_data = "";
    var desired_price_data = "";
    var minimum_price_data = "";
    var maximum_price_data = "";
    var sub_cat = "";
    type_data = jQuery("#product_type").val();
    stock_status = jQuery("#stock_status_id").val();
    category_data = jQuery("#category_select").chosen().val();
    attribute_data = getValue_attrib_name();
    if (jQuery("#subcat_check").is(":checked")) {
      sub_cat = true;
    }
    if (getValue_attrib_name() != "") {
      attribute_value_data = jQuery("#select_input_attributes").chosen().val();
    } else {
      attribute_value_data = "";
    }
    range_data = jQuery("#regular_price_range_select").val();
    if (jQuery("#regular_price_range_select").val() != "all") {
      if (jQuery("#regular_price_range_select").val() != "|")
        desired_price_data = jQuery("#regular_price_text_val").val();
      else {
        minimum_price_data = jQuery("#regular_price_min_text").val();
        maximum_price_data = jQuery("#regular_price_max_text").val();
      }
    }
    jQuery.ajax({
      url: ajaxurl,
      data: {
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        action: "eh_bep_count_products",
        query_all: true,
        sub_category: sub_cat,
        count_products: true,
        type: type_data,
        category: category_data,
        attribute: attribute_data,
        attribute_value: attribute_value_data,
        range: range_data,
        desired_price: desired_price_data,
        minimum_price: minimum_price_data,
        maximum_price: maximum_price_data
      },
      success: function (response) {
        filtered_ids = jQuery.parseJSON(response);
        chunk_data = chunkArray(filtered_ids, 100);
        jQuery(".loader").css("display", "none");
        var desc = "";
        if (filtered_ids.length === 0) {
          return alert('Please Select a product first');
        } else {
          desc = "Products Selected : " + (
            filtered_ids.length) + " " + (
              filtered_ids.length === 1
                ? "Product"
                : "Products");
        }
        Swal.fire({
          title: js_obj.process_edit_alert_title,
          html: desc,
          showCancelButton: true,
          allowOutsideClick: false,
          allowEscapeKey: false,
          confirmButtonColor: "#0085ba",
          confirmButtonText: js_obj.process_edit_alert_confirm_button,
          cancelButtonText: js_obj.process_edit_alert_cancel_button
        }).then(function (result) {
          if (result.isConfirmed === true) {
            document.getElementById("wrap_table").hidden = true;
            document.getElementById("top_filter_tag").hidden = true;
            document.getElementById("edit_product").hidden = false;
            jQuery("#step2").removeClass("active");
            jQuery("#step3").addClass("active");
            jQuery("#undo_update_html").empty();
            jQuery("#wrap_table").css("display", "none");
            jQuery("#edit_product").css("display", "block");
          }
        });
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  });
  jQuery("#edit_product").on("click", "#edit_back", function () {
    unchecked_products = 0;
    jQuery("#wrap_table").css("display", "block");
    jQuery("#edit_product").css("display", "none");
    document.getElementById("wrap_table").hidden = false;
    document.getElementById("edit_product").hidden = true;
    jQuery("#step3").removeClass("active");
    jQuery("#step2").addClass("active");
    jQuery("#add_undo_now_tooltip").trigger("mouseout");
  });
  jQuery("#undo_update_html").on("click", "#undo_cancel_button", function () {
    jQuery("#top_filter_tag").css("display", "block");
    document.getElementById("top_filter_tag").hidden = false;
    document.getElementById("wrap_table").hidden = true;
    jQuery("#step1").addClass("active");
    jQuery("#step3").removeClass("active");
    jQuery("#edit_product").css("display", "none");
    jQuery("#undo_update_html").empty();
    jQuery("html, body").animate({
      scrollTop: jQuery(".tab_bulk_edit").offset().top
    }, 1000);
    jQuery("#add_undo_now_tooltip").trigger("mouseout");
  });
  jQuery("#wrap_table").on("click", "#save_dislay_count_order", function () {
    jQuery("#save_dislay_count_order").prop("disabled", "disabled");
    var row_count = jQuery("#display_count_order").val();
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        action: "eh_bulk_edit_display_count",
        row_count: row_count
      },
      success: function (response) {
        bep_ajax_filter_products();
        jQuery("#save_dislay_count_order").removeAttr("disabled");
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  });

  //save settings tab fields
  jQuery("#save_filter_setting_fields").click(function () {
    var metas = "";
    if (!jQuery("#update_meta_values").val() == "") {
      metas = jQuery("#update_meta_values").val().split(",");
    }
    jQuery(".loader").css("display", "block");
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        action: "eh_bulk_edit_save_filter_setting_tab",
        metas_to_save: metas
      },
      success: function (response) {
        jQuery(".loader").css("display", "none");
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  });

  jQuery("#undo_display_update_button, #undo_update_finish_page").click(function () {
    jQuery(".loader").css("display", "block");
    document.getElementById("edit_product").hidden = true;

    document.getElementById("wrap_table").hidden = true;
    jQuery("#undo_update_html").empty();
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        action: "eh_bep_undo_html"
      },
      success: function (response) {
        undo_scheduled_job = 0;
        document.getElementById("top_filter_tag").hidden = true;
        jQuery(".loader").css("display", "none");
        jQuery("#step3").addClass("active");
        jQuery("#step1").removeClass("active");
        jQuery("#step5").removeClass("active");
        jQuery("#top_filter_tag").hide();
        jQuery("#edit_product").css("display", "none");
        jQuery("#wrap_table").css("display", "none");
        document.getElementById("update_logs").hidden = true;
        jQuery("#undo_update_html").html(response);
        jQuery(".tooltip").darkTooltip();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  });

  jQuery("#undo_update_html").on("click", "#undo_update_button", function () {
    Swal.fire({
      title: js_obj.undo_alert_title,
      text: js_obj.undo_alert_subtitle,
      showCancelButton: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      confirmButtonColor: "#0085ba",
      confirmButtonText: js_obj.undo_alert_confirm_button,
      cancelButtonText: js_obj.undo_alert_cancel_button
    }).then(function (result) {
      if (result.isConfirmed === true) {
        jQuery("#undo_update").css("display", "none");
        jQuery("#undo_update_logs").show();
        jQuery("#undo_logs_val").show;
        jQuery("#step3").removeClass("active");
        jQuery("#step5").addClass("active");
        jQuery("#undo_logs_loader").html('<img src="./images/loading.gif">');
        xa_undo_update();
      }
    });
  });
  jQuery("#edit_product").on("click", "#reset_update_button", function () {
    clear_edit_data();
    jQuery("html, body").animate({
      scrollTop: jQuery(".tab_bulk_edit").offset().top
    }, 1000);
  });
  jQuery("#data_table").on("change", "#regular_price_range_select", function () {
    var dom_bet = '<input type="text"style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_min_placeholder + '" id="regular_price_min_text"><input type="text" style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_max_placeholder + '" id="regular_price_max_text">';
    var dom_sing = '<input type="text" style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_desired_placeholder + '" id="regular_price_text_val">';
    switch (jQuery(this).val()) {
      case "|":
        jQuery("#regular_price_range_text").empty();
        jQuery("#regular_price_range_text").append(dom_bet);
        break;
      case "all":
        jQuery("#regular_price_range_text").empty();
        break;
      default:
        jQuery("#regular_price_range_text").empty();
        jQuery("#regular_price_range_text").append(dom_sing);
    }
  });

  jQuery("#data_table").on("change", "#product_title_select", function () {
    var dom_title = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Title Text" id="product_title_text_val">';
    jQuery("#product_title_text").empty();
    jQuery("#product_title_text").append(dom_title);
    if (jQuery("#product_title_select").val() == "title_regex") {
      jQuery("#regex_flags_field").show();
      jQuery("#regex_help_link").show();
    } else {
      jQuery("#regex_flags_field").hide();
      jQuery("#regex_help_link").hide();
    }
    if (jQuery(this).val() == "all") {
      jQuery("#product_title_text").empty();
    }
  });

  jQuery("#data_table").on("change", "#product_description_select", function () {
    var dom_description = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Description Text" id="product_description_text_val">';
    jQuery("#product_description_text").empty();
    jQuery("#product_description_text").append(dom_description);
    if (jQuery("#product_description_select").val() == "description_regex") {
      jQuery("#regex_flags_field_description").show();
      jQuery("#regex_help_link_description").show();
    } else {
      jQuery("#regex_flags_field_description").hide();
      jQuery("#regex_help_link_description").hide();
    }
    if (jQuery(this).val() == "all") {
      jQuery("#product_description_text").empty();
    }
  });

  jQuery("#data_table").on("change", "#product_short_description_select", function () {
    var dom_short_description = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Short Description Text" id="product_short_description_text_val">';
    jQuery("#product_short_description_text").empty();
    jQuery("#product_short_description_text").append(dom_short_description);
    if (jQuery("#product_short_description_select").val() == "short_description_regex") {
      jQuery("#regex_flags_field_short_description").show();
      jQuery("#regex_help_link_short_description").show();
    } else {
      jQuery("#regex_flags_field_short_description").hide();
      jQuery("#regex_help_link_short_description").hide();
    }
    if (jQuery(this).val() == "all") {
      jQuery("#product_short_description_text").empty();
    }
  });

  jQuery("#edit_product").on("change", "#title_action", function () {
    var dom_new = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_new_placeholder + '" id="title_textbox">';
    var dom_app = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_append_placeholder + '" id="title_textbox">';
    var dom_pre = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_prepand_placeholder + '" id="title_textbox">';
    var dom_rep = '<input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_title_replaceable_placeholder + '" id="replaceable_title_textbox"><input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_title_replace_placeholder + '" id="title_textbox">';
    var dom_reg_rep = '<input type="text" style="height:28px; width:36%;vertical-align:top;" placeholder="Pattern" id="regex_replaceable_title_textbox"><input type="text" style="height:28px;width:35%;vertical-align:top;" placeholder="Replacement" id="title_textbox">';
    switch (jQuery(this).val()) {
      case "append":
        jQuery("#title_text").empty();
        jQuery("#regex_flags_field_title").hide();
        jQuery("#regex_help_link_title").hide();
        jQuery("#title_text").append(dom_app);
        break;
      case "prepand":
        jQuery("#title_text").empty();
        jQuery("#regex_flags_field_title").hide();
        jQuery("#regex_help_link_title").hide();
        jQuery("#title_text").append(dom_pre);
        break;
      case "set_new":
        jQuery("#title_text").empty();
        jQuery("#regex_flags_field_title").hide();
        jQuery("#regex_help_link_title").hide();
        jQuery("#title_text").append(dom_new);
        break;
      case "replace":
        jQuery("#title_text").empty();
        jQuery("#regex_flags_field_title").hide();
        jQuery("#regex_help_link_title").hide();
        jQuery("#title_text").append(dom_rep);
        break;
      case "regex_replace":
        jQuery("#title_text").empty();
        jQuery("#regex_flags_field_title").show();
        jQuery("#regex_help_link_title").show();
        jQuery("#title_text").append(dom_reg_rep);
        break;
      default:
        jQuery("#regex_flags_field_title").hide();
        jQuery("#regex_help_link_title").hide();
        jQuery("#title_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#sku_action", function () {
    var dom_new = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_new_placeholder + '" id="sku_textbox">';
    var dom_app = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_append_placeholder + '" id="sku_textbox">';
    var dom_pre = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_prepand_placeholder + '" id="sku_textbox">';
    var dom_rep = '<input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_sku_replaceable_placeholder + '" id="replaceable_sku_textbox"><input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_sku_replace_placeholder + '" id="sku_textbox">';
    var dom_reg_rep = '<input type="text" style="height:28px;width:36%;vertical-align:top;" placeholder="Pattern" id="regex_replaceable_sku_textbox"><input type="text" style="height:28px;width:35%;vertical-align:top;" placeholder="Replacement" id="sku_textbox">';
    switch (jQuery(this).val()) {
      case "append":
        jQuery("#sku_text").empty();
        jQuery("#regex_flags_field_sku").hide();
        jQuery("#regex_help_link_sku").hide();
        jQuery("#sku_text").append(dom_app);
        break;
      case "prepand":
        jQuery("#sku_text").empty();
        jQuery("#regex_flags_field_sku").hide();
        jQuery("#regex_help_link_sku").hide();
        jQuery("#sku_text").append(dom_pre);
        break;
      case "set_new":
        jQuery("#sku_text").empty();
        jQuery("#regex_flags_field_sku").hide();
        jQuery("#regex_help_link_sku").hide();
        jQuery("#sku_text").append(dom_new);
        break;
      case "replace":
        jQuery("#sku_text").empty();
        jQuery("#regex_flags_field_sku").hide();
        jQuery("#regex_help_link_sku").hide();
        jQuery("#sku_text").append(dom_rep);
        break;
      case "regex_replace":
        jQuery("#sku_text").empty();
        jQuery("#regex_flags_field_sku").show();
        jQuery("#regex_help_link_sku").show();
        jQuery("#sku_text").append(dom_reg_rep);
        break;
      default:
        jQuery("#regex_flags_field_sku").hide();
        jQuery("#regex_help_link_sku").hide();
        jQuery("#sku_text").empty();
    }
  });

  jQuery("#edit_product").on("change", "#description_action", function () {
    if (jQuery(this).val() == "") {
      jQuery("#description_tr").hide();
    } else {
      jQuery("#description_tr").show();
    }
  });
  jQuery("#edit_product").on("change", "#short_description_action", function () {
    if (jQuery(this).val() == "") {
      jQuery("#short_description_tr").hide();
    } else {
      jQuery("#short_description_tr").show();
    }
  });

  jQuery("#edit_product").on("change", "#gallery_image_action", function () {
    if (jQuery(this).val() == "") {
      jQuery("#gallery_images_tr").hide();
    } else {
      jQuery("#gallery_images_tr").show();
    }
  });

  jQuery("#edit_product").on("change", "#stock_quantity_action", function () {
    var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="quantity_textbox">';
    var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="quantity_textbox">';
    var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="quantity_textbox">';
    switch (jQuery(this).val()) {
      case "add":
        jQuery("#stock_quantity_text").empty();
        jQuery("#stock_quantity_text").append(dom_add);
        break;
      case "sub":
        jQuery("#stock_quantity_text").empty();
        jQuery("#stock_quantity_text").append(dom_sub);
        break;
      case "replace":
        jQuery("#stock_quantity_text").empty();
        jQuery("#stock_quantity_text").append(dom_rep);
        break;
      default:
        jQuery("#stock_quantity_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#length_action", function () {
    var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="length_textbox">';
    var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="length_textbox">';
    var dom_rep = '<input type="text" style="height:28px;vertical-align:top;"  placeholder="' + js_obj.edit_rep_placeholder + '" id="length_textbox">';
    switch (jQuery(this).val()) {
      case "add":
        jQuery("#length_text").empty();
        jQuery("#length_text").append(dom_add);
        break;
      case "replace":
        jQuery("#length_text").empty();
        jQuery("#length_text").append(dom_rep);
        break;
      case "sub":
        jQuery("#length_text").empty();
        jQuery("#length_text").append(dom_sub);
        break;
      default:
        jQuery("#length_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#width_action", function () {
    var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="width_textbox">';
    var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="width_textbox">';
    var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="width_textbox">';
    switch (jQuery(this).val()) {
      case "add":
        jQuery("#width_text").empty();
        jQuery("#width_text").append(dom_add);
        break;
      case "sub":
        jQuery("#width_text").empty();
        jQuery("#width_text").append(dom_sub);
        break;
      case "replace":
        jQuery("#width_text").empty();
        jQuery("#width_text").append(dom_rep);
        break;
      default:
        jQuery("#width_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#height_action", function () {
    var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="height_textbox">';
    var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="height_textbox">';
    var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="height_textbox">';
    switch (jQuery(this).val()) {
      case "add":
        jQuery("#height_text").empty();
        jQuery("#height_text").append(dom_add);
        break;
      case "sub":
        jQuery("#height_text").empty();
        jQuery("#height_text").append(dom_sub);
        break;
      case "replace":
        jQuery("#height_text").empty();
        jQuery("#height_text").append(dom_rep);
        break;
      default:
        jQuery("#height_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#weight_action", function () {
    var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="weight_textbox">';
    var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="weight_textbox">';
    var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="weight_textbox">';
    switch (jQuery(this).val()) {
      case "add":
        jQuery("#weight_text").empty();
        jQuery("#weight_text").append(dom_add);
        break;
      case "sub":
        jQuery("#weight_text").empty();
        jQuery("#weight_text").append(dom_sub);
        break;
      case "replace":
        jQuery("#weight_text").empty();
        jQuery("#weight_text").append(dom_rep);
        break;
      default:
        jQuery("#weight_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#shipping_unit_action", function () {
    var dom_add = '<input type="text" placeholder="' + js_obj.edit_shipping_unit_add_placeholder + '" id="shipping_unit_textbox">';
    var dom_sub = '<input type="text" placeholder="' + js_obj.edit_shipping_unit_sub_placeholder + '" id="shipping_unit_textbox">';
    var dom_rep = '<input type="text" placeholder="' + js_obj.edit_shipping_unit_rep_placeholder + '" id="shipping_unit_textbox">';
    switch (jQuery(this).val()) {
      case "add":
        jQuery("#shipping_unit_text").empty();
        jQuery("#shipping_unit_text").append(dom_add);
        break;
      case "sub":
        jQuery("#shipping_unit_text").empty();
        jQuery("#shipping_unit_text").append(dom_sub);
        break;
      case "replace":
        jQuery("#shipping_unit_text").empty();
        jQuery("#shipping_unit_text").append(dom_rep);
        break;
      default:
        jQuery("#shipping_unit_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#manage_stock_action", function () {
    switch (jQuery(this).val()) {
      case "":
        jQuery("#manage_stock_check_text").empty();
        break;
      default:
        jQuery("#manage_stock_check_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#allow_backorder_action", function () {
    switch (jQuery(this).val()) {
      case "":
        jQuery("#backorder_text").empty();
        break;
      default:
        jQuery("#backorder_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#shipping_class_action", function () {
    switch (jQuery(this).val()) {
      case "":
        jQuery("#shipping_class_check_text").empty();
        break;
      default:
        jQuery("#shipping_class_check_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#sale_price_action", function () {
    var dom_up_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_per_placeholder + '" id="sale_textbox">';
    var dom_down_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_per_placeholder + '" id="sale_textbox">';
    var dom_up_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_pri_placeholder + '" id="sale_textbox">';
    var dom_down_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_pri_placeholder + '" id="sale_textbox">';
    var dom_flat_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_flat_pri_placeholder + '" id="sale_textbox">';
    var dom_round = '<select id="sale_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
    switch (jQuery(this).val()) {
      case "up_percentage":
        jQuery("#sale_price_text").empty();
        jQuery("#sale_price_text").append(dom_up_per);
        jQuery("#sale_price_text").append(dom_round);
        jQuery("#regular_checkbox").hide();
        break;
      case "down_percentage":
        jQuery("#sale_price_text").empty();
        jQuery("#sale_price_text").append(dom_down_per);
        jQuery("#sale_price_text").append(dom_round);
        jQuery("#regular_checkbox").show();

        break;
      case "up_price":
        jQuery("#sale_price_text").empty();
        jQuery("#sale_price_text").append(dom_up_pri);
        jQuery("#sale_price_text").append(dom_round);
        jQuery("#regular_checkbox").hide();
        break;
      case "down_price":
        jQuery("#sale_price_text").empty();
        jQuery("#sale_price_text").append(dom_down_pri);
        jQuery("#sale_price_text").append(dom_round);
        jQuery("#regular_checkbox").show();
        break;
      case "flat_all":
        jQuery("#sale_price_text").empty();
        jQuery("#sale_price_text").append(dom_flat_pri);
        jQuery("#regular_checkbox").hide();
        break;
      default:
        jQuery("#sale_price_text").empty();
        jQuery("#regular_checkbox").hide();
    }
  });

  jQuery("#edit_product").on("change", "#sale_round_select", function () {
    var dom_round = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_round_off + '" id="sale_round_textbox"> ';

    switch (jQuery(this).val()) {
      case "up":
        jQuery("#sale_round_textbox").remove();
        jQuery("#sale_price_text").append(dom_round);
        break;
      case "down":
        jQuery("#sale_round_textbox").remove();
        jQuery("#sale_price_text").append(dom_round);
        break;
      default:
        jQuery("#sale_round_textbox").remove();
    }
  });

  jQuery("#edit_product").on("change", "#regular_price_action", function () {
    var dom_up_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_per_placeholder + '" id="regular_textbox">';
    var dom_down_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_per_placeholder + '" id="regular_textbox">';
    var dom_up_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_pri_placeholder + '" id="regular_textbox">';
    var dom_down_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_pri_placeholder + '" id="regular_textbox">';
    var dom_flat_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_flat_pri_placeholder + '" id="regular_textbox">';
    var dom_round = '<select id="regular_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
    switch (jQuery(this).val()) {
      case "up_percentage":
        jQuery("#regular_price_text").empty();
        jQuery("#regular_price_text").append(dom_up_per);
        jQuery("#regular_price_text").append(dom_round);
        break;
      case "down_percentage":
        jQuery("#regular_price_text").empty();
        jQuery("#regular_price_text").append(dom_down_per);
        jQuery("#regular_price_text").append(dom_round);
        break;
      case "up_price":
        jQuery("#regular_price_text").empty();
        jQuery("#regular_price_text").append(dom_up_pri);
        jQuery("#regular_price_text").append(dom_round);
        break;
      case "down_price":
        jQuery("#regular_price_text").empty();
        jQuery("#regular_price_text").append(dom_down_pri);
        jQuery("#regular_price_text").append(dom_round);
        break;
      case "flat_all":
        jQuery("#regular_price_text").empty();
        jQuery("#regular_price_text").append(dom_flat_pri);
        break;
      default:
        jQuery("#regular_price_text").empty();
    }
  });
  jQuery("#edit_product").on("change", "#regular_round_select", function () {
    var dom_round = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_round_off + '" id="regular_round_textbox"> ';

    switch (jQuery(this).val()) {
      case "up":
        jQuery("#regular_round_textbox").remove();
        jQuery("#regular_price_text").append(dom_round);
        break;
      case "down":
        jQuery("#regular_round_textbox").remove();
        jQuery("#regular_price_text").append(dom_round);
        break;
      default:
        jQuery("#regular_round_textbox").remove();
    }
  });
  jQuery("#cat_update input:radio").click(function () {
    switch (jQuery(this).val()) {
      case "cat_none":
        jQuery("input[name='cat_update']").prop("checked", false);
        jQuery("#cat_select").hide();
        break;
      case "cat_add":
        jQuery("input[name='cat_update']").prop("checked", false);
        jQuery("#cat_select").show();
        break;
      case "cat_remove":
        jQuery("input[name='cat_update']").prop("checked", false);
        jQuery("#cat_select").show();
        break;
      case "cat_replace":
        jQuery("input[name='cat_update']").prop("checked", false);
        jQuery("#cat_select").show();
        break;
      default:
        jQuery("#cat_select").hide();
    }
  });

  jQuery("#edit_product").on("change", "#attribute_action", function () {
    jQuery("#attribu_name input:checked").each(function () {
      jQuery(this).removeAttr("checked");
    });
    jQuery("#add_attribute_value_select").remove();
    jQuery("#new_attr_values").remove();
    jQuery("#select_variation").remove();
    jQuery("#select_visible").remove();
    switch (jQuery(this).val()) {
      case "add":
        jQuery("#attr_names").show();
        break;
      case "remove":
        jQuery("#attr_names").show();
        break;
      case "replace":
        jQuery("#attr_names").show();
        break;
      default:
        jQuery("#attr_names").hide();
    }
  });

  jQuery("#attribu_name input[type='checkbox']").click(function () {
    var display = jQuery("#attribu_name input[type=checkbox]:checked").length;
    if (display == 0) {
      jQuery("#add_attribute_value_select").remove();
      jQuery("#new_attr_values").remove();
      jQuery("#select_variation").remove();
      jQuery("#select_visible").remove();
      document.getElementById("new_attr").innerHTML = "";
    } else {
      if (!jQuery("#add_attribute_value_select").length) {
        var tool_tip = "";
        var new_tool_tip = "";
        if (jQuery("#attribute_action").val() == "add") {
          tool_tip = "Choose an existing attribute value(s) to be added to the product attribute(s)";
          new_tool_tip = "Specify new values to be added to the selected attribute(s). Enter each value in a new line";
        }
        if (jQuery("#attribute_action").val() == "remove") {
          tool_tip = "Choose existing attribute value(s) to be removed from the product attribute(s)";
        }
        if (jQuery("#attribute_action").val() == "replace") {
          tool_tip = "Select existing attribute value(s) to be added to the product attribute(s). This will replace any already existing attribute value(s) from the product attribute";
          new_tool_tip = "Specify new values to be added to the selected attribute(s). Enter each value in a new line. This will replace any already existing attribute value(s) from the product attribute";
        }

        var dom = "<tr id='add_attribute_value_select'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + tool_tip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_add_attributes'></select></span></td><td style='width:38%;'></td></tr>";
        var dom_new_attr = "<tr id='new_attr_values'><td>" + "Attribute Values (New)" + "</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + new_tool_tip + "'></span></td><td><span class='select-eh' ><textarea  id='new_attribute_values_textarea' style='width:210px; height:66px;'></textarea></span></td></tr>";
        var dom_variation_check = "<tr id='select_variation'><td class='eh-edit-tab-table-left'>Used for Variations</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='Choose if selected attribute values are to be used for variations'></span></td> <td class='eh-edit-tab-table-input-td'> <select id='attr_variationa_action' style='width:210px;'><option value=''>< No Change ></option><option value='add'>Enable</option><option value='remove'>Disable</option></select></td></tr>";
        var dom_visible_check = "<tr id='select_visible'><td class='eh-edit-tab-table-left'>Used for Visible on the product page</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='Select an option to determine if selected attribute values are to be used for variations'></span></td> <td class='eh-edit-tab-table-input-td'> <select id='attr_visiblea_action' style='width:210px;'><option value=''>< No Change ></option><option value='add'>Enable</option><option value='remove'>Disable</option></select></td></tr>";
        jQuery("#attr_names").after(dom);
        jQuery(".attribute-chosen").chosen();
        jQuery(".tooltip").darkTooltip();
        if (jQuery("#attribute_action").val() == "add" || jQuery("#attribute_action").val() == "replace") {
          jQuery("#new_attr").after(dom_new_attr);
          jQuery("#variation_select").after(dom_variation_check);
          jQuery("#variation_select").after(dom_visible_check);
          jQuery(".tooltip").darkTooltip();
        } else {
          jQuery("#new_attr_values").remove();
          jQuery("#select_variation").remove();
          jQuery("#select_visible").remove();
        }
      }
      if (!jQuery(this).is(":checked")) {
        remove_edit_attribute_value(jQuery(this).val());
      } else {
        append_edit_attribute_value(jQuery(this).val());
      }
    }
  });

  function remove_edit_attribute_value(attrib_name) {
    var id = "#grp_" + attrib_name;
    jQuery(id).remove();
    jQuery(".attribute-chosen").trigger("chosen:updated");
  }

  function append_edit_attribute_value(attrib_name) {
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        action: "eh_bep_get_attributes_action",
        attrib: attrib_name
      },
      success: function (data) {
        jQuery("#select_input_add_attributes").append(data);
        jQuery(".attribute-chosen").trigger("chosen:updated");
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }

  function getValue_attribu_name() {
    var chkArray = [];
    jQuery("#attribu_name input:checked").each(function () {
      chkArray.push(jQuery(this).val());
    });
    var selected;
    selected = chkArray.join(",") + ",";
    if (selected.length > 1) {
      return selected.slice(0, -1);
    } else {
      return "";
    }
  }

  jQuery("#attrib_name input[type='checkbox']").click(function () {
    var display = jQuery("#attrib_name input[type=checkbox]:checked").length;
    if (display == 0) {
      jQuery("#attribute_value_select").remove();
    } else {
      if (!jQuery("#attribute_value_select").length) {
        var dom = "<tr id='attribute_value_select'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-content-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + js_obj.filter_attribute_value_tooltip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_attributes'></select></span></td></tr>";
        jQuery("#attribute_types").after(dom);
        jQuery(".attribute-chosen").chosen();
        jQuery(".tooltip").darkTooltip();
      }
      if (!jQuery(this).is(":checked")) {
        remove_attribute_value(jQuery(this).val());
      } else {
        append_attribute_value(jQuery(this).val());
      }
    }
  });

  function remove_attribute_value(attrib_name) {
    var id = "#grp_" + attrib_name;
    jQuery(id).remove();
    jQuery(".attribute-chosen").trigger("chosen:updated");
  }

  function append_attribute_value(attrib_name) {
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        action: "eh_bep_get_attributes_action",
        attrib: attrib_name
      },
      success: function (data) {
        jQuery("#select_input_attributes").append(data);
        jQuery(".attribute-chosen").trigger("chosen:updated");
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }

  jQuery("#attrib_name_and input[type='checkbox']").click(function () {
    var display = jQuery("#attrib_name_and input[type=checkbox]:checked").length;
    if (display == 0) {
      jQuery("#attribute_value_select_and").remove();
    } else {
      if (!jQuery("#attribute_value_select_and").length) {
        var dom = "<tr id='attribute_value_select_and'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-content-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + js_obj.filter_attribute_value_tooltip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_attributes_and'></select></span></td></tr>";
        jQuery("#attribute_types_and").after(dom);
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
        jQuery("#select_input_attributes_and").append(data);
        jQuery(".attribute-chosen").trigger("chosen:updated");
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }

  //Variation change attributes
  jQuery("#vari_attribu_name input[type='checkbox']").click(function () {
    var display = jQuery("#vari_attribu_name input[type=checkbox]:checked").length;
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
        action: "elex_variations_attribute_change",
        attrib: attrib_name
      },
      success: function (data) {
        jQuery(".loader").css("display", "none");
        jQuery("#variations_attribute_rows").after(data);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }

  jQuery("html").on("click", "#why_update_undo", function () {
    jQuery("html, body").animate({
      scrollTop: jQuery(".tab_bulk_edit").offset().top
    }, 1000);
  });
  jQuery("#update_logs").on("click", "#update_finished", function () {
    document.getElementById("update_logs").hidden = true;
    document.getElementById("wrap_table").hidden = false;
    jQuery("#update_logs").css("display", "none");
    jQuery("#wrap_table").css("display", "block");
    jQuery("#edit_product").css("display", "none");
    jQuery("html, body").animate({
      scrollTop: jQuery(".tab_bulk_edit").offset().top
    }, 1000);
    jQuery("#add_undo_now_tooltip").trigger("mouseout");
    bep_ajax_filter_products();
  });
  jQuery("#schedule_update_button").click(function () {
    if (jQuery("#elex_schedule_options").val() == "schedule_later") {
      var scheduled_date = jQuery("#schedule_date").val();
      if (scheduled_date == "") {
        jQuery("#schedule_date").addClass("input-error");
        return;
      }
    }
    Swal.fire({
      title: js_obj.process_update_alert_title,
      html: jQuery("#add_undo_now").is(":checked")
        ? "<span style='color:green;'>Undo operation is enabled for this Update.</span>"
        : "<span style='color:red;'>Undo operation is disabled for this Update.</span><span style='color:blue;padding-left:5px;cursor:pointer;' id='why_update_undo'>Why?</span>",
      showCancelButton: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      confirmButtonColor: "#0085ba",
      confirmButtonText: js_obj.process_update_alert_confirm_button,
      cancelButtonText: js_obj.process_update_alert_cancel_button
    }).then(function (result) {
      if (result.isConfirmed === true) {
        jQuery("#elex_schedule_field").hide();
        document.getElementById("edit_product").hidden = true;
        jQuery("#step4").removeClass("active");
        jQuery("#step5").addClass("active");
        jQuery("#logs_val").html("");
        jQuery("#edit_product").css("display", "none");
        jQuery("#update_logs").show();
        jQuery("#logs_val").show;
        jQuery("#logs_loader").html('<img src="./images/loading.gif">');
        update_index = 0;
        jQuery("#finish_cancel").show();
        jQuery("#update_finished").hide();
        jQuery("#undo_update_finish_page").hide();
        xa_update_products();
      }
    });
  });
  jQuery("#edit_product").on("click", "#update_button", function () {
    jQuery("#step3").removeClass("active");
    jQuery("#step4").addClass("active");
    jQuery("#edit_product").css("display", "none");

    jQuery("#title_textbox").removeClass("input-error");
    jQuery("#replaceable_title_textbox").removeClass("input-error");
    jQuery("#sku_textbox").removeClass("input-error");
    var title_vali = false;
    if (jQuery("#title_action").val() != "") {
      if (jQuery("#title_textbox").val() == "") {
        jQuery("#title_textbox").addClass("input-error");
      } else {
        title_vali = true;
      }
      if (jQuery("#title_action").val() == "replace") {
        if (jQuery("#replaceable_title_textbox").val() == "") {
          jQuery("#replaceable_title_textbox").addClass("input-error");
          title_vali = false;
        }
      }
      if (jQuery("#title_action").val() == "regex_replace") {
        if (jQuery("#regex_replaceable_title_textbox").val() == "") {
          jQuery("#regex_replaceable_title_textbox").addClass("input-error");
          title_vali = false;
        }
      }
    } else {
      title_vali = true;
    }
    var sku_vali = false;
    if (jQuery("#sku_action").val() != "") {
      if (jQuery("#sku_textbox").val() == "") {
        jQuery("#sku_textbox").addClass("input-error");
      } else {
        sku_vali = true;
      }
    } else {
      sku_vali = true;
    }
    jQuery("#quantity_textbox").removeClass("input-error");
    var quanity_vali = false;
    if (jQuery("#stock_quantity_action").val() != "") {
      if (!/^\d+$/.test(jQuery("#quantity_textbox").val())) {
        jQuery("#quantity_textbox").addClass("input-error");
      } else {
        quanity_vali = true;
      }
    } else {
      quanity_vali = true;
    }
    jQuery("#length_textbox").removeClass("input-error");
    jQuery("#width_textbox").removeClass("input-error");
    jQuery("#height_textbox").removeClass("input-error");
    jQuery("#weight_textbox").removeClass("input-error");
    var length_vali = false;
    var width_vali = false;
    var height_vali = false;
    var weight_vali = false;
    if (jQuery("#length_action").val() != "") {
      if (!jQuery.isNumeric(jQuery("#length_textbox").val())) {
        jQuery("#length_textbox").addClass("input-error");
      } else {
        length_vali = true;
      }
    } else {
      length_vali = true;
    }
    if (jQuery("#width_action").val() != "") {
      if (!jQuery.isNumeric(jQuery("#width_textbox").val())) {
        jQuery("#width_textbox").addClass("input-error");
      } else {
        width_vali = true;
      }
    } else {
      width_vali = true;
    }
    if (jQuery("#height_action").val() != "") {
      if (!jQuery.isNumeric(jQuery("#height_textbox").val())) {
        jQuery("#height_textbox").addClass("input-error");
      } else {
        height_vali = true;
      }
    } else {
      height_vali = true;
    }
    if (jQuery("#weight_action").val() != "") {
      if (!jQuery.isNumeric(jQuery("#weight_textbox").val())) {
        jQuery("#weight_textbox").addClass("input-error");
      } else {
        weight_vali = true;
      }
    } else {
      weight_vali = true;
    }
    jQuery("#sale_textbox").removeClass("input-error");
    jQuery("#regular_textbox").removeClass("input-error");
    var sale_vali = false;

    if (jQuery("#sale_price_action").val() != "" && jQuery("#sale_price_action").val() != "flat_all") {
      if (!jQuery.isNumeric(jQuery("#sale_textbox").val())) {
        jQuery("#sale_textbox").addClass("input-error");
      } else {
        sale_round_type = jQuery("#sale_round_select").val();
        sale_round_val = jQuery("#sale_round_textbox").val();
        sale_vali = true;
      }
    } else {
      sale_vali = true;
    }
    var regualr_vali = false;

    if (jQuery("#regular_price_action").val() != "" && jQuery("#regular_price_action").val() != "flat_all") {
      if (!jQuery.isNumeric(jQuery("#regular_textbox").val())) {
        jQuery("#regular_textbox").addClass("input-error");
      } else {
        regular_round_type = jQuery("#regular_round_select").val();
        regular_round_val = jQuery("#regular_round_textbox").val();
        regualr_vali = true;
      }
    } else {
      regualr_vali = true;
    }
    if (title_vali && sku_vali && quanity_vali && sale_vali && regualr_vali && length_vali && width_vali && height_vali && weight_vali) {
      jQuery("#elex_schedule_field").show();
    } else {
      jQuery("#update_logs").css("display", "none");
      jQuery("#edit_product").css("display", "block");

      document.getElementById("edit_product").hidden = false;
      jQuery("#step5").removeClass("active");
      jQuery("#step3").addClass("active");
      if (!title_vali || !sku_vali) {
        jQuery("html, body").animate({
          scrollTop: jQuery("#edit_product").offset().top
        }, 1000);
      } else if (!sale_vali || !regualr_vali) {
        jQuery("html, body").animate({
          scrollTop: jQuery("#update_general_table").offset().top
        }, 1000);
      } else if (!quanity_vali) {
        jQuery("html, body").animate({
          scrollTop: jQuery("#update_price_table").offset().top
        }, 1000);
      } else if (!length_vali || !width_vali || !height_vali || !weight_vali) {
        jQuery("html, body").animate({
          scrollTop: jQuery("#update_stock_table").offset().top
        }, 1000);
      }
    }
  });
  jQuery("#edit_cancel, #clear_filter_button, #preview_cancel, #elex_schedule_cancel").click(function () {
    Swal.fire({
      title: js_obj.clear_product_alert_title,
      text: js_obj.clear_product_alert_subtitle,
      showCancelButton: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      confirmButtonColor: "#0085ba",
      confirmButtonText: js_obj.clear_product_alert_confirm_button,
      cancelButtonText: js_obj.clear_product_alert_cancel_button
    }).then(function (result) {
      if (result.isConfirmed === true) {
        jQuery(".loader").css("display", "block");
        jQuery.ajax({
          type: "post",
          url: ajaxurl,
          data: {
            _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
            action: "eh_bep_clear_products"
          },
          success: function (response) {
            jQuery.ajax({
              type: "post",
              url: ajaxurl,
              data: {
                action: "elex_bep_update_checked_status",
                _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
                operation: "delete"
              }
            });
            jQuery("input[id=bep_filter_select_unselect_all_products_checkbox]").attr("checked", "checked");
            jQuery("#undo_update_html").empty();
            jQuery("#wrap_table").css("display", "none");
            jQuery("#edit_product").css("display", "none");
            jQuery("#top_filter_tag").css("display", "block");
            jQuery("#elex_schedule_field").hide();
            jQuery("#step2").removeClass("active");
            jQuery("#step3").removeClass("active");
            jQuery("#step4").removeClass("active");
            document.getElementById("wrap_table").hidden = true;
            document.getElementById("top_filter_tag").hidden = false;
            jQuery("#step1").addClass("active");
            jQuery(".loader").css("display", "none");
            clear_filters();
            var response = jQuery.parseJSON(response);
            if (response.rows.length)
              jQuery("#the-list").html(response.rows);
            if (response.column_headers.length)
              jQuery("thead tr, tfoot tr").html(response.column_headers);
            if (response.pagination.bottom.length)
              jQuery(".tablenav.top .tablenav-pages").html(jQuery(response.pagination.top).html());
            if (response.pagination.top.length)
              jQuery(".tablenav.bottom .tablenav-pages").html(jQuery(response.pagination.bottom).html());
            list.init();
          },
          error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
          }
        });
      }
    });
  });

  jQuery("#filter_products_button").click(function () {
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        action: "elex_bep_update_checked_status",
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        operation: "delete"
      }
    });
    bep_ajax_filter_products();
  });

  //Schedule feature
  jQuery("#elex_schedule_options").change(function () {
    if (jQuery(this).val() == "bulk_update_now") {
      jQuery("#elex_schedule_date_and_time").hide();
      jQuery("#elex_undo_enable_field").show();
      jQuery("#elex_revert_date_and_time").hide();
      jQuery("#elex_save_job_checkbox").show();
      jQuery("#elex_save_job_text").show();
      jQuery("#schedule_frequency_options").hide();
      jQuery("#select_days_weekly").hide();
      jQuery("#select_days_monthly").hide();
      jQuery("#schedule_frequency").val("");
      jQuery("#stop_schedule_field").hide();
    } else {
      jQuery("#elex_schedule_date_and_time").show();
      jQuery("#elex_undo_enable_field").hide();
      jQuery("#elex_revert_date_and_time").show();
      jQuery("#elex_save_job_checkbox").hide();
      jQuery("#elex_save_job_text").hide();
      jQuery("#schedule_frequency_options").show();
    }
  });

  jQuery("#elex_schedule_back").click(function () {
    jQuery("#elex_schedule_field").hide();
    jQuery("#edit_product").show();
    jQuery("#step4").removeClass("active");
    jQuery("#step3").addClass("active");
  });

  jQuery("#schedule_frequency").change(function () {
    if (jQuery(this).val() == "weekly") {
      jQuery("#select_days_weekly").show();
      jQuery("#select_days_monthly").hide();
      jQuery("#stop_schedule_field").show();
    } else if (jQuery(this).val() == "monthly") {
      jQuery("#select_days_weekly").hide();
      jQuery("#select_days_monthly").show();
      jQuery("#stop_schedule_field").show();
    } else if (jQuery(this).val() == "daily") {
      jQuery("#select_days_weekly").hide();
      jQuery("#select_days_monthly").hide();
      jQuery("#stop_schedule_field").show();
    } else {
      jQuery("#select_days_weekly").hide();
      jQuery("#select_days_monthly").hide();
      jQuery("#stop_schedule_field").hide();
    }
  });

  function xa_update_products() {
    var undo_update = jQuery("#add_undo_now").is(":checked") ? "yes" : "";
    var title_select_data = jQuery("#title_action").val();
    var sku_select_data = jQuery("#sku_action").val();
    var catalog_select_data = jQuery("#catalog_action").val();
    var featured_product = jQuery("#is_featured").val();
    var is_product_type = jQuery("#is_product_type").val();
    var shipping_select_data = jQuery("#shipping_class_action").val();
    var sale_select_data = jQuery("#sale_price_action").val();
    var regular_select_data = jQuery("#regular_price_action").val();
    var stock_manage_select_data = jQuery("#manage_stock_action").val();
    var quantity_select_data = jQuery("#stock_quantity_action").val();
    var backorder_select_data = jQuery("#allow_backorder_action").val();
    var stock_status_select_data = jQuery("#stock_status_action").val();
    var length_select_data = jQuery("#length_action").val();
    var width_select_data = jQuery("#width_action").val();
    var height_select_data = jQuery("#height_action").val();
    var weight_select_data = jQuery("#weight_action").val();
    var shipping_unit_select_data = jQuery("#shipping_unit_action").val() == undefined
      ? ""
      : jQuery("#shipping_unit_action").val();

    var hide_price_select = jQuery("#visibility_price").val() == undefined
      ? ""
      : jQuery("#visibility_price").val();
    var hide_price_role_select = jQuery("#hide_price_role_select").val() == undefined
      ? ""
      : jQuery("#hide_price_role_select").chosen().val();
    var price_adjustment_select = jQuery("#price_adjustment_action").val() == undefined
      ? ""
      : jQuery("#price_adjustment_action").val();
    var title_text_data = jQuery("#title_textbox").val() == undefined
      ? ""
      : jQuery("#title_textbox").val();
    var replace_title_text_data = jQuery("#replaceable_title_textbox").val() == undefined
      ? ""
      : jQuery("#replaceable_title_textbox").val();
    var regex_replace_title_text_data = jQuery("#regex_replaceable_title_textbox").val() == undefined
      ? ""
      : jQuery("#regex_replaceable_title_textbox").val();
    var sku_text_data = jQuery("#sku_textbox").val() == undefined
      ? ""
      : jQuery("#sku_textbox").val();
    var replace_sku_text_data = jQuery("#replaceable_sku_textbox").val() == undefined
      ? ""
      : jQuery("#replaceable_sku_textbox").val();
    var regex_replace_sku_text_data = jQuery("#regex_replaceable_sku_textbox").val() == undefined
      ? ""
      : jQuery("#regex_replaceable_sku_textbox").val();
    var sale_text_data = jQuery("#sale_textbox").val() == undefined
      ? ""
      : jQuery("#sale_textbox").val();
    var regular_text_data = jQuery("#regular_textbox").val() == undefined
      ? ""
      : jQuery("#regular_textbox").val();
    var quantity_text_data = jQuery("#quantity_textbox").val() == undefined
      ? ""
      : jQuery("#quantity_textbox").val();
    var length_text_data = jQuery("#length_textbox").val() == undefined
      ? ""
      : jQuery("#length_textbox").val();
    var width_text_data = jQuery("#width_textbox").val() == undefined
      ? ""
      : jQuery("#width_textbox").val();
    var height_text_data = jQuery("#height_textbox").val() == undefined
      ? ""
      : jQuery("#height_textbox").val();
    var weight_text_data = jQuery("#weight_textbox").val() == undefined
      ? ""
      : jQuery("#weight_textbox").val();
    var shipping_unit_text_data = jQuery("#shipping_unit_textbox").val() == undefined
      ? ""
      : jQuery("#shipping_unit_textbox").val();
    var custom_meta_value = [];

    var type_data = "";
    var custom_attribute_data = "";
    var category_data = "";
    var attribute_data = "";
    var attribute_value_data = "";
    var range_data = "";
    var desired_price_data = "";
    var minimum_price_data = "";
    var maximum_price_data = "";
    var tax_status_actions = jQuery("#tax_status_action").val();
    var tax_class_actions = jQuery("#tax_class_action").val();
    var regex_flag_title = jQuery("#regex_flags_values_title").val() == undefined
      ? ""
      : jQuery("#regex_flags_values_title").val();
    var regex_flag_sku = jQuery("#regex_flags_values_sku").val() == undefined
      ? ""
      : jQuery("#regex_flags_values_sku").val();
    type_data = jQuery("#product_type").val();
    stock_status = jQuery("#stock_status_id").val();
    custom_attribute_data = jQuery("#elex_select_custom_attribute").val();
    category_data = jQuery("#category_select").chosen().val();
    attribute_data = getValue_attribu_name();

    if (getValue_attribu_name() != "")
      attribute_value_data = jQuery("#select_input_add_attributes").chosen().val();
    else {
      attribute_value_data = "";
    }
    var att_action = jQuery("#attribute_action").val();
    range_data = jQuery("#regular_price_range_select").val();
    if (jQuery("#regular_price_range_select").val() != "all") {
      if (jQuery("#regular_price_range_select").val() != "|")
        desired_price_data = jQuery("#regular_price_text_val").val();
      else {
        minimum_price_data = jQuery("#regular_price_min_text").val();
        maximum_price_data = jQuery("#regular_price_max_text").val();
      }
    }
    var new_attrib_val = "";
    var att_variation = "";
    var att_visible = "";
    if (jQuery("#attribute_action").val() == "add" || jQuery("#attribute_action").val() == "replace") {
      if (jQuery("#new_attribute_values_textarea").length && jQuery("#new_attribute_values_textarea").val() != "") {
        new_attrib_val = jQuery("#new_attribute_values_textarea").val().split("\n");
      }
      att_variation = jQuery("#attr_variationa_action").val();
      att_visible = jQuery("#attr_visiblea_action").val();
    }

    //categroy update feature
    var sel_categories_to_update = [];
    jQuery.each(jQuery("input[name='cat_update']:checked"), function () {
      sel_categories_to_update.push(jQuery(this).val());
    });
    var cat_update_option = jQuery("input[name='edit_category']:checked").val();

    //custom meta fields
    jQuery('input[name="meta_keys"]').each(function () {
      custom_meta_value.push(jQuery(this).val());
    });
    var metas = [];
    if (edit_job) {
      metas = prev_metas;
    } else {
      if (jQuery("#update_meta_values").val() != "") {
        metas = jQuery("#update_meta_values").val().split(",");
      }
    }

   
    //use regular value
    var use_regular_val = 0;
    if(jQuery('#regular_val_check').is(":checked")){
        use_regular_val = 1;
    }else {
        use_regular_val = 0;
    }

    //schedule
    var schedule_action = jQuery("#elex_schedule_options").val();
    var save_job_check = true;
    var scheduled_date = "";
    var schedule_hour = "";
    var schedule_min = "";
    var revert_date = "";
    var revert_hour = "";
    var revert_min = "";
    if (schedule_action == "bulk_update_now") {
      if (jQuery("#elex_save_job_checkbox").is(":checked")) {
        save_job_check = true;
      } else {
        save_job_check = false;
      }
    } else {
      scheduled_date = jQuery("#schedule_date").val();
      revert_date = jQuery("#revert_date").val();
      schedule_hour = jQuery("#schedule_hr").val();
      schedule_min = jQuery("#schedule_min").val();
      revert_hour = jQuery("#revert_hr").val();
      revert_min = jQuery("#revert_min").val();
    }

    var schedule_frequency_action = jQuery("#schedule_frequency").val();
    var schedule_weekly_days = jQuery("#schedule_days_weekly").val();
    var schedule_monthly_days = jQuery("#schedule_days_monthly").val();
    var stop_schedule_date = jQuery("#stop_schedule_date").val();
    var stop_hr = jQuery("#stop_hr").val();
    var stop_min = jQuery("#stop_min").val();

    var schedule_name = jQuery("#schedule_name").val();
    var log_file = false;
    if (jQuery("#create_log_file").is(":checked")) {
      log_file = true;
    }

    var category_data_filter = [];
    jQuery.each(jQuery("input[name='cat_filter']:checked"), function () {
      category_data_filter.push(jQuery(this).val());
    });

    //exclude products
    var ids_to_exclude_filter = "";
    if (jQuery("#exclude_ids").length && jQuery("#exclude_ids").val() != "") {
      ids_to_exclude_filter = jQuery("#exclude_ids").val().split(",");
    }

    var cats_to_exclude_filter = [];
    var exclude_sub_cat_filter = "";
    var exclude_prods_filter = "";
    if (jQuery("#enable_exclude_products").is(":checked")) {
      exclude_prods_filter = 1;
      jQuery.each(jQuery("input[name='cat_exclude']:checked"), function () {
        cats_to_exclude_filter.push(jQuery(this).val());
      });
      if (jQuery("#exclude_subcat_check").is(":checked")) {
        exclude_sub_cat_filter = 1;
      }
    }
    var attribute_value_data_and = "";
    if (getValue_attrib_name_and() != "")
      attribute_value_data_and = jQuery("#select_input_attributes_and").chosen().val();
    else {
      attribute_value_data_and = "";
    }

    var attribute_value_data_or = "";
    if (getValue_attrib_name() != "")
      attribute_value_data_or = jQuery("#select_input_attributes").chosen().val();
    else {
      attribute_value_data_or = "";
    }

    var sub_cat_filter = "";
    if (jQuery("#subcat_check").is(":checked")) {
      sub_cat_filter = true;
    }
    var regex_flag_values = "";
    var prod_title_select = jQuery("#product_title_select").val();
    if (prod_title_select == "title_regex") {
      regex_flag_values = jQuery("#regex_flags_values").val();
    }
    var prod_title_text = "";
    if (jQuery("#product_title_select").val() != "all") {
      prod_title_text = jQuery("#product_title_text_val").val();
    }

    var regex_flag_values_description = "";
    var prod_description_select = jQuery("#product_description_select").val();
    if (prod_description_select == "description_regex") {
      regex_flag_values_description = jQuery("#regex_flags_values_description").val();
    }
    var prod_description_text = "";
    if (jQuery("#product_description_select").val() != "all") {
      prod_description_text = jQuery("#product_description_text_val").val();
    }

    var regex_flag_values_short_description = "";
    var prod_short_description_select = jQuery("#product_short_description_select").val();
    if (prod_short_description_select == "short_description_regex") {
      regex_flag_values_short_description = jQuery("#regex_flags_values_short_description").val();
    }
    var prod_short_description_text = "";
    if (jQuery("#product_short_description_select").val() != "all") {
      prod_short_description_text = jQuery("#product_short_description_text_val").val();
    }

    var tags = jQuery("#elex_product_tags").val();

    //Change atirbute for variations
    var attribute_change_arr = [];
    jQuery("input[name=vari_attribu_name]:checked").each(function () {
      var existing_attr = jQuery("#vari_attr_change_" + jQuery(this).val()).val();
      var attr_to_change = jQuery("#vari_attr_to_change_" + jQuery(this).val()).val();
      var combined_attr = existing_attr + "," + attr_to_change;
      attribute_change_arr.push(combined_attr);
    });

    //descriptions
    var product_description_action = jQuery("#description_action").val();
    var product_short_description_action = jQuery("#short_description_action").val();
    if (tinyMCE.get("elex_product_description") != null) {
      var product_description = tinyMCE.get("elex_product_description").getContent();
    } else {
      var product_description = jQuery("#elex_product_description").val();
    }

    if (tinyMCE.get("elex_product_short_description") != null) {
      var product_short_description = tinyMCE.get("elex_product_short_description").getContent();
    } else {
      var product_short_description = jQuery("#elex_product_short_description").val();
    }

    //Delete products
    var delete_product = jQuery("#delete_product_action").val();

    //Product image
    var prod_image = jQuery("#elex_product_main_image").val();
    var prod_gallery_images = "";
    var gallery_image_action = jQuery("#gallery_image_action").val();
    if (jQuery("#elex_product_gallery_images").length && jQuery("#elex_product_gallery_images").val() != "" && gallery_image_action != "") {
      prod_gallery_images = jQuery("#elex_product_gallery_images").val().split(",");
    }
    var unchecked_products = jQuery.ajax({
      type: "post",
      url: ajaxurl,
      async: false,
      data: {
        action: "elex_bep_update_checked_status",
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        operation: "count"
      },
      success: function (data) {
        return data;
      }
    }).responseText;
    if (parseInt(unchecked_products) >= 1) { // Set exclude_prods_filter to 1, when any of the checkbox is unselected.
      exclude_prods_filter = 1;
    }
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        action: "eh_bep_update_products",
        query_all: true,
        type: type_data,
        custom_attribute: custom_attribute_data,
        category: category_data,
        pid: chunk_data[update_index],
        index_val: update_index,
        chunk_length: chunk_data.length,
        attribute: attribute_data,
        attribute_value: attribute_value_data,
        attribute_action: att_action,
        new_attribute_values: new_attrib_val,
        attribute_variation: att_variation,
        attr_visible_action: att_visible,
        tax_status_action: tax_status_actions,
        tax_class_action: tax_class_actions,
        categories_to_update: sel_categories_to_update,
        category_update_option: cat_update_option,
        range: range_data,
        desired_price: desired_price_data,
        minimum_price: minimum_price_data,
        maximum_price: maximum_price_data,
        undo_update_op: undo_update,
        shipping_unit: shipping_unit_text_data,
        shipping_unit_select: shipping_unit_select_data,
        title_select: title_select_data,
        sku_select: sku_select_data,
        catalog_select: catalog_select_data,
        is_featured: featured_product,
        is_product_type: is_product_type,
        shipping_select: shipping_select_data,
        sale_select: sale_select_data,
        sale_round_select: sale_round_type,
        regular_check_val: use_regular_val,
        regular_round_select: regular_round_type,
        regular_select: regular_select_data,
        stock_manage_select: stock_manage_select_data,
        quantity_select: quantity_select_data,
        backorder_select: backorder_select_data,
        stock_status_select: stock_status_select_data,
        length_select: length_select_data,
        width_select: width_select_data,
        height_select: height_select_data,
        weight_select: weight_select_data,
        title_text: title_text_data,
        replace_title_text: replace_title_text_data,
        regex_replace_title_text: regex_replace_title_text_data,
        sku_text: sku_text_data,
        sku_replace_text: replace_sku_text_data,
        regex_sku_replace_text: regex_replace_sku_text_data,
        sale_text: sale_text_data,
        sale_round_text: sale_round_val,
        regular_round_text: regular_round_val,
        regular_text: regular_text_data,
        quantity_text: quantity_text_data,
        length_text: length_text_data,
        width_text: width_text_data,
        height_text: height_text_data,
        weight_text: weight_text_data,
        hide_price: hide_price_select,
        hide_price_role: hide_price_role_select,
        price_adjustment: price_adjustment_select,
        regex_flag_sele_title: regex_flag_title,
        regex_flag_sele_sku: regex_flag_sku,
        custom_meta: custom_meta_value,
        meta_fields: metas,

        scheduled_action: schedule_action,
        save_job: save_job_check,
        schedule_date: scheduled_date,
        revert_date: revert_date,
        scheduled_hour: schedule_hour,
        scheduled_min: schedule_min,
        revert_hour: revert_hour,
        revert_min: revert_min,
        schedule_frequency_action: schedule_frequency_action,
        schedule_weekly_days: schedule_weekly_days,
        schedule_monthly_days: schedule_monthly_days,
        stop_schedule_date: stop_schedule_date,
        stop_hr: stop_hr,
        stop_min: stop_min,
        job_name: schedule_name,
        create_log_file: log_file,
        is_edit_job: edit_job,

        category_filter: category_data_filter,
        sub_category_filter: sub_cat_filter,
        attribute_value_filter: attribute_value_data_or,
        attribute_value_and_filter: attribute_value_data_and,
        exclude_ids: ids_to_exclude_filter,
        exclude_categories: cats_to_exclude_filter,
        exclude_subcat_check: exclude_sub_cat_filter,
        enable_exclude_prods: exclude_prods_filter,
        product_title_select: prod_title_select,
        product_title_text: prod_title_text,
        regex_flags: regex_flag_values,
        product_description_select: prod_description_select,
        product_description_text: prod_description_text,
        regex_flags_description: regex_flag_values_description,
        product_short_description_select: prod_short_description_select,
        product_short_description_text: prod_short_description_text,
        regex_flags_short_description: regex_flag_values_short_description,
        vari_attribute: attribute_change_arr,
        description_action: product_description_action,
        short_description_action: product_short_description_action,
        description: product_description,
        short_description: product_short_description,
        delete_product_action: delete_product,
        prod_tags: tags,
        main_image: prod_image,
        gallery_images_action: gallery_image_action,
        gallery_images: prod_gallery_images,
      },
      success: function (response) {
        var unchecked_products = jQuery.ajax({
          type: "post",
          url: ajaxurl,
          async: false,
          data: {
            action: "elex_bep_update_checked_status",
            _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
            operation: "count"
          },
          success: function (data) {
            return data;
          }
        }).responseText;
        var d = new Date();
        d = d.toUTCString();
        if (response == "part_scheduled") {
          jQuery("#logs_val").append("<b>" + d + "</b> " + "          " + "<b style='color:blue;' >" + (update_index + 1) * 100 + "</b> products scheduled, <b style='color:blue;' >" + (filtered_ids.length - parseInt(unchecked_products) - (update_index + 1) * 100) + "</b> products remaining...<br><br>");
          jQuery("#logs_loader").html('<img src="./images/loading.gif">');
          update_index++;
          xa_update_products();
        } else if (response == "scheduled") {
          jQuery("#logs_loader").html("<br>The bulk edit job is successfully scheduled. You can view the scheduled job in the Jobs tab.");
          jQuery("#update_finished").show();
          jQuery("#finish_cancel").hide();
        } else {
          product_ids = jQuery.parseJSON(response);
          var resp_length = product_ids.length;
          jQuery(".loader").css("display", "none");
          if (product_ids[resp_length - 1] != "done") {
            jQuery("#logs_val").append("<b>" + d + "</b> " + "<b style='color:blue;' >" + (update_index + 1) * 100 + "</b> products updated, <b style='color:blue;' >" + ((filtered_ids.length - parseInt(unchecked_products)) - ((update_index + 1) * 100)) + "</b> products remaining...<br><br>");
            update_index++;
            if (product_ids != "") {
              xa_warning_display(product_ids);
            }
            jQuery("#logs_loader").html('<img src="./images/loading.gif">');
            xa_update_products();
          } else {
            xa_warning_display(product_ids);
            jQuery("#logs_loader").html("All products updated<br><br>");
            jQuery("#update_finished").show();
            jQuery("#undo_update_finish_page").show();
            jQuery("#finish_cancel").hide();
          }
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }
});

//if the Sale price is greater then Regular.
function xa_warning_display(products_skipped) {
  var id_length = products_skipped.length;
  if (products_skipped[id_length - 1] == "done") {
    id_length--;
  }
  for (var i = 0; i < id_length; i++) {
    var pr_id_link = products_skipped[i].link("./post.php?post=" + products_skipped[i + 1] + "&action=edit");
    pr_id_link = pr_id_link.replace("<a", "<a target=_blank");
    if (products_skipped[i + 3] == "variable") {
      jQuery("#logs_val").append("<b>[Warning]</b> Skipping updation of " + products_skipped[i + 2] + " Price for the Product " + pr_id_link + " as it is a Variable Parent Product.<br><br>");
    } else {
      jQuery("#logs_val").append("<b>[Warning]</b> Skipping updation of " + products_skipped[i + 2] + " Price for the Product " + pr_id_link + " as Sales Price set is greater than Regular Price.<br><br>");
    }
    i = i + 3;
  }
  jQuery("#logs_val").append("<br><br>");
}

function get_bulk_undo_fields() {
  var chkArray = [];
  jQuery('input[name="undo_checkbox_values"]:checked').each(function () {
    chkArray.push(jQuery(this).val());
  });
  var selected;
  selected = chkArray.join(",") + ",";
  if (selected.length > 1) {
    return selected.slice(0, -1);
  } else {
    return "";
  }
}

function xa_undo_update() {
  jQuery.ajax({
    type: "post",
    url: ajaxurl,
    data: {
      _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
      action: "eh_bep_undo_update",
      index: undo_index,
      undo_values: get_bulk_undo_fields(),
      undo_sch_job: undo_scheduled_job,
      file: file_to_undo
    },
    success: function (response) {
      jQuery(".loader").css("display", "none");
      var d = new Date();
      d = d.toUTCString();
      if (response != "done") {
        jQuery("#undo_logs_val").append("<b>" + d + "</b> " + (
          undo_index + 1) * 100 + " products updated," + (
            response - (undo_index + 1) * 100) + " products remaining...<br><br>");
        jQuery("#undo_logs_loader").html('<img src="./images/loading.gif">');
        undo_index++;
        xa_undo_update();
      } else {
        jQuery("#undo_logs_loader").html("All products updated");
        Swal.fire({
          title: js_obj.undo_success_alert_title,
          type: "success",
          allowOutsideClick: false,
          allowEscapeKey: false,
          confirmButtonColor: "#3085d6",
          confirmButtonText: js_obj.edit_success_alert_button
        }).then(function (result) {
          if (result.isConfirmed === true) {
            bep_ajax_filter_products();
            jQuery("#add_undo_button_tooltip").trigger("mouseout");
            jQuery("#edit_product").css("display", "none");
            jQuery("#undo_update_html").empty();
            jQuery("html, body").animate({
              scrollTop: jQuery(".tab_bulk_edit").offset().top
            }, 1000);
            jQuery("#add_undo_now_tooltip").trigger("mouseout");
          }
        });
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}

function bep_ajax_filter_products() {
  jQuery(".loader").css("display", "block");
  var type_data = "";
  var custom_attribute_data = "";
  var tags = "";
  var attribute_data = "";
  var attribute_value_data = "";
  var attribute_value_data_and = "";
  var range_data = "";
  var desired_price_data = "";
  var minimum_price_data = "";
  var maximum_price_data = "";
  var attribute_data_and = "";
  var sub_cat = "";
  var regex_flag_values = "";

  type_data = jQuery("#product_type").val();
  stock_status = jQuery("#stock_status_id").val();
  custom_attribute_data = jQuery("#elex_select_custom_attribute").val();
  tags = jQuery("#elex_product_tags").val();
  //category_data = (jQuery("#category_select").chosen().val());
  var category_data = [];
  jQuery.each(jQuery("input[name='cat_filter']:checked"), function () {
    category_data.push(jQuery(this).val());
  });
  attribute_data = getValue_attrib_name();
  attribute_data_and = getValue_attrib_name_and();
  if (jQuery("#subcat_check").is(":checked")) {
    sub_cat = true;
  }
  if (getValue_attrib_name() != "") {
    attribute_value_data = jQuery("#select_input_attributes").chosen().val();
  } else {
    attribute_value_data = "";
  }
  if (getValue_attrib_name_and() != "") {
    attribute_value_data_and = jQuery("#select_input_attributes_and").chosen().val();
  } else {
    attribute_value_data_and = "";
  }

  if (jQuery("#regular_price_range_select").val() != "all") {
    if (jQuery("#regular_price_range_select").val() != "|")
      desired_price_data = jQuery("#regular_price_text_val").val();
    else {
      minimum_price_data = jQuery("#regular_price_min_text").val();
      maximum_price_data = jQuery("#regular_price_max_text").val();
    }
  }
  if (desired_price_data != "" || minimum_price_data != "" || maximum_price_data != "") {
    range_data = jQuery("#regular_price_range_select").val();
  }
  var prod_title_select = jQuery("#product_title_select").val();
  if (prod_title_select == "title_regex") {
    regex_flag_values = jQuery("#regex_flags_values").val();
  }
  var prod_title_text = "";
  if (jQuery("#product_title_select").val() != "all") {
    prod_title_text = jQuery("#product_title_text_val").val();
  }
  var regex_flag_values_description = "";
  var prod_description_select = jQuery("#product_description_select").val();
  if (prod_description_select == "description_regex") {
    regex_flag_values_description = jQuery("#regex_flags_values_description").val();
  }
  var prod_description_text = "";
  if (jQuery("#product_description_select").val() != "all") {
    prod_description_text = jQuery("#product_description_text_val").val();
  }

  var regex_flag_values_short_description = "";
  var prod_short_description_select = jQuery("#product_short_description_select").val();
  if (prod_short_description_select == "short_description_regex") {
    regex_flag_values_short_description = jQuery("#regex_flags_values_short_description").val();
  }
  var prod_short_description_text = "";
  if (jQuery("#product_short_description_select").val() != "all") {
    prod_short_description_text = jQuery("#product_short_description_text_val").val();
  }
  //exclude products
  var ids_to_exclude = "";
  if (jQuery("#exclude_ids").length && jQuery("#exclude_ids").val() != "") {
    ids_to_exclude = jQuery("#exclude_ids").val().split(",");
  }
  var cats_to_exclude = [];
  var exclude_sub_cat = 0;
  var exclude_prods = 0;
  if (jQuery("#enable_exclude_products").is(":checked")) {
    exclude_prods = 1;
    jQuery.each(jQuery("input[name='cat_exclude']:checked"), function () {
      cats_to_exclude.push(jQuery(this).val());
    });
    if (jQuery("#exclude_subcat_check").is(":checked")) {
      exclude_sub_cat = 1;
    }
  }

  var data = {
    paged: "1"
  };
  jQuery.ajax({
    type: "post",
    url: ajaxurl,
    data: jQuery.extend({
      _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
      action: "eh_bep_filter_products",
      type: type_data,
      stock_status: stock_status,
      custom_attribute: custom_attribute_data,
      category_filter: category_data,
      sub_category_filter: sub_cat,
      attribute: attribute_data,
      product_title_select: prod_title_select,
      product_title_text: prod_title_text,
      regex_flags: regex_flag_values,
      product_description_select: prod_description_select,
      product_description_text: prod_description_text,
      regex_flags_description: regex_flag_values_description,
      product_short_description_select: prod_short_description_select,
      product_short_description_text: prod_short_description_text,
      regex_flags_short_description: regex_flag_values_short_description,
      attribute_value_filter: attribute_value_data,
      attribute_and: attribute_data_and,
      attribute_value_and_filter: attribute_value_data_and,
      range: range_data,
      desired_price: desired_price_data,
      minimum_price: minimum_price_data,
      maximum_price: maximum_price_data,
      exclude_ids: ids_to_exclude,
      exclude_categories: cats_to_exclude,
      exclude_subcat_check: exclude_sub_cat,
      enable_exclude_prods: exclude_prods,
      undo_sch_job: undo_scheduled_job,
      file: file_to_undo,
      prod_tags: tags
    }, data),
    success: function (response) {
      jQuery("#top_filter_tag").css("display", "none");
      document.getElementById("top_filter_tag").hidden = true;
      document.getElementById("wrap_table").hidden = false;
      jQuery("#step1").removeClass("active");
      jQuery("#step5").removeClass("active");
      jQuery("#step3").removeClass("active");
      jQuery("#step2").addClass("active");
      jQuery("#undo_update_html").empty();
      jQuery("#undo_update_logs").hide();
      jQuery("#wrap_table").css("display", "block");
      jQuery(".loader").css("display", "none");
      jQuery("#edit_product").css("display", "none");

      var response = jQuery.parseJSON(response);
      if (response.rows.length)
        jQuery("#the-list").html(response.rows);
      if (response.column_headers.length)
        jQuery("thead tr, tfoot tr").html(response.column_headers);
      if (response.pagination.bottom.length)
        jQuery(".tablenav.top .tablenav-pages").html(jQuery(response.pagination.top).html());
      if (response.pagination.top.length)
        jQuery(".tablenav.bottom .tablenav-pages").html(jQuery(response.pagination.bottom).html());
      list.init();
      if (response.total_items_count <= 0) {
        jQuery("#search_id-search-input").attr("disabled", "disabled");
        jQuery("#process_edit").attr("disabled", "disabled");
        if (response.regex_error == true) {
          jQuery(".colspanchange").append(" Invalid regex expression.");
        }
      } else {
        jQuery("#search_id-search-input").removeAttr("disabled");
        jQuery("#process_edit").removeAttr("disabled");
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}

function clear_edit_data() {
  jQuery("#title_action").prop("selectedIndex", 0);
  jQuery("#title_text").empty();
  jQuery("#sku_action").prop("selectedIndex", 0);
  jQuery("#sku_text").empty();
  jQuery("#is_featured").prop("selectedIndex", 0);
  jQuery("#is_product_type").prop("selectedIndex", 0);
  jQuery("#description_action").prop("selectedIndex", 0);
  jQuery("#description_tr").hide();
  jQuery("#short_description_action").prop("selectedIndex", 0);
  jQuery("#short_description_tr").hide();
  jQuery("#elex_product_main_image").val("");
  jQuery("#gallery_image_action").prop("selectedIndex", 0);
  jQuery("#gallery_images_tr").hide();
  jQuery("#catalog_action").prop("selectedIndex", 0);
  jQuery("#shipping_class_action").prop("selectedIndex", 0);
  jQuery("#shipping_class_check_text").empty();
  jQuery("#stock_quantity_action").prop("selectedIndex", 0);
  jQuery("#stock_quantity_text").empty();
  jQuery("#allow_backorder_action").prop("selectedIndex", 0);
  jQuery("#stock_status_action").prop("selectedIndex", 0);
  jQuery("#manage_stock_action").prop("selectedIndex", 0);
  jQuery("#manage_stock_check_text").empty();
  jQuery("#sale_price_action").prop("selectedIndex", 0);
  jQuery("#sale_price_text").empty();
  jQuery("#regular_price_action").prop("selectedIndex", 0);
  jQuery("#regular_price_text").empty();
  jQuery("#length_action").prop("selectedIndex", 0);
  jQuery("#length_text").empty();
  jQuery("#backorder_text").empty();
  jQuery("#width_action").prop("selectedIndex", 0);
  jQuery("#width_text").empty();
  jQuery("#height_action").prop("selectedIndex", 0);
  jQuery("#height_text").empty();
  jQuery("#weight_action").prop("selectedIndex", 0);
  jQuery("#weight_text").empty();
  jQuery("#attribute_action").prop("selectedIndex", 0);
  jQuery("#attr_names").hide();
  jQuery("#tax_status_action").prop("selectedIndex", 0);
  jQuery("#tax_class_action").prop("selectedIndex", 0);
  jQuery("input[id='size']").prop("checked", false);
  jQuery("input[id='color']").prop("checked", false);
  //jQuery("#size").prop("checked", false); vari_attr_changesize delete_product_action
  jQuery("#delete_product_action").prop("selectedIndex", 0);
  jQuery("#vari_attr_changesize").hide();
  jQuery("#vari_attr_changecolor").hide();
  jQuery(".regex-flags-edit-table").val("").trigger("chosen:updated");
  jQuery("#add_attribute_value_select").remove();
  jQuery("#new_attr_values").remove();
  jQuery("#select_variation").remove();
  jQuery("#select_visible").remove();
  jQuery("#shipping_unit_action").prop("selectedIndex", 0);
  jQuery("#shipping_unit_text").empty();
  jQuery("#regex_flags_field_title").hide();
  jQuery("#regex_help_link_title").hide();
  jQuery("#regex_flags_field_sku").hide();
  jQuery("#regex_help_link_sku").hide();
  jQuery("#price_adjustment_action").prop("selectedIndex", 0);
  jQuery("#visibility_price").prop("selectedIndex", 0);
  jQuery(".hide-price-role-select-chosen").val("").trigger("chosen:updated");
  jQuery("input[name='cat_update']").prop("checked", false);
  jQuery("#cat_select").hide();
  jQuery("#cat_update_none").prop("checked", true);
  jQuery("#regular_val_check").removeAttr("checked");
  jQuery("#regular_checkbox").hide();
  jQuery("input[name='meta_keys']").val("");
}

function clear_filters() {
  //var regex_default = ['g','m'];
  jQuery("#product_type").prop("selectedIndex", 0);
  jQuery("#elex_select_custom_attribute").prop("selectedIndex", 0);
  jQuery("#stock_status_id").prop("selectedIndex", 0);
  jQuery(".category-chosen").val("").trigger("chosen:updated");
  jQuery("#attrib_name input:checked").each(function () {
    jQuery(this).removeAttr("checked");
  });
  jQuery("#subcat_check").removeAttr("checked");
  jQuery("#regular_price_range_select").prop("selectedIndex", 0);
  jQuery("#attribute_value_select").remove();
  jQuery("#attribute_value_select_and").remove();
  jQuery("#attrib_name_and input:checked").each(function () {
    jQuery(this).removeAttr("checked");
  });
  jQuery("#regular_price_range_text").empty();
  jQuery(".attribute-chosen").val("").trigger("chosen:updated");
  jQuery("#product_title_select").prop("selectedIndex", 0);
  jQuery("#product_title_text").empty();
  jQuery("#regex_flags_field").hide();
  jQuery("#regex_help_link").hide();

  jQuery("#product_description_select").prop("selectedIndex", 0);
  jQuery("#product_description_text").empty();
  jQuery("#regex_flags_field_description").hide();
  jQuery("#regex_help_link_description").hide();

  jQuery("#product_short_description_select").prop("selectedIndex", 0);
  jQuery("#product_short_description_text").empty();
  jQuery("#regex_flags_field_short_description").hide();
  jQuery("#regex_help_link_short_description").hide();

  jQuery("input[name='cat_filter']").prop("checked", false);
  jQuery("input[name='cat_exclude']").prop("checked", false);
  jQuery("#exclude_subcat_check").removeAttr("checked");
  jQuery("#enable_exclude_products").removeAttr("checked");
  jQuery("#exclude_ids").val("");
  jQuery("#exclude_products").hide();
}

//schedule functions
function elex_bep_edit_copy_job(file, action) {
  jQuery(".loader").css("display", "block");
  jQuery.ajax({
    type: "post",
    url: ajaxurl,
    data: {
      _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
      action: "elex_bep_edit_job",
      file: file
    },
    success: function (job_response) {
      job_response = jQuery.parseJSON(job_response);
      var response = "";
      response = job_response["param_to_save"];
      filtered_ids = response["pid"];
      chunk_data = chunkArray(filtered_ids, 100);
      jQuery(".loader").css("display", "none");
      document.getElementById("wrap_table").hidden = true;
      document.getElementById("top_filter_tag").hidden = false;
      document.getElementById("edit_product").hidden = true;
      jQuery(".all-step").show();
      jQuery("#step2").removeClass("active");
      jQuery("#step1").removeClass("active");
      jQuery("#step3").addClass("active");
      jQuery("#undo_update_html").empty();
      jQuery("#wrap_table").css("display", "none");
      jQuery("#top_filter_tag").css("display", "block");
      jQuery("#manage_schedule_tasks").hide();
      jQuery("#edit_cancel").hide();
      jQuery("#edit_back").hide();
      if (action == "edit") {
        edit_job = true;
      }

      //prefill values

      //Filter section
      switch (response["product_title_select"]) {
        case "all":
          break;
        case "starts_with":
        case "ends_with":
        case "contains":
          var dom_title = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Title Text" id="product_title_text_val">';
          jQuery("#product_title_text").empty();
          jQuery("#product_title_text").append(dom_title);
          jQuery("#product_title_select").val(response["product_title_select"]);
          jQuery("#product_title_text_val").val(response["product_title_text"]);
          break;
        case "title_regex":
          var dom_title = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Title Text" id="product_title_text_val">';
          jQuery("#product_title_select").val(response["product_title_select"]);
          jQuery("#product_title_text").empty();
          jQuery("#product_title_text").append(dom_title);
          jQuery("#product_title_text_val").val(response["product_title_text"]);
          jQuery("#regex_flags_values").val(response["regex_flags"]).trigger("chosen:updated");
          jQuery("#regex_flags_field").show();
          jQuery("#regex_help_link").show();
      }

      // Description section
      switch (response["product_description_select"]) {
        case "all":
          break;
        case "starts_with":
        case "ends_with":
        case "contains":
          var dom_description = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Description Text" id="product_description_text_val">';
          jQuery("#product_description_text").empty();
          jQuery("#product_description_text").append(dom_description);
          jQuery("#product_description_select").val(response["product_description_select"]);
          jQuery("#product_description_text_val").val(response["product_description_text"]);
          break;
        case "description_regex":
          var dom_description = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Description Text" id="product_description_text_val">';
          jQuery("#product_description_select").val(response["product_description_select"]);
          jQuery("#product_description_text").empty();
          jQuery("#product_description_text").append(dom_description);
          jQuery("#product_description_text_val").val(response["product_description_text"]);
          jQuery("#regex_flags_values_description").val(response["regex_flags_description"]).trigger("chosen:updated");
          jQuery("#regex_flags_field_description").show();
          jQuery("#regex_help_link_description").show();
      }

      // Short Description section
      switch (response["product_short_description_select"]) {
        case "all":
          break;
        case "starts_with":
        case "ends_with":
        case "contains":
          var dom_short_description = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Short Description Text" id="product_short_description_text_val">';
          jQuery("#product_short_description_text").empty();
          jQuery("#product_short_description_text").append(dom_short_description);
          jQuery("#product_short_description_select").val(response["product_short_description_select"]);
          jQuery("#product_short_description_text_val").val(response["product_short_description_text"]);
          break;
        case "short_description_regex":
          var dom_short_description = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Short Description Text" id="product_short_description_text_val">';
          jQuery("#product_short_description_select").val(response["product_short_description_select"]);
          jQuery("#product_short_description_text").empty();
          jQuery("#product_short_description_text").append(dom_short_description);
          jQuery("#product_short_description_text_val").val(response["product_short_description_text"]);
          jQuery("#regex_flags_values_short_description").val(response["regex_flags_short_description"]).trigger("chosen:updated");
          jQuery("#regex_flags_field_short_description").show();
          jQuery("#regex_help_link_short_description").show();
      }

      if (response["type"]) {
        jQuery("#product_type").val(response["type"]).trigger("chosen:updated");
      }
      if (response["custom_attribute"]) {
        jQuery("#elex_select_custom_attribute").val(response["custom_attribute"]).trigger("chosen:updated");
      }
      if (response["prod_tags"]) {
        jQuery("#elex_product_tags").val(response["prod_tags"]).trigger("chosen:updated");
      }
      if (response["category_filter"]) {
        jQuery("input[name=cat_filter]").val(response["category_filter"]);
      }
      if (response["sub_category_filter"]) {
        jQuery("#subcat_check").prop("checked", true);
      }
      switch (response["range"]) {
        case "all":
          break;
        case ">":
        case "<":
        case "=":
          var dom_sing = '<input type="text" style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_desired_placeholder + '" id="regular_price_text_val">';
          jQuery("#regular_price_range_text").empty();
          jQuery("#regular_price_range_select").val(response["range"]);
          jQuery("#regular_price_range_text").append(dom_sing);
          jQuery("#regular_price_text_val").val(response["desired_price"]);
          break;
        case "|":
          var dom_bet = '<input type="text"style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_min_placeholder + '" id="regular_price_min_text"><input type="text" style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_max_placeholder + '" id="regular_price_max_text">';
          jQuery("#regular_price_range_text").empty();
          jQuery("#regular_price_range_text").append(dom_bet);
          jQuery("#regular_price_range_select").val(response["range"]);
          jQuery("#regular_price_min_text").val(response["minimum_price"]);
          jQuery("#regular_price_max_text").val(response["maximum_price"]);
      }

      if (response["attribute_value_filter"]) {
        jQuery.ajax({
          type: "post",
          url: ajaxurl,
          data: {
            _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
            action: "eh_bep_get_attributes_action_edit",
            attr_action: "or",
            attributes: response["attribute_value_filter"]
          },
          success: function (data) {
            var response_or = jQuery.parseJSON(data);
            jQuery("#attrib_name input:checked").each(function () {
              jQuery(this).removeAttr("checked");
            });
            jQuery("#attribute_value_select").remove();
            jQuery.each(response_or["attributes"], function (key, val) {
              jQuery("input[name='attrib_name'][id='" + val + "']").prop("checked", true);
            });

            var dom = "<tr id='attribute_value_select'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-content-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + js_obj.filter_attribute_value_tooltip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_attributes'></select></span></td></tr>";
            jQuery("#attribute_types").after(dom);
            jQuery(".attribute-chosen").chosen();
            jQuery(".tooltip").darkTooltip();

            jQuery("#select_input_attributes").append(response_or["return_select"]);
            jQuery(".attribute-chosen").trigger("chosen:updated");
          },
          error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
          }
        });
      }

      if (response["attribute_value_and_filter"]) {
        jQuery.ajax({
          type: "post",
          url: ajaxurl,
          data: {
            _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
            action: "eh_bep_get_attributes_action_edit",
            attr_action: "and",
            attributes: response["attribute_value_and_filter"]
          },
          success: function (response) {
            var response_and = jQuery.parseJSON(response);
            jQuery("#attrib_name_and input:checked").each(function () {
              jQuery(this).removeAttr("checked");
            });
            jQuery("#attribute_value_select_and").remove();
            jQuery.each(response_and["attributes"], function (key, val) {
              jQuery("input[name='attrib_name_and'][id='" + val + "']").prop("checked", true);
            });

            var dom = "<tr id='attribute_value_select_and'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-content-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + js_obj.filter_attribute_value_tooltip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_attributes_and'></select></span></td></tr>";
            jQuery("#attribute_types_and").after(dom);
            jQuery(".attribute-chosen").chosen();
            jQuery(".tooltip").darkTooltip();

            jQuery("#select_input_attributes_and").append(response_and["return_select"]);
            jQuery(".attribute-chosen").trigger("chosen:updated");
          },
          error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
          }
        });
      }

      // Second condition to check if exclude ids is added from unchecked products
      if (response["enable_exclude_prods"] || response["exclude_ids"].length >= 1) {
        jQuery("#enable_exclude_products").prop("checked", true);
        jQuery("#exclude_products").show();
        var excl_ids = "";
        if (response["exclude_ids"] != "") {
          jQuery.each(response["exclude_ids"], function (key, val) {
            excl_ids += val + ",";
          });
          excl_ids = excl_ids.slice(0, -1);
        }
        jQuery("#exclude_ids").val(excl_ids);

        if (response["exclude_categories"]) {
          jQuery("input[name=cat_exclude]").val(response["exclude_categories"]);
        }
        if (response["exclude_subcat_check"]) {
          jQuery("#exclude_subcat_check").prop("checked", true);
        }
      }

      //Edit products section
      jQuery("#title_textbox").remove();
      jQuery("#regex_replaceable_title_textbox").remove();
      jQuery("#sku_textbox").remove();
      jQuery("#regex_replaceable_sku_textbox").remove();
      switch (response["title_select"]) {
        case "":
          break;
        case "set_new":
          var dom_new = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_new_placeholder + '" id="title_textbox">';
          jQuery("#title_action").val("set_new");
          jQuery("#title_text").append(dom_new);
          jQuery("#title_textbox").val(response["title_text"]);
          break;
        case "append":
          var dom_app = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_append_placeholder + '" id="title_textbox">';
          jQuery("#title_action").val("append");
          jQuery("#title_text").append(dom_app);
          jQuery("#title_textbox").val(response["title_text"]);
          break;

        case "prepand":
          var dom_pre = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_prepand_placeholder + '" id="title_textbox">';
          jQuery("#title_action").val("prepand");
          jQuery("#title_text").append(dom_pre);
          jQuery("#title_textbox").val(response["title_text"]);
          break;
        case "replace":
          var dom_rep = '<input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_title_replaceable_placeholder + '" id="replaceable_title_textbox"><input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_title_replace_placeholder + '" id="title_textbox">';
          jQuery("#title_action").val("replace");
          jQuery("#title_text").append(dom_rep);
          jQuery("#title_textbox").val(response["title_text"]);
          jQuery("#replaceable_title_textbox").val(response["replace_title_text"]);
          break;
        case "regex_replace":
          var dom_reg_rep = '<input type="text" style="height:28px; width:36%;vertical-align:top;" placeholder="Pattern" id="regex_replaceable_title_textbox"><input type="text" style="height:28px;width:35%;vertical-align:top;" placeholder="Replacement" id="title_textbox">';
          jQuery("#title_action").val("regex_replace");
          jQuery("#title_text").append(dom_reg_rep);
          jQuery("#regex_flags_field_title").show();
          jQuery("#title_textbox").val(response["title_text"]);
          jQuery("#regex_replaceable_title_textbox").val(response["regex_replace_title_text"]);
          jQuery("#regex_flags_values_title").val(response["regex_flag_sele_title"]).trigger("chosen:updated");
          jQuery("#regex_help_link_title").show();
          break;
      }

      switch (response["sku_select"]) {
        case "":
          break;
        case "set_new":
          var dom_new = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_new_placeholder + '" id="sku_textbox">';
          jQuery("#sku_action").val("set_new");
          jQuery("#sku_text").append(dom_new);
          jQuery("#sku_textbox").val(response["sku_text"]);
          break;
        case "append":
          var dom_app = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_append_placeholder + '" id="sku_textbox">';
          jQuery("#sku_action").val("append");
          jQuery("#sku_text").append(dom_app);
          jQuery("#sku_textbox").val(response["sku_text"]);
          break;

        case "prepand":
          var dom_pre = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_prepand_placeholder + '" id="sku_textbox">';
          jQuery("#sku_action").val("prepand");
          jQuery("#sku_text").append(dom_pre);
          jQuery("#sku_textbox").val(response["sku_text"]);
          break;
        case "replace":
          var dom_rep = '<input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_sku_replaceable_placeholder + '" id="replaceable_sku_textbox"><input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_sku_replace_placeholder + '" id="sku_textbox">';
          jQuery("#sku_action").val("replace");
          jQuery("#sku_text").append(dom_rep);
          jQuery("#sku_textbox").val(response["sku_text"]);
          jQuery("#replaceable_sku_textbox").val(response["sku_replace_text"]);
          break;
        case "regex_replace":
          var dom_reg_rep = '<input type="text" style="height:28px;width:36%;vertical-align:top;" placeholder="Pattern" id="regex_replaceable_sku_textbox"><input type="text" style="height:28px;width:35%;vertical-align:top;" placeholder="Replacement" id="sku_textbox">';
          jQuery("#sku_action").val("regex_replace");
          jQuery("#sku_text").append(dom_reg_rep);
          jQuery("#regex_flags_field_sku").show();
          jQuery("#sku_textbox").val(response["sku_text"]);
          jQuery("#regex_replaceable_sku_textbox").val(response["regex_sku_replace_text"]);
          jQuery("#regex_flags_values_sku").val(response["regex_flag_sele_sku"]).trigger("chosen:updated");
          jQuery("#regex_help_link_sku").show();
          break;
      }
      if (response["catalog_select"] != "") {
        jQuery("#catalog_action").val(response["catalog_select"]);
      }

      if (response["is_featured"] != "") {
        jQuery("#is_featured").val(response["is_featured"]);
      }

      if (response['is_product_type'] != "") {
        jQuery("#is_product_type").val(response['is_product_type']);
      }

      if (response["shipping_select"] != "") {
        jQuery("#shipping_class_action").val(response["shipping_select"]);
      }

      if (response["description"] != "" && response["description_action"] != "") {
        jQuery("#description_tr").show();
        jQuery("#description_action").val(response["description_action"]);
        if (tinyMCE.get("elex_product_description") != null) {
          tinyMCE.get("elex_product_description").setContent(response["description"]);
        } else {
          jQuery("#elex_product_description").val(response["product_description"]);
        }
      }
      if (response["short_description"] != "" && response["short_description_action"] != "") {
        jQuery("#short_description_tr").show();
        jQuery("#short_description_action").val(response["short_description_action"]);
        if (tinyMCE.get("elex_product_short_description") != null) {
          tinyMCE.get("elex_product_short_description").setContent(response["short_description"]);
        } else {
          jQuery("#elex_product_short_description").val(response["product_short_description"]);
        }
      }
      if (response["main_image"]) {
        jQuery("#elex_product_main_image").val(response["main_image"]);
      }
      if (response["gallery_images"] && response["gallery_images_action"] != "") {
        jQuery("#gallery_image_action").val(response["gallery_images_action"]);
        jQuery("#gallery_images_tr").show();
        var gallery_url = "";
        if (response["gallery_images"] != "") {
          jQuery.each(response["gallery_images"], function (key, val) {
            gallery_url += val + ",";
          });
          gallery_url = gallery_url.slice(0, -1);
        }
        jQuery("#elex_product_gallery_images").val(gallery_url);
      }

      //Price
      jQuery("#regular_textbox").remove();
      jQuery("#regular_round_select").remove();
      jQuery("#regular_round_textbox").remove();
      jQuery("#sale_textbox").remove();
      jQuery("#sale_round_select").remove();
      jQuery("#sale_round_textbox").remove();
      var reg_round = true;
      var sale_round = true;
      switch (response["regular_select"]) {
        case "":
          reg_round = false;
          break;
        case "up_percentage":
          var dom_up_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_per_placeholder + '" id="regular_textbox">';
          var dom_round = '<select id="regular_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
          jQuery("#regular_price_text").append(dom_up_per);
          jQuery("#regular_price_action").val("up_percentage");
          jQuery("#regular_price_text").append(dom_round);
          jQuery("#regular_textbox").val(response["regular_text"]);
          break;
        case "down_percentage":
          var dom_down_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_per_placeholder + '" id="regular_textbox">';
          var dom_round = '<select id="regular_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
          jQuery("#regular_price_text").append(dom_down_per);
          jQuery("#regular_price_action").val("down_percentage");
          jQuery("#regular_price_text").append(dom_round);
          jQuery("#regular_textbox").val(response["regular_text"]);
          break;
        case "up_price":
          var dom_up_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_pri_placeholder + '" id="regular_textbox">';
          var dom_round = '<select id="regular_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
          jQuery("#regular_price_text").append(dom_up_pri);
          jQuery("#regular_price_action").val("up_price");
          jQuery("#regular_price_text").append(dom_round);
          jQuery("#regular_textbox").val(response["regular_text"]);
          break;
        case "down_price":
          var dom_down_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_pri_placeholder + '" id="regular_textbox">';
          var dom_round = '<select id="regular_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
          jQuery("#regular_price_text").append(dom_down_pri);
          jQuery("#regular_price_action").val("down_price");
          jQuery("#regular_price_text").append(dom_round);
          jQuery("#regular_textbox").val(response["regular_text"]);
          break;
        case "flat_all":
          var dom_flat_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_flat_pri_placeholder + '" id="regular_textbox">';
          jQuery("#regular_price_text").append(dom_flat_pri);
          jQuery("#regular_price_action").val("flat_all");
          jQuery("#regular_textbox").val(response["regular_text"]);
          reg_round = false;
          break;
      }
      if (reg_round) {
        var dom_round = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_round_off + '" id="regular_round_textbox"> ';
        switch (response["regular_round_select"]) {
          case "up":
            jQuery("#regular_price_text").append(dom_round);
            jQuery("#regular_round_select").val("up");
            jQuery("#regular_round_textbox").val(response["regular_round_text"]);
            break;
          case "down":
            jQuery("#regular_price_text").append(dom_round);
            jQuery("#regular_round_select").val("down");
            jQuery("#regular_round_textbox").val(response["regular_round_text"]);
            break;
        }
      }

      switch (response["sale_select"]) {
        case "":
          sale_round = false;
          break;
        case "up_percentage":
          var dom_up_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_per_placeholder + '" id="sale_textbox">';
          var dom_round = '<select id="sale_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
          jQuery("#sale_price_text").append(dom_up_per);
          jQuery("#sale_price_action").val("up_percentage");
          jQuery("#sale_price_text").append(dom_round);
          jQuery("#sale_textbox").val(response["sale_text"]);
          break;
        case "down_percentage":
          var dom_down_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_per_placeholder + '" id="sale_textbox">';
          var dom_round = '<select id="sale_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
          jQuery("#sale_price_text").append(dom_down_per);
          jQuery("#sale_price_action").val("down_percentage");
          jQuery("#sale_price_text").append(dom_round);
          jQuery("#sale_textbox").val(response["sale_text"]);
          jQuery("#regular_checkbox").show();
          if (response["regular_check_val"]) {
            jQuery("#regular_val_check").prop("checked", true);
          }
          break;
        case "up_price":
          var dom_up_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_pri_placeholder + '" id="sale_textbox">';
          var dom_round = '<select id="sale_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
          jQuery("#sale_price_text").append(dom_up_pri);
          jQuery("#sale_price_action").val("up_price");
          jQuery("#sale_price_text").append(dom_round);
          jQuery("#sale_textbox").val(response["sale_text"]);
          break;
        case "down_price":
          var dom_down_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_pri_placeholder + '" id="sale_textbox">';
          var dom_round = '<select id="sale_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
          jQuery("#sale_price_text").append(dom_down_pri);
          jQuery("#sale_price_action").val("down_price");
          jQuery("#sale_price_text").append(dom_round);
          jQuery("#sale_textbox").val(response["sale_text"]);
          jQuery("#regular_checkbox").show();
          if (response["regular_check_val"]) {
            jQuery("#regular_val_check").prop("checked", true);
          }
          break;
        case "flat_all":
          var dom_flat_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_flat_pri_placeholder + '" id="sale_textbox">';
          jQuery("#sale_price_text").append(dom_flat_pri);
          jQuery("#sale_price_action").val("flat_all");
          jQuery("#sale_textbox").val(response["sale_text"]);
          sale_round = false;
          break;
      }
      if (sale_round) {
        var dom_round = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_round_off + '" id="sale_round_textbox"> ';
        switch (response["sale_round_select"]) {
          case "up":
            jQuery("#sale_price_text").append(dom_round);
            jQuery("#sale_round_select").val("up");
            jQuery("#sale_round_textbox").val(response["sale_round_text"]);
            break;
          case "down":
            jQuery("#sale_price_text").append(dom_round);
            jQuery("#sale_round_select").val("down");
            jQuery("#sale_round_textbox").val(response["sale_round_text"]);
            break;
        }
      }

      //Stock
      if (response["stock_manage_select"] != "") {
        jQuery("#manage_stock_action").val(response["stock_manage_select"]);
      }
      jQuery("#quantity_textbox").remove();
      switch (response["quantity_select"]) {
        case "":
          break;
        case "add":
          var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="quantity_textbox">';
          jQuery("#stock_quantity_text").append(dom_add);
          jQuery("#stock_quantity_action").val("add");
          jQuery("#quantity_textbox").val(response["quantity_text"]);
          break;
        case "sub":
          var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="quantity_textbox">';
          jQuery("#stock_quantity_text").append(dom_sub);
          jQuery("#stock_quantity_action").val("sub");
          jQuery("#quantity_textbox").val(response["quantity_text"]);
          break;
        case "replace":
          var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="quantity_textbox">';
          jQuery("#stock_quantity_text").append(dom_rep);
          jQuery("#stock_quantity_action").val("replace");
          jQuery("#quantity_textbox").val(response["quantity_text"]);
          break;
      }

      if (response["backorder_select"] != "") {
        jQuery("#allow_backorder_action").val(response["backorder_select"]);
      }

      if (response["stock_status_select"] != "") {
        jQuery("#stock_status_action").val(response["stock_status_select"]);
      }

      //Dimensions
      jQuery("#length_textbox").remove();
      jQuery("#width_textbox").remove();
      jQuery("#height_textbox").remove();
      jQuery("#weight_textbox").remove();

      switch (response["length_select"]) {
        case "":
          break;
        case "add":
          var len_dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="length_textbox">';
          jQuery("#length_text").append(len_dom_add);
          jQuery("#length_action").val("add");
          jQuery("#length_textbox").val(response["length_text"]);
          break;
        case "sub":
          var len_dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="length_textbox">';
          jQuery("#length_text").append(len_dom_sub);
          jQuery("#length_action").val("sub");
          jQuery("#length_textbox").val(response["length_text"]);
          break;
        case "replace":
          var len_dom_rep = '<input type="text" style="height:28px;vertical-align:top;"  placeholder="' + js_obj.edit_rep_placeholder + '" id="length_textbox">';
          jQuery("#length_text").append(len_dom_rep);
          jQuery("#length_action").val("replace");
          jQuery("#length_textbox").val(response["length_text"]);
          break;
      }
      switch (response["width_select"]) {
        case "":
          break;
        case "add":
          var width_dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="width_textbox">';
          jQuery("#width_text").append(width_dom_add);
          jQuery("#width_action").val("add");
          jQuery("#width_textbox").val(response["width_text"]);
          break;
        case "sub":
          var width_dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="width_textbox">';
          jQuery("#width_text").append(width_dom_sub);
          jQuery("#width_action").val("sub");
          jQuery("#width_textbox").val(response["width_text"]);
          break;
        case "replace":
          var width_dom_rep = '<input type="text" style="height:28px;vertical-align:top;"  placeholder="' + js_obj.edit_rep_placeholder + '" id="width_textbox">';
          jQuery("#width_text").append(width_dom_rep);
          jQuery("#width_action").val("replace");
          jQuery("#width_textbox").val(response["width_text"]);
          break;
      }
      switch (response["height_select"]) {
        case "":
          break;
        case "add":
          var height_dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="height_textbox">';
          jQuery("#height_text").append(height_dom_add);
          jQuery("#height_action").val("replace");
          jQuery("#height_textbox").val(response["height_text"]);
          break;
        case "sub":
          var height_dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="height_textbox">';
          jQuery("#height_text").append(height_dom_sub);
          jQuery("#height_action").val("replace");
          jQuery("#height_textbox").val(response["height_text"]);
          break;
        case "replace":
          var height_dom_rep = '<input type="text" style="height:28px;vertical-align:top;"  placeholder="' + js_obj.edit_rep_placeholder + '" id="height_textbox">';
          jQuery("#height_text").append(height_dom_rep);
          jQuery("#height_action").val("replace");
          jQuery("#height_textbox").val(response["height_text"]);
          break;
      }
      switch (response["weight_select"]) {
        case "":
          break;
        case "add":
          var weight_dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="weight_textbox">';
          jQuery("#weight_text").append(weight_dom_add);
          jQuery("#weight_action").val("replace");
          jQuery("#weight_textbox").val(response["weight_text"]);
          break;
        case "sub":
          var weight_dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="weight_textbox">';
          jQuery("#weight_text").append(weight_dom_sub);
          jQuery("#weight_action").val("replace");
          jQuery("#weight_textbox").val(response["weight_text"]);
          break;
        case "replace":
          var weight_dom_rep = '<input type="text" style="height:28px;vertical-align:top;"  placeholder="' + js_obj.edit_rep_placeholder + '" id="weight_textbox">';
          jQuery("#weight_text").append(weight_dom_rep);
          jQuery("#weight_action").val("replace");
          jQuery("#weight_textbox").val(response["weight_text"]);
          break;
      }

      //Attribute
      switch (response["attribute_action"]) {
        case "":
          break;
        case "add":
          jQuery("#attribute_action").val("add");
          jQuery("#attr_names").show();
          if (response["attribute"] != "") {
            elex_attribute_prefill(response);
          }
          break;
        case "remove":
          jQuery("#attribute_action").val("remove");
          jQuery("#attr_names").show();
          if (response["attribute"] != "") {
            elex_attribute_prefill(response);
          }
          break;
        case "replace":
          jQuery("#attribute_action").val("replace");
          jQuery("#attr_names").show();
          if (response["attribute"] != "") {
            elex_attribute_prefill(response);
          }
          break;
      }
      if (response["vari_attribute"]) {
        jQuery.each(response["vari_attribute"], function (key, val) {
          jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
              _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
              action: "elex_variations_attribute_change",
              attrib: val,
              attr_edit: true
            },
            success: function (data) {
              var response_vari_attr = jQuery.parseJSON(data);
              jQuery(".loader").css("display", "none");
              jQuery("input[name='vari_attribu_name'][id='" + response_vari_attr["attribute"] + "']").prop("checked", true);
              jQuery("#variations_attribute_rows").after(response_vari_attr["return"]);
            },
            error: function (jqXHR, textStatus, errorThrown) {
              console.log(textStatus, errorThrown);
            }
          });
        });
      }

      //Category
      if (response["category_update_option"] != "cat_none") {
        jQuery("input[type=radio][name=edit_category][value=" + response["category_update_option"] + "]").attr("checked", true);
        jQuery("#cat_select").show();
        jQuery("input[name=cat_update]").val(response["categories_to_update"]);
      }

      //delete products
      if (response["delete_product_action"] != "") {
        jQuery("#delete_product_action").val(response["delete_product_action"]);
      }

      //Custom meta
      jQuery("#update_meta_table").remove();
      if (response["custom_meta"] != undefined) {
        var dom = "<table class='eh-edit-table' id='update_meta_table'><tr><td class='eh-edit-tab-table-left'><h2>Update meta values</h2><hr></td></tr>";
        for (var i = 0; i < response["custom_meta"].length; i++) {
          dom += "<tr><td class='eh-edit-tab-table-left'>" + response["meta_fields"][i] + "</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='Update meta'></span></td><td class='eh-edit-tab-table-input-td'><input type='text' name='meta_keys' placeholder='Enter meta value' value = '" + response["custom_meta"][i] + "'></td></tr>";
        }
        dom += "</table></div>";
      }
      jQuery("#update_category_table").append(dom);
      prev_metas = response["meta_fields"];
      //Schedule
      jQuery("#elex_schedule_options").val(response["scheduled_action"]);
      if (response["scheduled_action"] != "bulk_update_now") {
        jQuery("#elex_save_job_checkbox").hide();
        jQuery("#elex_save_job_text").hide();
        jQuery("#elex_schedule_date_and_time").show();
        jQuery("#elex_undo_enable_field").hide();
        jQuery("#elex_revert_date_and_time").show();
        jQuery("#schedule_frequency_options").show();
        jQuery("#schedule_frequency").val(response["schedule_frequency_action"]);
        if (response["schedule_frequency_action"] == "weekly") {
          jQuery("#select_days_weekly").show();
          jQuery("#schedule_days_weekly").val(response["schedule_weekly_days"]).trigger("chosen:updated");
        }
        if (response["schedule_frequency_action"] == "monthly") {
          jQuery("#select_days_monthly").show();
          jQuery("#schedule_days_monthly").val(response["schedule_monthly_days"]).trigger("chosen:updated");
        }

        if (response["schedule_frequency_action"] != "") {
          jQuery("#stop_schedule_field").show();
          jQuery("#stop_schedule_date").val(response["stop_schedule_date"]);
          jQuery("#stop_hr").val(response["stop_hr"]);
          jQuery("#stop_min").val(response["stop_min"]);
        }

        jQuery("#schedule_date").val(response["schedule_date"]);
        jQuery("#revert_date").val(response["revert_date"]);
        jQuery("#schedule_hr").val(response["scheduled_hour"]);
        jQuery("#schedule_min").val(response["scheduled_min"]);
        jQuery("#revert_hr").val(response["revert_hour"]);
        jQuery("#revert_min").val(response["revert_min"]);
      }
      if (action == "copy") {
        jQuery("#schedule_name").val("Copy of " + job_response["job_name"]);
      } else {
        jQuery("#schedule_name").val(job_response["job_name"]);
      }
      if (response["create_log_file"] == "true") {
        jQuery("#create_log_file").prop("checked", true);
      }
      if (!response["undo_update_op"]) {
        jQuery("#add_undo_now").prop("checked", false);
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}

function elex_attribute_prefill(response) {
  var tool_tip = "";
  var new_tool_tip = "";
  jQuery("#attribu_name input:checked").each(function () {
    jQuery(this).removeAttr("checked");
  });
  jQuery("#add_attribute_value_select").remove();
  if (jQuery("#attribute_action").val() == "add") {
    tool_tip = "Choose an existing attribute value(s) to be added to the product attribute(s)";
    new_tool_tip = "Specify new values to be added to the selected attribute(s). Enter each value in a new line";
  }
  if (jQuery("#attribute_action").val() == "remove") {
    tool_tip = "Choose existing attribute value(s) to be removed from the product attribute(s)";
  }
  if (jQuery("#attribute_action").val() == "replace") {
    tool_tip = "Select existing attribute value(s) to be added to the product attribute(s). This will replace any already existing attribute value(s) from the product attribute";
    new_tool_tip = "Specify new values to be added to the selected attribute(s). Enter each value in a new line. This will replace any already existing attribute value(s) from the product attribute";
  }

  var dom = "<tr id='add_attribute_value_select'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + tool_tip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_add_attributes'></select></span></td><td style='width:38%;'></td></tr>";
  var dom_new_attr = "<tr id='new_attr_values'><td>" + "Attribute Values (New)" + "</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + new_tool_tip + "'></span></td><td><span class='select-eh' ><textarea  id='new_attribute_values_textarea' style='width:210px; height:66px;'></textarea></span></td></tr>";
  var dom_variation_check = "<tr id='select_variation'><td class='eh-edit-tab-table-left'>Used for Variations</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='Choose if selected attribute values are to be used for variations'></span></td> <td class='eh-edit-tab-table-input-td'> <select id='attr_variationa_action' style='width:210px;'><option value=''>< No Change ></option><option value='add'>Enable</option><option value='remove'>Disable</option></select></td></tr>";
  var dom_visible_check = "<tr id='select_visible'><td class='eh-edit-tab-table-left'>Used for Visible on the product page</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='Select an option to determine if selected attribute values are to be used for variations'></span></td> <td class='eh-edit-tab-table-input-td'> <select id='attr_visiblea_action' style='width:210px;'><option value=''>< No Change ></option><option value='add'>Enable</option><option value='remove'>Disable</option></select></td></tr>";
  jQuery("#attr_names").after(dom);
  jQuery(".attribute-chosen").chosen();
  jQuery(".tooltip").darkTooltip();

  if (response["attribute_value"]) {
    jQuery.ajax({
      type: "post",
      url: ajaxurl,
      data: {
        _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
        action: "eh_bep_get_attributes_action_edit",
        attr_action: "or",
        attributes: response["attribute_value"]
      },
      success: function (data) {
        var response_edit = jQuery.parseJSON(data);
        jQuery.each(response_edit["attributes"], function (key, val) {
          jQuery("input[name='attribu_name'][id='" + val + "']").prop("checked", true);
        });

        jQuery("#select_input_add_attributes").append(response_edit["return_select"]);
        jQuery(".attribute-chosen").trigger("chosen:updated");
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }

  if (jQuery("#attribute_action").val() == "add" || jQuery("#attribute_action").val() == "replace") {
    jQuery("#new_attr").after(dom_new_attr);
    jQuery("#variation_select").after(dom_variation_check);
    jQuery("#variation_select").after(dom_visible_check);
    jQuery(".tooltip").darkTooltip();

    jQuery("#select_input_add_attributes").val(response["attribute_value"]).trigger("chosen:updated");
    var new_attr = "";
    if (response["new_attribute_values"] != "") {
      jQuery.each(response["new_attribute_values"], function (key, val) {
        new_attr += val + "\n";
      });
    }
    jQuery("#new_attribute_values_textarea").val(new_attr);
    if (response["attribute_variation"] != "") {
      jQuery("#attr_variationa_action").val(response["attribute_variation"]);
    }
    if (response["attribute_visible"] != "") {
      jQuery("#attr_visiblea_action").val(response["attribute_visible"]);
    }
  } else {
    jQuery("#new_attr_values").remove();
    jQuery("#select_variation").remove();
    jQuery("#select_visible").remove();
  }
}

function elex_bep_run_now(file) {
  var run_file = confirm("Do you want to run the job " + file + " now?");
  if (!run_file == true) {
    return;
  }
  jQuery(".loader").css("display", "block");
  jQuery.ajax({
    type: "post",
    url: ajaxurl,
    data: {
      _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
      action: "elex_bep_run_job",
      file: file
    },
    success: function (response) {
      jQuery(".loader").css("display", "none");
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}

function elex_bep_revert_now(file) {
  if (file == "") {
    return;
  }
  undo_scheduled_job = 1;
  file_to_undo = file;
  jQuery(".loader").css("display", "block");
  jQuery.ajax({
    type: "post",
    url: ajaxurl,
    data: {
      _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
      action: "elex_bep_revert_job",
      file: file
    },
    success: function (response) {
      jQuery(".loader").css("display", "none");
      document.getElementById("wrap_table").hidden = true;
      document.getElementById("top_filter_tag").hidden = true;
      document.getElementById("edit_product").hidden = false;
      jQuery(".all-step").show();
      jQuery("#step2").removeClass("active");
      jQuery("#step1").removeClass("active");
      jQuery("#step3").addClass("active");
      jQuery("#undo_update_html").empty();
      jQuery("#wrap_table").css("display", "none");
      jQuery("#undo_update_html").css("display", "block");
      jQuery("#manage_schedule_tasks").hide();
      jQuery("#edit_cancel").hide();
      jQuery("#edit_back").hide();
      jQuery("#undo_update_html").html(response);
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}

function elex_bep_delete_job(file) {
  var delete_job = confirm("Do you want to delete job " + file + "?");
  if (!delete_job == true) {
    return;
  }
  jQuery(".loader").css("display", "block");
  jQuery.ajax({
    type: "post",
    url: ajaxurl,
    data: {
      _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
      action: "elex_bep_delete_job",
      file: file
    },
    success: function (response) {
      jQuery("#" + file).remove();
      jQuery(".loader").css("display", "none");
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}

function elex_bep_cancel_job(file) {
  if (file == "") {
    return;
  }
  var cancel_schedule = confirm("Do you want to cancel schedule " + file + "?");
  if (!cancel_schedule == true) {
    return;
  }
  jQuery(".loader").css("display", "block");

  jQuery.ajax({
    type: "post",
    url: ajaxurl,
    data: {
      _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
      action: "elex_bep_cancel_schedule",
      file: file
    },
    success: function (response) {
      jQuery(".loader").css("display", "none");
      window.location.reload();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}

function getValue_attrib_name() {
  var chkArray = [];
  jQuery("#attrib_name input:checked").each(function () {
    chkArray.push(jQuery(this).val());
  });
  var selected;
  selected = chkArray.join(",") + ",";
  if (selected.length > 1) {
    return selected.slice(0, -1);
  } else {
    return "";
  }
}

function getValue_attrib_name_and() {
  var chkArray = [];
  jQuery("#attrib_name_and input:checked").each(function () {
    chkArray.push(jQuery(this).val());
  });
  var selected;
  selected = chkArray.join(",") + ",";
  if (selected.length > 1) {
    return selected.slice(0, -1);
  } else {
    return "";
  }
}

function chunkArray(myArray, chunk_size) {
  var index = 0;
  var arrayLength = myArray.length;
  var tempArray = [];

  for (index = 0; index < arrayLength; index += chunk_size) {
    myChunk = myArray.slice(index, index + chunk_size);
    // Do something if you want with the group
    tempArray.push(myChunk);
  }

  return tempArray;
}

jQuery(document).ready(function () {
  jQuery("table.wp-list-table").tableSearch();
});
(function (jQuery) {
  jQuery.fn.tableSearch = function (options) {
    if (!jQuery(this).is("table")) {
      return;
    }
    var tableObj = jQuery(this),
      inputObj = jQuery("#search_id-search-input");
    inputObj.off("keyup").on("keyup", function () {
      var searchFieldVal = jQuery(this).val();
      tableObj.find("tbody tr").hide().each(function () {
        var currentRow = jQuery(this);
        currentRow.find("td").each(function () {
          if (jQuery(this).html().indexOf(searchFieldVal) > -1) {
            currentRow.show();
            return false;
          }
        });
      });
    });
  };
})(jQuery);


