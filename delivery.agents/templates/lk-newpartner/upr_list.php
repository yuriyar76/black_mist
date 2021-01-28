<script type="text/javascript">

	$(function () {
		$(window).resize(function () {
			$('#tableId').bootstrapTable('resetView');
		});
	});
	/*
	var html = '<div class="pull-left search">' +
		'<div class="btn-group btn-group-justified"> '+
		'<a href="/agents/index.php?mode=add" class="btn btn-warning"><span>Добавить агента</span></a> ' +
		'<a href="/agents/index.php?mode=list" class="btn btn-default">Список агентов</a>' +
		'</div>' +
		'</div>';
	$('.fixed-table-toolbar').append(html);
	*/
</script>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-danger fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["ERRORS"]);?>
    </div>
    <?
}
if (count($arResult["MESSAGE"]) > 0) 
{
	?>
    <div role="alert" class=" ">
    
    <div class="alert alert-dismissable alert-success fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["MESSAGE"]);?>
    </div>
    <?
}
if (count($arResult["WARNINGS"]) > 0)
{
	?>
    <div class="alert alert-dismissable alert-warning fade in" role="alert">
    	<button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
		<?=implode('</br>',$arResult["WARNINGS"]);?>
    </div>
    <?
}


?>
<div class="row">
	<div class="col-md-4">
		<div class="btn-group btn-group-justified">
			<?
            foreach ($arResult["BUTTONS"] as $b)
            {
                if (in_array($arResult['MODE'],$b["in_mode"]))
                {
                    ?>
                    <a href="<?=$b["link"];?>" class="btn btn-warning"><span><?=$b["title"];?></a> 
                    <?
                }
            }
			foreach ($arResult["MENU_TOP"] as $k => $v)
			{
				?>
				<a href="index.php?mode=<?=$k?>" class="btn btn-default"><?=$v?></a>
				<?
			}
            ?>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<?
		if (count($arResult["LIST"]) > 0)
		{
			?>
            <table class="table table-condensed table-hover" data-toggle="table" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-height="610" id="tableId">
                <thead>
                    <tr>
                        <th data-field="column1" data-sortable="true" data-switchable="true"><?=GetMessage("TABLE_HEAD_2");?></th>
                        <th data-field="column2" data-sortable="true" data-switchable="true"><?=GetMessage("TABLE_HEAD_12");?></th>
                        <th data-field="column10" data-sortable="true" data-switchable="true">ИНН</th>
                        <th data-field="column3" data-sortable="true" data-switchable="true"><?=GetMessage("TABLE_HEAD_3");?></th>
                        <th data-field="column4" data-sortable="false" data-switchable="true"><?=GetMessage("TABLE_HEAD_8");?></th>
                        <th data-field="column5" data-sortable="false" data-switchable="true"><?=GetMessage("TABLE_HEAD_9");?></th>
                        <th data-field="column6" data-sortable="true" data-switchable="true" data-align="center"><?=GetMessage("TABLE_HEAD_7");?></th>
                        <th data-field="column7" data-sortable="true" data-switchable="true" data-align="center"><?=GetMessage("TABLE_HEAD_10");?></th>
                        <th data-field="column8" data-sortable="true" data-switchable="true"><?=GetMessage("TABLE_HEAD_11");?></th>
                        <th data-field="column9" data-sortable="true" data-switchable="true"><?=GetMessage("TABLE_HEAD_13");?></th>
                    </tr>
                </thead>
                <tbody>
                <?
                foreach ($arResult["LIST"] as $c) {
                    ?>
                    <tr>
                        <td><a href="/agents/index.php?mode=agent&id=<?=$c['ID'];?>"><?=$c['NAME'];?></a></td>
                        <td><?=$c['PROPERTY_INN_VALUE'];?></td>
                        <td><?=$c['PROPERTY_INN_REAL_VALUE'];?></td>
                        <td><?=$c['PROPERTY_CITY'];?></td>
                        <td><?=$c['PROPERTY_PHONES_VALUE'];?></td>
                        <td><a href="mailto:<?=$c['PROPERTY_EMAIL_VALUE'];?>"><?=$c['PROPERTY_EMAIL_VALUE'];?></a></td>
                        <td><?=($c['ACTIVE'] == 'Y') ? '&bull;' : '';?></td>
                        <td><?=(intval($c['PROPERTY_BRANCH_VALUE']) == 1) ? '&bull;' : '';?></td>
                        <td><?=$c['PROPERTY_TYPE_AGENT_VALUE'];?></td>
                        <td><?=substr($c['PROPERTY_LAST_DATE_AGENT_VALUE'],0,10);?></td>
                    </tr>
                    <?
                }
                ?>
                </tbody>
            </table>
            <br>
			<?
		}
		else
		{
			?>
            <br>
			<div class="alert alert-dismissable alert-warning fade in" role="alert">
				Список агентов пуст
			</div>
            <br>
			<?
		}
		?>
	</div>
</div>