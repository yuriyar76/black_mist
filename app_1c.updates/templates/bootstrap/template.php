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
		<h3><?=(intval($arResult["ELEMENT"]["PROPERTY_FILE_VALUE"]) > 0) ? '' : '[Анонс] '; ?><?=$arResult["TITLE"];?></h3>
    <?
    if (is_array($arResult["ELEMENT"])) {
		?>
        <p><strong>Дата выхода:</strong> <?=$arResult["ELEMENT"]["DATE_CREATE"];?></p>
        <p><strong>
        <? echo ($arResult["ELEMENT"]["IBLOCK_SECTION_ID"] == 2368) ? 'Обновляемая версия' : 'Номер версии'; ?>
        :</strong> <?=$arResult["ELEMENT"]["PROPERTY_VERSION_VALUE"];?></p>
        <?=$arResult["ELEMENT"]["DETAIL_TEXT"];?>
        <? if (intval($arResult["ELEMENT"]["PROPERTY_FILE_VALUE"]) > 0) { ?>
        <p><a href="<?=$arResult["ELEMENT"]["FILE"];?>">
         <? echo ($arResult["ELEMENT"]["IBLOCK_SECTION_ID"] == 2368) ? ' Скачать zip-архив с пакетом установки' : 'Скачать файл'; ?></a></p>
         <? } ?>
        <p><br><a href="/update/">Вернуться к списку доступных обновлений</a></p>
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
					<th colspan="2">Номер версии</th>
                    <th>Дата выхода</th>
                    <th>Обновляемая версия</th>
                    <th>Zip-архив с пакетом установки</th>
				</tr>
			</thead>
        <tbody>
        <?
		foreach ($arResult["LIST"] as $el) {
			$page = $APPLICATION->GetCurPageParam("file_id=".$el["PROPERTY_FILE_VALUE"]);
			?>
            <tr>
          <td><?
		  echo (intval($el["PROPERTY_FILE_VALUE"]) > 0) ? '' : '[Анонс] ';
		  ?>
          <?=$el["NAME"];?></td><td><a href="/update/index.php?mode=detail&id=<?=$el["ID"];?>">Описание</a></td><td><?=$el["DATE_CREATE"];?></td><td><?=$el["PROPERTY_VERSION_VALUE"];?></td><td> <? if (intval($el["PROPERTY_FILE_VALUE"]) > 0) { ?>
          <a href="<?=$el["FILE"];;?>">Скачать</a>
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
        <h2>Дополнительные файлы</h2>
		<table class="table table-striped table-hover">
        	<thead>
            	<tr>
					<th colspan="2">Наименование</th>
                    <th>Дата выхода</th>
                    <th>Номер версии</th>
                    <th>Файл</th>
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
                    <td><a href="/update/index.php?mode=detail&id=<?=$el["ID"];?>">Описание</a></td>
                    <td><?=$el["DATE_CREATE"];?></td>
                    <td><?=$el["PROPERTY_VERSION_VALUE"];?></td>
                    <td>
                        <? if (strlen($el["FILE"])) : ?>
                        <a href="<?=$el["FILE"];?>">Скачать</a>
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

