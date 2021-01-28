<?
$arJson = array();
/*
if ($USER->GetID() == 102)
{
	echo '<pre>';
	print_r($arResult);
	echo '</pre>';
}
*/
if (strlen($arResult['NUMBERS']))
{
	foreach ($arResult['AR_NUMBERS'] as $n)
	{
		//$k = iconv('windows-1251','utf-8',$n);
		$k = $n;
		$arJson[$k] = array();
		if (mb_detect_encoding($n) == 'UTF-8')
		{
			$n = iconv('utf-8','windows-1251',$n);
		}
		/*
		if ($arParams['TEST_MODE'] == 'Y')
		{
			echo $n.' '.mb_detect_encoding($n);;
		}
		*/
		if (isset($arResult['ALL_EVENTS'][$n]))
		{
			/*
			if ($arParams['TEST_MODE'] == 'Y')
			{
				echo 'yes';
			}
			*/
			$delivered = false;
			foreach ($arResult['DATES_NAKLS_SORT'][$n] as $key => $date)
			{
				$event = $arResult['ALL_EVENTS'][$n][$key];
				
				if ($_REQUEST['DD'] == 'Y')
				{
					if ($event['Event'] == 'Доставлено')
					{
						$aRev['DateEvent'] = iconv('windows-1251','utf-8',$event['DateEvent'].' '.$event['TimeEvent'].':00');
						$aRev['Event'] = iconv('windows-1251','utf-8',$event['Event']);
						$aRev['InfoEvent'] = iconv('windows-1251','utf-8',$event['InfoEvent']);
						$aRev['INN'] = iconv('windows-1251','utf-8',$event['INN']);
						$arJson[$k][] = $aRev;
					}
				}
				else
				{
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
					$aRev['DateEvent'] = iconv('windows-1251','utf-8',$event['DateEvent'].' '.$event['TimeEvent'].':00');
					$aRev['Event'] = iconv('windows-1251','utf-8',$event['Event']);
					$aRev['InfoEvent'] = iconv('windows-1251','utf-8',$event['InfoEvent']);
					$aRev['INN'] = iconv('windows-1251','utf-8',$event['INN']);
					$arJson[$k][] = $aRev;
				}
			}
		}
	}
}
/*
if ($arParams['TEST_MODE'] == 'Y')
{
	print_r($arJson);
}
*/
echo json_encode($arJson);
?>