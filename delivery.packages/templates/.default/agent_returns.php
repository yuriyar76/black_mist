<script type="text/javascript">
	function SelectRow(ch,ro)
	{
		if ($("#"+ch).is(":checked"))
		{
			$("#"+ro).addClass('CheckedRow');
		}
		else
		{
			$("#"+ro).removeClass('CheckedRow');
		}
	}
</script>
<div class="new_menu">
    <ul>
        <?
        foreach ($arResult["MENU"] as $k => $v)
		{
			$s = ($arResult['MODE'] == $k) ? ' class="active"' : '';
            ?>
            <li<?=$s;?>><a href="index.php?mode=<?=$k?>"><?=$v?></a></li>
            <?
        }
        ?>
    </ul>
</div>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	echo '<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
}
if (count($arResult["MESSAGE"]) > 0) 
{
	echo '<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
}
if (count($arResult["WARNINGS"]) > 0)
{
	echo '<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}

if (count($arResult["LIST"]) > 0)
{
	?>
    <form action="" method="post" name="curform">
    	<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
        <table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows date_icon_in">
        	<thead>
                <tr>
                	<td width="10"><input type="checkbox" name="set" onclick="setCheckedNew(this,'ids')" /></td>
                    <td><?=GetMessage('TABLE_HEAD_1');?></td>
                    <td><?=GetMessage('TABLE_HEAD_2');?></td>
                    <td><?=GetMessage('TABLE_HEAD_3');?></td>
                    <td><?=GetMessage('TABLE_HEAD_4');?></td>
                </tr>
			</thead>
            <tbody>
            	<?
				foreach ($arResult['LIST'] as $pack)
				{
					?>
					<tr id="row_<?=$pack["ID"];?>" class="CheckedRows">
                    	<td><input type="checkbox" name="ids[]" value="<?=$pack['ID'];?>" onChange="SelectRow('check_<?=$pack["ID"];?>','row_<?=$pack["ID"];?>');" id="check_<?=$pack["ID"];?>"></td>
						<td><a href="index.php?mode=package&id=<?=$pack['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
                        <td><?=WeightFormat($pack['PROPERTY_WEIGHT_VALUE']);?></td>
                        <td><?=WeightFormat($pack['PROPERTY_OB_WEIGHT_VALUE']);?></td>
                         <td><?=$pack['PROPERTY_PLACES_VALUE'];?></td>
                    </tr>
					<?
					
				}
				?>
            </tbody>
		</table>
        <div class="pagination" style="margin-bottom:0;">
			<?=GetMessage("LABEL_CARRIER");?> 
            <select name="carriers" size="1">
                <?
                foreach ($arResult["CARRIERS"] as $cc) {
                    ?>
                    <option value="<?=$cc['ID'];?>"><?=$cc['NAME'];?></option>
                    <?
                }
                ?>
            </select> 
            <?=GetMessage("LABEL_DATE");?> 
            <input type="text" name="date_send" value="<?=date('d.m.Y H:i');?>"/> 
            <? $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
                "SHOW_INPUT" => "N",
                "FORM_NAME" => "curform",
                "INPUT_NAME" => "date_send",
                "INPUT_NAME_FINISH" => "",
                "INPUT_VALUE" => "",
                "INPUT_VALUE_FINISH" => "",
                "SHOW_TIME" => "Y",
                "HIDE_TIMEBAR" => "N"
                ), false
            );
            ?> 
            <label for=""><?=GetMessage("LABEL_SETTLEMENT_DATE_TO");?></label>
            <input type="text" name="settlement_date" value=""/> 
            <? $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
                "SHOW_INPUT" => "N",
                "FORM_NAME" => "curform",
                "INPUT_NAME" => "settlement_date",
                "INPUT_NAME_FINISH" => "",
                "INPUT_VALUE" => "",
                "INPUT_VALUE_FINISH" => "",
                "SHOW_TIME" => "N",
                "HIDE_TIMEBAR" => "Y"
                ), false
            );
            ?> 
            <?=GetMessage("LABEL_NUMBER");?> 
            <input type="text" name="number_send" value="<?=$_POST['number_send'];?>"/> 
             <?=GetMessage("LABEL_PLACES");?> 
            <input type="text" name="places" value="<?=(intval($_POST['places']) > 1) ? intval($_POST['places']) : 1;?>"/> 
        </div>
        <input type="submit" name="send" value="<?=GetMessage('BTN');?>">
    </form>
    <?
	$APPLICATION->IncludeComponent(
		'black_mist:delivery.pagination',
		'',
		array(
			'PAGE' => $APPLICATION->GetCurUri(),
			'HID_FIELDS' => array(
				'mode' => $_GET['mode']
			),
			'NAV_STRING' => $arResult['NAV_STRING']
		),
		false
	);
}
else
{
	?>
	<p><?=GetMessage('NO_ORDERS');?></p>
    <?
}
?>
<br>