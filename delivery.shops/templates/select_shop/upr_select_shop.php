 <h2>Интернет-магазины</h2>
<?
if (is_array($arResult["LIST"]))
{
	?>
	<form action="" method="post">
		<p>
			<label for="shop_id">Интернет-магазин:</label> 
            <select id="shop_id" name="shop_id" size="1">
				<option value="0">&nbsp;</option>
				<?
                foreach ($arResult["LIST"] as $v)
                {
                    if ($_SESSION['CURRNET_SHOP'] == $v['ID']) $ch = ' selected'; else $ch = '';
                    ?>
                    <option value="<?=$v['ID'];?>"<?=$ch;?>><?=$v['NAME'];?></option>
                    <?
                }
                ?>
			</select>
		</p>
		<p>
			<label for="action">Действие:</label> 
            <select id="action" name="action" size="1">
				<option value="select_shop">Перейти к товарам</option>
                <option value="add_purchase">Оформить приходную накладную</option>
                <option value="add_purchase_outgo">Оформить расходную накладную</option>
                <option value="add_correction">Оформить корректировку остатков</option>
                <option value="add_correction_price">Оформить корректировку цен</option>
				<option value="list_purchase">Список приходных накладных</option>
                <option value="list_purchase_outgo">Список расходных накладных</option>
                <option value="list_correction">Список корректировок остатков</option>
                <option value="list_correction_price">Список корректировок цен</option>
			</select>
		</p>
		<input type="submit" name="save" value="Выбрать">
	</form>
	<?
}
?>