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
	echo '
		<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
		


if (count($arResult["MESSAGE"]) > 0) 
	echo '
		<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
else {
	if (count($arResult["WARNINGS"]) > 0) 
	echo '
		<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}
if (count($arResult['LIST']) > 0) {
?>
<form action="" method="post" name="curform">
        <table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows date_icon_in">
        <thead>
        <tr>
        	<td><?=GetMessage("MESS_1");?></td>
            <td><?=GetMessage("MESS_2");?></td>
            <td><?=GetMessage("MESS_3");?></td>
            <td><?=GetMessage("MESS_4");?></td>
            <td><?=GetMessage("MESS_5");?></td>
            <td width="120"><?=GetMessage("MESS_6");?></td>
            <td width="250"><?=GetMessage("MESS_7");?></td>
            <td width="175"><?=GetMessage("MESS_8");?></td>
            <td width="20"></td>
        </tr>
        </thead><tbody>
        <?
		foreach ($arResult['LIST'] as $pack) {
			?>
            <tr>
            <td><a href="index.php?mode=package&id=<?=$pack['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=nZakaz($pack['PROPERTY_N_ZAKAZ_VALUE']);?></a></td>
           <td><?=$pack['PROPERTY_ADRESS_VALUE'];?></td>
            <td><?=$pack['PROPERTY_PHONE_VALUE'];?></td>
             <td><input type="text" name="fio[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_RECIPIENT_VALUE'];?>"></td>
            <td><?=CurrencyFormat($pack["PROPERTY_COST_2_VALUE"],"RUU");?>
            <input type="hidden" name="summ[<?=$pack['ID'];?>]" value="<?=$pack["PROPERTY_COST_2_VALUE"];?>">
            </td>
            <td><a href="/management/index.php?mode=courier&id=<?=$pack['PROPERTY_COURIER_VALUE'];?>"><?=$pack["PROPERTY_COURIER_NAME"];?></a>
            <input type="hidden" name="COURIER_ID[<?=$pack['ID'];?>]" value="<?=$pack["PROPERTY_COURIER_VALUE"];?>">
            </td>
           
        <td align="center">
        <select name="operation[<?=$pack['ID'];?>]" size="1" style="width:250px;">
        <option value="0"></option>
<? foreach ($arParams["STATUS"] as $k => $v) {
	?>
    <option value="<?=$k?>">[<?=$k?>] <?=$v;?></option>
	<?
}
?>
</select>
<input type="hidden" name="id_in[<?=$pack['ID'];?>]" value="<?=nZakaz($pack['PROPERTY_N_ZAKAZ_VALUE']);?>" />
<input type="hidden" name="cur_fio[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_COURIER_NAME'];?>" />
<input type="hidden" name="shop[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_CREATOR_VALUE'];?>" />
		</td>
         <td><input type="text" value="<?=date('d.m.Y H:i');?>" name="date_delivery[<?=$pack['ID'];?>]"  />
        <? $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
	"SHOW_INPUT" => "N",
	"FORM_NAME" => "curform",
	"INPUT_NAME" => "date_delivery[".$pack['ID']."]",
	"INPUT_NAME_FINISH" => "",
	"INPUT_VALUE" => "",
	"INPUT_VALUE_FINISH" => "",
	"SHOW_TIME" => "Y",
	"HIDE_TIMEBAR" => "N"
	),
	false
);?></td> 
                <td>
        <a href="/warehouse/index.php?mode=package_print&id=<?=$pack['ID'];?>&pdf=Y" title="<?=GetMessage("MESS_9");?>" target="_blank"><img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20"></a>
        </td>
		</tr>
            <?
		}
		?>
        </tbody></table>
       <input type="submit" name="save" value="<?=GetMessage("MESS_10");?>">
        </form>
        <div class="pagination">
                <div class="left_pag">
                <form action="<?=$APPLICATION->GetCurPageParam("", array());?>" method="get">
                <input type="hidden" name="mode" value="<?=$_GET['mode'];?>">
                <?=GetMessage("MESS_11");?> <select name="on_page" size="1">
                <option value="10"<? echo ($_GET['on_page'] == 10) ? ' selected': ''; ?>>10</option>
                <option value="20"<? echo ($_GET['on_page'] == 20) ? ' selected': ''; ?>>20</option>
                <option value="50"<? echo ($_GET['on_page'] == 50) ? ' selected': ''; ?>>50</option>
                <option value="100"<? echo ($_GET['on_page'] == 100) ? ' selected': ''; ?>>100</option>
                <option value="500"<? echo ($_GET['on_page'] == 500) ? ' selected': ''; ?>>500</option>
                </select>
                <input type="submit" name="" value="<?=GetMessage("MESS_12");?>">
                </form>
                </div>
                <div class="right_pag">
                <?=$arResult["NAV_STRING"];?>
                </div><br class="clear"></div>
<? 
}
else {
		?>
        <p><?=GetMessage("MESS_13");?></p>
        <?
	}
?>