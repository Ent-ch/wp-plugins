<?php
/*
Plugin Name: Xml Shop
Plugin URI: http://www.gdeslon.ru/
Description: Партнерский магазин "Где Слон"
Author: RVM
Version: 0.9
Author URI:
*/  


require_once("xmlshop-inc.php");

class blockkat extends WP_Widget {
	function blockkat() {
		parent::WP_Widget(false, $name = _('Категории товаров'));
	}

	function widget($args, $instance) {
		echo $args['before_widget'] . $args['before_title'] . $args['widget_name'] . $args['after_title'];
		$debug = "";

		global $lstcat;
		$burl = get_bloginfo('wpurl');
		$cid = 0;
		if(isset($_GET['cid'])) $cid = intval($_GET['cid']);
		
		$lstcat = '';
		prn_struc(listcat($cid));
		echo $lstcat;

		echo $args['after_widget'];
	}
}
class blocktovsrc extends WP_Widget {
	function blocktovsrc() {
		parent::WP_Widget(false, $name = _('Поиск товаров'));
	}

	function widget($args, $instance) {
		echo $args['before_widget'] . $args['before_title'] . $args['widget_name'] . $args['after_title'];

		echo xmlshop_search_form($keywords);

		echo $args['after_widget'];
	}
}

class blocktovspec extends WP_Widget {
	function blocktovspec() {
		parent::WP_Widget(false, $name = _('Специальное предложение'));
	}

	function widget($args, $instance) {
		echo $args['before_widget'] . $args['before_title'] . $args['widget_name'] . $args['after_title'];

		echo xmlshop_hit();

		echo $args['after_widget'];
	}
}

function sshop_options() {
	global $status;
	$status = "";
	
	if(isset($_GET['edit'])) {
		$pid = intval($_GET['edit']);
		echo xmlshop_edit_form($pid);
	}
	else {
		$arrparam = unserialize(get_option('xmlshop_param'));
	//	$debug = print_r($arrparam, true);
		if (isset($_POST['xmlparam'])) {
			update_option('xmlshop_param', serialize($_POST['xmlparam']));
			$arrparam = $_POST['xmlparam'];
			$tstatus = "<h2>Сохранено</h2>";
		}
		if(isset($_POST['upcat']) and intval($_POST['upcat']) == 1){
			$begtime = time();
			$colpos = xmlshopload();
			$begtime = time() - $begtime;
			$tstatus = "<h2>Загрузка завершена! Время загрузки $begtime с. Позиций $colpos</h2>";
		}
		
		echo xmlshop_param_form($arrparam) . $tstatus;
	}
}

/* START view functions */
function displayShop() {
    global $rns_prop; 
    switch (get_query_var('op')) {
        case 'all': xml_showAll();
                    break;
        case 'cat': xml_showCat(get_query_var('id'));
                    break;
        case 'prog': xml_showProd(get_query_var('id'));
       // echo "<pre>--"; print_r($rns_prop); echo "--*</pre>"; 
                    break;
        default: xml_showAll();            
      }           
}  
  /*** shows all categories ***/
function xml_showAll() {

	if(isset($_GET['cid'])) $cid = intval($_GET['cid']);
	
	if(isset($_POST['searchword'])){
		$keywords = cleardata($_POST['searchword']);
		if (!$keywords) echo "<h2>Недопустимые параметры поиска!</h2>";
		else echo prnsearch($keywords);
		return;
	}

	if(isset($_GET['cid'])) {
	
		if(isset($_GET['pid'])) {
			$pid = intval($_GET['pid']);
			echo prnoffer($pid);
		}
		else{
			$insarr = array($cid);
			$mycats = incat($insarr);
			echo prncat($mycats);
		}
	}
	else{
		echo xmlshop_hit();
	}

	
//	$degug = print_r($mycats, true);
//	echo "<br> $degug  cid $cid<br>";

}

  /*** shows prods in specific category ***/
  function xml_showCat($id) {
	  $debug = print_r($_GET, true);
      echo '<div style="float:right;height:20px;">proga "'. $debug . '</div>';

  }   
  
  /*** shows specific offer details ***/
  function xml_showProd() {
      echo "";
  }
/* END view functions */     

/** Rewrite rules START **/
function rns_var_flush_rewrite() {
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}

function rns_var_vars($public_query_vars) {
    $public_query_vars[] = 'id';
    $public_query_vars[] = 'op';
    $public_query_vars[] = 'cat';
    $public_query_vars[] = 'pi';
    return $public_query_vars;
}

function rns_var_add_rewrite_rules($wp_rewrite) {
  $new_rules = array(
     'xmlshop/category/(.*)/(.*)' => 'index.php?pagename=xmlshop&op=cat&id='.$wp_rewrite->preg_index(1).'&pi='.$wp_rewrite->preg_index(2),
     'xmlshop/category/(.*)'      => 'index.php?pagename=xmlshop&op=cat&id='.$wp_rewrite->preg_index(1),     
     'xmlshop/(.*)' => 'index.php?pagename=xmlshop&op=prog&id=' . $wp_rewrite->preg_index(1)
  );
  $wp_rewrite->use_trailing_slashes = 0;
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}
/** Rewrite rules END **/ 

// Generate META tags
function rns_title($title){
        global $rns_prop, $post;
        if($post->post_content != '[regnowshop]') return;    
		$arrmeta = xmlshop_title(" | ");
		$rns_prop->title = $arrmeta['name'];

        return $rns_prop->title;
}

function rns_metas($arg) {
    global $post;
	$arrmeta = xmlshop_title();
    if($post->post_content != '[regnowshop]') return;    
    echo "<meta name='description' content='{$arrmeta['name']}' />";
    echo "<meta name='keywords' content='{$arrmeta['usmeta']}' />";  
}


function regnow_admin_actions() {  
  add_options_page("xmlShop", "Партнерская сеть \"Где Слон?\"", 1, "xmlShop", "sshop_options");  
}  

function add_my_stylesheet() {
	$myStyleUrl = WP_PLUGIN_URL . '/xml-shop/xml-shop.css';
	$myStyleFile = WP_PLUGIN_DIR . '/xml-shop/xml-shop.css';
	if ( file_exists($myStyleFile)) {
		wp_register_style('myStyleSheets2', $myStyleUrl);
		wp_enqueue_style( 'myStyleSheets2');
	}
}

function plugin_activate() {
	$my_post = array();
	$my_post['post_name'] = 'xmlshop';
	$my_post['post_title'] = __("Партнерская сеть \"Где Слон?\"");
	$my_post['post_content'] = '[regnowshop]';
	$my_post['post_status'] = 'publish';
	$my_post['post_type'] = 'page';
	$my_post['post_author'] = 1;
	$my_post['comment_status'] = 'closed';
	$my_post['post_category'] = array(0);
	$id = wp_insert_post( $my_post );
	update_option('rgn_page_id', $id);

	wp_schedule_event(time(), 'evtenminutes2', 'xmlshop_cron2');

	mysql_query2(cat_table(3));
	mysql_query2(cat_table());
}

function plugin_deactivate() {
    wp_delete_post(get_option('rgn_page_id'), true);
    delete_option('rgn_page_id');

	wp_clear_scheduled_hook('xmlshop_cron');
	
	mysql_query2(cat_table(2));
	mysql_query2(cat_table(4));
}

function xmlshop_add_evtenminutes2( $schedules ) {
	$schedules['evtenminutes2'] = array(
		'interval' => 600,
		'display' => __('Ten minutes')
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'xmlshop_add_evtenminutes2' ); 
add_action('xmlshop_cron2', 'xmlshop_sync2');
function xmlshop_sync2() {
	wp_mail('ent-ch@ya.ru', 'Xml sync', 'Hello, this is an automatically scheduled email from WordPress.');
}
   

add_action('widgets_init', create_function('', 'return register_widget("blockkat");'));
add_action('widgets_init', create_function('', 'return register_widget("blocktovsrc");'));
//add_action('widgets_init', create_function('', 'return register_widget("blocktovspec");'));

add_action('admin_menu', 'regnow_admin_actions');             
register_activation_hook( __FILE__, 'plugin_activate' );
register_deactivation_hook(__FILE__, 'plugin_deactivate');
add_shortcode('regnowshop', 'displayShop');
add_action('wp_head','rns_metas');
add_action('init', 'rns_var_flush_rewrite');
add_filter('query_vars', 'rns_var_vars');
add_action('generate_rewrite_rules', 'rns_var_add_rewrite_rules');
add_filter('wp_title', 'rns_title');
add_action('wp_print_styles', 'add_my_stylesheet');

?>
