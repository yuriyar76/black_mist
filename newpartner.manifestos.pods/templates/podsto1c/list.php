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
	
	function ShowPack(number)
	{
		$('#show_pack_info').html('');
		$('#SpinnerImage').show();
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
						$('#links_to_nakl_'+key).css('color','#119504');
					}
				});
				if (delivered != 1)
				{

					items.push( ''+
					'<form class="form-inline" name="curform">'+
						'<tr>'+
							'<td>'+
								'<input type="hidden" id="eventNumber" value="'+number+'" name="eventNumber">'+
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
			}).appendTo( '#show_pack_info');
			$('.maskdatetime').mask('99.99.9999 99:99:00');
			$('#SpinnerImage').hide();
			
		});
	}

	function AddEvent()
	{
		$('#enter_pod').html('');
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
			$('#show_pack_info').html('');
			$('#SpinnerImage').show();
			$.post(
				'/functions.php',
				{ "json": "1", "addto1c": "addto1c", "number" : eventNumber, "date" : eventDate, "event" : eventType, "situation" : EventSituation, "descr" : eventDescr},
				function(data){
					if (data['ERRORS'])
					{
						var err_string = data['ERRORS'].join('<br>');
						$('#enter_pod').html('<p style="color:#f00; text-align:center; font-weight:bold;">'+err_string+'</p>');

					}
					if ((data['RESULT'][eventNumber]['status'] == 'Y') && (eventType == 15679 ))
					{
						//$('#links_to_nakl_'+eventNumber).css('color','#2fa4e7');
						$('#links_to_nakl_'+eventNumber).css('color','#119504');
					}
					ShowPack(eventNumber);
				},
				"json"
			);
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
	if (count($arResult['NOT_DELIVERED']) > 0)
	{
		?>
        <div class="row">
            <div class="col-md-6">
                <table width="100%" cellpadding="3" cellspacing="0" class="table table-bordered manifestos">
                    <thead>
                    </thead>
                    <tbody>
                        <?
                        foreach ($arResult['NOT_DELIVERED'] as $k => $v)
                        {
                            ?>
                            <tr class="searchrow">
                                <td width="50%">
                                	<? /* ?>
                                    <a href="javascript:void(0);" onClick="ShowPack('<?=$v;?>');" style="color:#F00;" class="links_to_nakl" id="links_to_nakl_<?=$v;?>"><?=$v;?></a>
                                    <? */ ?>
                                    <a href="javascript:void(0);" onClick="ShowPack('<?=$v;?>');" class="links_to_nakl" id="links_to_nakl_<?=$v;?>"><?=$v;?></a>
                                </td>
                            </tr>
                            <?
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <div id="SpinnerImage" style="text-align:center; margin:50px 0; height:32px; display:none;">
                	<img src="/bitrix/templates/lk-newpartner/images/ajax-loader.gif" width="32" height="32">
                </div>
                <div id="enter_pod"></div>
                <div id="show_pack_info"></div>
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