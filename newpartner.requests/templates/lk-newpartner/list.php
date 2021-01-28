<script type="text/javascript">
	$(function () {
		$(window).resize(function () {
			$('#tableId').bootstrapTable('resetView');
		});
		<?
		// if ((count($arResult['REQUESTS']) > 0) && ($arParams['REGISTRATION'] == 1) && (!$arResult['ADMIN_AGENT']))
		if ($arParams['REGISTRATION'] == 1)
		{
			?>
			var html = '<div class="pull-left search">' +
			'<div class="btn-group" role="group" aria-label="...">' +
           	'<input type="submit" name="send" value="<?=GetMessage("SEND_BTN");?>" class="btn btn-primary">' +
            ' <input type="submit" name="delete" value="<?=GetMessage("DELETE_BTN");?>" class="btn btn-default"> ' +
             '</div>' +
			'</div>';
			$('.fixed-table-toolbar').append(html);
			<?
		}
		?>
	});
	
	function setChecked(obj,name)
	{
		var check = document.getElementsByName(name+"[]");
		for (var i=0; i<check.length; i++)
		{
			check[i].checked = obj.checked;
		}
		$('tr.CheckedRows').each(function(){
			if(obj.checked)
			{
				$(this).addClass('info');
			}
			else
			{
				$(this).removeClass('info');
			}
		});
	}
	
	function ChangePeriod()
	{
		var y = $("select#year").val();
		var m = $("select#month").val();
		location.href = '<?=$arParams['LINK'];?>index.php?ChangePeriod=Y&year='+y+'&month='+m;
	}
	
	function ChangeAgent()
	{
		var ag = $("select#agent").val();
		location.href = '<?=$arParams['LINK'];?>index.php?ChangeAgent=Y&agent='+ag;
	}
	
	$(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
    
    function setMessOff(idmes)
    {
        $.post("/search_city.php?setMessOff=Y", {id_mess: idmes},
            function(data){
                if (data["result"] == 'Y')
                {
                    $('#mesblock'+idmes).remove();
                    $('#link_to_open'+idmes).remove();
                    $('#id_to_mess'+idmes).remove();
                    var n = $('.input_to_open').length;
                    if (n == 0)
                    {
                        $('#alert_mess').remove();
                    }
                }
            }
			, "json"
        );
    }
</script>
<?
/*
if ($arResult['ADMIN_AGENT'])
{
	echo '<pre>';
	foreach ($arResult['times'] as $v)
	{
		printf('%.4F сек.: '.$v['name'], $v['val']);
		echo '<br>';
	}
	echo '</pre>';
}
*/
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
	?>
    <div class="row">
        <div class="col-md-4">
            <div class="btn-group btn-group-justified">
                <a href="<?=$arParams['LINK'];?>index.php?mode=add" class="btn btn-warning">Новая заявка</a>
                <a href="<?=$arParams['LINK'];?>index.php" class="btn btn-default">
                    <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Обновить список
                </a>
            </div>
        </div>
        <div class="col-md-8 text-right">
            <form action="" method="get" name="filterform" class="form-inline">
				<?
				if ($arResult['LIST_OF_AGENTS'])
				{
					?>
                    <div class="form-group">
                    	<select name="agent" size="1" class="form-control" id="agent" onChange="ChangeAgent();">
                        	<option value="0"></option>
                            <?
							foreach ($arResult['LIST_OF_AGENTS'] as $k => $v)
							{
								$s = ($arResult['CURRENT_AGENT'] == $k) ? ' selected' : '';
								?>
                                <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                <?
							}
							?>
                        </select>
                    </div>
                    <?
				}
				?>
				<div class="form-group">
                    <select name="month" size="1" class="form-control" id="month" onChange="ChangePeriod();">
                        <?
                        foreach ($arResult['MONTHS'] as $k => $m)
                        {
							$s = ($arResult['CURRENT_MONTH'] == $k) ? ' selected' : '';
                            ?>
                            <option value="<?=$k;?>"<?=$s;?>><?=$m;?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="year" size="1" class="form-control" id="year" onChange="ChangePeriod();">
                        <?
                        foreach ($arResult['YEARS']  as $k => $y)
                        {
							$s = ($arResult['CURRENT_YEAR'] == $k) ? ' selected' : '';
                            ?>
                            <option value="<?=$k;?>"<?=$s;?>><?=$y;?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
	<div class="row"><div class="col-md-12">&nbsp;</div></div>
    <?
    if (count($arResult["MESS"]) > 0)
    {
        /*
        ?>
        <div class="alert alert-dismissable alert-warning fade in" role="alert" id="alert_mess">
            <div class="row">
                <div class="col-md-12">
                    <?
                    $armm = array();
                    foreach ($arResult["MESS"] as $m)
                    {
                        $armm[] = '<a role="button" data-toggle="collapse" href="#mesblock'.$m["ID"].'" aria-expanded="false" aria-controls="mesblock'.$m["ID"].'" id="link_to_open'.$m["ID"].'">'.$m["PROPERTY_COMMENT_VALUE"].' <span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span></a>';
                        ?>
                        <input type="hidden" class="input_to_open" id="id_to_mess<?=$m["ID"];?>">
                        <?
                    }
                    ?>
                   У вас имеются непрочитанные сообщения по накладным: <?=implode(', ',$armm);?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?
                foreach ($arResult["MESS"] as $m)
                {
                    ?>
                    <div class="collapse" id="mesblock<?=$m["ID"];?>">
                       <div class="well">
                           <p><?=$m["NAME"];?><p>
                            <div class="btn-group" role="group" aria-label="...">
                                <a class="btn btn-primary" href="/index.php?mode=invoice1c_modal&f001=<?=$m["PROPERTY_COMMENT_VALUE"];?>&pdf=Y" data-toggle="modal" data-target="#modal_inv1c_<?=$m["PROPERTY_COMMENT_VALUE"];?>">Открыть накладную <?=$m["PROPERTY_COMMENT_VALUE"];?></a>
                                <a class="btn btn-default" href="javascript:void(0);" onClick="setMessOff(<?=$m["ID"];?>);">Отметить сообщение как прочитанное</a>
                            </div>
                        </div>
                    </div>
                    <?   
                }
                ?>
            </div>
        </div>
        <?
        */
    }
	/*
	?>
	<div class="panel panel-default">
		<div class="panel-body">
                <div class="row">
                    <div class="col-md-10">
						<form action="" method="get" name="filterform" class="form-inline">
                        	<div class="form-group">
                                <label for="number" class="control-label">Номер:</label>
                                <input type="text" name="number" value="<?=trim($_GET['number']);?>" id="number" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="state" class="control-label">Статус: </label>
                                <select name="state" size="1" class="form-control">
                                    <option value="0" <?=(intval($_GET['state']) == 0) ? ' selected' : '';?>>Любой</option>
                                    <?
                                    foreach ($arResult['STATES'] as $k => $v)
                                    {
                                        ?>
                                        <option value="<?=$k;?>" <?=(intval($_GET['state']) == $k) ? ' selected' : '';?>><?=$v;?></option>
                                        <?
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date" class="control-label">Дата создания: </label>
                                <?
                                $APPLICATION->IncludeComponent(
                                    "bitrix:main.calendar",
                                    ".default",
                                    array(
                                        "SHOW_INPUT" => "Y",
                                        "FORM_NAME" => "filterform",
                                        "INPUT_NAME" => "date_from",
                                        "INPUT_NAME_FINISH" => "date_to",
                                        "INPUT_VALUE" => $_GET['date_from'],
                                        "INPUT_VALUE_FINISH" => $_GET["date_to"],
                                        "SHOW_TIME" => "N",
                                        "HIDE_TIMEBAR" => "Y",
                                        "INPUT_ADDITIONAL_ATTR" => 'placeholder="ДД.ММ.ГГГГ" class="form-control"'
                                    ),
                                    false
                                );
                                ?>
                            </div>
                            <div class="form-group">
								<input type="submit" name="" value="Фильтровать" class="btn btn-default">
							</div>
						</form>
					</div>
                    <div class="col-md-2">
                        <form action="" method="get" class="form-inline">
                            <input type="submit" name="" value="Сбросить фильтр" class="btn btn-default">
                        </form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?
	*/
	if ((count($arResult['REQUESTS']) > 0) || (count($arResult['ARCHIVE']) > 0))
	{
		if ($arResult['ADMIN_AGENT'])
		{
			/*
			echo '<pre>';
			print_r($arResult['ARCHIVE']);
			echo '</pre>';
			*/
		}
		if ((intval($arParams['REGISTRATION']) == 1) || ($arResult['ADMIN_AGENT'])):
		?>
		<form action="" method="post">
			<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
			<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
            <? endif; ?>
			<table class="table table-condensed table-hover" data-toggle="table" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-height="800" id="tableId">
				<thead>
					<tr>
						<th width="20" data-field="column1" data-switchable="false"><input type="checkbox" name="set" onclick="setChecked(this,'ids');" style="margin:0;"></th>
						<th data-field="column2" data-sortable="true" data-switchable="false"><?=GetMessage('TABLE_HEAD_1');?></th>
                        <th data-field="column17" data-sortable="true" data-switchable="true"><?=GetMessage('TABLE_HEAD_14');?></th>
						<th data-field="date" data-sortable="true"><?=GetMessage('TABLE_HEAD_3');?></th>
                        <th data-field="perform" data-sortable="true"><?=GetMessage('TABLE_HEAD_13');?></th>
						<th data-field="column5" data-sortable="true"><?=GetMessage('TABLE_HEAD_5');?></th>
                        <th data-field="column6" data-sortable="true"><?=GetMessage('TABLE_HEAD_4');?></th>
						<th data-field="column7" data-sortable="true"><?=GetMessage('TABLE_HEAD_7');?></th>
                        <th data-field="column8" data-sortable="true"><?=GetMessage('TABLE_HEAD_6');?></th>
						<th data-field="column9"><?=GetMessage('TABLE_HEAD_8');?></th>
						<th data-field="column10"><?=GetMessage('TABLE_HEAD_9');?></th>
						<th data-field="column11"><?=GetMessage('TABLE_HEAD_10');?></th>
						<th data-field="column12" data-sortable="true" data-switchable="false"><?=GetMessage('TABLE_HEAD_11');?></th>
                        <th data-field="column15" data-switchable="false" width="20"></th>
  						<th data-field="column16" width="20" data-switchable="false" data-align="center"></th>
                        <th data-field="column14" width="20" data-switchable="false" data-align="center"></th>
						<th data-field="state" data-sortable="true"><?=GetMessage('TABLE_HEAD_2');?></th>
                        <th data-field="column13" data-sortable="true" data-switchable="true"><?=GetMessage('TABLE_HEAD_15');?></th>
                        <th data-field="column18" data-sortable="true" data-switchable="true"><?=GetMessage('TABLE_HEAD_12');?></th>
					</tr>
				</thead>
				<tbody>
					<?
					foreach ($arResult['REQUESTS'] as $r)
					{
						$show_inp = false;
						if ($arResult['ADMIN_AGENT'])
						{
							if ($r['PROPERTY_STATE_ENUM_ID'] == 261)
							{
								$show_inp = true;
							}
						}
						else
						{
							if (in_array($r['PROPERTY_STATE_ENUM_ID'], array(236,240))) 
							{
								$show_inp = true;
							}
						}
						
						
						?>
						<tr class="<?=$r['ColorRow'];?> <?=($show_inp) ? 'CheckedRows ' : '';?>">
                        	
							<td>
								<?
								if ($show_inp)
								{
									?>
                                    <input type="checkbox" name="ids[]" value="<?=$r['ID'];?>" style="margin:0;">
                                    <?
								}
								?>
							</td>
							<td>
								<?=$r['PROPERTY_NUMBER_VALUE'];?>
								<?=(count($r['PROPERTY_FILES_VALUE']) > 0) ? '&nbsp;&nbsp;<span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span>' : '';?>
							</td>
                            <td><?=$r['PROPERTY_NUMBER_IN_VALUE'];?></td>
							<td><?=substr($r['DATE_CREATE'],0,10);?></td>
                            <td>
								<?=$r['PROPERTY_DATE_TAKE_VALUE'];?>
                                <?=strlen($r['PROPERTY_TIME_TAKE_FROM_VALUE']) ? ' с '.$r['PROPERTY_TIME_TAKE_FROM_VALUE'] : '';?>
                                <?=strlen($r['PROPERTY_TIME_TAKE_TO_VALUE']) ? ' до '.$r['PROPERTY_TIME_TAKE_TO_VALUE'] : '';?>
                            </td>
							<td><strong><?=$r['PROPERTY_CITY_SENDER_NAME'];?></strong></td>
							<td><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
							<td><strong><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></strong></td>
							<td><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
							<td><?=$r['PROPERTY_PLACES_VALUE'];?></td>
							<td><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
							<td><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                            <td></td>
                            <td></td>
                            <td>
                            	<a href="<?=$arParams['LINK'];?>index.php?mode=request_pdf&id=<?=$r['ID'];?>&pdf=Y">
                                	<span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Скачать заявку"></span>
								</a>
							</td>
							<td>
								<?

								if ($arResult['ADMIN_AGENT'])
								{
									$mode = 'request';
								}
								else
								{
									
									$mode = (in_array($r['PROPERTY_STATE_ENUM_ID'], $arResult['modes_edit']))  ? 'request_edit' : 'request';
								}
								?>
                            	<a href="<?=$arParams['LINK'];?>index.php?mode=<?=$mode;?>&id=<?=$r['ID'];?>"><?=$r['state_icon'];?></a>
                            </td>
							<td>
                            	<?
								if (strlen($r['PROPERTY_COMMENT_VALUE']))
								{
									?>
                                    <span data-toggle="tooltip" data-placement="right" title="<?=$r['PROPERTY_COMMENT_VALUE'];?>" class="tooltip-text"><?=$r['PROPERTY_STATE_VALUE'];?></span>
                                    <?
								}
								else
								{
									echo $r['PROPERTY_STATE_VALUE'];
								}
								?>
                             </td>
                            <td><?=$r['CREATED_BY_NAME'];?></td>
                            <td></td>
						</tr>
						<?
					}
					if ((count($arResult['REQUESTS']) > 0) && ($arParams['REGISTRATION'] == 1) && (!$arResult['ADMIN_AGENT']))
					{
						/*
						?>
                        <tr>
                        	<td colspan="13" style="background:#fff; border-left-color:#fff;  border-right-color:#fff;">
                            	<div class="btn-group" role="group" aria-label="...">
                            	<input type="submit" name="send" value="<?=GetMessage("SEND_BTN");?>" class="btn btn-primary">
                                <input type="submit" name="delete" value="<?=GetMessage("DELETE_BTN");?>" class="btn btn-default">
                                </div>
							</td>
                        </tr>
                        <?
						*/
					}
					foreach ($arResult['ARCHIVE'] as $r)
					{
						$i++;
						?>
                        <tr class="<?=$r['ColorRow'];?>">
                            <td></td>
                            <td>
                                <?=$r['NumRequest'];?>
								<?= (count($r['files']) > 0) ? '&nbsp;&nbsp;<span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span>' : '';?>
                            </td>
                            <td><?=$r['numberin'];?></td>
                            <td><?=$r['start_date'];?></td>
                            <td><?=$r['DateOfCompletion'];?></td>
                            <td><strong><?=$r['CitySenderName'];?></strong></td>
                            <td><?=(strlen($r['CompanySender'])) ? $r['CompanySender'] : $r['NameSender'];?></td>
							<td><strong><?=$r['CityRecipientName'];?></strong></td>
                            <td><?=(strlen($r['CompanyRecipient'])) ? $r['CompanyRecipient'] : $r['NameRecipient'];?></td>
							<td><?=intval($r['Places']);?></td>
                            <td><?=$r['Weight'];?></td>
							<td><?=$r['ObW'];?></td>
                            <td class="text-right" nowrap><?=$r['NumDoc'];?></td> 
                            <td>
                            	<? if (strlen($r['NumDoc'])) : ?>
                                	<a href="<?=$arParams['LINK'];?>index.php?mode=invoice1c_modal&f001=<?=$r['NumDoc'];?>&pdf=Y" data-toggle="modal" data-target="#modal_inv1c_<?=$r['NumDoc'];?>">
                                    <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр накладной"></span>
                                    </a>
                               	<? endif; ?>
                            </td>
                            <td>
                            	<? if (strlen($r['ID'])) : ?>
                            	<a href="<?=$arParams['LINK'];?>index.php?mode=request_pdf&id=<?=$r['ID'];?>&pdf=Y">
                                	<span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Печать заявки"></span>
								</a>
                                <? endif; ?>
                            </td>
							<td>
								<? if (strlen($r['ID'])) : ?>
                                <a href="<?=$arParams['LINK'];?>index.php?mode=request_modal&id=<?=$r['ID'];?>&NumDoc=<?=$r['NumDoc'];?>&pdf=Y" data-toggle="modal" data-target="#modal_reqv_<?=$r['NumDoc'];?>"><?=$r['state_icon'];?></a>
                                <? else : ?>
                                <?=$r['state_icon'];?>
                                <? endif; ?>
							</td>
                            <td><?=$r['stateEdit'];?></td>
                            <td><?=$r['CREATED_BY_NAME'];?></td>
                            <td><?=$r['Manager'];?></td>
                        </tr>
                        <?
					}
					?>
				</tbody>
			</table>
            <? if ((intval($arParams['REGISTRATION']) == 1) || ($arResult['ADMIN_AGENT'])): ?>
		</form>
		<?
		endif;
		?>
        <br>
        <?
		foreach ($arResult['ARCHIVE'] as $r)
		{
			?>
			<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_reqv_<?=$r['NumDoc'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_inv1c_<?=$r['NumDoc'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
            <?
		}
	}
	else
	{
		$prevY = intval($arResult['CURRENT_YEAR']);
		$prevM = intval($arResult['CURRENT_MONTH']) - 1;
		if ($prevM == 0)
		{
			$prevM = 12;
			$prevY = $prevY - 1;
		}
		$prevM = str_pad($prevM,2,'0',STR_PAD_LEFT);
		?>
		<div class="alert alert-dismissable alert-warning fade in" role="alert">
			Список заявок за <?=$arResult['MONTHS'][$arResult['CURRENT_MONTH']];?> <?=$arResult['YEARS'][$arResult['CURRENT_YEAR']];?> пуст, <a href="<?=$arParams['LINK'];?>index.php?ChangePeriod=Y&year=<?=$prevY;?>&month=<?=$prevM;?>">перейти к списку заявок за <?=$arResult['MONTHS'][$prevM];?> <?=$arResult['YEARS'][$prevY];?></a>
    	</div>
		<?
	}
}
?>