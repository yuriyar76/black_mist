<script type="text/javascript">
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
				$(this).addClass('CheckedRow');
			}
			else
			{
				$(this).removeClass('CheckedRow');
			}
		});
	}
</script>
<h2 class="partner"><?=$arResult['TITLE'];?></h2>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	echo '<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
}
if (count($arResult["MESSAGE"]) > 0) 
{
	echo '<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
}
if (count($arResult["WARNINGS"]) > 0)
{
	echo '<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}

if ($arResult['OPEN']) 
{
	?>
	<p align="right" class="partner"><a href="/partner/index.php?mode=add">Оформить новую заявку</a></p>
	<div class="filter">
		<form action="" method="get" name="filterform">
			<label for="number">Номер:</label>
			<input type="text" name="number" value="<?=trim($_GET['number']);?>" id="number">
			<label for="state">Статус: </label>
			<select name="state" size="1">
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
			<label for="date">Дата создания: </label>
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
					"INPUT_ADDITIONAL_ATTR" => 'placeholder="ДД.ММ.ГГГГ" pattern="[0-9]{2}.[0-9]{2}.[0-9]{4}" class="datetime"'
				),
				false
			);
			?>
			<input type="submit" name="" value="Фильтровать">
		</form>
		<form action="" method="get">
			<input type="submit" name="" value="Сбросить фильтр">
		</form>
	</div>
	<?
	if (count($arResult['REQUESTS']) > 0)
	{
		?>
		<form action="" method="post">
			<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
			<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
			<table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#CCC" class="requests">
				<thead>
					<tr>
						<td width="20" rowspan="2"><input type="checkbox" name="set" onclick="setChecked(this,'ids');"></td>
						<td class="sorts" rowspan="2">
							<div>
								<?=GetMessage('TABLE_HEAD_1');?>
								<a href="/partner/index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=PROPERTY_NUMBER&sort=asc" class="asc<?=(($arResult['SORT_BY'] == 'PROPERTY_NUMBER')&&($arResult['SORT'] == 'asc')) ? ' active' : '';?>"></a>
								<a href="/partner/index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=PROPERTY_NUMBER&sort=desc" class="desc<?=(($arResult['SORT_BY'] == 'PROPERTY_NUMBER')&&($arResult['SORT'] == 'desc')) ? ' active' : '';?>"></a>
							</div>
						</td>
						<td class="sorts" rowspan="2">
							<div>
								<?=GetMessage('TABLE_HEAD_2');?>
								<a href="/partner/index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=PROPERTY_STATE&sort=asc" class="asc<?=(($arResult['SORT_BY'] == 'PROPERTY_STATE')&&($arResult['SORT'] == 'asc')) ? ' active' : '';?>"></a>
								<a href="/partner/index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=PROPERTY_STATE&sort=desc" class="desc<?=(($arResult['SORT_BY'] == 'PROPERTY_STATE')&&($arResult['SORT'] == 'desc')) ? ' active' : '';?>"></a>
							</div>
						</td>
						<td class="sorts" rowspan="2">
							<div>
								<?=GetMessage('TABLE_HEAD_3');?>
								<a href="/partner/index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=DATE_CREATE&sort=asc" class="asc<?=(($arResult['SORT_BY'] == 'DATE_CREATE')&&($arResult['SORT'] == 'asc')) ? ' active' : '';?>"></a>
								<a href="/partner/index.php?number=<?=$_GET['number'];?>&state=<?=$_GET['state'];?>&date_from<?=$_GET['date_from'];?>&date_to<?=$_GET['date_to'];?>&sort_by=DATE_CREATE&sort=desc" class="desc<?=(($arResult['SORT_BY'] == 'DATE_CREATE')&&($arResult['SORT'] == 'desc')) ? ' active' : '';?>"></a>
							</div>
						</td>
						<td colspan="2"><?=GetMessage('TABLE_HEAD_5');?></td>
						<td colspan="2"><?=GetMessage('TABLE_HEAD_7');?></td>
						<td rowspan="2"><?=GetMessage('TABLE_HEAD_8');?></td>
						<td rowspan="2"><?=GetMessage('TABLE_HEAD_9');?></td>
						<td rowspan="2"><?=GetMessage('TABLE_HEAD_10');?></td>
						<td rowspan="2"><?=GetMessage('TABLE_HEAD_11');?></td>
					</tr>
					<tr>
						<td><?=GetMessage('TABLE_HEAD_4');?></td>
						<td><?=GetMessage('TABLE_HEAD_6');?></td>
						<td><?=GetMessage('TABLE_HEAD_4');?></td>
						<td><?=GetMessage('TABLE_HEAD_6');?></td>
					</tr>
				</thead>
				<tbody>
					<?
					foreach ($arResult['REQUESTS'] as $r)
					{
						?>
						<tr>
							<td>
								<?
								if ($r['PROPERTY_STATE_ENUM_ID'] == 236)
								{
									?>
									<input type="checkbox" name="ids[]" value="<?=$r['ID'];?>">
									<?
								}
								?>
							</td>
							<td>
								<?
								$mode = ($r['PROPERTY_STATE_ENUM_ID'] == 236) ? 'request_edit' : 'request';
								?>
								<a href="/partner/index.php?mode=<?=$mode;?>&id=<?=$r['ID'];?>"><?=$r['PROPERTY_NUMBER_VALUE'];?></a>
							</td>
							<td><?=$r['PROPERTY_STATE_VALUE'];?></td>
							<td><?=substr($r['DATE_CREATE'],0,10);?></td>
							<td><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
							<td><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
							<td><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
							<td><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
							<td><?=$r['PROPERTY_PLACES_VALUE'];?></td>
							<td><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
							<td><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
							<td></td>
						</tr>
						<?
					}
					?>
				</tbody>
			</table>
			<br>
			<input type="submit" name="send" value="<?=GetMessage("SEND_BTN");?>">
		</form>
		<?
	}
	else
	{
		?>
		<p>Список заявок пуст</p>
		<?
	}
	?>
	<div class="pagination">
		<div class="left_pag">
			<form action="<?=$APPLICATION->GetCurUri();?>" method="get">
				<input type="hidden" name="number" value="<?=$_GET['number'];?>">
				<input type="hidden" name="state" value="<?=$_GET['state'];?>">
				<input type="hidden" name="date_from" value="<?=$_GET['date_from'];?>">
				<input type="hidden" name="date_to" value="<?=$_GET['date_to'];?>">
				Показывать на одной странице: 
				<select name="on_page" size="1">
					<?
					foreach ($arResult["PAGES"] as $p)
					{
						?>
						<option value="<?=$p;?>"<?=($arResult['ON_PAGE'] == $p) ? ' selected': ''; ?>><?=$p;?></option>
						<?
					}
					?>
				</select>
				<input type="submit" name="" value="Применить">
			</form>
		</div>
		<div class="right_pag">
			<?=$arResult["NAV_STRING"];?>
		</div>
		<br class="clear">
	</div>
	<?
}
?>