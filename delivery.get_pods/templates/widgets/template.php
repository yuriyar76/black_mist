<?
if ($arParams['SHOW_TITLE'] == 'Y')
{
	?>
	<div class="row">
		<div class="col">
			<h2><?=$arResult['TYTLE'];?></h2>
		</div>
	</div>
	<?
}
if ($arParams['SHOW_FORM'] == 'Y')
{
	?>
	<div class="row">
		<div class="col">
			<form action="http://agent.newpartner.ru/widgets/tracking.php" method="GET">
				<div class="input-group my-3">
					<input class="form-control" name="f001" value="<?=$arResult['NUMBERS'];?>" type="text" id="f001" placeholder="<?=GetMessage('PLACEHOLDER_NUMBER');?>" aria-describedby="basic-addon"  aria-label="<?=GetMessage('PLACEHOLDER_NUMBER');?>">
					<div class="input-group-append">
						<button class="btn btn-outline-secondary" type="submit"><?=GetMessage('BTN');?></button>
					</div>
				</div>
			</form>
		</div>
	</div>
    <?
}
if (strlen($arResult['NUMBERS']))
{
	foreach ($arResult['AR_NUMBERS'] as $n)
	{
		?>
		<div class="row">
			<div class="col">
				<?
				if (isset($arResult['ALL_EVENTS'][$n]))
				{
					?>
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th colspan="3"><?=GetMessage('TRACK_TITLE', array('#NUMBER#' => $n));?></th>
							</tr>
						</thead>
						<tbody>
							<?
							$delivered = false;
							foreach ($arResult['DATES_NAKLS_SORT'][$n] as $key => $date)
							{
								$event = $arResult['ALL_EVENTS'][$n][$key];
								$sec = ($arParams['SHOW_SEC']) ? ':00' : '';
								if ($event['Event'] == 'Доставлено')
								{
									if ($delivered)
									{
										continue;
									}
									else
									{
										$delivered = true;
									}
								}
								?>
								<tr>
									<td width="30%"><?=$event['DateEvent'].' '.$event['TimeEvent'].$sec;?></td>
									<td width="35%"><?=$event['Event'];?></td>
									<td width="35%"><?=$event['InfoEvent'];?></td>
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
					<p><?=GetMessage('NO_NAKL', array('#NUMBER#' => $n));?></p>
					<?
				}
				?>
			</div>
		</div>
		<?
	}
}
?>