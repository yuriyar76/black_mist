<?
if (count($arResult["ERRORS"]) > 0) echo '
	<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
if (count($arResult["MESSAGE"]) > 0) echo '
	<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
	
switch ($arParams['MODE'])
{
	case 'detail': 
		$page = $APPLICATION->GetCurPageParam("file_id=".$arResult["ELEMENT"]["PROPERTY_FILE_VALUE"]);
		?>
		<h3><?=(intval($arResult["ELEMENT"]["PROPERTY_FILE_VALUE"]) > 0) ? '' : '[�����] '; ?><?=$arResult["TITLE"];?></h3>
    <?
    if (is_array($arResult["ELEMENT"])) {
		?>
        <p><strong>���� ������:</strong> <?=$arResult["ELEMENT"]["DATE_CREATE"];?></p>
        <p><strong>
        <? echo ($arResult["ELEMENT"]["IBLOCK_SECTION_ID"] == 2368) ? '����������� ������' : '����� ������'; ?>
        :</strong> <?=$arResult["ELEMENT"]["PROPERTY_VERSION_VALUE"];?></p>
        <?=$arResult["ELEMENT"]["DETAIL_TEXT"];?>
        <? if (intval($arResult["ELEMENT"]["PROPERTY_FILE_VALUE"]) > 0) { ?>
        <p><a href="<?=$arResult["ELEMENT"]["FILE"];?>">
         <? echo ($arResult["ELEMENT"]["IBLOCK_SECTION_ID"] == 2368) ? ' ������� zip-����� � ������� ���������' : '������� ����'; ?></a></p>
         <? } ?>
        <p><br><a href="/update/">��������� � ������ ��������� ����������</a></p>
        <?
	}
	break;
	default:
	if (count($arResult["LIST"]) > 0)
	{
		?>
        <table class="table table-striped table-hover ">
			<thead>
            	<tr>
					<th colspan="2">����� ������</th>
                    <th>���� ������</th>
                    <th>����������� ������</th>
                    <th>Zip-����� � ������� ���������</th>
				</tr>
			</thead>
        <tbody>
        <?
		foreach ($arResult["LIST"] as $el) {
			$page = $APPLICATION->GetCurPageParam("file_id=".$el["PROPERTY_FILE_VALUE"]);
			?>
            <tr>
          <td><?
		  echo (intval($el["PROPERTY_FILE_VALUE"]) > 0) ? '' : '[�����] ';
		  ?>
          <?=$el["NAME"];?></td><td><a href="/update/index.php?mode=detail&id=<?=$el["ID"];?>">��������</a></td><td><?=$el["DATE_CREATE"];?></td><td><?=$el["PROPERTY_VERSION_VALUE"];?></td><td> <? if (intval($el["PROPERTY_FILE_VALUE"]) > 0) { ?>
          <a href="<?=$el["FILE"];;?>">�������</a>
          <? } ?>
          </td>
        </tr>
            <?
		}
		?>
        </tbody></table>
        <?
	}
	if (count($arResult["LIST2"]) > 0)
	{
		?>
        <h2>�������������� �����</h2>
		<table class="table table-striped table-hover">
        	<thead>
            	<tr>
					<th colspan="2">������������</th>
                    <th>���� ������</th>
                    <th>����� ������</th>
                    <th>����</th>
				</tr>
			</thead>
            <tbody>
            <?
            foreach ($arResult["LIST2"] as $el)
            {
                $page = $APPLICATION->GetCurPageParam("file_id=".$el["PROPERTY_FILE_VALUE"]);
                ?>
                <tr>
                    <td><?=$el["NAME"];?></td>
                    <td><a href="/update/index.php?mode=detail&id=<?=$el["ID"];?>">��������</a></td>
                    <td><?=$el["DATE_CREATE"];?></td>
                    <td><?=$el["PROPERTY_VERSION_VALUE"];?></td>
                    <td>
                        <? if (strlen($el["FILE"])) : ?>
                        <a href="<?=$el["FILE"];?>">�������</a>
                        <? endif; ?>
                    </td>
                </tr>
                <?
            }
            ?>
			</tbody>
		</table>
        <?
	}
	break;
}

