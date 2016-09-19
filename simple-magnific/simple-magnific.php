<?php
/*
Plugin Name: Simple Magnific Popup
Author: Ent-ch
Version: 0.1
*/

add_action('wp_enqueue_scripts', 'com_grp_stylesheet');
//add_action('admin_enqueue_scripts', 'com_grp_stylesheet');

function com_grp_stylesheet()
{
//    wp_enqueue_style( 'spfilter-style-bxslider', plugins_url('css/animate.min.css', __FILE__) );


    wp_enqueue_style( 'spfilter-style-bxslider', plugins_url('css/magnific-popup.css', __FILE__) );
    wp_enqueue_style( 'spfilter-style-bxslider2', plugins_url('css/simpl-magn.css', __FILE__) );
    wp_enqueue_script( 'spfilter-script-rating',  plugins_url('js/jquery.magnific-popup.min.js', __FILE__), array( 'jquery'));
    wp_enqueue_script( 'spfilter-script-rating2',  plugins_url('js/simpl-magn.js', __FILE__), array( 'jquery'));


}



function add_image_sizes() {
    add_image_size( 'galery-thumb2', 115, 115, true);
}
add_action( 'init', 'add_image_sizes' );

function display_image_sizes($sizes) {
    $sizes['galery-thumb2'] = __('Превью галереи 2');

    return $sizes;
}

add_filter('image_size_names_choose', 'display_image_sizes');


?>
