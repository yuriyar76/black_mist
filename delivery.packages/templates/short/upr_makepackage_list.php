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
				?>
                <table cellpadding="3" cellspacing="0" border="1" bordercolor="#CCCCCC" style="font-size:11px;"><thead><tr>
                <td>�����������</td><td colspan="2">������ � ������</td>
                </tr></thead><tbody>
                <?
				foreach ($arResult["LIST"] as $p) {
					?>
                    <tr><td><a href="/shops/index.php?mode=shop&id=<?=$p['PROPERTY_CREATOR_VALUE'];?>"><?=$p['PROPERTY_CREATOR_NAME'];?></a></td>
                    <td><?
					if (count($p["GOODS_LIST"]) > 0) {
						?>
                        <table cellpadding="2" cellspacing="0" border="0" bordercolor="#CCCCCC" style="font-size:11px;" class="short_good_table">
                        <thead><tr><td>ID</td><td>������������</td><td>����</td><td>���</td><td class="last">����������</td></tr></thead>
                        <tbody>
                        <?
						foreach ($p["GOODS_LIST"] as $k => $g) {
							?>
                            <tr><td width="8%" nowrap><?=$g["GOOD_ID"];?></td><td nowrap><?=$g["NAME"];?></td><td width="12%" nowrap><?=$g["COST"];?> ���.</td><td width="10%" nowrap><?=$g["WEIGHT"];?> ��</td><td width="8%" nowrap class="last"><?=$g["COUNT"];?></td></tr>
                            <?
						}
						?>
                        </tbody></table>
                        <?
					}
                    ?></td><td><a href="/warehouse/index.php?mode=makepackage&id=<?=$p["ID"];?>&back_url=makepackage_list">�������� �����</a></td>
                    </tr>
                    <?
				}
				?>
                </tbody></table>
                <?
}
else {
 } ?>