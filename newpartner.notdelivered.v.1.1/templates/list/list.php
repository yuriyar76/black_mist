<script type="text/javascript">
	$(document).ready(function () {
		$("#filter_invoive").on("keyup click input", function () {
			if (this.value.length > 0) {
				$(".searchrow").show().filter(function () {	
					return $(this).find('.links_to_nakl').text().toLowerCase().indexOf($("#filter_invoive").val().toLowerCase()) == -1;
				}).hide();
			}
			else {
				$(".searchrow").show();
			}
		});
        $('.maskdatetime').mask('99.99.9999 99:99:00');
	});
    
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	});

	function ChangeType(eventNumber)
	{
		var ev = $('#modal_'+eventNumber+' select.event').val();
		$('#modal_'+eventNumber+' .ErrDescr').css('display','none');
		$('#modal_'+eventNumber+' .ErrSit').css('display','none');
		if (ev == 15679)
		{
			$('#modal_'+eventNumber+' .ErrDescr').css('display','block');
		}
		if (ev == 15680)
		{
			$('#modal_'+eventNumber+' .ErrSit').css('display','block');
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


	function ShowPack(number)
	{
        //$('#modal_'+number+' .SpinnerImage').show();
		$('#modal_'+number+' .show_pack_info').html('');
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
								'<div class="form-group clearErr ErrDate"><input type="text" class="eventDate form-control input-sm maskdatetime" name="date"></div>'+
							'</td>'+
							'<td>'+
								'<div class="form-group clearErr ErrType"><select name="event" size="1" onChange="ChangeType( \''+number+' \');" class="event form-control input-sm">'+
									'<option value="0"></option><option value="15679">Доставлено</option>'+
									'<option value="15680">Исключительная ситуация!</option>'+
								'</select></div>'+
							'</td>'+
							'<td>'+
								'<div style="display:none;" class="form-group clearErr ErrDescr">'+
									'<input type="text" class="form-control input-sm eventDescr">'+
								'</div>'+
								'<div style="display:none;" class="form-group clearErr ErrSit">'+
									'<select name="situation" size="1" class="form-control input-sm EventSituation">'+
										'<option value="0"></option><?=$arResult['SITUATION_TEXT'];?>'+
									'</select>'+
								'</div>'+
							'</td>'+
						'</tr>'+
						'<tr>'+
							'<td colspan="3" class="text-center">'+
								'<button class="btn btn-sm btn-primary" onClick="AddEvent( \''+number+' \');">Добавить</button>'+
							'</td>'+
						'</tr>'+
					'</form>'
					);
				}
			});
			$( "<table/>", {
			"class": "table table-bordered manifestos",
			html: items.join( "" )
			}).appendTo( '#modal_'+number+' .show_pack_info');
			$('#modal_'+number+' .maskdatetime').mask('99.99.9999 99:99:00');
            //$('#modal_'+number+' .SpinnerImage').hide();
		});
	}

	function AddEvent(eventNumber)
	{
		$('#modal_'+eventNumber+' .enter_pod').html('');
		$('#modal_'+eventNumber+' .clearErr').removeClass('has-error');
		var eventDate = $('#modal_'+eventNumber+' .eventDate').val();
		var eventType = $('#modal_'+eventNumber+' .event').val();
		var eventDescr = $('#modal_'+eventNumber+' .eventDescr').val().replace(/\s+/g, '');
		var EventSituation = $('#modal_'+eventNumber+' .EventSituation').val();
		var eventDescrTotal = '';
		var f1 = 0;
		var f2 = 0;
		var f3 = 0;
		if (eventDate.length)
		{
			var time = eventDate.substr(eventDate.length - 8);
			if (time == '00:00:00')
			{
				$('#modal_'+eventNumber+' .ErrDate').addClass('has-error');
			}
			else
			{
				f1 = 1;
			}
		}
		else
		{
			$('#modal_'+eventNumber+' .ErrDate').addClass('has-error');
		}
		if (eventType == 0)
		{
			$('#modal_'+eventNumber+' .ErrType').addClass('has-error');
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
					$('#modal_'+eventNumber+' .ErrDescr').addClass('has-error');
				}
			}
			else
			{
				if (EventSituation == 0)
				{
					$('#modal_'+eventNumber+' .ErrSit').addClass('has-error');
				}
				else
				{
					f3 = 1;
				}
			}
		}
		if ((f1 == 1) &&(f2 == 1) && (f3 == 1))
		{
            $('#modal_'+eventNumber+' .SpinnerImage').show();
            $('#modal_'+eventNumber+' .enter_pod').html('');
            $('#modal_'+eventNumber+' .enter_pod').show();
			var posting = $.post(
				'/functions.php',
				{ "json": "1", "addto1c": "addto1c", "number" : eventNumber, "date" : eventDate, "event" : eventType, "situation" : EventSituation, "descr" : eventDescr},
				function(data){
                    if (data['RESULT'][eventNumber]['status'] == 'Y')
                    {
                        if (eventType == 15679)
                        {
                            $('#modal_'+eventNumber).removeClass('panel-default');
                            $('#modal_'+eventNumber).addClass('panel-success');
                        }
                        $('#modal_'+eventNumber+' .enter_pod').html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><p>Данные успешно отправлены</p></div>');
                        ShowPack(eventNumber);
                    }
                    else
                    {
                        $('#modal_'+eventNumber+' .enter_pod').html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><p>'+data['RESULT'][eventNumber]['comment']+'</p></div>');
                    }
				},
				"json"
			);
            posting.always(function( data ) {
                $('#modal_'+eventNumber+' .SpinnerImage').hide();
            });
		}
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
        <div class="col-md-3">
            <div class="btn-group btn-group-justified">
                <a href="<?=$arParams['LINK'];?>" class="btn btn-default">
                    <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Обновить список
                </a>
            </div>
        </div>
        <div class="col-md-2">
           <? if (count($arResult['NOT_DELIVERED']) > 0) : ?>
            <form action="<?=$arParams['LINK'];?>?mode=list_xls&pdf=Y" method="post" name="xlsform" class="form-inline" target="_blank">
                <input type="hidden" name="DATA" value="<?=htmlspecialchars($arResult['LIST_JSON'],ENT_COMPAT);?>">
                <button type="submit" class="btn btn-warning" data-toggle="tooltip" data-placement="right" title="Скачать список накладных">
                    <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                </button>
            </form>
            <? endif; ?>
        </div>
        <div class="col-md-7 text-right">
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
                    	<input type="text" id="filter_invoive" class="form-control" placeholder="Номер накладной">
                    </div>
                    <?
				}
				?>
            </form>
        </div>
    </div>
	<div class="row"><div class="col-md-12">&nbsp;</div></div>
    <?
	if (count($arResult['NOT_DELIVERED']) > 0)
	{
		?>
        <div class="row">
            <div class="col-md-6">
                <div class="panel-group">
                    <?
                    foreach ($arResult['NOT_DELIVERED'] as $v)
                    {
                        ?>
                        <div class="panel panel-default searchrow" id="modal_<?=$v;?>">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" href="#collapse_<?=$v;?>" class="links_to_nakl" id="links_to_nakl_<?=$v;?>"><?=$v;?></a></h4>
                            </div>
                            <div id="collapse_<?=$v;?>" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="SpinnerImage" style="text-align:center; margin:0 0 30px; display:none;">
                                        <div class="alert alert-info" role="alert"><p><strong>Выполняется передача данных, пожалуйста, подождите.</strong></p></div>
                                        <p><img src="/bitrix/templates/lk-newpartner/images/ajax-loader.gif" width="32" height="32"></p>
                                    </div>
                                    <div class="enter_pod"></div>
                                    <div class="show_pack_info">
                                        <?
                                        if (count($arResult['EVENTS'][$v]) > 0)
                                        {
                                            ?>
                                            <table class="table table-bordered manifestos">
                                                <tbody>
                                                    <?
                                                    foreach ($arResult['EVENTS'][$v] as $event)
                                                    {
                                                        ?>
                                                        <tr>
                                                            <td width="33%"><?=$event['date'];?></td>
                                                            <td width="33%"><?=$event['event'];?></td>
                                                            <td><?=$event['desc'];?></td>
                                                        </tr>
                                                        <?
                                                    }
                                                    ?>
                                                    <form class="form-inline" name="curform">
                                                        <tr>
                                                            <td>
                                                                <div class="form-group clearErr ErrDate">
                                                                    <input type="text" class="form-control input-sm maskdatetime eventDate" name="date">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-group clearErr ErrType">
                                                                   <select name="event" size="1" onChange="ChangeType('<?=$v;?>');" class="form-control input-sm event">
                                                                        <option value="0"></option>
                                                                        <option value="15679">Доставлено</option>
                                                                        <option value="15680">Исключительная ситуация!</option>
                                                                    </select>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div style="display:none;" class="form-group clearErr ErrDescr">
                                                                    <input type="text" class="form-control input-sm eventDescr">
                                                                </div>
                                                                <div style="display:none;" class="form-group clearErr ErrSit">
                                                                    <select name="situation" size="1" class="form-control input-sm EventSituation">
                                                                        <option value="0"></option>
                                                                        <?=$arResult['SITUATION_TEXT'];?>
                                                                    </select>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </form>
                                                    <tr>
                                                        <td colspan="3" class="text-center">
                                                            <button class="btn btn-sm btn-primary" onClick="AddEvent('<?=$v;?>');">Добавить</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <?
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-6">
                <!--Загрузка файлов-->
            </div>
        </div>
        <?
	}
	else
	{
		?>
		<div class="alert alert-dismissable alert-warning fade in" role="alert"><?=GetMessage('NO_DATA');?>
		</div>
        <?
	}
}