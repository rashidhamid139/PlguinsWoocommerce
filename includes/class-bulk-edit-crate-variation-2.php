<?php

function elex_bep_create_variation( $parent_product_id, $attribute_array ){
    
    //Create main product
    $variable_product_object = new WC_Product_Variable( $parent_product_id );
    $attr_data = array(
            'color'=> array( 'G', 'B', 'Y'),
            'size'=> array( 'XX', 'XXL', 'L')
    );
    
    $attribute_array = array();


    foreach( $attr_data as $term=> $option ){
        $attribute  = new WC_Product_Attribute();
        $attribute->set_id( 0 );
        $attribute->set_name( $term );
        $attribute->set_options( $option );
        $attribute->set_position( 0 );
        $attribute->set_visible( 1 );
        $attribute->set_variation( 1 );
        array_push( $attribute_array, $attribute );
    }
    $variable_product_object->set_attributes( $attribute_array );
    $id = $variable_product_object->save();
    error_log( $id );
    elex_bep_create_variation_from_attributes( $parent_product_id, $attr_data );

    return;
    
}




function elex_bep_create_variation_from_attributes( $product_id, $variation_data ) {
    //Get varaible product object parent
    $product = wc_get_product( $product_id );
    //
    $variation_post = array(
        'post_title' => $product->get_name(),
        'post_name' => 'product-'.$product_id.'-varation',
        'post_status' => 'publish',
        'post_parent' => $product_id,
        'post_type' => 'product_variation',
        'guid' => $product->get_permalink()
    );

    $variation_id = wp_insert_post( $variation_post );
    $variation = new WC_Product_Variation( $variation_id );
    error_log( $variation_id );

    foreach ($variation_data as $attribute => $term_names )
    {
        $taxonomy = 'pa_'.$attribute; // The attribute taxonomy
        error_log( taxonomy_exists( $taxonomy ));
        // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
        if( ! taxonomy_exists( $taxonomy ) ){ 
            register_taxonomy(
                $taxonomy, 'product_variation',
                array(
                    'hierarchical' => false,
                    'label' => ucfirst( $attribute ),
                    'query_var' => true,
                    'rewrite' => array( 'slug' => sanitize_title($attribute) ), // The base slug
                )
            );
        }

        foreach( $term_names as $key=> $term_name  ){
            error_log( $key. '   '. $term_name );
            // Check if the Term name exist and if not we create it.
            if( ! term_exists( $term_name, $taxonomy ) ){
                wp_insert_term( $term_name, $taxonomy ); // Create the term
            }
            $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug
            error_log( print_r($term_slug, TRUE ));

            // // Get the post Terms names from the parent variable product.
            $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

            // // Check if the post term exist and if not we set it in the parent variable product.
            if( ! in_array( $term_name, $post_term_names ) )
                wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

            // // Set/save the attribute data in the product variation
            update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
        }
    }
    $variation->set_manage_stock(false);

    $variation->set_weight(''); // weight (reseting)
    
    error_log( print_r( $variation, TRUE ));
    $variation->save(); 
}
