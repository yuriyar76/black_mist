<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
} ?>
<script type="text/javascript">
	
	function ChangePeriod()
	{
		var y = $("select#year").val();
		var m = $("select#month").val();
		location.href = '<?=$arParams['LINK'];?>index.php?ChangePeriod=Y&year='+y+'&month='+m;
	}
	function ChangeClient()
	{
		var cl = $("select#client").val();
		location.href = '<?=$arParams['LINK'];?>index.php?ChangeClient=Y&client='+cl;
	}
	function ChangeBranch()
	{
		var br = $("select#branch").val();
		location.href = '<?=$arParams['LINK'];?>index.php?ChangeBranch=Y&branch='+br;
	}
		function ChangeAgent()
	{
		var ag = $("select#agent").val();
		location.href = '<?=$arParams['LINK'];?>index.php?ChangeAgent=Y&agent='+ag;
	}
	
	function PullCost()
	{
		$('.calc_costs').each(function()
		{
			var	cost = $(this).val();
			if (cost.length > 0)
			{
				var id_string = $(this).data("item");
				$('#rate_'+id_string).val(cost);
			}
		})	
		return false;
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
		<div class="col-md-12 text-right">
            <form action="" method="get" name="filterform" class="form-inline">
				<?
				if ($arResult['LIST_OF_AGENTS'])
				{
					?>
                    <div class="form-group">
                    	<select name="agent" size="1" class="form-control" id="agent" onChange="ChangeAgent();">
                        	<option value="0">Все</option>
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
				if ($arResult['LIST_OF_CLIENTS'])
				{
					?>
                    <div class="form-group">
                    	<select name="client" size="1" class="form-control" id="client" onChange="ChangeClient();">
                        	<option value="0">Все</option>
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
                    <?
				}
				if ($arResult['LIST_OF_BRANCHES'])
				{
					?>
                    <div class="form-group">
                    	<select name="branch" size="1" class="form-control" id="branch" onChange="ChangeBranch();">
                        	<option value="0">Все</option>
                            <?
							foreach ($arResult['LIST_OF_BRANCHES'] as $k => $v)
							{
								$s = ($arResult['CURRENT_BRANCH'] == $k) ? ' selected' : '';
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
	if (count($arResult['REQUESTS']) > 0)
	{
		?>
        <form class="form-inline" method="post">
			<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
			<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
            <table width="100%" cellpadding="3" cellspacing="0" class="table table-striped table-bordered table-hover requests">
                <thead>
                    <tr>
                        <th width="20" rowspan="2"></th>
                        <th class="sorts">
                        
                                                <div>
                                                Номер накладной
                            <?=GetMessage('TABLE_HEAD_1');?>
                            <a href="<?=$arParams['LINK'];?>index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=name&sort=asc" class="asc<?=(($arResult['SORT_BY'] == 'name')&&($arResult['SORT'] == 'asc')) ? ' active' : '';?>"></a>
                            <a href="<?=$arParams['LINK'];?>index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=name&sort=desc" class="desc<?=(($arResult['SORT_BY'] == 'name')&&($arResult['SORT'] == 'desc')) ? ' active' : '';?>"></a>
                        </div>
                        </th>
                        <th class="sorts">
                        
                        <div>
                        Дата
                        <a href="<?=$arParams['LINK'];?>index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=created&sort=asc" class="asc<?=(($arResult['SORT_BY'] == 'created')&&($arResult['SORT'] == 'asc')) ? ' active' : '';?>"></a>
                            <a href="<?=$arParams['LINK'];?>index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=created&sort=desc" class="desc<?=(($arResult['SORT_BY'] == 'created')&&($arResult['SORT'] == 'desc')) ? ' active' : '';?>"></a>
                        </div>
                        </th>
                        <th>Агент</th>
                        <th>Филиал</th>
                        <th>Город отправления</th>
                        <th>Город назначения</th>
                        <th>Вес</th>
                        <th>Объемный вес</th>
                        <th>Транзит<br>через<br>Москву</th>
                        <th>Стоимость</th>
                        <th>Расчетная стоимость</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    $ii = 0;
                    foreach ($arResult['REQUESTS'] as $r)
                    {
                        $ii++;
                        ?>
                        <tr 
                        <?
						if (!strlen(trim($r["PROPERTY_RATE_VALUE"])))
						{
							echo ' class="warning"';
						}
						else
						{
							echo (trim($r["PROPERTY_RATE_VALUE"]) != trim($r['CALCULATED_COST'])) ? ' class="danger"' : '';
						}
						?>
                        >
                            <td align="right"><?=$ii;?></td>
                            <td><a href="http://delivery-russia.ru/tracking.php?f001=<?=trim($r['NAME']);?>" target="_blank"><?=trim($r['NAME']);?></a></td>
                            <td><?=substr($r['DATE_CREATE'],0,10);?></td>
                            <td><?=$r['PROPERTY_AGENT_NAME'];?></td>
                            <td><?=$r['PROPERTY_BRANCH_NAME'];?></td>
                            <td><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
                            <td><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
                            <td><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
                            <td><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                            <td align="center"><?=($r['PROPERTY_TRANSIT_MOSCOW_VALUE'] == 1) ? '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>' : '';?></td>
                            <td>
                            	<input type="text" class="form-control input-sm" 
                                	name="rate[<?=$r['ID'];?>]" value="<?=trim($r["PROPERTY_RATE_VALUE"]);?>" id="rate_<?=$r['ID'];?>">
                            </td>
                            <td>
							<?=str_replace('.',',',trim($r['CALCULATED_COST']));?>
                            <input type="hidden" class="calc_costs" value="<?=trim($r['CALCULATED_COST']);?>" data-item="<?=$r['ID'];?>">
                            </td>
                        </tr>
                        <?
                    }
                    ?>
                </tbody>
            </table>
            <input type="submit" name="save" value="Сохранить" class="btn btn-primary"> 
            <button type="button" class="btn btn-default" onClick="PullCost();">Заполнить стоимость из расчетных значений</button>
        </form>
        <br>
		<?
	}
	else
	{
		if ($arResult['ADMIN_AGENT'])
		{
			if ((intval($arResult['CURRENT_AGENT']) == 0) && (intval($arResult['CURRENT_CLIENT']) == 0))
			{
				?>
                <div class="alert alert-dismissable alert-warning fade in" role="alert">Не выбран агент или клиент</div>
                <?
			}
			else
			{
				?>
                <div class="alert alert-dismissable alert-warning fade in" role="alert">Список накладных пуст</div>
                <?
			}
		}
		else
		{
			?>
            <div class="alert alert-dismissable alert-warning fade in" role="alert">Список накладных пуст</div>
            <?
		}
	}
}
?>