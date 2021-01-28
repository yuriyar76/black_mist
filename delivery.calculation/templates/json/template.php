<?
$arRes = array();
foreach ($arResult as $k => $v)
{
	if (is_array($v))
	{
		foreach ($v as $kk => $vv)
		{
			$arRes[$k][$kk] = iconv("windows-1251","utf-8",$vv);
		}
	}
	else
	{
		$arRes[$k] = iconv("windows-1251","utf-8",$v);
	}
}
$out = json_encode($arRes);
echo $out;
?>