<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$arResult["ELEMENTS"] = array();

if ($arParams["N_ZAKAZ"] == "Y")
{
	$arResult["ELEMENTS"][] = array(
		"SHOW" => '
			<label for="number">'.GetMessage("LABEL_N_ZAKAZ").': </label>
			<input type="text" name="number" id="number" value="'.trim($_GET['number']).'">'
		);
}

if ($arParams["DATE_CREATE"] == "Y")
{
	$d_from = (isset($_GET['date_from'])) ? $_GET['date_from'] : '';
	$d_to = (isset($_GET['date_to'])) ? $_GET['date_to'] : '';
	$m = array();	
	$m["CALENDAR"] = true;
	$m["PARAMS"] = array("INPUT_VALUE" => $d_from, "INPUT_VALUE_FINISH" => $d_to, 'INPUT_NAME' => 'date_from', 'INPUT_NAME_FINISH' => 'date_to');
	$m["SHOW"] = '<label for="date_from">'.GetMessage("LABEL_DATE_CREATE").': </label>';
	$arResult["ELEMENTS"][] = $m;
}

if ($arParams["DATE_TO_DELIVERY"] == "Y")
{
	if (isset($_GET['date_to_delivery_from'])) $d_from = $_GET['date_to_delivery_from']; else $d_from = date('01.m.Y');
	if (isset($_GET['date_to_delivery_to'])) $d_to = $_GET['date_to_delivery_to']; else $d_to = date('d.m.Y');
	$m = array();	
	$m["CALENDAR"] = true;
	$m["PARAMS"] = array("INPUT_VALUE" => $d_from, "INPUT_VALUE_FINISH" => $d_to, 'INPUT_NAME' => 'date_to_delivery_from', 'INPUT_NAME_FINISH' => 'date_to_delivery_to');
	$m["SHOW"] = '<label for="date_to_delivery_from">'.GetMessage("LABEL_DATE_TO_DELIVERY").': </label>';
	$arResult["ELEMENTS"][] = $m;
}

if ($arParams["AGENTS"] == "Y")
{
	$arResult["AGENTS_ARRAY"] = AvailableAgents(false, $arParams['UK_ID']);
	$m = 
	'<label for="agent">'.GetMessage("LABEL_AGENT").': </label>
	<select name="agent" id="agent">
		<option value="0">'.GetMessage("OPTION_ALL").'</option>';
		foreach ($arResult["AGENTS_ARRAY"] as $k => $v)
		{
			if ($_GET['agent'] == $k)
				$s = ' selected';
			else 
				$s = '';
			$m .= '<option value="'.$k.'"'.$s.'>'.$v.'</option>';
		}
	$m .= '
	</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams["STATES_SHORT"] == "Y")
{
	$m = '
		<label for="status_short">'.GetMessage("LABEL_FILTER").': </label> 
		<select name="status_short" id="status_short">
			<option value="0"></option>';
	$db_enum_list = CIBlockProperty::GetPropertyEnum(229, array("sort"=>"asc"));
	while ($ar_enum_list = $db_enum_list->GetNext())
	{
		if ($_GET['status_short'] == $ar_enum_list["ID"]) 
		{
			$s = ' selected'; 
		}
		else
		{
			$s = '';
		}
		$m .= '
			<option value="'.$ar_enum_list["ID"].'"'.$s.'>'.$ar_enum_list["VALUE"].'</option>';
	}
	$m .= 
		'</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams["EXCEPTIONS"] == "Y")
{
	$m = '
		<label for="exceptions">'.GetMessage("LABEL_EXCEPTIONS").': </label> 
			<select name="exceptions" id="exceptions">
				<option value="false"';
	if ($_GET['exceptions'] == 'false')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("EXCEPTIONS_ALL").'</option>
				<option value="1"';
	if ($_GET['exceptions'] == '1')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("EXCEPTIONS_YES").'</option>
				<option value="0"';
	if ($_GET['exceptions'] == '0')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("EXCEPTIONS_NO").'</option>
		</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams['SHOPS'] == 'Y')
{
	$current_uk = '';
	$arShops = TheListOfShops(0, false, true, false, '', $arParams['UK_ID']);
	$m = 
	'<label for="agent">'.GetMessage("LABEL_SHOP").': </label>
	<select name="shop" id="shop">
		<option value="0">'.GetMessage("OPTION_ALL").'</option>';
		foreach ($arShops as $v)
		{
			if ($_GET['shop'] == $v['ID'])
				$s = ' selected';
			else 
				$s = '';
			$m .= '<option value="'.$v['ID'].'"'.$s.'>'.$v['NAME'].'</option>';
		}
	$m .= '
	</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams['SIGN_IM'] == 'Y')
{
	$m = '
		<label for="sign_im">'.GetMessage("LABEL_SIGN_IM").': </label> 
			<select name="sign_im" id="sign_im">
				<option value="false"';
	if ($_GET['sign_im'] == 'false')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("SIGN_IM_ALL").'</option>
				<option value="1"';
	if ($_GET['sign_im'] == '1')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("SIGN_IM_YES").'</option>
				<option value="0"';
	if ($_GET['sign_im'] == '0')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("SIGN_IM_NO").'</option>
		</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams['SIGN_UK'] == 'Y')
{
	$m = '
		<label for="sign_uk">'.GetMessage("LABEL_SIGN_UK").': </label> 
			<select name="sign_uk" id="sign_uk">
				<option value="false"';
	if ($_GET['sign_uk'] == 'false')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("SIGN_UK_ALL").'</option>
				<option value="1"';
	if ($_GET['sign_uk'] == '1')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("SIGN_UK_YES").'</option>
				<option value="0"';
	if ($_GET['sign_uk'] == '0')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("SIGN_UK_NO").'</option>
		</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams['PAYMENTS'] == 'Y')
{
	$m = '
		<label for="payments">'.GetMessage("LABEL_PAYMENTS").': </label> 
			<select name="payments" id="payments">
				<option value="true"';
	if ($_GET['payments'] == 'true')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("PAYMENTS_ALL").'</option>
				<option value="Y"';
	if ($_GET['payments'] == 'Y')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("PAYMENTS_YES").'</option>
				<option value="0"';
	if ($_GET['payments'] == '0')
	{
		$m .= ' selected';
	}
	$m .= '>'.GetMessage("PAYMENTS_NO").'</option>
		</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams['CITY'] == 'Y')
{
	$m = '
		<label for="city">'.GetMessage("LABEL_CITY").': </label> 
			<select name="city" id="city">
				<option value="0"></option>';
	foreach ($arParams['ARCITIES'] as $k => $v)
	{
		$s = ($_GET['city'] == $k) ? ' selected' : '';
		$m .= '<option value="'.$k.'"'.$s.'>'.$v.'</option>';
	}
	$m .= '</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams['MESSAGE_TYPE'] == 'Y')
{
	$m = '
		<label for="message_type">'.GetMessage("LABEL_MESSAGE_TYPE").': </label> 
		<select name="message_type" id="message_type">
			<option value="0"></option>';
	$db_enum_list = CIBlockProperty::GetPropertyEnum(236, array("value"=>"asc"));
	while ($ar_enum_list = $db_enum_list->GetNext())
	{
		if ($_GET['message_type'] == $ar_enum_list["ID"]) 
		{
			$s = ' selected'; 
		}
		else
		{
			$s = '';
		}
		$m .= '
			<option value="'.$ar_enum_list["ID"].'"'.$s.'>'.$ar_enum_list["VALUE"].'</option>';
	}
	$m .= 
		'</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

if ($arParams['MESSAGE_READ'] == 'Y')
{
	$y = ($_GET['message_read'] == 'N') ? ' selected' : '';
	$n = ($_GET['message_read'] == 'Y') ? ' selected' : '';
	$m = '
		<label for="message_read">'.GetMessage("LABEL_MESSAGE_READ").': </label> 
		<select name="message_read" id="message_read">
			<option value="">'.GetMessage('All').'</option>
			<option value="N"'.$y.'>'.GetMessage('YES').'</option>
			<option value="Y"'.$n.'>'.GetMessage('NO').'</option>
		</select>';
	$arResult["ELEMENTS"][] = array("SHOW" => $m);
}

$arResult['on_page'] = 10;
if (isset($_GET['on_page']))
{
	$arResult['on_page'] = intval($_GET['on_page']);
}
else
{
	if (isset($_SESSION['ON_PAGE_GLOBAL']))
	{
		$arResult['on_page'] = intval($_SESSION['ON_PAGE_GLOBAL']);
	}
	else
	{
		$arResult['on_page'] = 10;
	}
}
if ($arResult['on_page'] < 10)
{
	$arResult['on_page'] = 10;
}
if ($arResult['on_page'] >= 200)
{
	$arResult['on_page'] = 200;
}

$this->IncludeComponentTemplate();