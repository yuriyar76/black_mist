<a href="javascript:void(0);" class="help" title="<?=GetMessage("GLOBAL_HELP");?>" style="display:block; position:absolute; top:10px; right:10px; width:50px; height:50px;">
    <img src="/bitrix/templates/portal/images/question.png" width="50" height="50">
</a>

<div class="new_menu">

    <ul>
        <?

        foreach ($arResult["MENU"] as $k => $v) {
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

if (count($arResult["LIST"]) > 0)
{
    ?>
    <table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#CCCCCC" class="rows">
        <thead>
        <tr>
            <td><?=GetMessage("TABLE_HEAD_1");?></td>
            <td><?=GetMessage("TABLE_HEAD_2");?></td>
            <td><?=GetMessage("TABLE_HEAD_3");?></td>
            <td><?=GetMessage("TABLE_HEAD_4");?></td>
            <td><?=GetMessage("TABLE_HEAD_5");?></td>
            <td><?=GetMessage("TABLE_HEAD_6");?></td>
            <td><?=GetMessage("TABLE_HEAD_7");?></td>
            <td width="20"></td>
        </tr>
        </thead>
        <tbody>
        <?
        foreach ($arResult["LIST"] as $p)
        {
            ?>
            <tr>
                <td><a href="index.php?mode=package&id=<?=$p['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
                <td><?=$p['DATE_CREATE'];?></td>
                <td><?=$p['PROPERTY_CITY_NAME'];?></td>
                <td nowrap><?=WeightFormat($p['PROPERTY_WEIGHT_VALUE']);?></td>
                <td align="center"><?=$p['PROPERTY_PLACES_VALUE'];?></td>
                <td><a href="/shops/index.php?mode=shop&id=<?=$p['PROPERTY_CREATOR_VALUE'];?>"><?=$p['PROPERTY_CREATOR_NAME'];?></a></td>
                <td><span class="colors color_<?=$p['PROPERTY_STATE_ENUM_ID'];?>"><?=$p['PROPERTY_STATE_VALUE'];?></span></td>
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

    <?
    $APPLICATION->IncludeComponent(
        "black_mist:delivery.pagination",
        ".default",
        array(
            "PAGE" => $APPLICATION->GetCurPageParam("", array()),
            "HID_FIELDS" => array(
                "mode" => $_GET['mode']
            ),
            "NAV_STRING" => $arResult["NAV_STRING"]
        ),
        false
    );
}
else
{
    echo GetMessage("ORDERS_NOT");
}
?>
<br>
