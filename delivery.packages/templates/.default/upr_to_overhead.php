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

    function ChangeLink()
    {
        var m = [];
        $('.cheks').each(function() {
            if ($(this).attr('checked'))
            {
                m.push($(this).val());
            }
        });
        var s = m.join(',');
        var ss = 'index.php?mode=register&pdf=Y&shop=<?=intval($_GET['shop']);?>&ids=' + s;
        $('#link_register').attr('href',ss);
    }
</script>

<a href="javascript:void(0);" class="help" title="<?=GetMessage("GLOBAL_HELP");?>" style="display:block; position:absolute; top:10px; right:10px; width:50px; height:50px;">
    <img src="/bitrix/templates/portal/images/question.png" width="50" height="50">
</a>

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
        if (count($arResult["LIST"]) > 0)
        {
            ?>
            <li class="nobg">
                <a href="index.php?mode=register&pdf=Y&shop=<?=intval($_GET['shop']);?>" title="<?=GetMessage("PRINT_REGISTER");?>" target="_blank" id="link_register">
                    <img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20">
                </a>
            </li>
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

$APPLICATION->IncludeComponent(
    "black_mist:delivery.filter",
    "",
    array(
        "CACHE_TYPE" => "N",
        "CACHE_TIME" => "3600",
        "CACHE_NOTES" => "",
        "N_ZAKAZ" => "Y",
        "AGENTS" => "N",
        "DATE_CREATE" => "N",
        "STATES_SHORT" => "N",
        'SHOPS' => 'Y',
        "STYLE_PAGINATION" => 'margin-top: 0;',
        'UK_ID' => $arResult['CURRENT_COMPANY']
    ),
    false
);

if (count($arResult["LIST"]) > 0)
{
    ?>
    <form action="" method="post" name="curform">
        <input type="hidden" name="mode" value="departurepackage" />
        <input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
        <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
        <table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#CCCCCC" class="rows tbl_overhead">
            <thead>
            <tr>
                <td width="20" align="center"><input type="checkbox" name="set" onclick="setCheckedNew(this,'id')" /></td>
                <td><?=GetMessage("TABLE_HEAD_1");?></td>
                <td><?=GetMessage("TABLE_HEAD_2");?></td>
                <td><?=GetMessage("TABLE_HEAD_3");?></td>
                <td><?=GetMessage("TABLE_HEAD_4");?></td>
                <td><?=GetMessage("TABLE_HEAD_5");?></td>
                <td><?=GetMessage("TABLE_HEAD_6");?></td>
                <td><?=GetMessage("TABLE_HEAD_7");?></td>
                <td colspan="4"><?=GetMessage("TABLE_HEAD_8");?></td>
                <td><?=GetMessage("TABLE_HEAD_9");?></td>
                <td><?=GetMessage("TABLE_HEAD_13");?></td>
                <td><?=GetMessage("TABLE_HEAD_10");?></td>
                <td><?=GetMessage("TABLE_HEAD_11");?></td>
                <td><?=GetMessage("TABLE_HEAD_12");?></td>
                <td width="20"></td>
            </tr>
            </thead>
            <tbody>
            <?
            $arPods = array();
            foreach ($arResult["LIST"] as $p)
            {
                $arPods[] = $p['PROPERTY_N_ZAKAZ_IN_VALUE'];
                ?>
                <tr id="row_<?=$p["ID"];?>" class="CheckedRows"<?=($p['PROPERTY_STATE_ENUM_ID'] ==39) ? ' style="opacity: 0.4;"' : '';?>>
                    <td align="center">
                        <input type="checkbox" name="id[]" value="<?=$p['ID'];?>" onChange="SelectRow('check_<?=$p["ID"];?>','row_<?=$p["ID"];?>'); ChangeLink();" id="check_<?=$p["ID"];?>"
                               class="cheks"
                        />
                        <input type="hidden" name="number[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?>" />
                    </td>
                    <td>
                        <a href="index.php?mode=package&id=<?=$p['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a>
                    </td>
                    <td><?=$p['PROPERTY_N_ZAKAZ_VALUE'];?></td>
                    <td><?=substr($p['DATE_TO_DELIVERY'],0,16);?></td>
                    <td align="center">
                        <? echo ($p["URGENCY_ORDER_ENUM_ID"] == 172) ? '&bull;' : '-';?>
                        <input type="hidden" name="urgency[<?=$p['ID'];?>]" value="<? echo ($p["URGENCY_ORDER_ENUM_ID"] == 172) ? 2 : 1;?>" />
                    </td>
                    <td>
                        <?=$p['PROPERTY_CITY_NAME'];?>
                        <input type="hidden" name="city[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_CITY_VALUE'];?>" />
                    </td>
                    <td>
                        <?=$p['PROPERTY_CONDITIONS_VALUE'];?>
                        <input type="hidden" name="conditions[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_CONDITIONS_ENUM_ID'];?>" />
                    </td>
                    <td nowrap>
                        <input type="hidden" name="weight_old[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_WEIGHT_VALUE'];?>" />
                        <input type="hidden" name="size_1_old[<?=$p['ID'];?>]" value="<? echo (strlen($p['PROPERTY_SIZE_1_VALUE'])) ? $p['PROPERTY_SIZE_1_VALUE'] : 0; ?>" />
                        <input type="hidden" name="size_2_old[<?=$p['ID'];?>]" value="<? echo (strlen($p['PROPERTY_SIZE_2_VALUE'])) ? $p['PROPERTY_SIZE_2_VALUE'] : 0; ?>" />
                        <input type="hidden" name="size_3_old[<?=$p['ID'];?>]" value="<? echo (strlen($p['PROPERTY_SIZE_3_VALUE'])) ? $p['PROPERTY_SIZE_3_VALUE'] : 0; ?>" />
                        <input type="text" value="<?=$p['PROPERTY_WEIGHT_VALUE'];?>" name="weight[<?=$p['ID'];?>]" class="medium"/> <?=GetMessage("KG");?>
                    </td>
                    <td width="32">
                        <input type="text" name="size_1[<?=$p['ID'];?>]" value="<? echo (strlen($p['PROPERTY_SIZE_1_VALUE'])) ? $p['PROPERTY_SIZE_1_VALUE'] : 0; ?>" class="short" />
                    </td>
                    <td width="32">
                        <input type="text" name="size_2[<?=$p['ID'];?>]" value="<? echo (strlen($p['PROPERTY_SIZE_2_VALUE'])) ? $p['PROPERTY_SIZE_2_VALUE'] : 0; ?>" class="short" />
                    </td>
                    <td width="32">
                        <input type="text" name="size_3[<?=$p['ID'];?>]" value="<? echo (strlen($p['PROPERTY_SIZE_3_VALUE'])) ? $p['PROPERTY_SIZE_3_VALUE'] : 0; ?>" class="short" />
                    </td>
                    <td width="32"><?=GetMessage("SM");?></td>
                    <td align="center" width="32">
                        <input type="text" name="places[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_PLACES_VALUE'];?>" class="short" />
                    </td>
                    <td nowrap>
                        <input type="text" value="<?=date('d.m.Y H:i:00');?>" name="date_departure[<?=$p['ID'];?>]">
                        <?$APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
                            "SHOW_INPUT" => "N",
                            "FORM_NAME" => "curform",
                            "INPUT_NAME" => "date_departure[".$p['ID']."]",
                            "INPUT_NAME_FINISH" => "",
                            "INPUT_VALUE" => "",
                            "INPUT_VALUE_FINISH" => "",
                            "SHOW_TIME" => "Y",
                            "HIDE_TIMEBAR" => "N"
                        ),false
                        );
                        ?>
                    </td>
                    <td>
                        <a href="/shops/index.php?mode=shop&id=<?=$p['PROPERTY_CREATOR_VALUE'];?>"><?=$p['PROPERTY_CREATOR_NAME'];?></a>
                        <input type="hidden" name="shop[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_CREATOR_VALUE'];?>" />
                    </td>
                    <td <? echo (($p['PROPERTY_TAKE_PROVIDER_ENUM_ID'] == 174) && (!strlen($p["PROPERTY_TAKE_DATE_VALUE"]))) ? ' align="center"' : ''; ?>>
                        <?
                        if ($p['PROPERTY_TAKE_PROVIDER_ENUM_ID'] == 174)
                            echo (strlen($p["PROPERTY_TAKE_DATE_VALUE"])) ? $p["PROPERTY_TAKE_DATE_VALUE"] : '&bull;';
                        ?>
                    </td>
                    <td>
                        <a href="/suppliers/index.php?mode=request&id=<?=$p["PROPERTY_CALL_COURIER_VALUE"];?>">
                            <?=$p["CALL_COURIER"];?>
                        </a>
                    </td>
                    <td>
                        <a href="/warehouse/index.php?mode=package_print&id=<?=$p['ID'];?>&pdf=Y" title="<?=GetMessage("PRINT_LABEL");?>" target="_blank">
                            <img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20">
                        </a>
                    </td>
                </tr>
                <?
            }
            ?>
            </tbody>
        </table>
        <input type="submit" name="departure_packages" value="<?=GetMessage("DEPARTURE_BTN");?>"/>
    </form>
    <p><a href="http://newpartner.ru/forms/form2.php?f001=<?=implode(', ', $arPods);?>&show_sec=Y" target="_blank"><?=GetMessage('LINK_TO_PODS');?></a></p>
    <?
    $APPLICATION->IncludeComponent(
        "black_mist:delivery.pagination",
        ".default",
        array(
            "PAGE" => $APPLICATION->GetCurPageParam("", array()),
            "HID_FIELDS" => array(
                'mode' => $_GET['mode'],
                'shop' => $_GET['shop'],
                'number' => $_GET['number']
            ),
            "NAV_STRING" => $arResult["NAV_STRING"]
        ),
        false
    );
}
else {
    echo GetMessage("PACKS_NOT");
}
?>
<br>
