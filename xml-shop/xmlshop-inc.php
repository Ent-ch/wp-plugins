<?php
require_once("xmlshop-db-inc.php");

function cat_table($tp = 1){
global $cat_tab, $prod_tab;
	if($tp == 5)
		$sql = "TRUNCATE TABLE `$cat_tab`"; 
	elseif($tp == 6)
		$sql = "TRUNCATE TABLE `$prod_tab`"; 
	elseif($tp == 2)
		$sql = "DROP TABLE `$cat_tab`";
	elseif($tp == 4)
		$sql = "DROP TABLE `$prod_tab`";
	elseif($tp == 3)
		$sql = "CREATE TABLE `$prod_tab` (
		`pr_id` INT( 15 ) NOT NULL DEFAULT  '1',
		`id` INT( 9 ) NOT NULL ,
		`category_id` INT( 9 ) NOT NULL ,
		`name` VARCHAR( 255 ) COLLATE utf8_general_ci NOT NULL ,
		`picture` VARCHAR( 255 ) COLLATE utf8_general_ci NOT NULL ,
		`price` FLOAT( 15.2 ) NOT NULL DEFAULT '0',
		`currencyId` VARCHAR( 3 ) COLLATE utf8_general_ci NOT NULL ,
		`url` VARCHAR( 255 ) COLLATE utf8_general_ci NOT NULL ,
		`description` TEXT COLLATE utf8_general_ci NULL,
		`usmeta` TEXT COLLATE utf8_general_ci NULL,
		`mod_date` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00',
		`enoff` INT( 1 ) NOT NULL DEFAULT  '1',
		`ushit` INT( 1 ) NOT NULL DEFAULT  '0',
		`usmod` INT( 1 ) NOT NULL DEFAULT  '0',
		PRIMARY KEY ( `id` )
		) ENGINE = INNODB;";
	else
		$sql = "CREATE TABLE `$cat_tab` (
		`pr_id` INT( 15 ) NOT NULL DEFAULT  '1',
		`id` INT( 9 ) NOT NULL ,
		`parent_id` INT( 9 ) NOT NULL ,
		`name` VARCHAR( 255 ) COLLATE utf8_general_ci NOT NULL ,
		`mod_date` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00',
		`encat` INT( 1 ) NOT NULL DEFAULT  '1',
		PRIMARY KEY ( `id` )
		) ENGINE = INNODB;";
	return $sql;
}


function instov(){
	global $cat_tab, $prod_tab, $xmlfile, $status;
	
	$cont = "";
	$timeload = date("Y-m-d H:i");
	
//	mysql_query2(cat_table(5));
//	mysql_query2(cat_table(6));

	$sxe = simplexml_load_file($xmlfile);
	
	foreach ($sxe->shop->categories->category as $test) {
		$name = strval($test);
		$atr = strval(intval($test['parent_id']));
		$sqlcnt = "SELECT COUNT(id) FROM $cat_tab where id = {$test['id']}";
		$numcats = mysql_result2(mysql_query2($sqlcnt), 0);
		if ($numcats == 0){
			$sqlcat = "INSERT INTO `$cat_tab` (`id` , `parent_id` , `name`, `mod_date`)
			VALUES ('{$test['id']}', '$atr', '$name', '$timeload');";
			$result = mysql_query2($sqlcat);
		}
		else{
			$sqlcat = "UPDATE $cat_tab SET `encat` = '1', `mod_date` = '$timeload' WHERE id = {$test['id']}";
			$result = mysql_query2($sqlcat);
		}
		
	}

	$colpos = 0;
	foreach ($sxe->shop->offers->offer as $test) {
		$colpos++;
		$clname = replsimb($test->name);
		$cldesc = replsimb($test->description);
		$sqlcnt = "SELECT COUNT(id) FROM $prod_tab where id = {$test['id']}";
		$numoffs = mysql_result2(mysql_query2($sqlcnt), 0);
		if ($numoffs == 0){
			$sqlcat = "INSERT INTO `$prod_tab` (`id` , `category_id` , `name`, `picture`, `currencyId`, `price`, `url`, `description`, `mod_date`)
			VALUES ('{$test['id']}',  '{$test->category_id}',  '$clname', '{$test->picture}', '{$test->currencyId}', '{$test->price}', '{$test->url}', '$cldesc', '$timeload');";
			$result = mysql_query2($sqlcat);
		}
		else{
			$sqlcat = "UPDATE $prod_tab SET `enoff` = '1', `mod_date` = '$timeload' WHERE id = {$test['id']}";
//			echo "$sqlcat <br>";
			$result = mysql_query2($sqlcat);
		}
	}
	$status = "load complite " . $cont;
	
	mysql_query2("UPDATE $cat_tab SET `encat` = '0' WHERE `mod_date` < '$timeload'");
	mysql_query2("UPDATE $prod_tab SET `enoff` = '0' WHERE `mod_date` < '$timeload'");
	
	return $colpos;

}

function listcat($cid = 0){
	global $cat_tab;
	
	$sqlcnt = "SELECT COUNT(id) FROM $cat_tab where id = $cid";
	$numcats = mysql_result2(mysql_query2($sqlcnt), 0);
	if ($numcats == 0 and $cid != 0){
		$cid = 0;
	}
	
	$prstruc = "";
	if(!$cid){
		$resultcat = mysql_query2("SELECT id, name FROM $cat_tab where parent_id = 0 and encat = 1 ORDER BY name ASC");
		while ($row = mysql_fetch_array2($resultcat)) {
			$prstruc[] = array('id'=>$row['id'], 'name'=>$row['name'], 'val'=>'');
		}
	}
	else{
		$cid2 = $cid;
		$resultcat = mysql_query2("SELECT id, name FROM $cat_tab where parent_id = $cid and encat = 1 ORDER BY name ASC");
		while ($row = mysql_fetch_array2($resultcat)) {
			$prstruc[] = array('id'=>$row['id'], 'name'=>$row['name'], 'val'=>'');
		}
		while ($cid2 != 0){
			$cstruc = array();
			$resultup = mysql_query2("SELECT parent_id FROM $cat_tab where id = $cid2 and encat = 1");
			$pcid = $cid2;
			$cid2 = mysql_result2($resultup, 0);

			
			$resultprn = mysql_query2("SELECT id, name FROM $cat_tab where parent_id = $cid2 and encat = 1 ORDER BY name ASC");
			while ($row = mysql_fetch_array2($resultprn)) {
				$sel = "";
				if ($row['id'] == $pcid) $sel = $prstruc;
				$cstruc[] = array('id'=>$row['id'], 'name'=>$row['name'], 'val'=>$sel);
				}
			$prstruc = $cstruc;
		}
	}
	return $prstruc;
}

function incat($arrcat){
		global $cat_tab;
		$begc = count($arrcat);
		$sql = "SELECT id FROM $cat_tab where encat = 1 and parent_id in('".join("','", $arrcat)."')";
		$resultcat = mysql_query2($sql);
		while ($row = mysql_fetch_array2($resultcat)){
			if(!array_search($row['id'], $arrcat)) $arrcat[] = $row['id'];
		}

	$lcat = $arrcat;

	if($begc != count($arrcat))	$lcat = incat($arrcat);

	return $lcat;
}


function prn_struc($arr_str, $level=0){
	global $lstcat;
	if(!is_array($arr_str)) return;
	foreach($arr_str as $el){
		$sel = '';
		if ($el['id'] == $_GET['cid']) $sel = ' id="selcat" ';
		$lstcat .= "<ul class=\"cat$level\">";
		$lstcat .= "<li><a href=\"/xmlshop/?cid={$el['id']}\" $sel>{$el['name']}</a>";
		if(is_array($el['val'])){
			$level++;
			prn_struc($el['val'], $level);
		}
		$lstcat .= "</ul>";
	}
}

function replsimb($instr){
$clstr = str_ireplace(array("'", "&apos;"), " ", $instr);
return $clstr;
}

function prncat($arrcat = array()){
	global $prod_tab, $cat_tab, $posperpage;
	$cont = '';

	$sqlcnt = "SELECT COUNT(id) FROM $cat_tab where id = {$arrcat[0]}";
	$numcats = mysql_result2(mysql_query2($sqlcnt), 0);
	if ($numcats == 0){
		return "Страница не найдена!";
	}

	if (isset($_GET['pg'])) $page = intval($_GET['pg']) - 1;
	else $page = 0;
	$offset = $page * $posperpage;
	
	$sqlcnt = "SELECT COUNT(id) FROM $prod_tab where enoff = 1 and category_id in('".join("','", $arrcat)."')";
	$tovs = mysql_result2(mysql_query2($sqlcnt), 0);
	$num_pages = ceil($tovs / $posperpage);

	$sql = "SELECT id, picture, name, price, url FROM $prod_tab where enoff = 1 and category_id in('".join("','", $arrcat)."') ORDER BY name ASC LIMIT $offset , $posperpage";
	$resultoff = mysql_query2($sql);
	if(!$resultoff) $cont .= "Invalid query on |$sql|: " . mysql_error() . "<br>";

	while ($row = mysql_fetch_array2($resultoff)){
		$cont .= "<div class=\"catoffer\"><img src=\"{$row['picture']}\"><span class=\"offname\">{$row['name']}</span>";
		$cont .= "<span class=\"offprice\">{$row['price']} руб.</span>";
		$cont .= "<span class=\"offurl\"><a href=\"/xmlshop?cid={$_GET['cid']}&pid={$row['id']}\">Подробнее</a></span>";
		$cont .= "</div>";
	}
		
	$mess = "<div class=\"offpager\"> Страница: ";
	for($i=1;$i<=$num_pages;$i++) {
		if ($i-1 == $page) {
			$mess .= " $i ";
		} else {
			$mess .= " <a href=\"/xmlshop/?cid={$_GET['cid']}&pg=$i\">$i</a> ";
		}
	}
	$mess .= "</div>";
	
	return $cont . $mess;	
}

function makedescr($indescr){
	$mddescr = '';
	$mddescr = str_ireplace("\n", "<br>", $indescr);
	return $mddescr;
}

function prnoffer($pid){
	global $prod_tab, $xmlshoppath, $offeditlink;
	$cont = '';
	
	$sql = "SELECT * FROM $prod_tab where enoff = 1 and id = $pid";
	$resultoff = mysql_query2($sql);

	$row = mysql_fetch_array2($resultoff);
	
	$row['description'] = makedescr($row['description']);

	$cont .= "<div class=\"pageoffer\"><h1>{$row['name']}</h1>";
	$cont .= "<br><div class=\"description\">{$row['description']}</div>$offeditlink</div>";
	$cont .= "<div class=\"pagepict\"><img src=\"{$row['picture']}\"></div>";
	$cont .= "<div class=\"offprice\">{$row['price']} руб.</div>";
	$cont .= "<div class=\"offurl\"><a href=\"{$row['url']}\"><img alt=\"Купить\" src=\"$xmlshoppath/keep.jpg\"></a></div>";
	
	return $cont;	
}

function xmlshop_param_form($arrparam) {
	global $helpdata;
	$srvtime = date("H:i");
	$form = '
	<div class="wrap">
	<h2>Параметры магазина</h2>
	<form method="post" action="">
		<table class="form-table">

		<tr valign="top">
			<th scope="row">Помощь</th>		
			<td collspan=2>'.$helpdata.'</td>
		</tr>
		<tr valign="top">
			<th scope="row">Адрес файла синхронизации</th>
			<td><input type="text" name="xmlparam[url]" value="'.$arrparam['url'].'" size="70" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Время синхронизации (чч:мм)</th>
			<td>
			<select name="xmlparam[timeh]">
			  <option value="'. (($arrparam['timeh'] == '00')? "{$arrparam['timeh']}\" selected" : "00\"") .'>00</option>
			  <option value="'. (($arrparam['timeh'] == '01')? "{$arrparam['timeh']}\" selected" : "01\"") .'>01</option>
			  <option value="'. (($arrparam['timeh'] == '02')? "{$arrparam['timeh']}\" selected" : "02\"") .'>02</option>
			  <option value="'. (($arrparam['timeh'] == '03')? "{$arrparam['timeh']}\" selected" : "03\"") .'>03</option>
			  <option value="'. (($arrparam['timeh'] == '04')? "{$arrparam['timeh']}\" selected" : "04\"") .'>04</option>
			  <option value="'. (($arrparam['timeh'] == '05')? "{$arrparam['timeh']}\" selected" : "05\"") .'>05</option>
			  <option value="'. (($arrparam['timeh'] == '06')? "{$arrparam['timeh']}\" selected" : "06\"") .'>06</option>
			  <option value="'. (($arrparam['timeh'] == '07')? "{$arrparam['timeh']}\" selected" : "07\"") .'>07</option>
			  <option value="'. (($arrparam['timeh'] == '08')? "{$arrparam['timeh']}\" selected" : "08\"") .'>08</option>
			  <option value="'. (($arrparam['timeh'] == '09')? "{$arrparam['timeh']}\" selected" : "09\"") .'>09</option>
			  <option value="'. (($arrparam['timeh'] == '10')? "{$arrparam['timeh']}\" selected" : "10\"") .'>10</option>
			  <option value="'. (($arrparam['timeh'] == '11')? "{$arrparam['timeh']}\" selected" : "11\"") .'>11</option>
			  <option value="'. (($arrparam['timeh'] == '12')? "{$arrparam['timeh']}\" selected" : "12\"") .'>12</option>
			  <option value="'. (($arrparam['timeh'] == '13')? "{$arrparam['timeh']}\" selected" : "13\"") .'>13</option>
			  <option value="'. (($arrparam['timeh'] == '14')? "{$arrparam['timeh']}\" selected" : "14\"") .'>14</option>
			  <option value="'. (($arrparam['timeh'] == '15')? "{$arrparam['timeh']}\" selected" : "15\"") .'>15</option>
			  <option value="'. (($arrparam['timeh'] == '16')? "{$arrparam['timeh']}\" selected" : "16\"") .'>16</option>
			  <option value="'. (($arrparam['timeh'] == '17')? "{$arrparam['timeh']}\" selected" : "17\"") .'>17</option>
			  <option value="'. (($arrparam['timeh'] == '18')? "{$arrparam['timeh']}\" selected" : "18\"") .'>18</option>
			  <option value="'. (($arrparam['timeh'] == '19')? "{$arrparam['timeh']}\" selected" : "19\"") .'>19</option>
			  <option value="'. (($arrparam['timeh'] == '20')? "{$arrparam['timeh']}\" selected" : "20\"") .'>20</option>
			  <option value="'. (($arrparam['timeh'] == '21')? "{$arrparam['timeh']}\" selected" : "21\"") .'>21</option>
			  <option value="'. (($arrparam['timeh'] == '22')? "{$arrparam['timeh']}\" selected" : "22\"") .'>22</option>
			  <option value="'. (($arrparam['timeh'] == '23')? "{$arrparam['timeh']}\" selected" : "23\"") .'>23</option>
			</select> : 
			<select name="xmlparam[timem]">
			  <option value="'. (($arrparam['timem'] == '00')? "{$arrparam['timem']}\" selected" : "00\"") .'>00</option>
			  <option value="'. (($arrparam['timem'] == '10')? "{$arrparam['timem']}\" selected" : "10\"") .'>10</option>
			  <option value="'. (($arrparam['timem'] == '20')? "{$arrparam['timem']}\" selected" : "20\"") .'>20</option>
			  <option value="'. (($arrparam['timem'] == '30')? "{$arrparam['timem']}\" selected" : "30\"") .'>30</option>
			  <option value="'. (($arrparam['timem'] == '40')? "{$arrparam['timem']}\" selected" : "40\"") .'>40</option>
			  <option value="'. (($arrparam['timem'] == '50')? "{$arrparam['timem']}\" selected" : "50\"") .'>50</option>
			</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Количество товаров на странице</th>
			<td><input type="text" name="xmlparam[offppage]" value="'.$arrparam['offppage'].'" size="10" /></td>
		</tr>
		</table>
		<input type="hidden" name="upcat" value="0" id="upcatval"/>
		<p class="submit">
		<input type="submit" class="button-primary" value="Сохранить" />
		<input type="submit" class="button-primary" value="Сохранить и обновить" onclick="document.getElementById(\'upcatval\').value = \'1\';" />
		</p>
	</form>
	</div>';

	return $form;
}

function xmlshop_edit_form($pid) {
	global $prod_tab;
	
	$status = '';
	if (isset($_POST['editoff'])){
		
		$savedata = array_map('stripslashes', $_POST['editoff']);
		if(isset($savedata['spec'])) $ushit = 1;
		else $ushit = 0;

		$sqlup = "UPDATE $prod_tab SET `name` = '{$savedata['name']}', `description` = '{$savedata['description']}', `usmeta` = '{$savedata['usmeta']}', `usmod` = '1', `ushit` = '$ushit' WHERE `id` = '$pid' LIMIT 1;";
		$resultup = mysql_query2($sqlup);
		$status = '<h2>Данные сохранены</h2>';
//		$status = print_r($savedata, true);
	}

	$sql = "SELECT * FROM $prod_tab where enoff = 1 and id = $pid";
	$resultoff = mysql_query2($sql);

	$arrtov = mysql_fetch_array2($resultoff);
	
	$form = '
	<div class="wrap">
	<h2>Редактировать товар</h2>
	<form method="post" action="">
		<table class="form-table">

		<tr valign="top">
			<th scope="row">Название</th>
			<td><input type="text" name="editoff[name]" value="'.$arrtov['name'].'" size="70" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Описание</th>
			<td><textarea name="editoff[description]" cols="50" rows="10" >'.$arrtov['description'].'</textarea></td>
		</tr>
		<tr valign="top">
			<th scope="row">Мета</th>
			<td><textarea name="editoff[usmeta]" cols="50" rows="5" >'.$arrtov['usmeta'].'</textarea></td>
		</tr>
		</table>
		<input name="editoff[spec]" type="checkbox" value="ON" '. (($arrtov['ushit'] == 1)? " checked" : "") .'> Специальное предложение
		<p class="submit">
		<input type="submit" class="button-primary" value="Сохранить" />
		</p>
	</form>
	</div>';

	return $form . $status;
}

function xmlshop_search_form($keywords) {
	if(isset($_POST['searchword'])){
		
		$keywords = cleardata($_POST['searchword']);
	}

	$form = '
	<div class="xmlshop_search">
	<form method="post" action="/xmlshop/">
		<input type="text" name="searchword" value="'.$keywords.'" size="20" />
		<input type="submit" class="button-primary" value="Найти" />
	</form>
	</div>';

	return $form;
}

function xmlshop_getfile($remfile, $zipfile, $tempxmlfile){
	if(!$putdata = fopen($remfile, "r")){
		echo "Не могу открыть файл ($remfile)";
		exit;
	}

	$fp = fopen($zipfile, "w");
	if(!$fp){
		echo "Не могу открыть файл ($zipfile)";
		exit;
	}

	$fpzip = fopen($tempxmlfile, "w");
	if(!$fp){
		echo "Не могу открыть файл ($tempxmlfile)";
		exit;
	}
	
	while ($data = fread($putdata, 1024))
	  fwrite($fp, $data);

	fclose($fp);
	fclose($putdata);

	$zip = zip_open($zipfile);
	if ($zip) {

		while ($zip_entry = zip_read($zip)) {
			if (zip_entry_open($zip, $zip_entry, "r")) {
				while ($data = zip_entry_read($zip_entry, 1024))
				  fwrite($fpzip, $data);
				zip_entry_close($zip_entry);
			}
		}
		zip_close($zip);
	}
	fclose($fpzip);
//	echo "download finish!";
}


function prnsearch($keywords){
	global $prod_tab, $cat_tab, $posperpage;
	$cont = '';

	if (isset($_GET['pg'])) $page = intval($_GET['pg']) - 1;
	else $page = 0;
	$offset = $page * $posperpage;
	
	$sqlcnt = "SELECT COUNT(id) FROM $prod_tab where enoff = 1 and name like '%$keywords%'";
	$tovs = mysql_result2(mysql_query2($sqlcnt), 0);

	if ($tovs == 0) return "<h2>По заданным критериям ничего не найдено!<h2>";
	
	$num_pages = ceil($tovs / $posperpage);

	$sql = "SELECT id, picture, name, price, url FROM $prod_tab where enoff = 1 and name like '%$keywords%' ORDER BY name ASC LIMIT $offset , $posperpage";
	$resultoff = mysql_query2($sql);
	if(!$resultoff) $cont .= "Invalid query on |$sql|: " . mysql_error() . "<br>";

	while ($row = mysql_fetch_array2($resultoff)){
		$cont .= "<div class=\"catoffer\"><img src=\"{$row['picture']}\"><span class=\"offname\">{$row['name']}</span>";
		$cont .= "<span class=\"offprice\">{$row['price']} руб.</span>";
		$cont .= "<span class=\"offurl\"><a href=\"/xmlshop?cid={$_GET['cid']}&pid={$row['id']}\">Подробнее</a></span>";
		$cont .= "</div>";
	}
		
	$mess = "<div class=\"offpager\"> Страница: ";
	for($i=1;$i<=$num_pages;$i++) {
		if ($i-1 == $page) {
			$mess .= " $i ";
		} else {
			$mess .= " <a href=\"/xmlshop/?cid={$_GET['cid']}&pg=$i\">$i</a> ";
		}
	}
	$mess .= "</div>";
	
	return $cont . $mess;	
}

function xmlshop_hit(){
	global $prod_tab, $cat_tab, $posperpage;
	$cont = '<h1>Специальное предложение</h1>';

	$sql = "SELECT id, picture, name, price FROM $prod_tab where enoff = 1 and ushit = 1;";
	$resultoff = mysql_query2($sql);
	if(!$resultoff) $cont .= "Invalid query on |$sql|: " . mysql_error() . "<br>";

	while ($row = mysql_fetch_array2($resultoff)){
		$cont .= "<div class=\"catoffer\"><img src=\"{$row['picture']}\"><span class=\"offname\">{$row['name']}</span>";
		$cont .= "<span class=\"offprice\">{$row['price']} руб.</span>";
		$cont .= "<span class=\"offurl\"><a href=\"/xmlshop?cid={$_GET['cid']}&pid={$row['id']}\">Подробнее</a></span>";
		$cont .= "</div>";
	}
		
	
	return $cont;	
}

function cleardata($text){
	$text = trim($text);
	if (preg_match("/[^(\w)|(\x7F-\xFF-)|(\s)]/",$text)){
	  $text = 'Недопустимые символы';
	  $text = '';
	}
	if (preg_match("/script|http|<|>|<|>|SELECT|UNION|UPDATE|exe|exec|INSERT|tmp/i",$text)){
	  $text = 'Недопустимые слова';
	  $text = '';
	}
	return $text;
}

function xmlshop_title($suff = "") {
	global $cat_tab, $prod_tab;
	$arrtov = array();
	
	if (isset($_GET['pid'])){
		$pid = intval($_GET['pid']);
		$sqloff = "SELECT name, usmeta FROM $prod_tab where enoff = 1 and id = $pid";
		$resultoff = mysql_query2($sqloff);
		$arrtov = mysql_fetch_array2($resultoff);
	}

	if (isset($_GET['cid']) and !isset($_GET['pid'])){
		$cid = intval($_GET['cid']);
		$sqlcnt = "SELECT COUNT(id) FROM $cat_tab where id = $cid";
		$numcats = mysql_result2(mysql_query2($sqlcnt), 0);
		if ($numcats == 0 and $cid != 0){
			return "Страница не найдена!";
		}
		$sqlcat = "SELECT name FROM $cat_tab where encat = 1 and id = $cid";
		$resultoff = mysql_query2($sqlcat);
		$arrtov = mysql_fetch_array2($resultoff);
	}
	$arrtov['name'] = $arrtov['name'] . $suff;
	return $arrtov;
}

?>