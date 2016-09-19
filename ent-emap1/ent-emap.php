<?php
/*
Plugin Name: Simple gmap
Description: 
Version: 0.1
Author: Ent-ch
*/

add_action( 'wp_enqueue_scripts', 'emap_head' );
add_action( 'admin_enqueue_scripts', 'emap_head' );

function emap_head() {
    wp_enqueue_style( 'emap-style', plugins_url('emap-style.css', __FILE__) );

    if (!is_admin()){
        wp_enqueue_script( 'emap-base', 'https://maps.googleapis.com/maps/api/js?v=3.exp');
        wp_enqueue_script( 'emap-script', plugins_url('emap.js', __FILE__));
    }
    else {
        wp_enqueue_script( 'emap-base', 'https://maps.googleapis.com/maps/api/js?v=3.exp', array( 'jquery'));
        wp_enqueue_script( 'emap-script', plugins_url('emap.js', __FILE__), array( 'jquery'));
    }


}

add_action( 'add_meta_boxes', 'emap_add_custom_box' );
add_action( 'save_post', 'emap_save_postdata' );

function emap_add_custom_box() {
    add_meta_box( 
        'emap_sectionid',
        __( 'Добавить положение', 'emap_textdomain' ),
        'emap_inner_custom_box',
        'apart' 
    );
}

function emap_inner_custom_box( $post ) {
	wp_nonce_field( 'emap_meta_box', 'emap_meta_box_nonce' );
	$value = get_post_meta( $post->ID, '_emap_coordinates', true );
	if ($value){
		$cord = explode(',', $value);
		echo "<script type=\"text/javascript\">var zoomlevel = 14; var clat = {$cord[0]}; var clon = {$cord[1]}; var arrlocations = [['Текущая позиция', {$cord[0]}, {$cord[1]}]];</script>";
	}
	echo '<input type="text" id="emap-coordinates" name="_emap_coordinates" value="' . esc_attr( $value ) . '" size="25" />';
	echo '<div id="map-canvas"></div>';

}

function emap_save_postdata( $post_id ) {
	if ( ! isset( $_POST['emap_meta_box_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['emap_meta_box_nonce'], 'emap_meta_box' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( isset( $_POST['post_type'] ) && 'post' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	if ( ! isset( $_POST['_emap_coordinates'] ) ) {
		return;
	}

	$my_data = sanitize_text_field( $_POST['_emap_coordinates'] );
	update_post_meta( $post_id, '_emap_coordinates', $my_data );
  
}


function emap_poi() {
	$value = get_post_meta( get_the_ID(), '_emap_coordinates', true );
	if ($value){
		$cord = explode(',', $value);
		$content .= "<script type=\"text/javascript\">var mrkdraggable = false; var zoomlevel = 14; var clat = {$cord[0]}; var clon = {$cord[1]}; var arrlocations = [['Текущая позиция', {$cord[0]}, {$cord[1]}]];</script>";
		$content .= '<div id="map-canvas"></div>';
	}
	return $content;
}
add_shortcode('emap_poi', 'emap_poi');


 ?>