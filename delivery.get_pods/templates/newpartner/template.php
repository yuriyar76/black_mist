<?
if ($arParams['SHOW_TITLE'] == 'Y')
{
	?>
	<h2><?=$arResult['TYTLE'];?></h2>
	<br>
	<?
}
if ($arParams['SHOW_FORM'] == 'Y')
{
	?>
    <form method="get" action="">
		<table cellspacing="0" cellpadding="5" border="0" width="" class="data-table">
        	<tbody>
				<tr>
                	<td><?=GetMessage('LABEL_NUMBER');?></td>
					<td><input type="text" value="<?=$arResult['NUMBERS'];?>" class="inp450" name="f001" placeholder="<?=GetMessage('PLACEHOLDER_NUMBER');?>"></td>
				</tr>
				<tr>
                	<td align="right" colspan="2"><input type="submit" class="track" value="<?=GetMessage('BTN');?>"></td>
				</tr>
			</tbody>
		</table>
    </form>
    <?
}
if (strlen($arResult['NUMBERS']))
{
	foreach ($arResult['AR_NUMBERS'] as $n)
	{
		if (isset($arResult['ALL_EVENTS'][$n]))
		{
			?>
			<table cellpadding="5" border="1" width="600" class="show_tracks">
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
	}
}
?>