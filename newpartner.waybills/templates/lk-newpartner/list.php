<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>
<script type="text/javascript">
	$(function () {
		$(window).resize(function () {
			$('#tableId').bootstrapTable('resetView');
		});
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
		location.href = '<?=$arParams['LINK'];?>?ChangePeriod=Y&year='+y+'&month='+m;
	}
	function ChangeClient()
	{
		var cl = $("select#client").val();
		location.href = '<?=$arParams['LINK'];?>?ChangeClient=Y&client='+cl;
	}
	function ChangeBranch()
	{
		var br = $("select#branch").val();
		location.href = '<?=$arParams['LINK'];?>?ChangeBranch=Y&branch='+br;
	}
		function ChangeAgent()
	{
		var ag = $("select#agent").val();
		location.href = '<?=$arParams['LINK'];?>?ChangeAgent=Y&agent='+ag;
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
                <a href="<?=$arParams['LINK'];?>?mode=add" class="btn btn-warning">Новая накладная</a>
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
	if ((count($arResult['REQUESTS']) > 0) || (count($arResult['ARCHIVE']) > 0))
	{
		?>
        <table class="table table-condensed table-hover" data-toggle="table" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-height="800" id="tableId">
			<thead>
            	<tr>
                    <th width="20" data-switchable="false" data-align="center"></th>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_1');?></th>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_3');?></th>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_12');?></th>
					<?
					if ($arResult['ADMIN_AGENT'])
					{
						?>
                        
                        <th data-sortable="true"><?=GetMessage('TABLE_HEAD_13');?></th>
                        <th data-sortable="true"><?=GetMessage('TABLE_HEAD_14');?></th>
                        <?
					}
					?>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_4');?></th>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_6');?></th>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_7');?></th>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_5');?></th>
                    <th><?=GetMessage('TABLE_HEAD_8');?></th>
                    <th><?=GetMessage('TABLE_HEAD_9');?></th>
                    <th><?=GetMessage('TABLE_HEAD_10');?></th>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_16');?></th>
                    <th data-sortable="true"><?=GetMessage('TABLE_HEAD_15');?></th>
                </tr>
            </thead>
            <tbody>
				<?
                foreach ($arResult['REQUESTS'] as $r)
                {
                    ?>
                    <tr <?=((!strlen($r['PROPERTY_CITY_SENDER_NAME']))||(!strlen($r['PROPERTY_CITY_RECIPIENT_NAME']))) ? ' class="danger"' : '';?>>
                        <td>
							<a href="<?=$arParams['LINK'];?>?mode=pdf&id=<?=$r['ID'];?>&pdf=Y">
                            	<span class="glyphicon glyphicon-cloud-download" title="Скачать накладную"></span>
							</a>
                        </td>
                        <td><a href="<?=$arParams['LINK'];?>?mode=invoice&id=<?=$r['ID'];?>"><?=$r['NAME'];?></a></td>
                        <td><?=substr($r['DATE_CREATE'],0,10);?></td>
                        <td></td>
                        <?
						if ($arResult['ADMIN_AGENT'])
						{
							?>
                            
                            <td><?=$r['PROPERTY_BRANCH_NAME'];?></td>
                            <td><?=$r['PROPERTY_CONTRACT_NAME'];?></td>
                            <?
						}
						?>
                        <td><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
                        <td><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
                        <td><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
                        <td><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
                        <td><?=$r['PROPERTY_PLACES_VALUE'];?></td>
                        <td><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
                        <td><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?
                }
				foreach ($arResult['ARCHIVE'] as $r)
				{
					?>
                    <tr class="<?=$r['ColorRow'];?>">
                        <td></td>
                        <td>
                        	<a href="<?=$arParams['LINK'];?>?mode=invoice1c_modal&f001=<?=$r['NumDoc'];?>&pdf=Y" data-toggle="modal" data-target="#modal_inv1c_<?=str_replace('.','__',$r['NumDoc']);?>"><?=$r['NumDoc'];?></a>
                        </td>
                        <td><?=$r['start_date'];?></td>
                        <td><?=$r['ZakazName'];?></td>
                        <?
						if ($arResult['ADMIN_AGENT'])
						{
							?>
                            
                            <td></td>
                            <td></td>
                            <?
						}
						?>
                        <td><?=$r['CitySenderName'];?></td>
                        <td><?=$r['CompanySender'];?></td>
                        <td><?=$r['CityRecipientName'];?></td>
                        <td><?=$r['CompanyRecipient'];?></td>
                        <td><?=$r['Places'];?></td>
                        <td><?=$r['Weight'];?></td>
                        <td><?=$r['ObW'];?></td>
                        <td><?=$r['state_icon'];?> <?=$r['stateEdit'];?></td>
                        <td><?=$r['Manager'];?></td>
                    </tr>
                    <?
				}
                ?>
            </tbody>
		</table>
        <?
		foreach ($arResult['ARCHIVE'] as $r)
		{
			?>
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