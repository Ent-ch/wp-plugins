<?php
/*
Plugin Name: Printing plat
Plugin URI: http://gps.ck.ua/
Description: Printing plat
Version: 0.3
Author: RVM
Author URI: http://gps.ck.ua/
License: GPL2
*/

require_once( dirname( __FILE__ ) . '/zagmetod.php' );
require_once( dirname( __FILE__ ) . '/print.php' );

function plat_install() {
	$option = get_option('plat_plugin');
	
	if (false === $option) {		
		$option = array();
		$option['version'] = "0.1";
		$option['dbtable_name'] = "plat_data";
		$option['uninstall'] = false;
		
		add_option('plat_plugin', $option);
		
	  global $wpdb;
	  $table = $wpdb->prefix.$option['dbtable_name'];
	  $structure = "CREATE TABLE IF NOT EXISTS $table (
									id INT(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									date_added DATETIME NOT NULL,
									plttyp INT(1) NOT NULL,
									fio VARCHAR(150),
									dopinf VARCHAR(150),
									cgrupp VARCHAR(150),
									fakult VARCHAR(150),
									bank VARCHAR(150),
									schet VARCHAR(40),
									summa FLOAT(15, 2),
									poluch VARCHAR(150),
									nazn VARCHAR(255),
									utv INT(1),
									uid INT(9)
									);";
	  $wpdb->query($structure);				

	  $tabledata = $wpdb->prefix . 'plat_spr';
	  $structure = "CREATE TABLE IF NOT EXISTS $tabledata (
									id INT(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									sprtyp INT(1) NOT NULL,
									data VARCHAR(255)
									);";
	  $wpdb->query($structure);				

	}
}

function plat_uninstaller() {
	$option = get_option('plat_plugin');
	
	global $wpdb;
	$table = $wpdb->prefix.$option['dbtable_name'];
//	$wpdb->query("DROP TABLE " . $table);

	delete_option('plat_plugin');
}

function plat_add_pages() {
    add_menu_page('Платежи', 'Платежи', 0, __FILE__, 'plat_toplevel_page');
    add_submenu_page(__FILE__, 'Добавление платежа', 'Добавление платежа', 0, 'plateji_sub-page', 'plat_chk_page');
    add_submenu_page(__FILE__, 'Отчет по платежам', 'Справочные данные', 1, 'plateji_sub-pg2', 'data_page');
    add_submenu_page(__FILE__, 'Отчет по платежам', 'Отчет по платежам', 1, 'plateji_sub-page2', 'report_page');
}

function add_my_stylesheet() {
        $myStyleUrl = WP_PLUGIN_URL . '/print_plat/style.css';
        $myStyleFile = WP_PLUGIN_DIR . '/print_plat/style.css';
        if ( file_exists($myStyleFile) ) {
            wp_register_style('myStyleSheets', $myStyleUrl);
            wp_enqueue_style( 'myStyleSheets');
        }
        $myStyleUrl = WP_PLUGIN_URL . '/print_plat/style2.css';
        $myStyleFile = WP_PLUGIN_DIR . '/print_plat/style2.css';
        if ( file_exists($myStyleFile) and !current_user_can('edit_users')) {
            wp_register_style('myStyleSheets2', $myStyleUrl);
            wp_enqueue_style( 'myStyleSheets2');
        }
}

function redirect_to_home() {
	$from = $_SERVER['HTTP_REFERER'];
	if(strpos($from, 'wp-login.php')){
		echo '<script type="text/javascript">location.replace("'.site_url().'/wp-admin/admin.php?page=print_plat/mainplat.php");</script>';
	}
}

add_action("admin_init", "redirect_to_home");
register_activation_hook( __FILE__, 'plat_install');
register_deactivation_hook( __FILE__, 'plat_uninstaller');
add_action('wp_print_styles', 'add_my_stylesheet');
add_action('admin_menu', 'plat_add_pages');



function plat_make_inp_form($ishdata = array()) {
?>
<div class="wrap">
<h2>СИСТЕМА УПРАВЛЕНИЯ ПЛАТЕЖАМИ</h2>

<form method="post" action="">
    <?php settings_fields( 'baw-settings-group' ); ?>
    <table class="form-table">
	<tr valign="top">
        <th scope="row">Тип оплаты</th>
        <td>
		<input name="plat[plttyp]" type="radio" value="1" <?php echo (($ishdata['plttyp'] == '1')? "checked" : "")?>> Наличные
		<input name="plat[plttyp]" type="radio" value="2" <?php echo (($ishdata['plttyp'] == '2')? "checked" : "")?>> Пл. карта
		</td>
    </tr>

	<tr valign="top">
        <th scope="row">Плательщик</th>
        <td><input type="text" name="plat[fio]" value="<?php echo $ishdata['fio']; ?>" size="50" /></td>
    </tr>
         
        <tr valign="top">
        <th scope="row">Курс</th>
        <td><?php echo plat_make_sel("plat[dopinf]", 1, $ishdata['dopinf']) ?></td>
        </tr>

        <tr valign="top">
        <th scope="row">Группа</th>
        <td><input type="text" name="plat[cgrupp]" value="<?php echo $ishdata['cgrupp']; ?>" size="50" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Факультет</th>
        <td><input type="text" name="plat[fakult]" value="<?php echo $ishdata['fakult']; ?>" size="50" /></td>
        </tr>

        
        <tr valign="top">
        <th scope="row">Банк получателя</th>
        <td><?php echo plat_make_sel("plat[bank]", 2, $ishdata['bank']) ?></td>
        </tr>

        <tr valign="top">
        <th scope="row">Банковский счет получателя</th>
        <td><input type="text" name="plat[schet]" value="<?php echo $ishdata['schet']; ?>" size="50" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Сумма</th>
        <td><input type="text" name="plat[summa]" value="<?php echo $ishdata['summa']; ?>" size="20" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Получатель</th>
        <td><?php echo plat_make_sel("plat[poluch]", 3, $ishdata['poluch']) ?></td>
        </tr>

        <tr valign="top">
        <th scope="row">Назначение платежа</th>
        <td><textarea name="plat[nazn]" cols="40" rows="3" ><?php echo $ishdata['nazn']; ?></textarea></td>
        </tr>
		</table>

   
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php 
}

function plat_frt($ishdata = array()) {

	if(!isset($ishdata['begdate'])) $ishdata['begdate'] = date("d.m.Y");
	if(!isset($ishdata['enddate'])) $ishdata['enddate'] = date("d.m.Y");
?>
<form method="post" action="/wp-admin/admin.php?page=print_plat/mainplat.php">
	<tr valign="top">
        <td>
		<div  style="width:110px;">
		с&nbsp;&nbsp;&nbsp;<input type="text" name="plat_flt[begdate]" value="<?php echo $ishdata['begdate']; ?>" size="10" /><br>
		по&nbsp;<input type="text" name="plat_flt[enddate]" value="<?php echo $ishdata['enddate']; ?>" size="10" />
		</div>
		</td>

        <td><input type="text" name="plat_flt[id]" value="<?php echo $ishdata['id']; ?>" size="3" /></td>

		<?php if (current_user_can('edit_users'))	echo '<td>'. plt_users() .'</td>'; ?>
		
        <td><input type="text" name="plat_flt[fio]" value="<?php echo $ishdata['fio']; ?>" size="13" /></td>

        <td><?php echo plat_make_sel("plat_flt[dopinf]", 1, $ishdata['dopinf'], true) ?></td>

        <td><?php echo plat_make_sel("plat_flt[bank]", 2, $ishdata['bank'], true) ?></td>

        <td><input type="text" name="plat_flt[schet]" value="<?php echo $ishdata['schet']; ?>" size="18" /></td>

        <td>
		<select name="plat_flt[plttyp]" >
			  <option value="" <?php echo ((!isset($ishdata['plttyp']))? "selected" : "")?>>Все</option>
			  <option value="1" <?php echo (($ishdata['plttyp'] == '1')? "selected" : "")?>>Наличные</option>
			  <option value="2" <?php echo (($ishdata['plttyp'] == '2')? "selected" : "")?>>Пл. карта</option>
		</select>
		</td>

        <td><?php echo plat_make_sel("plat_flt[poluch]", 3, $ishdata['poluch'], true) ?></td>

        <td><input type="text" name="plat_flt[summa]" value="<?php echo $ishdata['summa']; ?>" size="7" /></td>

        <td><input type="submit" class="button-primary" value="Фильтр" /></td>
    </tr>
</form>
<?php 
}

function plat_make_head($htext = "") {

?>
<div style="width:100%;">
	<h2><?php echo $htext; ?></h2>
</div>
<div style="width:100%;">
	<div style="width:60px;height:25px; border:1px #3366CC solid; background: #3366CC; color:#FFFFFF; font-weight:bold; font-size:14px; float:left; padding-left:5px; padding-right:5px; padding-top:5px; font:'Tahoma', Times New Roman, serif">Дата:</div>
    <div style="width:100px;height:25px; border:1px #3366CC solid; font-weight:bold; font-size:14px;float:left; padding-left:5px; padding-right:5px; padding-top:5px;"><?php echo date("d.m.Y"); ?></div>
    <div style="width:120px;height:25px; border:1px #7f7f7f solid; background: #7f7f7f; color:#FFFFFF; font-weight:bold; font-size:14px; float:left; padding-left:5px; padding-right:5px; padding-top:5px; font:'Tahoma', Times New Roman, serif">Пользователь:</div>
    <div style="width:250px;height:25px; border:1px #7f7f7f solid; padding-left:5px; padding-right:5px; padding-top:5px; font-weight:bold; font-size:14px;float:left"><?php global $current_user; get_currentuserinfo(); echo $current_user->user_lastname . ' ' . $current_user->user_firstname . "\n"; ?></div>
    <div style="width:160px;height:25px; border:1px #7f7f7f solid; background: #7f7f7f; color:#FFFFFF; font-weight:bold; font-size:14px; float:left; padding-left:5px; padding-right:5px; padding-top:5px; font:'Tahoma', Times New Roman, serif">Всего поступлений:</div>
    <div style="width:250px;height:25px; border:1px #7f7f7f solid; padding-left:5px; padding-right:5px; padding-top:5px; font-weight:bold; font-size:14px;float:left"><span id="plt_itog"> 0</span></div>    
</div>

<?php 
}

function plat_chk_page() {
	global $wpdb, $current_user;
    get_currentuserinfo();
	
	if (current_user_can('edit_users')){
		echo "<h1>Вы не можете вносить платежи!</h1>";
		return;
	}
	
	$newarr = array();

	$option = get_option('plat_plugin');
	$table = $wpdb->prefix.$option['dbtable_name'];		
	
	if(isset($_GET['eid'])){
		plat_make_head("Добавление платежа");
		$eid = $_GET['eid'];
		$sql = "SELECT * FROM $table Where id = $eid";
		$newarr = $wpdb->get_row($sql, ARRAY_A);
	}
	else {
		plat_make_head("Редактирование");
	
	}

	if(isset($_POST['plat'])){
		$newarr = $_POST['plat'];

		if(isset($_GET['eid'])){
			$cid = $_GET['eid'];
			$wpdb->query("DELETE FROM $table WHERE `id` = $cid");
			$newarr['id'] = $cid;
			
		}

		$newarr['date_added'] = current_time('mysql');
		$newarr['uid'] = $current_user->id;
		$newarr['utv'] = 0;
	
		$res = $wpdb->insert($table, $newarr);
		$debug = print_r($newarr, true);
//		$debug = print_r($newarr, true);
		if (!$res) echo "Ошибочные символы в данных! $debug";
		else{
			echo '<script type="text/javascript">
				location.replace("'.site_url().'/wp-admin/admin.php?page=print_plat/mainplat.php");
				</script>';
			echo "Данные внесены! ";
		}
	}

	plat_make_inp_form($newarr);

}

function plat_toplevel_page() {
	global $wpdb, $current_user;

	$option = get_option('plat_plugin');
	$table = $wpdb->prefix.$option['dbtable_name'];
	
	if (current_user_can('edit_users'))
		$arrhd = array("Дата", "№", "Кассир", "Плательщик", "Курс", "Банк получателя", "Банковский счет", "Тип", "Получатель", "Сумма (сум)", "Состояние");
	else
		$arrhd = array("Дата", "№", "Плательщик", "Курс", "Банк получателя", "Банковский счет", "Тип", "Получатель", "Сумма (сум)", "Состояние");
	
	$strhd = "";
	$el = 0;
	foreach($arrhd as $val){
		$el++;
		if(isset($_GET['sord']) and $_GET['sord'] == $el)
			$strhd .= "<td><a href=\"/wp-admin/admin.php?page=print_plat/mainplat.php&sordb={$el}\">" . $val . "</a></td>";
		else
			$strhd .= "<td><a href=\"/wp-admin/admin.php?page=print_plat/mainplat.php&sord={$el}\">" . $val . "</a></td>";
	}

	plat_make_head("Таблица платежей");

	echo '<br><br><br><table class="plttable">';

	$rur = substr($_SERVER['REQUEST_URI'], 0, 48);
	
	$arrftr = array();
	$arrusl = array();
	
	if (!current_user_can('edit_users')) $usfltr = " and uid = '{$current_user->id}' ";
	else $usfltr = "";

	$cdate = date("Y-m-d");
	$arrusl[] = "date_added > '$cdate 00:00'";
	$arrusl[] = "date_added < '$cdate 23:59'";
	
	$usl = "";
	if (isset($_POST['plat_flt'])){
		
		$arrftr = $_POST['plat_flt'];
		$arrusl = array();
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
				elseif ($key == 'id'){
					$arrid = explode('-', $val);
					$arrusl[] = "id >= {$arrid[0]}";
					if(isset($arrid[1])) $arrusl[] = "id <= {$arrid[1]}";
					else $arrusl[] = "id <= {$arrid[0]}";
				}
				else
				$arrusl[] = "$key Like '%$val%'";
			}
		}
	}
	if (count($arrusl)>0)
		$usl = " where ". implode(" and ", $arrusl) . $usfltr;
	else
		$usl = " where id > 0 " . $usfltr;

	plat_frt($arrftr);

	echo '<tr valign="top">';
	echo $strhd;
	echo '</tr>';
	
	if(isset($_GET['upid'])) upstat($_GET['upid']);

	if(isset($_GET['sord'])) $sord = " ORDER BY {$_GET['sord']} ASC";
	elseif(isset($_GET['sordb'])) $sord = " ORDER BY {$_GET['sordb']} DESC";
	else $sord = " ORDER BY utv ASC ";
	
//	$sql = "SELECT * FROM $table $usl $sord";
	
	if (current_user_can('edit_users'))
		$sql = "SELECT `date_added`, `id`, `uid`, `fio`, `dopinf`, `bank`, `schet`, `plttyp`, `poluch`, `summa`, `utv` FROM $table $usl $sord";
	else
		$sql = "SELECT `date_added`, `id`, `fio`, `dopinf`, `bank`, `schet`, `plttyp`, `poluch`, `summa`, `utv` FROM $table $usl $sord";
	
	$res = $wpdb->get_results($sql);
	$itog = 0;
	foreach ($res as $el) {
		echo '<tr valign="top">';
	
		$datetime = strtotime($el->date_added);
		$tdate = date("d.m.Y", $datetime);
		
		if($el->utv == NULL or $el->utv == 0){
			if (current_user_can('edit_users')){
				$upurl = $rur . '&upid=' . $el->id;
				$prnurl = '<a href="' . $upurl . '">Утвердить</a>';

				$prnurl .= ' / <a href="' . $upurl . '&del=1">Удалить</a>';

			}
			else
				$prnurl = '<a href="/wp-admin/admin.php?page=plateji_sub-page&eid='.$el->id.'">Редактировать</a>';
		}
		else{
			$upurl = $rur . '&prnid=' . $el->id;
			$prnurl = '<a href="' . $upurl . '">Печать</a>';

			$upurl = '?prnid=' . $el->id;
			$prnscr = plugins_url('print.php', __FILE__) . $upurl;
			$prnurl = '<a href="' . $prnscr . '" target="_blanck">Печать</a>';
		}
		
		$us_fname = "";
		if (current_user_can('edit_users')){
			$user_info = get_userdata($el->uid);
			$us_fname = $user_info->user_lastname . ' ' . $user_info->user_firstname ;
		}

		echo "<td>$tdate</td><td>{$el->id}</td>" .((current_user_can('edit_users'))? "<td>{$us_fname}</td>": "") . "<td>{$el->fio}</td>";
		echo "<td>{$el->dopinf}</td><td>{$el->bank}</td><td>{$el->schet}</td><td>" .(($el->plttyp == 2)? "Пл. карта": "Наличные") . "</td>";
		echo "<td>{$el->poluch}</td><td align=\"right \" >" . number_format($el->summa, 0, '.', ' ') ."</td><td>{$prnurl}</td></tr>";
		
		$itog += $el->summa;
	}	
	$debug = print_r($sql, true);
	echo "</table>";
	
	$itog = number_format($itog, 0, '.', ' ');
	echo "<script type=\"text/javascript\">
		document.getElementById('plt_itog').innerHTML = '<b> $itog сум</b>';
		</script>";
	
//	echo $debug;
	
	$srv =  $_SERVER;
	$cont = print_r($upurl, true);
	
	
//	echo $cont;
}

function upstat($cid){
	global $wpdb;
	$option = get_option('plat_plugin');
	$table = $wpdb->prefix.$option['dbtable_name'];

	if(isset($_GET['del'])){
		$wpdb->query("DELETE FROM $table WHERE `id` = $cid");
	}
	else{
		$wpdb->query("UPDATE $table SET `utv` = '1' WHERE `id` = $cid");
	}

}

function puttext($pic, $h, $w, $size, $puttext, $fonttype = 1){
	if ($fonttype == 1)
		$font = WP_PLUGIN_DIR . '/print_plat/arial.ttf';
	else
		$font = WP_PLUGIN_DIR . '/print_plat/ariali.ttf';
	$color=ImageColorAllocate($pic, 0, 0, 0);
	ImageTTFtext($pic, $size, 0, $w, $h, $color, $font, $puttext);
}

function plat_make_sel($selname, $seltype, $selval = "", $defval = false){
	global $wpdb;
	$tabledata = $wpdb->prefix . 'plat_spr';

	$retsel = "<select name=\"$selname\" >";
	if($defval) $retsel .= "<option value=\"\" $seltd>Все</option>";
	$sql = "SELECT * FROM $tabledata WHERE sprtyp = $seltype";
	$res = $wpdb->get_results($sql);
	foreach ($res as $el) {
		if($el->data == $selval) $seltd = "selected";
		else $seltd = "";
		$retsel .= "<option value=\"{$el->data}\" $seltd>{$el->data}</option>";
	}
	$retsel .= "</select>";

	return $retsel;
}

function data_page(){
	global $wpdb;
	$tabledata = $wpdb->prefix . 'plat_spr';

	if(isset($_GET['cldb'])){
		echo "<h2>Введите код подтверждения:</h2>" . 
		'<form method="post" action=""><input type="password" name="cldbcod" value="" size="8" />'.
		'<input type="submit" class="button-primary" value="Подтвердить" /></form>';
		return;
	}	
	
	if(isset($_GET['eid'])){
		$cid = intval($_GET['eid']);
		$wpdb->query("DELETE FROM $tabledata WHERE `id` = $cid");
	}

	if(isset($_POST['spr'])){
		$newarr = $_POST['spr'];
		$res = $wpdb->insert($tabledata, $newarr);
	}

	plat_make_head("Справочные данные");
	
?>
<div class="wrap"><h2>Справочные данные</h2>
<form method="post" action="">
		Тип значения : 
		<select name="spr[sprtyp]" >
			  <option value="1">Курс</option>
			  <option value="2">Банк получатель</option>
			  <option value="3">Получатель</option>
		</select>
		<br /><br />
		Значение : 
        <input type="text" name="spr[data]" value="<?php echo $spr['data']; ?>" size="80" />
		<br /><br />
        <input type="submit" class="button-primary" value="Добавить" />

</form>
</div>
<?php

	if (current_user_can('activate_plugins')) echo "<br><a href=\"/wp-admin/admin.php?page=plateji_sub-pg2&cldb=1\">Очистить все таблицы!</a><br>";
	
	$arrtype = array("1"=>"Курс", "2"=>"Банк получатель", "3"=>"Получатель");
	
	$sql = "SELECT * FROM $tabledata";
	$res = $wpdb->get_results($sql);
	
	echo '<br><table width="100%" class="plttable">';
	echo '<tr valign="top"><td>Тип значения</td><td>Значение</td><td>Операция</td></tr>';

	foreach ($res as $el) {
		echo '<tr valign="top">';
		echo "<td>{$arrtype[$el->sprtyp]}</td><td>{$el->data}</td><td><a href=\"/wp-admin/admin.php?page=plateji_sub-pg2&eid={$el->id}\">Удалить</a></td>";
		echo '</tr>';
	}	

	echo "</table>";

}

function plt_users(){
	$pltusers = '<select name="plat_flt[uid]">';
	$pltusers .=  '<option value="" '.((!isset($ishdata['uid']))? "selected" : "").'>Все</option>';
	$blogusers = get_users('blog_id=1&orderby=nicename&role=subscriber');
	foreach ($blogusers as $user) {
		$user_info = get_userdata($user->ID);
		$us_fname = $user_info->user_lastname . ' ' . $user_info->user_firstname ;
		$pltusers .= '<option value= "'.$user->ID.'" '. (($ishdata['uid'] == $user->ID)? "selected" : "") .' > ' . $us_fname .' </option>';
	}
	$pltusers .= '</select>';
	return $pltusers;
}

function report_page(){

	plat_make_head("Отчет по платежам");
	$ishdata = array();
?>
	<div class="wrap"><h2>Платежи</h2>
	<form method="post" action="<?php echo plugins_url('report.php', __FILE__); ?>" target="_blanck">

		Период с :<input type="text" name="plat_flt[begdate]" value="<?php echo date("d.m.Y"); ?>" size="10" /> по 
		<input type="text" name="plat_flt[enddate]" value="<?php echo date("d.m.Y"); ?>" size="10" /><br> Кассир:<br><br>

        № платежки: <input type="text" name="plat_flt[id]" value="<?php echo $ishdata['id']; ?>" size="3" /> <br><br>

		Кассир: <?php echo plt_users(); ?> <br><br>

        Курс: <?php echo plat_make_sel("plat_flt[dopinf]", 1, $ishdata['dopinf'], true) ?><br><br>

        Банк <?php echo plat_make_sel("plat_flt[bank]", 2, $ishdata['bank'], true) ?><br><br>

		Тип: <select name="plat_flt[plttyp]" >
			  <option value="" <?php echo ((!isset($ishdata['plttyp']))? "selected" : "")?>>Все</option>
			  <option value="1" <?php echo (($ishdata['plttyp'] == '1')? "selected" : "")?>>Наличные</option>
			  <option value="2" <?php echo (($ishdata['plttyp'] == '2')? "selected" : "")?>>Пл. карта</option>
		</select><br><br>
		
        Получатель: <?php echo plat_make_sel("plat_flt[poluch]", 3, $ishdata['poluch'], true) ?><br><br>

		<input type="submit" class="button-primary" value="Показать" />
	</form>
	</div>
<?php

}

function plat_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if( isset( $user->user_login ) )
    {
		wp_redirect( '/wp-admin/admin.php?page=print_plat/mainplat.php' );
//		die();
    }

}


?>