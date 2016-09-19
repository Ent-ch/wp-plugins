<?php
/*
  Plugin Name: Simple posts filter
  Description: 
  Version: 1.0
  Author: Ent_ch
  License: GPLv2
 */

define('NUM_TAG_BLOCKS', 4);
 
register_activation_hook(__FILE__, spfilter_install());
Register_uninstall_hook(__FILE__, spfilter_drop());

function spfilter_install() {
}

function spfilter_drop() {
}

add_action( 'wp_enqueue_scripts', 'spfilter_stylesheet' );
add_action( 'admin_enqueue_scripts', 'spfilter_stylesheet' );

function spfilter_stylesheet() {
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    wp_enqueue_style( 'spfilter-style-sumo', plugins_url('style/sumoselect.css', __FILE__) );
    wp_enqueue_style( 'spfilter-style', plugins_url('style/style.css', __FILE__) );

	wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script( 'spfilter-script-sumo',  plugins_url('js/jquery.sumoselect.min.js', __FILE__), array( 'jquery'));
    wp_enqueue_script( 'spfilter-script', plugins_url('js/simple-post-filter.js', __FILE__), array( 'jquery'));


}

class spfilter_widget extends WP_Widget {
  function __construct() {
    parent::__construct('spfilter_widget', 'Фильтр по категориям',
    array( 'description' => 'Фильтр по категориям', ) 
    );
  }

  private function spfilter_build($typeCategory, $strSelCats = false, $strCats = false, $nameSelect = 'catfilter') {
    if ($strSelCats)
      $SelCats = explode(',', $strSelCats);

	if ($strCats)
      $curCats = explode(',', $strCats);

    $argc = array(
      'orderby' => 'Name', 
      'show_count' => false, 
      'hierarchical' => false, 
	  'taxonomy' => $typeCategory,
      'echo' => 0,
       ); 
    
	
	$arrcats = get_categories($argc);
	
	$html = "<select name=\"".$this->get_field_name($nameSelect)."[]\" class=\"spfilter-{$typeCategory}\" multiple=\"multiple\">";
	foreach($arrcats as $curcat){
		$sel = ($strSelCats && in_array($curcat->term_id, $SelCats)) ? " selected=\"selected\"" : "";
		if (!$strCats)
			$html .= "<option value=\"{$curcat->term_id}\" $sel>{$curcat->name}</option>";
		elseif ($strCats && in_array($curcat->term_id, $curCats))
			$html .= "<option value=\"{$curcat->term_id}\" $sel>{$curcat->name}</option>";
	}
	$html .= "</select>";

	return $html;
  }

  private function spfilter_build_new($typeCategory, $strSelCats = false, $strCats = false, $nameSelect = 'catfilter') {
    if ($strSelCats)
      $SelCats = explode(',', $strSelCats);

	if ($strCats)
      $curCats = explode(',', $strCats);

    $argc = array(
      'orderby' => 'Name', 
      'show_count' => false, 
      'hierarchical' => false, 
	  'taxonomy' => $typeCategory,
      'echo' => 0,
       ); 
    
	
	$arrcats = get_categories($argc);
	
	$html = "<ul class=\"spfilter2-{$typeCategory}\" >";
	foreach($arrcats as $curcat){
//		$sel = ($strSelCats && in_array($curcat->term_id, $SelCats)) ? " selected=\"selected\"" : "";
		$sel = "";
		if (!$strCats)
			$html .= "<li value=\"{$curcat->term_id}\" $sel><a>{$curcat->name}</a></li>";
		elseif ($strCats && in_array($curcat->term_id, $curCats))
			$html .= "<li value=\"{$curcat->term_id}\" $sel><a>{$curcat->name}</a></li>";
	}
	$html .= "</ul>";

	return $html;
  }

  
  public function widget( $args, $instance ) {
   	global $wp_query;

	$datefrom = isset($_GET['datefrom']) ? $_GET['datefrom'] : '';
	$dateto = isset($_GET['dateto']) ? $_GET['dateto'] : '';
	$fcats = isset($_GET['fcats']) ? $_GET['fcats'] : false;
	$ftags = isset($_GET['ftags']) ? $_GET['ftags'] : false;

	extract( $args );
    $title = apply_filters( 'widget_title', $instance['title'] );
    echo $before_widget; 

    if (! empty( $title ))
      echo $before_title . $title . $after_title; 

    if ($ftags){
	}

    if ($datefrom){
		$ctime = round((time() - strtotime($datefrom))/ (60 * 60 * 24));
		
	}
	?>
	
	<div class="spfilter-selper">
		<span>Период</span>
		<a id="sp-filter-w" <?php echo ($ctime == 7)?'class="sfa-sel"':''; ?> href="#">Последняя неделя</a>
		<a id="sp-filter-m" <?php echo ($ctime == 30)?'class="sfa-sel"':''; ?> href="#">Последний месяц</a>
		<a id="sp-filter-y" <?php echo ($ctime == 365)?'class="sfa-sel"':''; ?> href="#">Последний год</a>
	</div>	
	<div class="selfilters">
		Период<br/>
		<label for="sp-filter-from">От</label><input type="text" id="sp-filter-from" name="sp_filter_from" value="<?=$datefrom?>">
		<label for="sp-filter-to">до</label><input type="text" id="sp-filter-to" name="sp_filter_to" value="<?=$dateto?>">
	</div>	
	<?php
	echo "<label class='sp-filter-first'> Выберите рубрики </label>";
    echo $this->spfilter_build('category', $fcats);
	
	for($i=1; $i<=NUM_TAG_BLOCKS; $i++){
		$avtag = (isset($instance["group{$i}"])) ? implode(',', $instance["group{$i}"]) : false;
		$label = (isset($instance["lab($i)"])) ? esc_attr($instance["lab($i)"]) : "";

		if($label or $avtag){
			echo "<p> $label </p>";
			echo $this->spfilter_build('post_tag', $ftags, $avtag);
		}
	}

    echo "<button class='spfilter-submit'>Отобрать</button>";
    
    echo $after_widget; 
  }
    
  public function form($instance) {
	$arrset = array();
	
    $title = (isset($instance['title'])) ? esc_attr($instance['title']) : "";
    echo "<p><label for=\"{$this->get_field_id('title')}\">Заголовок виджета</label>";
    echo "<input class=\"widefat\" id=\"{$this->get_field_id('title')}\" 
    name=\"{$this->get_field_name('title')}\" type=\"text\" value=\"$title\" /></p>";

	for($i=1; $i<=NUM_TAG_BLOCKS; $i++){
		$arrset["sel{$i}"] = (isset($instance["group{$i}"])) ? implode(',', $instance["group{$i}"]) : false;
		$arrset["lab{$i}"] = (isset($instance["lab($i)"])) ? esc_attr($instance["lab($i)"]) : "";

		$idLable = $this->get_field_id("lab($i)");
		$nameLable = $this->get_field_name("lab($i)");
		
		echo "<p><label for=\"{$idLable}\">Заголовок блока тегов $i</label>";
		echo "<input class=\"widefat\" id=\"{$idLable}\" 
		name=\"{$nameLable}\" type=\"text\" value=\"{$arrset["lab{$i}"]}\" /></p>";

		echo "<label> Выберите набор тегов $i</label>";
		echo $this->spfilter_build('post_tag', $arrset["sel{$i}"], false, "group{$i}");
		
	}
	
	?>
	<script>
	(function($) {
	$(function() {
			$( ".spfilter-post_tag" ).SumoSelect({
				placeholder: 'Выберите теги',
				csvDispCount: 10
			});
		})
	})(jQuery)
	</script>
	<?php
}
  
  public function update($new_instance, $old_instance ) {
    return $new_instance;
  }

} 

add_action('widgets_init', create_function('', 'return register_widget("spfilter_widget");'));

add_filter( 'posts_where', 'filter_where' );

function filter_where( $where ) {
	global $wp_query;

	$datefrom = isset($_GET['datefrom']) ? date('Y-m-d H:i:s', strtotime($_GET['datefrom'])) : false;
	$dateto = isset($_GET['dateto']) ? date('Y-m-d H:i:s', strtotime($_GET['dateto'])) : false;

	if ($datefrom)
		$where .= " AND post_date >= '$datefrom'";
	if ($dateto)
		$where .= " AND post_date <= '$dateto'";

	
	return $where;
}

add_filter('request', 'filter_tag_loop');
    
function filter_tag_loop($params) {
	$fcats = isset($_GET['fcats']) ? $_GET['fcats'] : false;
	$ftags = isset($_GET['ftags']) ? $_GET['ftags'] : false;

	if ($ftags) {
		$params['tag__and'] = array_map("intval", explode(',', $ftags));
	}

	if ($fcats) {
		$params['category__in'] = array_map("intval", explode(',', $fcats));
//		$params['category__and'] = array_map("intval", explode(',', $fcats));
	}

	return $params;
}

/* function custom_rewrite_tag() {
	add_rewrite_tag('%datefrom%', '([^&]+)');
	add_rewrite_tag('%dateto%', '([^&]+)');
}
add_action('init', 'custom_rewrite_tag', 10, 0); */

?>
