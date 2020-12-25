<?php


function elex_bep_get_attribute_and_terms(){
    $attribute_names = array();
    $attribute_and_terms = array();
    $attributes = wc_get_attribute_taxonomies();
    error_log( print_r( $attributes, TRUE ));
    foreach ( $attributes as $term_id=> $term_value ) {
        array_push( $attribute_names, $term_value->attribute_name );
    }
    foreach( $attribute_names as $key => $name ){
        array_push( $attribute_and_terms, get_terms( 'pa_'.$name ));
    }

    error_log( print_r( $attribute_and_terms, TRUE ));
}


function elex_bep_create_variationw( $parent_product_id, $attribute_array ){
    
    //Create main product
    $variable_product_object = new WC_Product_Variable( $parent_product_id );

    //Create the attribute object
    $attribute = new WC_Product_Attribute();

    //pa_size tax id
    $attribute->set_id( 0 ); // -> SET to 0

    //pa_size slug
    $attribute->set_name( 'size' ); // -> removed 'pa_' prefix

    //Set terms slugs
    $attribute->set_options( array(
        'M',
        'L'
    ) );

    $attribute->set_position( 0 );
    //If enabled
    $attribute->set_visible( 1 );

    //If we are going to use attribute in order to generate variations
    $attribute->set_variation( 1 );

    $attribute2 = new WC_Product_Attribute();

    //pa_size tax id
    $attribute2->set_id( 0 ); // -> SET to 0

    //pa_size slug
    $attribute2->set_name( 'color' ); // -> removed 'pa_' prefix

    //Set terms slugs
    $attribute2->set_options( array(
        'GG',
        'YY'
    ) );

    $attribute2->set_position( 0 );

    //If enabled
    $attribute2->set_visible( 1 );

    //If we are going to use attribute in order to generate variations
    $attribute2->set_variation( 1 );
    error_log( print_r( $attribute2, TRUE ));
    $variable_product_object->set_attributes(array($attribute, $attribute2));
    $id = $variable_product_object->save();
    ###

        $attr_terms1 = array(
            "GG", "YY"
        );
        $attr_terms2 = array(
            "M", "L"
        );

        for ($x = 0; $x < count($attr_terms1); $x++) {
            for ($y = 0; $y < count($attr_terms2); $y++) {
                $variation = new WC_Product_Variation();
                // $variation->set_regular_price(10);
                $variation->set_parent_id($id);
                
                //Set attributes requires a key/value containing
                // tax and term slug
                $variation->set_attributes(array(
                    'size' => $attr_terms2[$y], // -> removed 'pa_' prefix
                    'color' =>  $attr_terms1[$x]
                ));
                
                //Save variation, returns variation id
                $variation->save(); 
              }
          }


    ###


    //Save main product to get its id
    // $id = $variable_product_object->save();
    // $variation = new WC_Product_Variation();
    // // $variation->set_regular_price(10);
    // $variation->set_parent_id($id);
    
    // //Set attributes requires a key/value containing
    // // tax and term slug
    // $variation->set_attributes(array(
    //     'size' => array("M", "L"), // -> removed 'pa_' prefix
    //     'color' =>  array('GG', 'YY')
    // ));
    
    // //Save variation, returns variation id
    // $variation->save();
    
    return;
    
}
