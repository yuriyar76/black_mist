<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$arResult['OPEN'] = true;
$arResult['USER_IN_BRANCH'] = false;
$arResult['CURRENT_BRANCH'] = false;


$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();
$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
$arResult['ADMIN_AGENT'] = false;
if ($agent_id > 0)
{
	$db_props = CIBlockElement::GetProperty(40, $agent_id, array("sort" => "asc"), Array("ID"=>211));
	if($ar_props = $db_props->Fetch())
	{
    	$agent_type = $ar_props["VALUE"];
		if (in_array($agent_type, array(51, $arParams["TYPE_WHO"])))
		{
			$arResult['AGENT'] = GetCompany($agent_id);
			if ($agent_type == 51)
			{
				$arResult['ADMIN_AGENT'] = true;
			}
			else
			{
				if ($arResult['AGENT']["PROPERTY_TYPE_WORK_BRANCHES_ENUM_ID"] == 301)
				{
					if (intval($_SESSION['CURRENT_BRANCH']) == 0)
					{
						LocalRedirect('/choice-branch/');
					}
					else
					{
						$arResult['USER_IN_BRANCH'] = true;
						$arResult['CURRENT_BRANCH'] = $_SESSION['CURRENT_BRANCH'];
					}
				}
				else
				{
					if (intval($arUser["UF_BRANCH"]))
					{
						$arResult['USER_IN_BRANCH'] = true;
						$arResult['CURRENT_BRANCH'] = intval($arUser["UF_BRANCH"]);
					}
				}
			}
		}
		else
		{
			$arResult['OPEN'] = false;
			$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
		}
	}
	else
	{
		$arResult['OPEN'] = false;
		$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
	}
}
else
{
	$arResult['OPEN'] = false;
	$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
}




if (is_array($_SESSION['MESSAGE']))
{
	$arResult["MESSAGE"] = $_SESSION['MESSAGE'];
	$_SESSION['MESSAGE'] = false;
}
if (is_array($_SESSION['ERRORS']))
{
	$arResult["ERRORS"] = $_SESSION['ERRORS'];
	$_SESSION['ERRORS'] = false;
}
if (is_array($_SESSION['WARNINGS']))
{
	$arResult["WARNINGS"] = $_SESSION['WARNINGS'];
	$_SESSION['WARNINGS'] = false;
}


// $arParams['TYPE'] = 259;
if ($arParams['TYPE'] == 259)
{
	$arParams['TYPE_NAME'] = 'Отправители';
	$arParams['TYPE_WHO_TEXT'] = 'отправителя';
	$arParams['TYPE_WHAT'] = 'отправителей';
	$arParams['TYPE_LINK'] = '/senders/';
	$arParams['TYPE_ONE'] = 'Отправитель';
	$arParams['TYPE_ONE_S'] = 'отправитель';
	$arParams['TYPE_NAME_S'] = 'отправители';
}
elseif ($arParams['TYPE'] == 260)
{
	$arParams['TYPE_NAME'] = 'Получатели';
	$arParams['TYPE_WHO_TEXT'] = 'получателя';
	$arParams['TYPE_WHAT'] = 'получателей';
	$arParams['TYPE_LINK'] = '/recipients/';
	$arParams['TYPE_ONE'] = 'Получатель';
	$arParams['TYPE_ONE_S'] = 'получатель';
	$arParams['TYPE_NAME_S'] = 'получатели';
}
else
{
	$arResult['OPEN'] = false;
	$arResult["ERRORS"][] = "Не определен тип контрагента";
}
if ($arParams['TYPE_WHO'] == 53)
{
	$arResult['TYPE_WHO_NAME'] = 'агент';
}
elseif ($arParams['TYPE_WHO'] == 242)
{
	$arResult['TYPE_WHO_NAME'] = 'клиент';
}
else
{
	$arResult['TYPE_WHO_NAME'] = 'контрагент';
}


$modes = array(
	'list',
	'add',
	'edit'
);

$arResult["PAGES"] = array(20, 50, 100, 200);

if ((strlen(trim($_GET['mode']))) && (in_array(trim($_GET['mode']), $modes)))
{
	$mode = trim($_GET['mode']);
}
else
{
	$mode = 'list';
}
if ($mode == 'list')
{
	if (isset($_POST['save']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if ((count($_POST['ids']) > 0) && (intval($_POST['action']) > 0))
			{
				switch (intval($_POST['action']))
				{
					case 1:
						foreach ($_POST['ids'] as $k)
						{
							$el = new CIBlockElement;
							$res = $el->Update($k, array("MODIFIED_BY" => $USER->GetID(),"ACTIVE" => "Y"));
						}
						$arResult["MESSAGE"][] = $arParams['TYPE_NAME'].' успешно активированы';
					break;
					case 2:
						foreach ($_POST['ids'] as $k)
						{
							$el = new CIBlockElement;
							$res = $el->Update($k, array("MODIFIED_BY" => $USER->GetID(),"ACTIVE" => "N"));
						}
						$arResult["MESSAGE"][] = $arParams['TYPE_NAME'].' успешно деактивированы';
					break;
					case 3:
						foreach ($_POST['ids'] as $k)
						{
							CIBlockElement::Delete($k);
						}
						$arResult["MESSAGE"][] = $arParams['TYPE_NAME'].' успешно удалены';
					break;
				}
			}
			else
			{
				$arResult["ERRORS"][] = 'Не выбраны '.$arParams['TYPE_NAME_S'].' или действие';
			}
		}
	}
	
	if (isset($_POST['activate']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (count($_POST['ids']) > 0)
			{
				foreach ($_POST['ids'] as $k)
				{
					$el = new CIBlockElement;
					$res = $el->Update($k, array("MODIFIED_BY" => $USER->GetID(),"ACTIVE" => "Y"));
				}
				$arResult["MESSAGE"][] = $arParams['TYPE_NAME'].' успешно активированы';
			}
			else
			{
				$arResult["ERRORS"][] = 'Не выбраны '.$arParams['TYPE_NAME_S'];
			}
		}
	}
	
	if (isset($_POST['deactivate']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (count($_POST['ids']) > 0)
			{
				foreach ($_POST['ids'] as $k)
				{
					$el = new CIBlockElement;
					$res = $el->Update($k, array("MODIFIED_BY" => $USER->GetID(),"ACTIVE" => "N"));
				}
				$arResult["MESSAGE"][] = $arParams['TYPE_NAME'].' успешно деактивированы';
			}
			else
			{
				$arResult["ERRORS"][] = 'Не выбраны '.$arParams['TYPE_NAME_S'];
			}
		}
	}
	
	if (isset($_POST['delete']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (count($_POST['ids']) > 0)
			{
				foreach ($_POST['ids'] as $k)
				{
					CIBlockElement::Delete($k);
				}
				$arResult["MESSAGE"][] = $arParams['TYPE_NAME'].' успешно удалены';
			}
			else
			{
				$arResult["ERRORS"][] = 'Не выбраны '.$arParams['TYPE_NAME_S'];
			}
		}
	}
	
	$f_name = false;
	if (strlen(trim($_GET['number'])))
	{
		$f_name = trim($_GET['number']);
	}

	$sorts_by = array("NAME");
	$sorts = array("asc","desc");
	if ((strlen($_GET['sort_by'])) && (in_array($_GET['sort_by'], $sorts_by)))
	{
		$arResult['SORT_BY'] = $_GET['sort_by'];
		$_SESSION['SORT_BY_REQVS'] = $arResult['SORT_BY'];
	}
	else
	{
		if (strlen($_SESSION['SORT_BY_COMPANIES']))
		{
			$arResult['SORT_BY'] = $_SESSION['SORT_BY_COMPANIES'];
		}
		else
		{
			$arResult['SORT_BY'] = $sorts_by[0];
		}
	}
	if ((strlen($_GET['sort'])) && (in_array($_GET['sort'], $sorts)))
	{
		$arResult['SORT'] = $_GET['sort'];
		$_SESSION['SORT_COMPS'] = $arResult['SORT'];
	}
	else
	{
		if (strlen($_SESSION['SORT_COMPS']))
		{
			$arResult['SORT'] = $_SESSION['SORT_COMPS'];
		}
		else
		{
			$arResult['SORT'] = $sorts[0];
		}
	}
	
	if (!$arResult['ADMIN_AGENT'])
	{
		$arResult['CURRENT_CLIENT'] = $agent_id;
		$arResult['LIST_OF_CLIENTS'] = false;
	}
	else
	{
		if (strlen($_SESSION['CURRENT_CLIENT']))
		{
			$arResult['CURRENT_CLIENT'] = $_SESSION['CURRENT_CLIENT'];
		}
		else
		{
			$arResult['CURRENT_CLIENT'] = 0;
		}
		if ($arParams['TYPE_WHO'] == 53)
		{
			$arResult['LIST_OF_CLIENTS'] = AvailableAgents(false, $agent_id);
		}
		else
		{
			$arResult['LIST_OF_CLIENTS'] = AvailableClients(false, false, $agent_id);
		}
		if ($_GET['ChangeClient'] == 'Y')
		{
			if (isset($arResult['LIST_OF_CLIENTS'][$_GET['client']]))
			{
				$_SESSION['CURRENT_CLIENT'] = $_GET['client'];
				$arResult['CURRENT_CLIENT'] = $_GET['client'];
			}
			elseif (intval($_GET['client']) == 0)
			{
				unset($_SESSION['CURRENT_CLIENT']);
				unset($_SESSION['CURRENT_CLIENT_INN']);
				$arResult['CURRENT_CLIENT'] = false;
				$arResult['CURRENT_CLIENT_INN'] = false;
			}
		}
	}
	if (intval($arResult['CURRENT_CLIENT']) > 0)
	{
		$listC = GetListContractors($arResult['CURRENT_CLIENT'], $arParams['TYPE'], true, $arParams['TYPE_NAME'], array($arResult['SORT_BY'] => $arResult['SORT']), $f_name, false, true, $arResult['CURRENT_BRANCH'], 200);
		$arResult["NAV_STRING"] = $listC["NAV_STRING"];
		$arResult["COMPANIES"] = $listC["COMPANIES"];
		//$arResult['TITLE'] = GetMessage('TITLE_MODE_LIST', array("#WHAT#" => $arParams['TYPE_WHAT']));
		$APPLICATION->SetTitle(GetMessage('TITLE_MODE_LIST', array("#WHAT#" => $arParams['TYPE_WHAT'])));
	}
	else
	{
		if (($arResult['ADMIN_AGENT']) && (intval($arResult['CURRENT_CLIENT']) == 0))
		{
			$arResult["WARNINGS"][] = 'Не выбран '.$arResult['TYPE_WHO_NAME'];
		}
		else
		{
			$arResult["WARNINGS"][] = 'Список '.$arParams['TYPE_WHAT'].' пуст';
		}
	}
	$arResult['TITLE'] = GetMessage('TITLE_MODE_LIST', array("#WHAT#" => $arParams['TYPE_WHAT']));
}


if ($mode == 'edit')
{
	if (isset($_POST['save']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			$arChanges = array(
				577 => trim($_POST['INDEX_SENDER']),
				574 => trim($_POST['NAME_SENDER']),
				575 => trim($_POST['PHONE_SENDER'])
			);
			/*
			if (!strlen($_POST['NAME_SENDER']))
			{
				$arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error';
			}
			else
			{
				$arChanges[574] = trim($_POST['NAME_SENDER']);
			}
			if (!strlen($_POST['PHONE_SENDER']))
			{
				$arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error';
			}
			else
			{
				$arChanges[575] = trim($_POST['PHONE_SENDER']);
			}
			*/
			if (!strlen($_POST['COMPANY_SENDER']))
			{
				$arResult["ERR_FIELDS"]["COMPANY_SENDER"] = 'has-error';
				$change_name = false;
			}
			else
			{
				$change_name = true;
			}
			if (!strlen($_POST['CITY_SENDER']))
			{
				$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
			}
			else
			{
				$city_sender = GetCityId(trim($_POST['CITY_SENDER']));
				if ($city_sender == 0)
				{
					$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
				}
				else
				{
					$arChanges[576] = $city_sender;
				}
			}
			if (!strlen($_POST['ADRESS_SENDER']))
			{
				$arResult["ERR_FIELDS"]["ADRESS_SENDER"] = 'has-error';
			}
			else
			{
				$arChanges[578] = trim($_POST['ADRESS_SENDER']);
			}
			CIBlockElement::SetPropertyValuesEx($_POST['id'], 84, $arChanges);
			if ($change_name)
			{
				$el = new CIBlockElement;
				$res = $el->Update($_POST['id'], array("MODIFIED_BY" => $USER->GetID(),"NAME" => NewQuotes($_POST['COMPANY_SENDER'])));
			}
			$_SESSION['MESSAGE'][] = 'Компания '.NewQuotes($_POST['COMPANY_SENDER']).' успешно изменена';
			LocalRedirect($arParams['TYPE_LINK']."index.php?mode=list");
		}
	}
	$arResult['COMPANY'] = false;
	$id_comp = intval($_GET['id']);
	$filter = array("IBLOCK_ID" => 84, "ID" => $id_comp, "PROPERTY_CREATOR" => $agent_id, 'PROPERTY_TYPE' => $arParams['TYPE']);
	if ($arResult['ADMIN_AGENT'])
	{
		unset($filter["PROPERTY_CREATOR"]);
	}
	if ($id_comp > 0)
	{
		$res = CIBlockElement::GetList(
			array("id" => "desc"), 
			$filter, 
			false, 
			false, 
			array(
				"ID",
				"NAME",
				"PROPERTY_NAME",
				"PROPERTY_PHONE",
				"PROPERTY_CITY",
				"PROPERTY_INDEX",
				"PROPERTY_ADRESS",
			)
		);
		if ($ob = $res->GetNextElement())
		{
			$r = $ob->GetFields();
			$r['PROPERTY_CITY'] = GetFullNameOfCity($r['PROPERTY_CITY_VALUE']);
			$arResult['COMPANY'] = $r;
			$arResult['TITLE'] = GetMessage("TITLE_MODE_EDIT", array("#WHAT#" => $arParams['TYPE_WHO_TEXT']));
			$APPLICATION->SetTitle(GetMessage("TITLE_MODE_EDIT", array("#WHAT#" => $arParams['TYPE_WHO_TEXT'])));
		}
		else
		{
			$arResult['TITLE'] = GetMessage('ERR_NO_COMPANY');
			$APPLICATION->SetTitle(GetMessage('ERR_NO_COMPANY'));
		}
	}
	else
	{
		$arResult['TITLE'] = GetMessage('ERR_NO_COMPANY');
		$APPLICATION->SetTitle(GetMessage('ERR_NO_COMPANY'));
	}
}

if ($mode == 'add')
{
	if (isset($_POST['add']))
	{
		AddToLogs('ContractorsAddPostValues',$_POST);
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			/*
			if (!strlen($_POST['NAME_SENDER']))
			{
				$arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error';
			}
			if (!strlen($_POST['PHONE_SENDER']))
			{
				$arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error';
			}
			*/
			if (!strlen($_POST['COMPANY_SENDER']))
			{
				$arResult["ERR_FIELDS"]["COMPANY_SENDER"] = 'has-error';
			}
			if (!strlen($_POST['CITY_SENDER']))
			{
				$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
			}
			else
			{
				$city_sender = GetCityId(trim($_POST['CITY_SENDER']));
				if ($city_sender == 0)
				{
					$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
				}
			}
			if (!strlen($_POST['ADRESS_SENDER']))
			{
				$arResult["ERR_FIELDS"]["ADRESS_SENDER"] = 'has-error';
			}

			if (count($arResult["ERR_FIELDS"]) == 0)
			{
				$el = new CIBlockElement;
				$arLoadProductArray = Array(
					"MODIFIED_BY" => $USER->GetID(), 
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 84,
					"PROPERTY_VALUES" => array(
						579 => $arResult['ADMIN_AGENT'] ? $_SESSION['CURRENT_CLIENT'] : $agent_id,
						580 => $arParams['TYPE'],
						574 => trim($_POST['NAME_SENDER']),
						575 => trim($_POST['PHONE_SENDER']),
						576 => $city_sender,
						577 => trim($_POST['INDEX_SENDER']),
						578 => trim($_POST['ADRESS_SENDER']),
						581 => 0,
						668 => $arResult['CURRENT_BRANCH']
					),
					"NAME" => trim($_POST['COMPANY_SENDER']),
					"ACTIVE" => "Y"
				);
				if ($z_id = $el->Add($arLoadProductArray))
				{
					$_SESSION['MESSAGE'][] = $arParams['TYPE_ONE']." успешно добавлен";				
					LocalRedirect($arParams['TYPE_LINK']."index.php");
				}
				else
				{
					$arResult['ERRORS'][] = $el->LAST_ERROR;
				}
			}
		}
	}
	
	$arResult['TITLE'] = GetMessage("TITLE_MODE_ADD", array("#WHAT#" => $arParams['TYPE_WHO_TEXT']));
	$APPLICATION->SetTitle(GetMessage("TITLE_MODE_ADD", array("#WHAT#" => $arParams['TYPE_WHO_TEXT'])));
}
$this->IncludeComponentTemplate($mode);