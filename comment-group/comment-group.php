<?php
/*
Plugin Name: Comment group
Description: 
Author: Ent-ch
Version: 0.1
*/ 
  
add_action('wp_enqueue_scripts', 'com_grp_stylesheet');
add_action('admin_enqueue_scripts', 'com_grp_stylesheet');

function com_grp_stylesheet()
{
    wp_enqueue_style( 'spfilter-style-bxslider', plugins_url('css/jquery.bxslider.css', __FILE__) );
    wp_enqueue_style( 'spfilter-style', plugins_url('css/cg-style.css', __FILE__) );
    wp_enqueue_script( 'spfilter-script-rating',  plugins_url('js/jquery.barrating.min.js', __FILE__), array( 'jquery'));
    wp_enqueue_script( 'spfilter-script-slider',  plugins_url('js/jquery.bxslider.min.js', __FILE__), array( 'jquery'));
    wp_enqueue_script( 'spfilter-script', plugins_url('js/cg-script.js', __FILE__), array( 'jquery'));

}

add_action('init', 'cptui_register_my_taxes');
function cptui_register_my_taxes() 
{
	$labels = array(
		'name' => 'Отзывы',
		'singular_name' => 'Отзыв',
		'add_new' => __('Добавить'),
		'add_new_item' => __('Добавить'),
		'edit_item' => __('Редактировать'),
		'new_item' => __('Новый'),
		'view_item' => __('Просмотр'),
		'search_items' => __('Поиск'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => '',
		'menu_name' => 'Отзывы'
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => null,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title','editor','custom-fields')
	  ); 

	register_post_type( 'mention' , $args );
    
    $labels = array (
          'search_items' => 'Найти',
          'popular_items' => '',
          'all_items' => 'Все элементы',
          'parent_item' => '',
          'parent_item_colon' => '',
          'edit_item' => 'Редактировать',
          'update_item' => '',
          'add_new_item' => 'Добавить',
          'new_item_name' => '',
          'separate_items_with_commas' => '',
          'add_or_remove_items' => '',
          'choose_from_most_used' => '',
        );
    register_taxonomy('brand', array (
          0 => 'mention',
        ),
        array('hierarchical' => true,
            'label' => 'Производетели',
            'show_ui' => true,
            'query_var' => true,
            'show_admin_column' => false,
            'labels' => $labels
        )); 
    register_taxonomy('element', array (
          0 => 'mention',
        ),
        array( 'hierarchical' => true,
            'label' => 'Элементы экипировки',
            'show_ui' => true,
            'query_var' => true,
            'show_admin_column' => false,
            'labels' => $labels
        )); 
}

add_action('admin_menu', 'comm_gr_pages');

function comm_gr_pages() 
{
	add_submenu_page('edit.php?post_type=mention', 'Настройки отзывов', 'Настройки отзывов', 8, 'comm_gr_manage', 'comm_gr_manage');
    
}

function comm_gr_manage()
{
      $argc = array(
      'orderby' => 'Name', 
	  'hide_empty' => false
       ); 
	
	$arrels = get_terms('element', $argc);
	$arrbrand = get_terms('brand', $argc);
	
    include_once(plugin_dir_path(__FILE__) . 'manage-page.php');
}

add_action('wp_ajax_commgr-get-brand', 'comm_gr_get_brand');
add_action('wp_ajax_nopriv_commgr-get-brand', 'comm_gr_get_brand');

function comm_gr_get_brand() 
{
    $comm_gr_array = unserialize(get_option('comm_gr_array'));
    $el = intval($_GET['pcid']);
    if (isset($comm_gr_array[$el]))
        $ret = $comm_gr_array[$el];
    else
        $ret = array();
    
    print(json_encode($ret));
    exit();
}

add_action('wp_ajax_commgr-set-brand', 'comm_gr_set_brand');

function comm_gr_set_brand() 
{
    $comm_gr_array = unserialize(get_option('comm_gr_array'));
    $el = intval($_POST['element']);
    if (isset($_POST['brand']))
        $arrbr = array_map('intval', $_POST['brand']);
    else
        $arrbr = array();
    
    $comm_gr_array[$el] = $arrbr;
    update_option('comm_gr_array', serialize($comm_gr_array));
//    print_r($comm_gr_array);
    exit();
}


function comm_gr_add_shortcode($atts) 
{
    if (! is_user_logged_in()){
        return 'Необходимо залогинится.';
    }
        
	if(isset($_POST['title'])){
		return comm_gr_add_post();
	}
    
    $argc = array(
      'orderby' => 'Name', 
	  'hide_empty' => false
       ); 
	$arrels = get_terms('element', $argc);
	$arrbrand = get_terms('brand', $argc);
    
    ob_start();
    include_once(plugin_dir_path(__FILE__) . 'comment-block.php');
    $form = ob_get_contents();
    ob_end_clean();
    return $form;
    }

add_shortcode( 'commgr-add-post', 'comm_gr_add_shortcode' );

function comm_gr_add_post()
{
	$title = sanitize_text_field($_POST['title']);
	$description = sanitize_text_field($_POST['description']);
	$brand_id = intval($_POST['brand']);
	$element_id = intval($_POST['element']);
	$rating = intval($_POST['rating']);
	$tags = sanitize_text_field($_POST['tags']);
	$nonce=$_POST['_wpnonce'];


	if (! wp_verify_nonce($nonce) )
		return 'Security check'; 
	
    $user = wp_get_current_user();
    $authorid = $user->ID;
    $new_post = array(
            'post_title'    => $title,
            'post_content'  => $description,
//            'tags_input'    => $tags,
            'post_status'   => 'pending',
            'post_type' => 'mention',  
            'tax_input' => array('brand' => $brand_id, 'element' => $element_id)
    );
    $pid = wp_insert_post($new_post);
    add_post_meta($pid, 'rating', $rating, true);
    add_post_meta($pid, 'lineyka', $tags, true);
	
	return '<h3>Запись отправлена на модерацию<h3>';
}

add_filter('request', 'filter_tag_loop');
    
function filter_tag_loop($params) {
    file_put_contents('deb.txt', print_r($params, true));
    if (isset($params['brand']) and isset($_GET['eldid'])){
        $params['tax_query'] =  array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'brand',
                'field'    => 'slug',
                'terms'    => array( $params['brand'] ),
            ),
            array(
                'taxonomy' => 'element',
                'field'    => 'id',
                'terms'    => array(intval($_GET['eldid'])),
//                    'operator' => 'NOT IN',
            ));
    }
    
    return $params;
}


/*

add_action( 'comment_form_logged_in_after', 'comm_gr_more_fields' );
add_action( 'comment_form_after_fields', 'comm_gr_more_fields' );

function comm_gr_more_fields()
{
    $argc = array(
      'orderby' => 'Name', 
	  'hide_empty' => false
       ); 
	$arrels = get_terms('element', $argc);
	$arrbrand = get_terms('brand', $argc);
    
    include_once(plugin_dir_path(__FILE__) . 'comment-block.php');
}

add_action('wp_insert_comment','comment_inserted',99,2);

function comment_inserted($comment_id, $comment_object) {
    file_put_contents('deb.txt', print_r($_POST, true));
} */

?>