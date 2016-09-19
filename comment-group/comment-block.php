<form class="ent-add-post" action="<?php print(esc_url(get_permalink())); ?>" method="post">
<label for="comm-sel-brand">Выберете производителя</label>
    <select name="brand" id="comm-sel-brand">
    <?php foreach($arrbrand as $curcat): ?>
        <option value="<?php echo $curcat->term_id; ?>" <?php echo (isset($_GET['bid']) && intval($_GET['bid']) == $curcat->term_id) ? 'selected' : '';?>><?php echo $curcat->name; ?></option>        
    <?php endforeach; ?>
    </select>
    <br />
    <label for="comm-sel-element">Выберете элемент экипировки</label>
    <select name="element" id="comm-sel-element" >
    <?php foreach($arrels as $curcat): ?>
        <option value="<?php echo $curcat->term_id; ?>"><?php echo $curcat->name; ?></option>        
    <?php endforeach; ?>
    </select>
    <br />
            <?php print(wp_nonce_field()); ?>
            <label for="commgr-tag">Укажите линейку экипировки</label><input type="text" name="tags" size="20" id="commgr-tag" >
    <br />
            <label for="commgr-title">Укажите модель</label><input type="text" name="title" size="20" id="commgr-title">
            <span class="add-post-alert">Внимательно указывайте линейку и модель. Неточно указанная информация, приведет к удалению Вашего отзыва.</span>
            <br />
            <div class="input select rating-a">
                <label for="commgr-rating">Ваша оценка</label>
                <select name="rating" id="rating-a" >
                <?php for($i=1; $i<=10; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>        
                <?php endfor; ?>
                </select>
            </div>
    
            <label for="commgr-desc">Ваш отзыв:</label>
                <textarea rows="15" cols="72" required="required" name="description" id="commgr-desc"></textarea>
            <input type="submit" value="Отправить">
</form>
    


<?php ?>
