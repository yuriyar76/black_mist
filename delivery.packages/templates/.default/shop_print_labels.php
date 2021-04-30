<?
if (count($arResult['PACKS'] > 0)) {
	foreach ($arResult['PACKS'] as $p) {
		?>
		<div class="package">
            <p><?=$p['CITY_NAME'];?></p>
            <p>Получатель: <?=$p['PROPERTY_RECIPIENT_VALUE'];?></p>
            <p>Тел.: <?=$p['PROPERTY_PHONE_VALUE'];?></p>
            <p>Отправитель: <?=$p['PROPERTY_CREATOR_NAME'];?></p>
            <script type="text/javascript">
				$(document).ready(function() {
					$("#bcTarget_<?=$p['ID'];?>").barcode("<?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?>", "code39");

				});
            </script>
            <div id="bcTarget_<?=$p['ID'];?>" class="target">
            </div>
		</div>
        <?
	}
}
?>