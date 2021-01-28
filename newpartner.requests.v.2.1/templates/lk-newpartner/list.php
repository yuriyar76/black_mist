<script type="text/javascript">
	$(document).ready(function(){
		$('.maskdate').mask('99.99.9999');
	});
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
	
	function ChangePeriodNew()
	{
		$('#input-group-list-from-date').removeClass('has-error');
		$('#input-group-list-to-date').removeClass('has-error');
		var datefrom = $("input#list-from-date").val();
		var dateto = $("input#list-to-date").val();
		if ((dateto.length > 0) && (datefrom.length > 0))
		{
			location.href = '<?=$arParams['LINK'];?>index.php?ChangePeriod=Y&datefrom='+datefrom+'&dateto='+dateto;
		}
		else
		{
			if (dateto.length <= 0)
			{
				$('#input-group-list-to-date').addClass('has-error');
			}
			if (datefrom.length <= 0)
			{
				$('#input-group-list-from-date').addClass('has-error');
			}
		}
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
    <?if(!empty($_SESSION['CURRENT_CLIENT_CODE'])):?>
    <div class="row">
        <div class="col-md-3">
            <p>ID клиента в 1С - <span><?=$_SESSION['CURRENT_CLIENT_CODE']?></span></p>
        </div>
    </div>

<?endif;?>
    <div class="row">
        <div class="col-md-3">
            <? if ($arResult['CURRENT_AGENT'] > 0) :?>
            <div class="btn-group btn-group-justified">
                <a href="<?=$arParams['LINK'];?>index.php?mode=add" class="btn btn-warning">Новая заявка</a>
                <a href="<?=$arParams['LINK'];?>index.php" class="btn btn-default">
                    <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Обновить список
                </a>
            </div>
            <? endif; ?>
        </div>
        <div class="col-md-2">
            <? if ((count($arResult['REQUESTS']) > 0) || (count($arResult['ARCHIVE']) > 0)) : ?>
            <form action="<?=$arParams['LINK'];?>index.php?mode=list_xls&pdf=Y" method="post" name="xlsform" class="form-inline" target="_blank">
                <input type="hidden" name="DATA" value="<?=htmlspecialchars($arResult['ARCHIVE_STR_JSON'],ENT_COMPAT);?>">
                <button type="submit" class="btn btn-warning" data-toggle="tooltip" data-placement="right" title="Скачать список заявок">
                    <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                </button>
            </form>
            <? endif;?>
        </div>
        <div class="col-md-7 text-right">
            <form action="" method="get" name="filterform" class="form-inline">
				<?
				if ($arResult['LIST_OF_AGENTS'])
				{
					?>
                    <div class="form-group">
                    	<select name="agent" size="1" class="form-control selectpicker" id="agent" onChange="ChangeAgent();"  data-live-search="true" data-width="auto">
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
					<div class="input-group" id="input-group-list-from-date">
						<input type="text" class="form-control maskdate" aria-describedby="basic-addon1" name="dateperiodfrom" placeholder="ДД.ММ.ГГГГ" value="<?=$arResult['LIST_FROM_DATE'];?>" onChange="ChangePeriodNew();" id="list-from-date">
						<span class="input-group-addon" id="basic-addon1">
							<?
							$APPLICATION->IncludeComponent(
								"bitrix:main.calendar",
								".default",
								array(
									"SHOW_INPUT" => "N",
									"FORM_NAME" => "",
									"INPUT_NAME" => "dateperiodfrom",
									"INPUT_NAME_FINISH" => "",
									"INPUT_VALUE" => "",
									"INPUT_VALUE_FINISH" => false,
									"SHOW_TIME" => "N",
									"HIDE_TIMEBAR" => "Y",
									"INPUT_ADDITIONAL_ATTR" => ''
								),
								false
							);
							?>
						</span>
					</div>
				</div>
				<div class="form-group">&nbsp;&mdash;&nbsp;</div>
				<div class="form-group">
					<div class="input-group" id="input-group-list-to-date">
						<input type="text" class="form-control maskdate" aria-describedby="basic-addon2" name="dateperiodto" placeholder="ДД.ММ.ГГГГ" value="<?=$arResult['LIST_TO_DATE'];?>" onChange="ChangePeriodNew();" id="list-to-date">
						<span class="input-group-addon" id="basic-addon2">
							<?
							$APPLICATION->IncludeComponent(
								"bitrix:main.calendar",
								".default",
								array(
									"SHOW_INPUT" => "N",
									"FORM_NAME" => "",
									"INPUT_NAME" => "dateperiodto",
									"INPUT_NAME_FINISH" => "",
									"INPUT_VALUE" => "",
									"INPUT_VALUE_FINISH" => false,
									"SHOW_TIME" => "N",
									"HIDE_TIMEBAR" => "Y",
									"INPUT_ADDITIONAL_ATTR" => ''
								),
								false
							);
							?>
						</span>
					</div>
				</div>
            </form>
        </div>
    </div>
	<div class="row"><div class="col-md-12">&nbsp;</div></div>
    <?
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
                        <th data-field="column19" data-sortable="false" data-switchable="false" width="20"></th>
					</tr>
				</thead>
				<tbody>
					<?
					foreach ($arResult['REQUESTS'] as $r)
					{
												/*
						/* 
						   пишем в лог 2 (ищем поле внетреннего номера нашей заявки!)				
						*/
						// echo "<pre> заявка :: ";
						// print_r ($r);
						// echo "</pre>";

						
						
						$show_inp = false;
						if ($arResult['ADMIN_AGENT'])
						{
							if ($r['PROPERTY_STATE_ENUM_ID'] == 261)
							//if (in_array($r['PROPERTY_STATE_ENUM_ID'], array(261,236,240)))
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
                            <td>
                            	<a href="<?=$arParams['LINK'];?>index.php?mode=add&copyfrom=<?=$r['ID'];?>&copy=Y">
                                	<span class="glyphicon glyphicon-copy" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Копировать"></span>
								</a>
                            </td>
						</tr>
						<?
					}
					
					
					
					foreach ($arResult['ARCHIVE'] as $r)
					{
						$i++;
						/*
						   пишем в лог 2 (ищем поле внетреннего номера нашей заявки!)				
						*/
						//echo "<pre> заявка :: " + $i + " ";
						// print_r ($r);
						//echo "</pre>";
						
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
                                	<a href="<?=$arParams['LINK'];?>index.php?mode=invoice1c_modal&f001=<?=$r['NumDoc'];?>&pdf=Y" data-toggle="modal" data-target="#modal_inv1c_<?=str_replace('.','__',$r['NumDoc']);?>">
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
                            <td>
                            	<?if (strlen($r['ID'])):?>
                            	<a href="<?=$arParams['LINK'];?>index.php?mode=add&copyfrom=<?=$r['ID'];?>&copy=Y">
                                	<span class="glyphicon glyphicon-copy" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Копировать"></span>
								</a>
                            	<?endif;?>
                            </td>
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
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_inv1c_<?=str_replace('.','__',$r['NumDoc']);?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
            <?
		}
	}
}
?>