<?php
function mysql_query2($sql){
	$result = mysql_query($sql);
//	$result = db_query($sql);
	return $result;
}

function mysql_fetch_array2($res){
	$result = mysql_fetch_array($res);
//	$result = db_fetch_array($res);
	return $result;
}

function mysql_result2($res, $selrow){
	$result = mysql_result($res, $selrow);
//	$result = db_result($res, $selrow);
	return $result;
}

function xmlshopload(){
	global $xmlfile, $xmldatapath;

	$zipfile = $xmldatapath . '/data.zip';

    $arrparam = unserialize(get_option('xmlshop_param'));
	$remfile = $arrparam['url'];
	xmlshop_getfile($remfile, $zipfile, $xmlfile);	
	return instov();
}


	global $cat_tab, $prod_tab, $xmlfile, $lstcat, $status, $xmlshoppath, $offeditlink, $posperpage, $xmldatapath, $helpdata;
	
	$srvtime = date("H:i");
	$helpdata = "Для автоматической синхронизации, вам необходимо добавить скрипт 'wp-cron.php'. В Расписание задач(Cron), в панеле управление вашим хостингом. Если вы не знаете как это сделать - обратитесь к вашему хостеру. <br> Серверное время $srvtime";
	
	$posperpage = 33;
	
	$offeditlink = "<br><a href=\"/wp-admin/options-general.php?page=xmlShop&edit={$_GET['pid']}\">Редактировать</a>";

	$cat_tab = 'wp_xml_shop_categories';
	$prod_tab = 'wp_xml_shop_offers';
	
	$xmldatapath = WP_CONTENT_DIR;
	$xmlfile = $xmldatapath . '/data.xml';
	
	$xmlshoppath = WP_PLUGIN_URL . '/xml-shop';
	
?>