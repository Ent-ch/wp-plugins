<?php
register_nav_menus( array( // Регистрируем 2 меню
	'primary-menu' => 'Верхнее меню',
	'left-menu' => 'Нижнее'
) );
add_theme_support('post-thumbnails'); // Включаем поддержку миниатюр
set_post_thumbnail_size(254, 190); // Задаем размеры миниатюре

if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'name' => 'Home left sidebar',
		'id' => 'sidebar-left',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	)); // Регистрируем сайдбар
	
add_action('init', 'cptui_register_my_cpt_catalog');
function cptui_register_my_cpt_catalog() {
register_post_type('catalog', array(
'label' => 'Товары',
'description' => '',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'catalog', 'with_front' => true),
'query_var' => true,
'supports' => array('title','editor','thumbnail'),
'labels' => array (
  'name' => 'Товары',
  'singular_name' => 'Товар',
  'menu_name' => 'Товары',
  'add_new' => 'Добавить товар',
  'add_new_item' => 'Добавить товар',
  'edit' => 'Edit',
  'edit_item' => 'Редактировать товар',
  'new_item' => 'Новый товар',
  'view' => 'View Товар',
  'view_item' => 'Просмотреть товар',
  'search_items' => 'Search Товары',
  'not_found' => 'No Товары Found',
  'not_found_in_trash' => 'No Товары Found in Trash',
  'parent' => 'Parent Товар',
)
) ); }

add_action('init', 'cptui_register_my_taxes_parts');
function cptui_register_my_taxes_parts() {
register_taxonomy( 'parts',array (
  0 => 'catalog',
),
array( 'hierarchical' => true,
	'label' => 'Группа',
	'show_ui' => true,
	'query_var' => true,
	'show_admin_column' => false,
	'labels' => array (
  'search_items' => 'Группа',
  'popular_items' => '',
  'all_items' => '',
  'parent_item' => '',
  'parent_item_colon' => '',
  'edit_item' => '',
  'update_item' => '',
  'add_new_item' => '',
  'new_item_name' => '',
  'separate_items_with_commas' => '',
  'add_or_remove_items' => '',
  'choose_from_most_used' => '',
)
) ); 
}

add_action('init', 'cptui_register_my_taxes_models');
function cptui_register_my_taxes_models() {
register_taxonomy( 'models',array (
  0 => 'catalog',
),
array( 'hierarchical' => true,
	'label' => 'Модель',
	'show_ui' => true,
	'query_var' => true,
	'show_admin_column' => false,
	'labels' => array (
  'search_items' => 'Модель',
  'popular_items' => '',
  'all_items' => '',
  'parent_item' => '',
  'parent_item_colon' => '',
  'edit_item' => '',
  'update_item' => '',
  'add_new_item' => '',
  'new_item_name' => '',
  'separate_items_with_commas' => '',
  'add_or_remove_items' => '',
  'choose_from_most_used' => '',
)
) ); 
}

class Parts_level extends Walker_Category {  
    private $model;
    
    public function __construct($model)
    {
        $this->model = $model;
    }
        
    function start_lvl(&$output, $depth=1, $args=array()) 
    {  
        $output .= "\n<ul class=\"product_cats22\">\n";  
    }  

    function end_lvl(&$output, $depth=0, $args=array()) 
    { 
        $output .= "</ul>\n";  
    }  

    function start_el(&$output, $item, $depth=0, $args=array()) 
    {
        if ($depth == 0) {
            $output .= "<li class=\"item\"><a href=\"#\">" .esc_attr($item->name);
        }
        else {
            $output .= "<li class=\"item\"><a href=\"{$model}?part={$item->term_id}\">" .esc_attr($item->name);
        }
    }  

    function end_el(&$output, $item, $depth=0, $args=array()) 
    {  
        $output .= "</a></li>\n";  
    }  
}

add_filter('request', 'filter_tag_loop');
function filter_tag_loop($params) {
//    file_put_contents('dex.txt', print_r($params, true));
    if (isset($params['models']) && isset($_GET['part'])){
        $partid = intval($_GET['part']);
        $params['tax_query'] =  array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'models',
                'field'    => 'slug',
                'terms'    => array($params['models']),
            ),
            array(
                'taxonomy' => 'parts',
                'field'    => 'id',
                'terms'    => array($partid),
//                    'operator' => 'NOT IN',
            ));
    }
    
    return $params;
}

function simple_cbutton() {
   if (!session_id()) {
        session_start();
    }
    $prid = 0;
    if (isset($_GET['clcart'])) {
        unset($_SESSION['prarr']);
    }
    if (isset($_POST['prid'])) {
        if (! isset($_SESSION['prarr'])) {
            $_SESSION['prarr'] = array();
        }
        $prid = intval($_POST['prid']);
        $_SESSION['prarr'][] = $prid;
    }
    print_r($_SESSION);
//        echo "adfad {$prid}";
     
   die();
}
 
add_action( 'wp_ajax_simple_cart-button', 'simple_cbutton' );
add_action( 'wp_ajax_nopriv_simple_cart-button', 'simple_cbutton' );


function simple_cart($type = 1){
    $cont = '';
    if (! isset($_SESSION['prarr'])) {
        if ($type == 1)
            return 'Необходимо выбрать ';
        else 
            return;
    }
    $prarr = array_unique($_SESSION['prarr']);
    foreach($prarr as $prid){
        $product = get_post($prid);
        if ($type == 1)
            $cont .= '<a class="scart-items" href="'.get_permalink($prid) ."\">{$product->post_title}</a><br>";
        else
            $cont .= get_permalink($prid) ."    {$product->post_title}, ";
    }
    return $cont;
}

add_shortcode('simple_cart', create_function('', 'return simple_cart();'));
add_shortcode('simple_cart_mail', create_function('', 'return simple_cart(2);'));



function simple_cart_popular()
{
    $ret = '';
    $args = array(
	'post_type' => 'catalog',
	'tax_query' => array(
		array(
			'taxonomy' => 'models',
			'field'    => 'id',
			'terms'    => SPECCAT,
		),
	   ),
    );
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) {
        ob_start();
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            get_template_part('prod-bl');
        }
        $ret = ob_get_contents();
        ob_end_clean();
    }
    wp_reset_postdata();
    
    return $ret;
}

add_shortcode('simple_cart_popular', 'simple_cart_popular');

define('SPECCAT', 806);


   if (!session_id()) {
        session_start();
    }



/*
add_filter('add_meta_boxes', 'hide_meta_boxes_concerts');
 
function hide_meta_boxes_concerts() {
    remove_meta_box('modelsdiv', 'catalog', 'side');
    add_meta_box( 'modelsdiv', 'XXXXXXXXXXX', 'post_categories_meta_box', 'catalog', 'side', 'high', array( 'taxonomy' => 'models' ));
}

*/
?>