<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Сводный отчет</title>
</head>

<body>
<?php
//header('Content-Type: text/html; charset=UTF-8');

require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

function print_report_page(){
	global $wpdb;

	echo "<h1>Отчет за период {$_POST['plat_flt']['begdate']} - {$_POST['plat_flt']['enddate']} </h1>";
	
	$option = get_option('plat_plugin');
	$table = $wpdb->prefix.$option['dbtable_name'];
	
	$arrhd = array("Дата", "№", "Кассир", "Плательщик", "Курс", "Банк получателя", "Банковский счет", "Тип", "Получатель", "Сумма(сум)");
	
	$strhd = "";
	$el = 0;
	foreach($arrhd as $val){
		$el++;
		$strhd .= "<td> $val </td>";
	}

	echo '<br><table width="100%" border="1px">';

	
	$arrftr = array();
	$arrusl = array();
	
	if (!current_user_can('edit_users')) $usfltr = " and uid = '{$current_user->id}' ";
	else $usfltr = "";

	$usl = "";
	if (isset($_POST['plat_flt'])){
		
		$arrftr = $_POST['plat_flt'];
		
		foreach ($arrftr as $key=>$val){
			if($val){
				if ($key == 'begdate'){
					$tarr = explode(".", $val);
					$tarr = array_reverse($tarr);
					$val = implode("-", $tarr);
					$arrusl[] = "date_added > '$val 00:00'";
				}
				elseif ($key == 'enddate'){
					$tarr = explode(".", $val);
					$tarr = array_reverse($tarr);
					$val = implode("-", $tarr);
					$arrusl[] = "date_added < '$val 23:59'";
				}
				else
				$arrusl[] = "$key Like '%$val%'";
			}
		}
		if (count($arrusl)>0) $usl = " where ". implode(" and ", $arrusl) . $usfltr;
	}
	else{
		if (!current_user_can('edit_users')) $usl = " where id > 0 " . $usfltr;
	}

	echo '<tr valign="top">'. $strhd . '</tr>';
	
	if(isset($_GET['upid'])) upstat($_GET['upid']);

	$sord = " ORDER BY utv ASC ";
	if(isset($_GET['sord'])) $sord = " ORDER BY {$_GET['sord']} ASC";
	
//	$sql = "SELECT `date_added`, `id`, `fio`, `bank`, `schet`, `summa`, `poluch`, `utv` FROM $table $usl $sord";
	$sql = "SELECT * FROM $table $usl $sord";
	
	$res = $wpdb->get_results($sql);
	$itog = 0;
	foreach ($res as $el) {
		echo '<tr valign="top">';
	
		$datetime = strtotime($el->date_added);
		$tdate = date("d.m.Y", $datetime);
		
		$user_info = get_userdata($el->uid);
		$us_fname = $user_info->user_lastname . ' ' . $user_info->user_firstname ;

		echo "<td>$tdate</td><td>{$el->id}</td><td>{$us_fname}</td><td>{$el->fio}</td>";
		echo "<td>{$el->dopinf}</td><td>{$el->bank}</td><td>{$el->schet}</td><td>" .(($el->plttyp == 2)? "Пл. карта": "Наличные") . "</td>";
		echo "<td>{$el->poluch}</td><td align=\"right \">" . number_format($el->summa, 0, '.', ' ') ."</td></tr>";
		
		$itog += $el->summa;
	}	
	$debug = print_r($sql, true);

	$itog = number_format($itog, 0, '.', ' ');
		
	echo "<td></td><td></td><td></td><td></td>";
	echo "<td></td><td></td><td></td><td></td><td align=\"right \">Итого:</td>";
	echo "<td align=\"right \"><b>$itog</b></td></tr>";

	echo "</table>";
	



//	print_r($sql);

}
/*	if(isset($_GET['prnid'])){
	} */

//	print_r($_POST);
	
	print_report_page();

?>

</body>

</html>