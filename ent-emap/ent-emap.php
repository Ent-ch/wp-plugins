<?php
/*
Plugin Name: Simple gmap
Description: 
Version: 0.1
Author: Ent-ch
*/
$asd;
if(false)
    echo '';
add_action( 'wp_enqueue_scripts', 'emap_head' );
add_action( 'admin_enqueue_scripts', 'emap_head' );

function emap_head() {
    wp_enqueue_style( 'emap-style', plugins_url('emap-style.css', __FILE__) );

    wp_enqueue_script( 'emap-base', 'https://maps.googleapis.com/maps/api/js?v=3.exp', array( 'jquery'));
    wp_enqueue_script( 'emap-script', plugins_url('emap.js', __FILE__), array( 'jquery'));


}

if ($df == 1) {
    echo 'ik';
}
    
add_action( 'add_meta_boxes', 'emap_add_custom_box' );

add_action( 'save_post', 'emap_save_postdata' );
    
function emap_add_custom_box() {
    add_meta_box( 
        'emap_sectionid',
        __( 'Добавить положение', 'emap_textdomain' ),
        'emap_inner_custom_box',
        'post' 
    );
}

function emap_inner_custom_box( $post ) {
	wp_nonce_field( 'emap_meta_box', 'emap_meta_box_nonce' );
	$value = get_post_meta( $post->ID, '_emap_coordinates', true );
	$zoomlevel = get_post_meta( $post->ID, '_emap_zoomlevel', true );
	if (!$zoomlevel)
		$zoomlevel = 14;
	if ($value){
		$cord = explode(',', $value);
		echo "<script type=\"text/javascript\">var zoomlevel = {$zoomlevel}; var clat = {$cord[0]}; var clon = {$cord[1]}; var arrlocations = [['Текущая позиция', {$cord[0]}, {$cord[1]}]];</script>";
	}
	echo '<input type="hidden" id="emap-coordinates" name="_emap_coordinates" value="' . esc_attr( $value ) . '" size="25" />';
	echo '<input type="hidden" id="emap-zoomlevel" name="_emap_zoomlevel" value="' . esc_attr( $zoomlevel ) . '" size="25" />';
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
	$my_data = sanitize_text_field( $_POST['_emap_zoomlevel'] );
	update_post_meta( $post_id, '_emap_zoomlevel', $my_data );
  
}

function emap_content_callback($content) {
	if(!is_single())
		return $content;
	$cords = get_post_meta( get_the_ID(), '_emap_coordinates', true );
	$zoomlevel = get_post_meta( get_the_ID(), '_emap_zoomlevel', true );
	if (!$zoomlevel)
		$zoomlevel = 14;

	if ($cords){
		$cord = explode(',', $cords);
		$content .= "<script type=\"text/javascript\">var mrkdraggable = false; var zoomlevel = {$zoomlevel}; var clat = {$cord[0]}; var clon = {$cord[1]}; var arrlocations = [['Текущая позиция', {$cord[0]}, {$cord[1]}]];</script>";
		$content .= '<div id="map-canvas"></div>';
	}
	return $content;
}

add_filter('the_content', 'emap_content_callback');

function emap_make_poi($cordstring, $title, $url, $cid) {
	$poi = '';
	$cord = explode(',', $cordstring);
	$poi = "['{$title}', {$cord[0]}, {$cord[1]}, '{$url}', $cid], ";
	return $poi;
}

function emap_all_poi() {
	$arrlocations = '';
	$query = new WP_Query( 'meta_key=_emap_coordinates' );
	$arr_cat = array();
	$content = "<script type=\"text/javascript\">var mrkdraggable = false; var zoomlevel = 6;";
	while ( $query->have_posts() ) {
		$query->the_post();
		$pid = get_the_ID();
		$cords = get_post_meta( $pid, '_emap_coordinates', true );
		$terms = wp_get_post_terms( $pid, 'category');
		foreach($terms as $term)
			$arr_cat[$term->term_id] = $term->name;
			
		$cid = $term->term_id;
		$cord = explode(',', $value);
//		$arrlocations .= '<li>' . get_the_title() . " cord $value  catid $cid</li>";
		$arrlocations .= emap_make_poi($cords, get_the_title(), get_permalink(), $cid);
	}
	wp_reset_postdata();

	$arrcatimg = $caturls = '';
	foreach($arr_cat as $cid => $cat){
		$img = get_post_meta($cid, '_emap_cat_image', true);
		if($img)
			$arrcatimg .= " catimgs[$cid] = '{$img}';";
	
		$caturls .= "<a href=\"#\" data-cid=\"$cid\" class=\"emap-filter\">{$cat}</a>";
	}
	
	$content .= "var arrlocations = [$arrlocations]; $arrcatimg</script>";

//	$content .= '<pre>'.print_r($arr_cat, true).'</pre>';
	$content .= '<div id="map-canvas"></div><a href="#" data-cid="0" class="emap-filter">Показать все</a>';

	
    return $content . $caturls;
}
add_shortcode('emap_all_poi', 'emap_all_poi');

function emap_add_shortcode( $atts ) {
	if(isset($_POST['title'])){
		return emap_add_post();
	}
    extract ( shortcode_atts (array(
        'author' => '1',
    ), $atts ) );

    $form = '<form class="ent-emap-add-post" action="'. esc_url( get_permalink() ) .'" method="post">
            <input type="text" name="author" size="60" required="required" placeholder="' . __('Введите ваше имя', 'ent-emap-post') . '">
            <input type="email" name="email" size="60" required="required" placeholder="' . __('Введите ваш email', 'ent-emap-post') . '">
            <input type="text" name="title" size="60" maxlength="120" required="required" placeholder="' . __('Введите заголовок', 'ent-emap-post') . '">
        '. wp_nonce_field() .'
            <textarea rows="15" cols="72" required="required" name="description" placeholder="' . __('Опишите точку', 'ent-emap-post') . '"></textarea>
	'. wp_dropdown_categories('show_option_none=Выберите категорию...&tab_index=4&taxonomy=category&hide_empty=0&echo=0') .'
    <input type="text" name="tags" size="60" placeholder="' . __('Введите теги через запятую', 'ent-emap-post') . '">
	<input type="hidden" value="'. $author .'" name="authorid">

		<input type="hidden" id="emap-coordinates" name="_emap_coordinates" size="25" />
		<input type="hidden" id="emap-zoomlevel" name="_emap_zoomlevel" size="25" />
		<div id="map-canvas"></div>
		
        <input type="submit" value="' . __('Отправить запись', 'ent-emap-post') . '"> <input type="reset" value="' . __('Очистить', 'ent-emap-post') . '">
        </form>';
	return $form;
    }
add_shortcode( 'emap-add-post', 'emap_add_shortcode' );

function emap_add_post(){
	$title = sanitize_text_field($_POST["title"]);
	$description = sanitize_text_field($_POST["description"]);
	$tags = sanitize_text_field($_POST["tags"]);
	$author = sanitize_text_field($_POST["author"]);
	$email = sanitize_email($_POST["email"]);
	$authorid = intval($_POST["authorid"]);
	$emap_coordinates = sanitize_text_field($_POST["_emap_coordinates"]);
	$emap_zoomlevel = sanitize_text_field($_POST["_emap_zoomlevel"]);
	$nonce=$_POST["_wpnonce"];


	if (! wp_verify_nonce($nonce) )
		return 'Security check'; 
	
    $user = get_user_by("login", $authorid);
    $authorid = $user->ID;
    $new_post = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_category' => array($_POST['cat']),
            'tags_input'    => $tags,
            'post_status'   => 'pending',
            'post_type' => 'post',  
            'post_author' => $authorid 
    );
    $pid = wp_insert_post($new_post);
     
    add_post_meta($pid, 'author', $author, true);
    add_post_meta($pid, '_emap_coordinates', $emap_coordinates, true);
    add_post_meta($pid, '_emap_zoomlevel', $emap_zoomlevel, true);
    add_post_meta($pid, 'author-email', $email, true);
	
	return '<h3>Запись отправлена на модерацию<h3>';
}

add_action('admin_menu', 'emap_add_pages');
function emap_add_pages() {
	add_management_page('Изображения категорий', 'Изображения категорий', 8, 'emapmanage', 'emap_manage_page');
}
function emap_manage_page() {

	if (isset($_POST['cat'])){
		$cat = intval($_POST['cat']);
		$uploadedfile = $_FILES['file-upload'];
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
		if ( $movefile ) {
			update_post_meta($cat, '_emap_cat_image', $movefile['url']);
		}
	}
	$categories = get_terms( 'category', 'orderby=count&hide_empty=0' );
//	print_r($categories);
?>
	<h2>Изображение категорий</h2><br />
	<?php 
		foreach($categories as $ccat){
			$img = get_post_meta($ccat->term_id, '_emap_cat_image', true);
			if($img)
				echo "{$ccat->name} - <img src='{$img}'><br>";
		}
	?>
	<form action="/wp-admin/tools.php?page=emapmanage" method="post" enctype="multipart/form-data">
	<?php wp_dropdown_categories('show_count=1&hierarchical=1'); ?>
	<input type="file" name="file-upload" id="file-upload" />
	<input type="submit" value="Загрузить">
	</form>
<?php
}



 ?>