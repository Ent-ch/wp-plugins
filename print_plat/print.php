<?php

//require_once($_SERVER['DOCUMENT_ROOT'].'/wp-blog-header.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

function prn_plar($id){
	global $wpdb;

	setlocale(LC_TIME, "ru_RU.utf8");
	
	$option = get_option('plat_plugin');
	$table = $wpdb->prefix.$option['dbtable_name'];
	
	$prndata = $wpdb->get_row("SELECT * FROM $table WHERE id = $id", ARRAY_A);

	$prndata['date_added'] = strtotime($prndata['date_added']);
	
	$img = plugins_url('plat.jpg', __FILE__);
//	$path = WP_CONTENT_DIR . "/tmp/";
	$path = WP_PLUGIN_DIR . '/print_plat/pic/';

	$pic = ImageCreateFromjpeg($img); 

	puttext($pic, 133, 874, 38, $prndata['id']);
	puttext($pic, 212, 310, 32, date("d",$prndata['date_added']));
	puttext($pic, 212, 410, 32, strftime("%B",$prndata['date_added']));
	puttext($pic, 212, 691, 32, date("y",$prndata['date_added']));
	
	puttext($pic, 320, 970, 32, $prndata['schet']);
	puttext($pic, 430, 970, 58, $prndata['summa']);
	puttext($pic, 510, 30, 28, prn_fld(num2str($prndata['summa']), 100), 2);
	

	puttext($pic, 290, 150, 28, prn_fld($prndata['fio'], 30));
	puttext($pic, 380, 263, 20, prn_fld($prndata['bank'], 70));
	puttext($pic, 440, 210, 20, prn_fld($prndata['poluch'], 70));
	puttext($pic, 630, 500, 20, prn_fld($prndata['nazn'], 100));

	puttext($pic, 849, 877, 38, $prndata['id']);
	puttext($pic, 930, 310, 32, date("d",$prndata['date_added']));
	puttext($pic, 930, 410, 32, strftime("%B",$prndata['date_added']));
	puttext($pic, 930, 691, 32, date("y",$prndata['date_added']));

	puttext($pic, 1060, 970, 32, $prndata['schet']);
	puttext($pic, 1160, 970, 58, $prndata['summa']);
	puttext($pic, 1240, 30, 28, prn_fld(num2str($prndata['summa']), 100), 2);

	puttext($pic, 1020, 150, 28, prn_fld($prndata['fio'], 30));
	puttext($pic, 1110, 263, 20, prn_fld($prndata['bank'], 70));
	puttext($pic, 1170, 210, 20, prn_fld($prndata['poluch'], 70));
	puttext($pic, 1360, 500, 20, prn_fld($prndata['nazn'], 100));
	

	puttext($pic, 1594, 874, 38, $prndata['id']);
	puttext($pic, 1660, 310, 32, date("d",$prndata['date_added']));
	puttext($pic, 1660, 410, 32, strftime("%B", $prndata['date_added']));
	puttext($pic, 1660, 691, 32, date("y",$prndata['date_added']));
	
	puttext($pic, 1980, 620, 32, $prndata['schet']);
	puttext($pic, 1790, 1160, 42, $prndata['summa']);

	puttext($pic, 1720, 150, 28, prn_fld($prndata['fio'], 30));
	puttext($pic, 1820, 270, 20, prn_fld($prndata['bank'], 15));
	puttext($pic, 1900, 190, 20, prn_fld($prndata['poluch'], 20));
	puttext($pic, 2130, 310, 24, prn_fld($prndata['nazn'], 70));
	
	$imgname = time() . ".jpg";
	$imglink = plugins_url('/pic/'. $imgname, __FILE__);

	Imagejpeg($pic, $path . $imgname);
	ImageDestroy($pic);
	return $imglink;
}

	if(isset($_GET['prnid'])){
		$imglink = prn_plar($_GET['prnid']);
		$prn = '<img src="" width="" height="" alt="" border="0">';
		$prn = '<img src="'. $imglink .'" border="0" width="800px">';
		echo "$prn";
	}

?>