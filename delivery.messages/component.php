<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule("currency");
$u_id = $USER->GetID();

$agent_array = GetCurrentAgent($u_id);
$agent_id = $agent_array['id'];
$componentPage = false;

$arResult['COMPANY_TYPE'] = $agent_array["type"];
$arResult['COMPANY_ID'] = $agent_array['id'];

$modes = array(
	'inbox',
	'outbox',
	'detail',
	'create'
);
if (in_array($_GET['mode'], $modes))
{
	$mode = $_GET['mode'];
}
else
{
	$mode = 'inbox';
}
	
$arResult["MENU"] = array(
	'inbox' => 'Входящие сообщения',
	'outbox' => 'Исходящие сообщения'
);
$arResult['MODE'] = $mode;
	
$componentPage = $mode;

if (isset($_POST['applay']))
{
	if ($_POST['action'] == 1)
	{
		foreach ($_POST['message'] as $id)
		{
			$el = new CIBlockElement;
			$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
		}
		if (count($_POST['message']) == 1) 
		{
			$arResult['MESSAGE'][] = 'Сообщение отмечено как непрочитанное';
		}
		elseif (count($_POST['message']) > 1)
		{
			$arResult['MESSAGE'][] = 'Сообщения отмечены как непрочитанные';
		}
	}
	if ($_POST['action'] == 2)
	{
		foreach ($_POST['message'] as $id)
		{
			$el = new CIBlockElement;
			$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
		}
		if (count($_POST['message']) == 1) 
		{
			$arResult['MESSAGE'][] = 'Сообщение  отмечено как прочитанное';
		}
		elseif (count($_POST['message']) > 1)
		{
			$arResult['MESSAGE'][] = 'Сообщения отмечены как прочитанные';
		}
	}
	if ($_POST['action'] == 3)
	{
		foreach ($_POST['message'] as $id)
		{
			CIBlockElement::Delete($id);
		}
		if (count($_POST['message']) == 1) 
		{
			$arResult['MESSAGE'][] = 'Сообщение успешно удалено';
		}
		elseif (count($_POST['message']) > 1)
		{
			$arResult['MESSAGE'][] = 'Сообщения успешно удалены';
		}
	}
}

$arResult["BUTTONS"] = array(
	"create" => array(
		"in_mode" => array("inbox"),
		"title" => GetMessage("CREATE_MESSAGE_BTN"),
		"link" => '/messages/index.php?mode=create'
	)
);
	
if ($mode == 'inbox')
{
	$arResult["TITLE"] = 'Входящие сообщения';
	$arResult["MESS"] = ListOfMasages(intval($_GET['shop']), $agent_id, $_GET['message_read'], 0, intval($_GET['message_type']));
	$arResult["NAV_STRING"] = $arResult["MESS"]["NAV_STRING"];
	unset($arResult["MESS"]["NAV_STRING"]);
}

if ($mode == 'outbox')
{
	$arResult["TITLE"] = 'Исходящие сообщения';
	$arResult["MESS"] = ListOfMasages($agent_id, intval($_GET['shop']), $_GET['message_read'], 0, intval($_GET['message_type']));
	$arResult["NAV_STRING"] = $arResult["MESS"]["NAV_STRING"];
	unset($arResult["MESS"]["NAV_STRING"]);
}

if ($mode == 'detail')
{
	/* if (isset($_POST['city_secc'])) {
		//находим заказы
		$res3 = CIBlockElement::GetList(array("timestamp_x"=>"DESC"), array("IBLOCK_ID"=>42,"PROPERTY_CITY"=>$_POST['city_id'],"PROPERTY_CREATOR"=>$_POST['to_who']), false, false, array("ID"));
		while($ob3 = $res3->GetNextElement()) {
			$arFields3 = $ob3->GetFields();
			CIBlockElement::SetPropertyValuesEx($arFields3["ID"], false, array(203=>39,229=>66));
		}
		//создаем сообщение
		$el = new CIBlockElement;
		$arLoadProductArray = Array(
				"MODIFIED_BY"    => $u_id,
  				"IBLOCK_ID"      => 50,
  				"PROPERTY_VALUES"=> array(234=>$agent_id,235=>$_POST['to_who'],236=>86,242=>GetFullNameOfCity($_POST['city_id'])),
				"NAME"           => "В систему добавлен город"
				);
		$PRODUCT_ID = $el->Add($arLoadProductArray);
		$arResult['MESSAGE'][] = 'Сообщение №'.$PRODUCT_ID.' успешно отправлено';
	}
	
	*/

	$mess_id = intval($_GET['id']);
	
	if ($mess_id > 0)
	{
		$arResult["MESS"] = ListOfMasages(0,0,'',$mess_id);
		unset($arResult["MESS"]["NAV_STRING"]);
		if (count($arResult["MESS"]) == 1)
		{
			$arResult["I"] = $agent_id;
			if (($arResult["MESS"][0]["PROPERTY_TO_VALUE"] == $agent_id) && ($arResult["MESS"][0]["PROPERTY_TYPE_ENUM_ID"] != 83))
			{
				$el = new CIBlockElement;
				$res = $el->Update($mess_id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
			}
			$arResult["TITLE"] = $arResult["MESS"][0]["PROPERTY_TYPE_VALUE"];
		}
		else
		{
			$arResult["TITLE"] = 'Сообщение не найдено';
		}
	}
	else
	{
		$arResult["TITLE"] = 'Сообщение не найдено';
	}
}

if ($mode == 'create')
{
}

$this->IncludeComponentTemplate($componentPage);