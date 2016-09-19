<h2>Настройки комментариев</h2><br />

<form id="selectgr">
    <label for="sel-element">Бренд</label>
    <select name="element" id="sel-element">
    <?php foreach($arrbrand as $curcat): ?>
        <option value="<?php echo $curcat->term_id; ?>"><?php echo $curcat->name; ?></option>        
    <?php endforeach; ?>
    </select>

    <br />
    <label for="sel-brand">Элемент экипировки</label>
    <select name="brand[]" id="sel-brand" multiple="multiple">
    <?php foreach($arrels as $curcat): ?>
        <option value="<?php echo $curcat->term_id; ?>"><?php echo $curcat->name; ?></option>        
    <?php endforeach; ?>
    </select>
    <br />
    <input type="submit" value="Обновить">
</form>
<div id="comm-message"></div>

<?php 
    $deb = '';
    print_r($arrcats); ?>
