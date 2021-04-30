<a href="javascript:void(0);" class="help" title="" style="display:block; position:absolute; top:10px; right:10px; width:50px; height:50px;">
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
else
{
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
		"DATE_CREATE" => "Y",
		"N_ZAKAZ" => "Y",
		'SHOPS' => 'Y',
		'UK_ID' => $arResult['AGENT_ID']
	),
false
);

if (count($arResult["LIST"]) > 0)
{
	?>
	<table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#CCCCCC" class="rows">
    	<thead>
        	<tr>
				<td width="120"><?=GetMessage("TABLE_HEAD_1");?></td>
				<td width="120"><?=GetMessage("TABLE_HEAD_2");?></td>
				<td width="250"><?=GetMessage("TABLE_HEAD_3");?></td>
				<td width="40"><?=GetMessage("TABLE_HEAD_4");?></td>
                <td width="30"><?=GetMessage("TABLE_HEAD_5");?></td>
                <td><?=GetMessage("TABLE_HEAD_6");?></td>
                <td><?=GetMessage("TABLE_HEAD_7");?></td>
                <td><?=GetMessage("TABLE_HEAD_8");?></td>
			</tr>
		</thead>
        <tbody>
			<?
			foreach ($arResult["LIST"] as $p)
			{
				?>
				<tr>
                	<td>
						<a href="index.php?mode=package&id=<?=$p['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a>
					</td>
					<td><?=substr($p['PROPERTY_DATE_TO_DELIVERY_VALUE'],0,16);?></td>
                    <td><?=$p['PROPERTY_CITY_NAME'];?></td>
                    <td nowrap><?=WeightFormat($p['PROPERTY_WEIGHT_VALUE']);?></td>
                    <td align="center"><?=$p['PROPERTY_PLACES_VALUE'];?></td>
					<td><a href="/shops/index.php?mode=shop&id=<?=$p['PROPERTY_CREATOR_VALUE'];?>"><?=$p['PROPERTY_CREATOR_NAME'];?></a></td>
					<td>
						<?
						if (strlen($p['PROPERTY_MANIFEST_VALUE']))
						{
							?>
							<a href="/manifesty/index.php?mode=manifest&id=<?=$p['PROPERTY_MANIFEST_VALUE'];?>"><?=$p['MAN_NAME'];?></a>
							<?
						}
						?>
					</td>
					<td><span class="colors color_<?=$p['PROPERTY_STATE_ENUM_ID'];?> <?=($p['PROPERTY_EXCEPTIONAL_SITUATION_VALUE'] == 1) ? 'color_exc' : '';?>"><?=$p['PROPERTY_STATE_VALUE'];?></span></td>
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
				'number' => $_GET['number'],
				'shop' => $_GET['shop']
			),
			'NAV_STRING' => $arResult['NAV_STRING']
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