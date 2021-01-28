<script type="text/javascript">
	function ChangeClient()
	{
		var cl = $("select#client").val();
		location.href = '<?=$arParams['TYPE_LINK'];?>index.php?ChangeClient=Y&client='+cl;
	}
	$(function () {
		$(window).resize(function () {
			$('#tableId').bootstrapTable('resetView');
		});
	});
</script>
<div class="row">
	<?
	if ($arResult['OPEN']) 
	{
		if ($arResult['ADMIN_AGENT'])
		{
            if ($arResult["CURRENT_CLIENT"] > 0):
			?>
            <div class="col-md-4">
                <div class="btn-group btn-group-justified">
                    <a href="<?=$arParams['TYPE_LINK'];?>index.php?mode=add" class="btn btn-warning"  id="add_customer">Новый <?=$arParams['TYPE_ONE_S'];?></a>
                    <a href="<?=$arParams['TYPE_LINK'];?>index.php" class="btn btn-default">
                        <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Обновить список
                    </a>
                </div>
            </div>
            <?else:?>
            <div class="col-md-4">
                <h4><?=$arResult['TITLE'];?></h4>
            </div>
            <?endif;?>
            <div class="col-md-offset-4 col-md-4 text-right">
            	<?if ($arResult['LIST_OF_CLIENTS']):?>
            	<form action="" method="get" name="filterform" class="form-inline">
                    <div class="form-group">
                    	<select name="client" size="1" class="form-control selectpicker" id="client" onChange="ChangeClient();" data-live-search="true" data-width="auto">
                        	<option value="0"></option>
                            <?
							foreach ($arResult['LIST_OF_CLIENTS'] as $k => $v)
							{
								$s = ($arResult['CURRENT_CLIENT'] == $k) ? ' selected' : '';
								?>
                                <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                <?
							}
							?>
                        </select>
                    </div>
				</form>
            	<?endif;?>
			</div>
            <?
		}
		else
		{
			?>
            <div class="col-md-4">
                <div class="btn-group btn-group-justified">
                    <a href="<?=$arParams['TYPE_LINK'];?>index.php?mode=add" class="btn btn-warning"  id="add_customer">Новый <?=$arParams['TYPE_ONE_S'];?></a>
                    <a href="<?=$arParams['TYPE_LINK'];?>index.php" class="btn btn-default">
                        <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Обновить список
                    </a>
                </div>
            </div>
		<?
		}
	}
	else
	{
		?>
		<div class="col-md-12">
            <h4><?=$arResult['TITLE'];?></h4>
        </div>
        <?
	}
	?>
</div>
<div class="row"><div class="col-md-12">&nbsp;</div></div>
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

if ($arResult['OPEN']) 
{
	/*
	?>
	<div class="panel panel-default">
		<div class="panel-body">
                <div class="row">
                    <div class="col-md-9">
						<form action="" method="get" name="filterform" class="form-inline">
                        <div class="form-group">
                            <label for="number">Наименование: </label>
                            <input type="text" name="number" value="<?=trim($_GET['number']);?>" id="number" class="form-control">
                        </div>
                    	<input type="submit" name="" value="Фильтровать" class="btn btn-default">
					</form>
				</div>
                <div class="col-md-3">
					<form action="" method="get" class="form-inline">
						<input type="submit" name="" value="Сбросить фильтр" class="btn btn-default">
					</form>
                </div>
			</div>
		</div>
	</div>
	<?
	*/
	if (count($arResult['COMPANIES']) > 0)
	{
		?>
        <form action="" method="post" class="form-inline">
        	<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
			<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
            <table class="table table-condensed table-hover" data-toggle="table" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-height="500" id="tableId">
                <thead>
                    <tr>
                        <th width="20" data-field="column1" data-switchable="false"></th>
                        <th data-field="column2" data-sortable="true" data-switchable="false">Наименование</th>
                        <th data-field="column4" data-sortable="true"><?=GetMessage('TABLE_HEAD_6');?></th>
                        <th data-field="column5" data-sortable="true"><?=GetMessage('TABLE_HEAD_2');?></th>
                        <th data-field="column6" data-sortable="true"><?=GetMessage('TABLE_HEAD_3');?></th>
                        <th data-field="column7"><?=GetMessage('TABLE_HEAD_4');?></th>
                        <th data-field="column8" data-sortable="true"><?=GetMessage('TABLE_HEAD_5');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    foreach ($arResult['COMPANIES'] as $r)
                    {
                        ?>
                        <tr>
                            <td class="text-center"><input type="checkbox" name="ids[]" value="<?=$r['id'];?>"  style="margin:0; padding:0;"></td>
                            <td><a href="<?=$arParams['TYPE_LINK'];?>index.php?mode=edit&id=<?=$r['id'];?>"><?=$r['company'];?></a></td>
                            <td class="text-center"><?=($r['active'] == 'Y') ? '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';?></td>
                            <td><?=$r['city'];?></td>
                            <td><?=$r['adress'];?></td>
                            <td><?=$r['phone'];?></td>
                            <td><?=$r['name'];?></td>
                        </tr>
                        <?
                    }
                    ?>
                </tbody>
            </table>
            <br>
			<div class="row">
				<div class="col-md-6">
					<div class="btn-group" role="group" aria-label="...">
						<button class="btn btn-default" type="submit" name="activate">Активировать</button>
						<button class="btn btn-default" type="submit" name="deactivate">Дективировать</button>
						<button class="btn btn-default" type="submit" name="delete">Удалить</button>
					</div>
					<? /* ?>
					<select name="action" size="1" class="form-control" id="action">
						<option value="0"></option>
						<option value="1">Активировать</option>
						<option value="2">Дективировать</option>
						<option value="3">Удалить</option>
					</select>
					<input type="submit" name="save" value="Применить" class="btn btn-default">
					<? */ ?>
				</div>
				<div class="col-md-6 text-right">
					<?=$arResult["NAV_STRING"];?>
				</div>
            </div>
        </form>
		<?
	}
	/* ?>
    <div class="panel panel-default">
		<div class="panel-body">
            <div class="row">
            	
            	<div class="col-md-4">
            		
					<form action="<?=$APPLICATION->GetCurUri();?>" method="get" class="form-inline">
                        <input type="hidden" name="number" value="<?=$_GET['number'];?>">
                         <div class="form-group">
                        <label for="on_page">Показывать на одной странице: </label>

                        <select name="on_page" size="1" class="form-control" id="on_page">
                            <?
                            foreach ($arResult["PAGES"] as $p)
                            {
                                ?>
                                <option value="<?=$p;?>"<?=($arResult['ON_PAGE'] == $p) ? ' selected': ''; ?>><?=$p;?></option>
                                <?
                            }
                            ?>
                        </select>
                        </div>
					<input type="submit" name="" value="Применить" class="btn btn-default">
                    </form>
                </div>
				
                <div class="col-md-12 text-right">
                	<?=$arResult["NAV_STRING"];?>
                </div>
            </div>
        </div>
    </div>
	<?
	*/
}
?>