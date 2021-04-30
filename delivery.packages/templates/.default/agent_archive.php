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

$APPLICATION->IncludeComponent(
	"black_mist:delivery.filter",
	"",
	Array(
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "3600",
		"CACHE_NOTES" => "",
		"N_ZAKAZ" => "Y",
        "DATE_CREATE" => "Y"
	),
false
);

if (count($arResult['LIST']) > 0)
{
	?>
	<table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
		<thead>
            <tr>
                <td><?=GetMessage("MESS_2");?></td>
                <td><?=GetMessage("MESS_1");?></td>
                <td><?=GetMessage("MESS_3");?></td>
                <td><?=GetMessage("MESS_4");?></td>
                <td><?=GetMessage("MESS_5");?></td>
                <td><?=GetMessage("MESS_6");?></td>
            </tr>
		</thead>
        <tbody>
			<?
			foreach ($arResult['LIST'] as $pack)
			{
				?>
				<tr>
					<td><a href="index.php?mode=package&id=<?=$pack['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
					<td width="120"><?=$pack['PROPERTY_DATE_TO_DELIVERY_VALUE'];?></td>
 					<td><?=$pack['PROPERTY_RECIPIENT_VALUE'];?></td>
					<td><?=($pack['PROPERTY_CONDITIONS_ENUM_ID'] == 37)? $pack['PROPERTY_CITY_NAME'].', '.$pack['PROPERTY_ADRESS_VALUE'] : $pack['PROPERTY_CONDITIONS_VALUE']; ?></td>
					<td><?=CurrencyFormat($pack['PROPERTY_COST_2_VALUE'],"RUU");?></td>
                    <td><span class="colors color_<?=$pack['PROPERTY_STATE_SHORT_ENUM_ID'];?>"><?=$pack['PROPERTY_STATE_SHORT_VALUE'];?></span></td>
				</tr>
				<?
			}
			?>
		</tbody>
	</table>
    <?
		$APPLICATION->IncludeComponent(
		'black_mist:delivery.pagination',
		'',
		array(
			'PAGE' => $APPLICATION->GetCurUri(),
			'HID_FIELDS' => array(
				'mode' => $_GET['mode'],
				'date_from' => $_GET['date_from'],
				'date_to' => $_GET['date_to'],
				'number' => $_GET['number']
			),
			'NAV_STRING' => $arResult['NAV_STRING']
		),
		false
	);
}
else
{
	?>
    <p><?=GetMessage("MESS_9");?></p>
    <?
}
?>
<br>