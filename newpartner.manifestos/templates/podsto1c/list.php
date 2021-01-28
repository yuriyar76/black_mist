<script type="text/javascript">
	$(document).ready(function () {
		$("#filter_invoive").on("keyup click input", function () {
			$(".links_to_nakl").removeClass('selected');
			$("tr.notshow").removeClass('active');
			$("#links_to_nakl_"+$("#filter_invoive").val().toLowerCase()).addClass('selected');
			$("#links_to_nakl_"+$("#filter_invoive").val().toLowerCase()).closest('tr.notshow').addClass('active');
			if (this.value.length > 0) {
				$(".filter_number").show().filter(function () {	
					// console.log($(".filter_number:visible").length);
					return $(this).attr('data-title').toLowerCase().indexOf($("#filter_invoive").val().toLowerCase()) == -1;
				}).hide();
			}
			else {
				$(".filter_number").show();
			}
		});
	});

	function ChangeType()
	{
		var ev = $("select#event").val();
		$('#ErrDescr').css('display','none');
		$('#ErrSit').css('display','none');
		if (ev == 15679)
		{
			$('#ErrDescr').css('display','block');
		}
		if (ev == 15680)
		{
			$('#ErrSit').css('display','block');
		}
	}
	
	function ChangePeriod()
	{
		var y = $("select#year").val();
		var m = $("select#month").val();
		location.href = '<?=$arParams['LINK'];?>?ChangePeriod=Y&year='+y+'&month='+m;
	}
	
	function ChangeAgent()
	{
		var ag = $("select#agent").val();
		location.href = '<?=$arParams['LINK'];?>?ChangeAgent=Y&agent='+ag;
	}
	
	function ChangeTypeM()
	{
		var tm = $("select#type_m").val();
		location.href = '<?=$arParams['LINK'];?>?ChangeTypeM=Y&typem='+tm;
	}
	
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	})
	
	function ShowManifest(idrow)
	{
		$('.notshow').removeClass('active');
		$('#'+idrow).addClass('active');
	}
	
	function ShowPack(row,number,mrj)
	{
		$('#show_pack_info_'+row).html('');
		$('.links_to_nakl').removeClass('active');
		$('#links_to_nakl_'+number).addClass('active');
		$('#SpinnerImage_'+row).show();
		$.getJSON( "http://agent.newpartner.ru/tracking.php?f001="+number+"&json=Y&pdf=Y", function( data ) {
			var items = [];
			var delivered = 0;
			$.each( data, function( key, val ) {
				items.push( '<tr><td colspan="3" align="center"><strong> Трек отправления ' + key + '</strong></td></tr>' );
				delivered = 0;
				$.each( val, function( key_2, val_2 ) {
					items.push( '<tr><td width="33%">' + val_2['DateEvent'] + '</td><td width="33%">'+ val_2['Event']+'</td><td>'+val_2['InfoEvent']+'</td></tr>' );
					if (val_2['Event'] == 'Доставлено')
					{
						delivered = 1;
					}
				});
				if (delivered != 1)
				{
					items.push( ''+
					'<form class="form-inline" name="curform">'+
						'<tr>'+
							'<td>'+
								'<input type="hidden" id="eventNumber" value="'+number+'" name="eventNumber">'+
								'<input type="hidden" name="row" value="'+row+'" id="row">'+
								'<div class="form-group clearErr" id="ErrDate"><input type="text" id="eventDate" class="form-control input-sm maskdatetime" name="date"></div>'+
							'</td>'+
							'<td>'+
								'<div class="form-group clearErr" id="ErrType"><select name="event" size="1" onChange="ChangeType();" id="event" class="form-control input-sm">'+
									'<option value="0"></option><option value="15679">Доставлено</option>'+
									'<option value="15680">Исключительная ситуация!</option>'+
								'</select></div>'+
							'</td>'+
							'<td>'+
								'<div id="ErrDescr" style="display:none;" class="form-group clearErr">'+
									'<input type="text" class="form-control input-sm" id="eventDescr">'+
								'</div>'+
								'<div id="ErrSit" style="display:none;" class="form-group clearErr">'+
									'<select name="situation" size="1" class="form-control input-sm" id="EventSituation">'+
										'<option value="0"></option><?=$arResult['SITUATION_TEXT'];?>'+
									'</select>'+
								'</div>'+
							'</td>'+
						'</tr>'+
						'<tr>'+
							'<td colspan="3" class="text-center">'+
								'<button class="btn btn-sm btn-primary" onClick="AddEvent();">Добавить</button>'+
							'</td>'+
						'</tr>'+
					'</form>'
					);
				}
			});
			$( "<table/>", {
			"class": "table table-bordered manifestos",
			html: items.join( "" )
			}).appendTo( '#show_pack_info_'+row );
			$('.maskdatetime').mask('99.99.9999 99:99:00');
			$('#SpinnerImage_'+row).hide();
			
		});
	}

	function AddEvent()
	{
		var row = $('#row').val();
		$('#enter_pod_'+row).html('');
		$('.clearErr').removeClass('has-error');
		var eventDate = $('#eventDate').val();
		var eventNumber = $('#eventNumber').val();
		var eventType = $('#event').val();
		var eventDescr = $('#eventDescr').val().replace(/\s+/g, '');
		var EventSituation = $('#EventSituation').val();
		var eventDescrTotal = '';
		var f1 = 0;
		var f2 = 0;
		var f3 = 0;
		if (eventDate.length)
		{
			var time = eventDate.substr(eventDate.length - 8);
			if (time == '00:00:00')
			{
				$('#ErrDate').addClass('has-error');
			}
			else
			{
				f1 = 1;
			}
		}
		else
		{
			$('#ErrDate').addClass('has-error');
		}
		if (eventType == 0)
		{
			$('#ErrType').addClass('has-error');
		}
		else
		{
			f2 = 1;
			if (eventType == 15679)
			{
				if (eventDescr.length)
				{
					f3 = 1;
				}
				else
				{
					$('#ErrDescr').addClass('has-error');
				}
			}
			else
			{
				if (EventSituation == 0)
				{
					$('#ErrSit').addClass('has-error');
				}
				else
				{
					f3 = 1;
				}
			}
		}
		if ((f1 == 1) &&(f2 == 1) && (f3 == 1))
		{
			$('#show_pack_info_'+row).html('');
			$('#SpinnerImage_'+row).show();
			$.post(
				'/functions.php',
				{ "json": "1", "addto1c": "addto1c", "number" : eventNumber, "date" : eventDate, "event" : eventType, "situation" : EventSituation, "descr" : eventDescr},
				function(data){
					if (data['ERRORS'])
					{
						var err_string = data['ERRORS'].join('<br>');
						$('#enter_pod_'+row).html('<p style="color:#f00; text-align:center; font-weight:bold;">'+err_string+'</p>');

					}
					if ((data['RESULT'][eventNumber]['status'] == 'Y') && (eventType == 15679 ))
					{
						// $('#links_to_nakl_'+eventNumber).css('color','#2fa4e7');
						$('#links_to_nakl_'+eventNumber).css('color','#119504');
						var count = $('#count_value_'+row).val();
						parseInt(count,10);
						count = count - 1;
						$('#count_value_'+row).val(count);
						if (count == 0)
						{
							$('#links_to_manif_'+row).css('color','#119504');
							$('#tr_manifest_'+row).removeClass('danger');
							$('#tr_manifest_'+row+' .glyphicon').removeClass('glyphicon-fire');
							$('#tr_manifest_'+row+' .glyphicon').addClass('glyphicon-ok');
							$('#cont_part_'+row).remove();
						}
					}
					// console.log(data);
					ShowPack(row, eventNumber, 0);
				},
				"json"
			);
		}
	}
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
if ($arResult['OPEN']) 
{
	?>
	<div class="row">
        <div class="col-md-4">
            <div class="btn-group btn-group-justified">
            <a href="<?=$arParams['LINK'];?>" class="btn btn-default">
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
                    	<div class="input-group">
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
                            <?
							if (intval($arResult['CURRENT_AGENT']) > 0)
							{
								?>
                                <span class="input-group-addon">
                                    <a href="/agents/index.php?mode=agent&id=<?=$arResult['CURRENT_AGENT'];?>" target="_blank">
                                        <span class="glyphicon glyphicon-user" aria-hidden="true" title="Профиль агента"></span>
                                    </a>
                                </span>
                            	<?
							}
							?>
                        </div>
                    </div>
                    <?
				}
				if ($arResult['CURRENT_AGENT'])
				{
					?>
					<div class="form-group">
                    	<select name="type_m" size="1" class="form-control" id="type_m" onChange="ChangeTypeM()">
                        	<option value="A"<?=($arResult['CURRENT_TYPE_M'] == 'A') ? ' selected' : '';?>>Все</option>
                            <option value="O"<?=($arResult['CURRENT_TYPE_M'] == 'O') ? ' selected' : '';?>>Входящие</option>
                            <option value="I"<?=($arResult['CURRENT_TYPE_M'] == 'I') ? ' selected' : '';?>>Исходящие</option>
						</select>
                    </div>
                    <div class="form-group">
                    	<input type="text" id="filter_invoive" class="form-control" placeholder="Номер накладной">
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
	if (count($arResult['MANIFESTOS']) > 0)
	{
		?>
        <table width="100%" cellpadding="3" cellspacing="0" class="table table-bordered manifestos">
        	<thead>
            	<tr>
                	<td width="20"></td>
                	<td width="15%"><?=GetMessage('TABLE_HEAD_1');?></td>
                    <td width="20%"><?=GetMessage('TABLE_HEAD_2');?></td>
                    <td width="15%"><?=GetMessage('TABLE_HEAD_3');?></td>
                    <td width="15%"><?=GetMessage('TABLE_HEAD_4');?></td>
                    <td><?=GetMessage('TABLE_HEAD_5');?></td>
                    <td width="15%"><?=GetMessage('TABLE_HEAD_6');?></td>
                    <td width="15%"><?=GetMessage('TABLE_HEAD_7');?></td>
                </tr>
            </thead>
        	<tbody>
            	<?
				foreach ($arResult['MANIFESTOS_DATES'] as $k => $t)
				{
					$m = $arResult['MANIFESTOS'][$k];
					$arListNumbers = array();
					foreach ($m['NUMBERS'] as $n => $d)
					{
						$arListNumbers[] = $n;
					}
					?>
                    <tr class="filter_number <?=($m['DELIVERED'] == 'N') ? ' danger' : '';?>"  id="tr_manifest_<?=$m['ID'];?>" data-title="<?=implode($arListNumbers, ',');?>">
                    	<td width="2"><?=$m['INBOUND'];?></td>
                        <td><?=$m['PROPERTY_DATEDOC_VALUE'];?></td>
                        <td nowrap>
                        	<? 
							if ($m['DELIVERED'] == 'N')
							{
								?>
                        		<span class="glyphicon glyphicon-fire" aria-hidden="true"></span> 
                            	<?
							}
							else
							{
								?>
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> 
                                <?
							}
							?>
							<a href="javascript:void(0);" onClick="ShowManifest('more_<?=$m['ID'];?>');" id="links_to_manif_<?=$m['ID'];?>" <?=($m['DELIVERED'] == 'N') ? 'style="color:#f00;"' : 'style="color:#119504;"';?>>
							<?=$m['PROPERTY_NUMBER_VALUE'];?>
                            </a>
                        	<?
							if ($m['DELIVERED'] == 'N')
							{
								?>
                                <span style="font-weight:bold; color:#f00;" id="cont_part_<?=$m['ID'];?>">(отсутствуют ПОДы)</span>
                                <?
								// ($m['COUNT']-$m['COUNT_DELIVERED']); из $m['COUNT'];
							}
							?>
                            <input type="hidden" name="count_value_<?=$m['ID'];?>" value="<?=$m['COUNT'];?>" id="count_value_<?=$m['ID'];?>">
                        </td>
                        <td><?=$m['PROPERTY_DEPARTUREDATE_VALUE'];?></td>
                        <td><?=$m['PROPERTY_CALCULATEDDATE_VALUE'];?></td>
                        <td><?=WeightFormat($m['PROPERTY_WEIGHT_VALUE'], false);?></td>
                        <td><?=WeightFormat($m['PROPERTY_VOLUMEWEIGHT_VALUE'], false);?></td>
                        <td><?=$m['PROPERTY_PLACES_VALUE'];?></td>
                    </tr>
                    <tr class="notshow" id="more_<?=$m['ID'];?>">
                    	<td colspan="2">
                        	<?
							$mrj = 0;
							if (count($m['NUMBERS']) > 0)
							{
								?>
                                <p><strong>Список накладных</strong></p>
                                <?
								foreach ($m['NUMBERS'] as $n => $delivered)
								{
									$mrj++;
									$s = ($delivered == 'N') ? ' style="color:#F00;"' : '';
									//$s = '';
									?>
									<p><a href="javascript:void(0);" onClick="ShowPack('<?=$m['ID'];?>','<?=$n;?>',<?=$mrj*27;?>);"<?=$s;?> class="links_to_nakl" id="links_to_nakl_<?=$n;?>"><?=$n;?></a></p>
									<?
								}
							}
							?>
                        </td>
                        <td colspan="4">
                        	<div id="SpinnerImage_<?=$m['ID'];?>" style="text-align:center; margin:50px 0; height:32px; display:none;">
                            	<img src="/bitrix/templates/lk-newpartner/images/ajax-loader.gif" width="32" height="32">
							</div>
                            <div id="enter_pod_<?=$m['ID'];?>"></div>
                        	<div id="show_pack_info_<?=$m['ID'];?>"></div>
                        </td>
                        <td colspan="2">
                        	<p>Номер манифеста: <strong><?=$m['PROPERTY_NUMBER_VALUE'];?></strong></p>
                            <p>Дата манифеста: <strong><?=$m['PROPERTY_DATEDOC_VALUE'];?></strong></p>
                            <p>Город назначения: <strong><?=$m['PROPERTY_CITY_NAME'];?></strong></p>
                            <p>Получатель: <strong><?=$m['PROPERTY_AGENT_NAME'];?></strong></p>
                            <p>Отправитель: <strong><?=$m['PROPERTY_ORGANIZATION_VALUE'];?></strong></p>
                            <p>Дата отгрузки: <strong><?=$m['PROPERTY_DEPARTUREDATE_VALUE'];?></strong></p>
                        	<p>Расчетная дата прибытия: <strong><?=$m['PROPERTY_CALCULATEDDATE_VALUE'];?></strong></p>
                            <p>Количество мест: <strong><?=$m['PROPERTY_PLACES_VALUE'];?></strong></p>
                            <p>Вес: <strong><?=WeightFormat($m['PROPERTY_WEIGHT_VALUE']);?></strong></p>
                            <p>Объемный вес: <strong><?=WeightFormat($m['PROPERTY_VOLUMEWEIGHT_VALUE']);?></strong></p>
                            <p>Перевозчик: <strong><?=$m['PROPERTY_CARRIER_VALUE'];?></strong></p>
                            <p>Перевозочный документ: <strong><?=$m['PROPERTY_TRANSPORTATIONDOCUMENT_VALUE'];?></strong></p>
                            <p>Ответственный: <strong><?=$m['PROPERTY_RESPONSIBLY_VALUE'];?></strong></p>
                            <p>Метод расчета: <strong><?=$m['PROPERTY_CALCULATIONVARIANT_VALUE'];?></strong></p>
                            <p>Метод транспортировки: <strong><?=$m['PROPERTY_TRANSPORTATIONMETHOD_VALUE'];?></strong></p>
                            <p>Сумма перевозки: <strong><?=$m['PROPERTY_TRANSPORTATIONCOST_VALUE'];?></strong></p>
                            <p>Комментарий: <strong><?=$m['PROPERTY_COMMENT_VALUE'];?></strong></p>	
                        </td>
                    </tr>
                    <?
				}
				?>
            </tbody>
        </table>
        <?
	}
}