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

//������������ �� ��������-���������
if (count($arResult["LIST"]) > 0) {
				foreach ($arResult["LIST"] as $p) {
					if (count($p["GOODS_LIST"]) > 0) {
						?>
                        <table cellpadding="2" cellspacing="0" border="1" bordercolor="#CCCCCC" style="font-size:11px;">
                        <thead><tr><td>������������</td><td>�������</td><td>����</td><td>���</td><td class="last">����������</td></tr></thead>
                        <tbody>
                        <?
						foreach ($p["GOODS_LIST"] as $k => $g) {
							?>
                            <tr><td nowrap><?=$g["NAME"];?></td><td><?=$g["ARTICLE"];?></td><td nowrap><?=$g["COST"];?> ���.</td><td nowrap><?=$g["WEIGHT"];?> ��</td><td nowrap class="last"><?=$g["COUNT"];?></td>
                            </tr>
                            <?
						}
						?>
                        </tbody></table>
                        <p><a href="/warehouse/index.php?mode=makepackageofgoods&id=<?=$p['ID'];?>">�������� �����</a></p>
                        <?
					}

				}
}
else {
 } ?>