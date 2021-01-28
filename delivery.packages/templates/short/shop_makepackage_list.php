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

//отправленные из интернет-магазинов
if (count($arResult["LIST"]) > 0) {
				foreach ($arResult["LIST"] as $p) {
					if (count($p["GOODS_LIST"]) > 0) {
						?>
                        <table cellpadding="2" cellspacing="0" border="1" bordercolor="#CCCCCC" style="font-size:11px;">
                        <thead><tr><td>Наименование</td><td>Артикул</td><td>Цена</td><td>Вес</td><td class="last">Количество</td></tr></thead>
                        <tbody>
                        <?
						foreach ($p["GOODS_LIST"] as $k => $g) {
							?>
                            <tr><td nowrap><?=$g["NAME"];?></td><td><?=$g["ARTICLE"];?></td><td nowrap><?=$g["COST"];?> руб.</td><td nowrap><?=$g["WEIGHT"];?> кг</td><td nowrap class="last"><?=$g["COUNT"];?></td>
                            </tr>
                            <?
						}
						?>
                        </tbody></table>
                        <p><a href="/warehouse/index.php?mode=makepackageofgoods&id=<?=$p['ID'];?>">Оформить заказ</a></p>
                        <?
					}

				}
}
else {
 } ?>