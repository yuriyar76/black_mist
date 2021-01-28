<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$u_id = $USER->GetID();
$agent_array = GetCurrentAgent($u_id);
if(intval($agent_array['type']) != 51)
{
    die();
}
$arResult['OPEN'] = true;
$arResult['quarter'] = GetQuarter();

$modes = array(
	'list',
	'add',
	'customer',
	'contract',
	'add_contract',
	'branch',
	'add_branch'
);

if ((strlen($arParams['MODE'])) && (in_array($arParams['MODE'], $modes)))
{
	$mode = $arParams['MODE'];
}
else
{
	if ((strlen(trim($_GET['mode']))) && (in_array(trim($_GET['mode']), $modes)))
	{
		$mode = trim($_GET['mode']);
	}
	else
	{
		$mode = $modes[0];
	}
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

if ($mode == 'list')
{
	if (isset($_POST['applay']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (count($_POST['customers']) == 0)
			{
				$arResult["ERRORS"][] = 'Не выбран клиент';
			}
			else
			{
				foreach ($_POST['customers'] as $id)
				{
					switch ($_POST['action'])
					{
						case 'Y':
							$el = new CIBlockElement;
							$res = $el->Update($id, array("ACTIVE"=>"Y"));
							$arResult['MESSAGE'][] = 'Клиенты успешно активированы';
							break;
						case 'N':
							$el = new CIBlockElement;
							$res = $el->Update($id, array("ACTIVE"=>"N"));
							$arResult['MESSAGE'][] = 'Клиенты успешно деактивированы';
							break;
						case 'D':
							CIBlockElement::Delete($id);
							$arResult['MESSAGE'][] = 'Клиенты успешно удалены';
							break;
					}
				}
			}
		}
	}
	$arResult["LIST"] = array();
	$res = CIBlockElement::GetList(
		array("NAME" => "asc"), 
		array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "PROPERTY_UK" => $agent_array['id']), 
		false, 
		false, 
		array("ID","NAME","ACTIVE", "PROPERTY_AVAILABLE_FOR_AGENT","PROPERTY_INN","PROPERTY_INN_REAL")
	);
	while ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arResult["LIST"][] = $arFields;
	}
}

if ($mode == 'add')
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
			if (!strlen(NewQuotes($_POST['NAME'])))
			{
				$arResult['ERR_FIELDS']['NAME'] = 'has-error';
			}
			else
			{
				$el = new CIBlockElement;
				$arLoadProductArray = Array(
					"MODIFIED_BY" => $USER->GetID(), 
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 40,
					"NAME" => NewQuotes($_POST['NAME']),
					"ACTIVE" => $_POST['ACTIVE'],
					"PROPERTY_VALUES" => array(
						635 => $_POST['AVAILABLE_FOR_AGENT'],
						304 => GetMaxIDIN(40, 3),
						211 => 242,
						681 => 5000,
						696 => (intval($_POST['TYPE_WORK_BRANCHES']) > 0) ? intval($_POST['TYPE_WORK_BRANCHES']) : false,
						697 => $_POST['SHOW_LIMITS'],
						467 => $agent_array['id'],
						746 => $_POST['AVAILABLE_WH_WH'],
						762 => $_POST['AVAILABLE_CALL_COURIER'],
						765 =>  ((!(isset ($_POST['AVAILABLE_EXPRESS2']))) ? 0 : 1),	 
						766 => 	((!(isset ($_POST['AVAILABLE_EXPRESS4']))) ? 0 : 1),	
						767 =>	((!(isset ($_POST['AVAILABLE_EXPRESS8']))) ? 0 : 1),	 
						770 =>	((!(isset ($_POST['AVAILABLE_EXPRESS']))) ? 0 : 1),	 
						768 =>	((!(isset ($_POST['AVAILABLE_STANDART']))) ? 0 : 1),	
						769 =>	((!(isset ($_POST['AVAILABLE_ECONOME']))) ? 0 : 1),
						773 => $_POST['SHOW_HIDDEN_INNER_NUMBER']
					)
				);
				if ($client_id = $el->Add($arLoadProductArray))
				{
					$_SESSION["MESSAGE"][] = 'Клиент '.NewQuotes($_POST['NAME']).' успешно добавлен';
					LocalRedirect($arParams['LINK'].'index.php?mode=customer&id='.$client_id);
				}
			}
		}
	}
}


if ($mode == 'customer')
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
			if (!strlen(NewQuotes($_POST['NAME'])))
			{
				$arResult['ERR_FIELDS']['NAME'] = 'has-error';
			}
			else
			{
				$city_id = GetCityId(trim($_POST['CITY']));
				$city_id = ($city_id != 0) ? $city_id: false;
					
				$el = new CIBlockElement;
				$res = $el->Update($_POST['id'], array("MODIFIED_BY" => $USER->GetID(),"NAME" => NewQuotes($_POST['NAME']),"ACTIVE" => $_POST['ACTIVE']));
				CIBlockElement::SetPropertyValuesEx($_POST['id'], false, array(
					635 => $_POST['AVAILABLE_FOR_AGENT'],
					187 => $city_id,
					243 => $_POST['EMAIL'],
					265 => $_POST['PHONES'],
					190 => $_POST['ADRESS'],
					625 => $_POST['ADRESS_FACT'],
					681	=> (intval($_POST['COEFFICIENT_VW']) > 0) ? intval($_POST['COEFFICIENT_VW']) : 5000,
					696 => (intval($_POST['TYPE_WORK_BRANCHES']) > 0) ? intval($_POST['TYPE_WORK_BRANCHES']) : false,
					697 => $_POST['SHOW_LIMITS'],
					237 => $_POST['INN'],
					729 => $_POST['INN_REAL'],
					714 => $_POST['BY_AGENT'],
					329 => NewQuotes($_POST['LEGAL_NAME']),
					378 => NewQuotes($_POST['LEGAL_NAME_FULL']),
					746 => $_POST['AVAILABLE_WH_WH'],
					762 => $_POST['AVAILABLE_CALL_COURIER'],
					765 =>	((!(isset ($_POST['AVAILABLE_EXPRESS2']))) ? 0 : 1),	 
					766 => 	((!(isset ($_POST['AVAILABLE_EXPRESS4']))) ? 0 : 1),	
					767 =>	((!(isset ($_POST['AVAILABLE_EXPRESS8']))) ? 0 : 1),	 
					770 =>	((!(isset ($_POST['AVAILABLE_EXPRESS']))) ? 0 : 1),	 
					768 =>	((!(isset ($_POST['AVAILABLE_STANDART']))) ? 0 : 1),	
					769 =>	((!(isset ($_POST['AVAILABLE_ECONOME']))) ? 0 : 1),
					773 => $_POST['SHOW_HIDDEN_INNER_NUMBER']
				));
				$arResult["MESSAGE"][] = 'Информация обновлена';
			}
		}
	}
	
	if (isset($_POST['applay_contracts']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (count($_POST['contracts']) == 0)
			{
				$arResult["ERRORS"][] = 'Не выбран договор';
			}
			else
			{
				foreach ($_POST['contracts'] as $id)
				{
					switch ($_POST['action'])
					{
						case 'Y':
							$el = new CIBlockElement;
							$res = $el->Update($id, array("ACTIVE"=>"Y"));
							$arResult['MESSAGE'][] = 'Договора успешно активированы';
							break;
						case 'N':
							$el = new CIBlockElement;
							$res = $el->Update($id, array("ACTIVE"=>"N"));
							$arResult['MESSAGE'][] = 'Договора успешно деактивированы';
							break;
						case 'D':
							CIBlockElement::Delete($id);
							$arResult['MESSAGE'][] = 'Договора успешно удалены';
							break;
					}
				}
			}
		}
	}
	
	if (isset($_POST['applay_branches']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (count($_POST['branches']) == 0)
			{
				$arResult["ERRORS"][] = 'Не выбран филиал';
			}
			else
			{
				foreach ($_POST['branches'] as $id)
				{
					switch ($_POST['action'])
					{
						case 'Y':
							$el = new CIBlockElement;
							$res = $el->Update($id, array("ACTIVE"=>"Y"));
							$arResult['MESSAGE'][] = 'Филиалы успешно активированы';
							break;
						case 'N':
							$el = new CIBlockElement;
							$res = $el->Update($id, array("ACTIVE"=>"N"));
							$arResult['MESSAGE'][] = 'Филиалы успешно деактивированы';
							break;
						case 'D':
							CIBlockElement::Delete($id);
							$arResult['MESSAGE'][] = 'Филиалы успешно удалены';
							break;
					}
				}
			}
		}
	}
	
	if (isset($_POST['change_branch']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			$change = false;
			foreach ($_POST['user_branch'] as $user => $new_b)
			{
				if ($_POST['user_branch_before'][$user] != $new_b)
				{
					$userB = new CUser;
					$userB->Update($user, array("UF_BRANCH" => $new_b));
					$change = true;
				}
			}
			if ($change)
			{
				$arResult["MESSAGE"][] = 'Изменения сохранены';
			}
			else
			{
				$arResult["WARNINGS"][] = 'Изменения не обнаружены';
			}
		}
		
	}
	
	$arResult["INFO"] = false;
	$res = CIBlockElement::GetList(
		array("NAME" => "asc"), 
		array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "ID" => intval($_GET['id']),"PROPERTY_UK" => $agent_array['id']), 
		false, 
		false, 
		array("ID","NAME","ACTIVE", "PROPERTY_AVAILABLE_FOR_AGENT","PROPERTY_CITY","PROPERTY_INN", 
		"PROPERTY_INN_REAL", "PROPERTY_EMAIL", "PROPERTY_PHONES", 
		"PROPERTY_ADRESS", "PROPERTY_ADRESS_FACT","PROPERTY_COEFFICIENT_VW","PROPERTY_TYPE_WORK_BRANCHES", 
		"PROPERTY_SHOW_LIMITS", 'PROPERTY_BY_AGENT', 
		'PROPERTY_LEGAL_NAME', 'PROPERTY_LEGAL_NAME_FULL',
		'PROPERTY_AVAILABLE_WH_WH','PROPERTY_AVAILABLE_CALL_COURIER',
		'PROPERTY_AVAILABLE_EXPRESS2',	 
		'PROPERTY_AVAILABLE_EXPRESS4',	
		'PROPERTY_AVAILABLE_EXPRESS8',	 
		'PROPERTY_AVAILABLE_EXPRESS',	 
		'PROPERTY_AVAILABLE_STANDART',	
		'PROPERTY_AVAILABLE_ECONOME',
		'PROPERTY_SHOW_HIDDEN_INNER_NUMBER')
	);
	if ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arResult["INFO"] = $arFields;
		$arResult["INFO"]["PROPERTY_CITY"] = GetFullNameOfCity($arResult["INFO"]["PROPERTY_CITY_VALUE"]);
		$arResult["INFO"]["CONTRACTS"] = array();
		$arResult["INFO"]["BRANCHES"] = array();
		$arResult["INFO"]['USERS'] = array();
		$res_2 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
			false, 
			false, 
			array("ID","NAME","ACTIVE", "PROPERTY_NUMBER", "PROPERTY_DATE", "PROPERTY_LIMIT")
		);
		while ($ob_2 = $res_2->GetNextElement())
		{
			$arFields_2 = $ob_2->GetFields();
			$arResult["INFO"]["CONTRACTS"][] = $arFields_2;
		}
		$res_3 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
			false, 
			false, 
			array("ID","NAME","ACTIVE","PROPERTY_CITY.NAME","PROPERTY_LIMIT","PROPERTY_PAYED",'PROPERTY_HEAD_BRANCH')
		);
		while ($ob_3 = $res_3->GetNextElement())
		{
			$arFields_3 = $ob_3->GetFields();
			$arFields_3["PROPERTY_LIMIT"] = $arFields_3["PROPERTY_LIMIT_VALUE"][$arResult['quarter']];
			$arResult["INFO"]["BRANCHES"][] = $arFields_3;
		}
		$rsUser = CUser::GetList(($by="last_name"), ($order="asc"),array("GROUPS_ID" => array(22), "UF_COMPANY_RU_POST" => $arResult["INFO"]['ID']), array("SELECT" => array("UF_BRANCH","UF_ROLE")));
		while($arUser = $rsUser->Fetch())
		{
			$arResult["INFO"]['USERS'][] = $arUser;;
		}
        $arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_array['id']);
		/*
		echo '<pre>';
		print_r($arResult["INFO"]['USERS']);	
		echo '</pre>';
		*/
	}
    else
    {
        $arResult["ERRORS"][] = 'Клиент не найден. <a href="/customers/">Вернуться к списку клиентов</a>';
    }
}

if ($mode == 'contract')
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
			if (!strlen(NewQuotes($_POST['NUMBER'])))
			{
				$arResult['ERR_FIELDS']['NUMBER'] = 'has-error';
			}
			if (!strlen($_POST['DATE']))
			{
				$arResult['ERR_FIELDS']['DATE'] = 'has-error';
			}
			if (count($arResult['ERR_FIELDS']) == 0)
			{
				$el = new CIBlockElement;
				$res = $el->Update($_POST['id'], array("MODIFIED_BY" => $USER->GetID(), "NAME" => "№".NewQuotes($_POST['NUMBER'])." от ".$_POST['DATE'],"ACTIVE" => $_POST['ACTIVE']));
				CIBlockElement::SetPropertyValuesEx($_POST['id'], false, array(
							627 => NewQuotes($_POST['NUMBER']),
							628 => $_POST['DATE'],
							630 => floatval(str_replace(',','.',$_POST['LIMIT'])),
							685 => json_encode($_POST['dateperiod'])
						));
				$arResult["MESSAGE"][] = 'Информация обновлена';
			}
		}
	}
	$arResult["INFO"] = false;
	$res = CIBlockElement::GetList(
		array("NAME" => "asc"), 
		array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "ID" => intval($_GET['id']), "PROPERTY_UK" => $agent_array['id']), 
		false, 
		false, 
		array("ID","NAME","ACTIVE", "PROPERTY_AVAILABLE_FOR_AGENT")
	);
	if ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arResult["INFO"] = $arFields;
		
		$res_2 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"], "ID" => intval($_GET['contract_id'])), 
			false, 
			false, 
			array("ID","NAME","ACTIVE", "PROPERTY_NUMBER", "PROPERTY_DATE", "PROPERTY_LIMIT","PROPERTY_PERIODS")
		);
		if ($ob_2 = $res_2->GetNextElement())
		{
			$arFields_2 = $ob_2->GetFields();
			$arFields_2["PROPERTY_PERIODS"] = json_decode(htmlspecialcharsBack($arFields_2["PROPERTY_PERIODS_VALUE"]), true);
			$arResult["INFO"]["CONTRACT"] = $arFields_2;
		}
		$arResult["INFO"]["CONTRACTS"] = array();
		$res_2 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
			false, 
			false, 
			array("ID","NAME","ACTIVE", "PROPERTY_NUMBER", "PROPERTY_DATE", "PROPERTY_LIMIT")
		);
		while ($ob_2 = $res_2->GetNextElement())
		{
			$arFields_2 = $ob_2->GetFields();
			$arResult["INFO"]["CONTRACTS"][] = $arFields_2;
		}
	}
}

if ($mode == 'add_contract')
{
	if (isset($_POST['add']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (!strlen(NewQuotes($_POST['NUMBER'])))
			{
				$arResult['ERR_FIELDS']['NUMBER'] = 'has-error';
			}
			if (!strlen($_POST['DATE']))
			{
				$arResult['ERR_FIELDS']['DATE'] = 'has-error';
			}
			if (count($arResult['ERR_FIELDS']) == 0)
			{
				$el = new CIBlockElement;
				$arLoadProductArray = Array(
					"MODIFIED_BY" => $USER->GetID(), 
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 88,
					"NAME" => "№".NewQuotes($_POST['NUMBER'])." от ".$_POST['DATE'],
					"ACTIVE" => $_POST['ACTIVE'],
					"PROPERTY_VALUES" => array(
						629 => $_POST['id'],
						627 => NewQuotes($_POST['NUMBER']),
						628 => $_POST['DATE'],
						630 => floatval(str_replace(',','.',$_POST['LIMIT']))
					)
				);
				if ($contract_id = $el->Add($arLoadProductArray))
				{
					$_SESSION["MESSAGE"][] = "№".NewQuotes($_POST['NUMBER'])." от ".$_POST['DATE']." успешно добавлен";
					LocalRedirect($arParams['LINK'].'index.php?mode=customer&id='.$_POST['id']);
				}
			}
		}
	}
	$arResult["INFO"] = false;
	$res = CIBlockElement::GetList(
		array("NAME" => "asc"), 
		array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "ID" => intval($_GET['id']), "PROPERTY_UK" => $agent_array['id']), 
		false, 
		false, 
		array("ID","NAME","ACTIVE", "PROPERTY_AVAILABLE_FOR_AGENT")
	);
	if ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arResult["INFO"] = $arFields;
		$arResult["INFO"]["CONTRACTS"] = array();
		$res_2 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
			false, 
			false, 
			array("ID","NAME","ACTIVE", "PROPERTY_NUMBER", "PROPERTY_DATE", "PROPERTY_LIMIT")
		);
		while ($ob_2 = $res_2->GetNextElement())
		{
			$arFields_2 = $ob_2->GetFields();
			$arResult["INFO"]["CONTRACTS"][] = $arFields_2;
		}
	}
	$arResult['TITLE'] = GetMessage('TITLE_MODE_ADD_CONTRACT');
	$APPLICATION->SetTitle(GetMessage('TITLE_MODE_ADD_CONTRACT'));
}

if ($mode == 'branch')
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
			if (!strlen(NewQuotes($_POST['NAME'])))
			{
				$arResult['ERR_FIELDS']['NAME'] = 'has-error';
			}
			if (!strlen(trim($_POST['CITY'])))
			{
				$arResult['ERR_FIELDS']['CITY'] = 'has-error';
			}
			else
			{
				$city_id = GetCityId($_POST['CITY']);
				if ($city_id == 0)
				{
					$arResult['ERR_FIELDS']['CITY'] = 'has-error';
				}
			}
            if (!strlen(trim($_POST['IN_1C_CODE'])))
            {
                $arResult['ERR_FIELDS']['IN_1C_CODE'] = 'has-error';
            }
			if (count($arResult['ERR_FIELDS']) == 0)
			{
				$el = new CIBlockElement;
				$res = $el->Update($_POST['id'],array("MODIFIED_BY" => $USER->GetID(),"NAME" => NewQuotes($_POST['NAME']),"ACTIVE" => $_POST['ACTIVE']));
				CIBlockElement::SetPropertyValuesEx($_POST['id'], false, array(
							636 => NewQuotes($_POST['FIO']),
							637 => NewQuotes($_POST['PHONE']),
							632 => $city_id,
							638 => NewQuotes($_POST['INDEX']),
							633 => NewQuotes($_POST['ADRESS']),
							643 => $_POST['IN_1C'],
							644 => $_POST['BY_AGENT'],
							645 => $_POST['EMAIL'],
							666 => $_POST['IN_1C_CODE'],
							667 => $_POST['IN_1C_PREFIX'],
							672 => $_POST['SEND_REPORT'],
                            731 => intval($_POST['HEAD_BRANCH'])
						));
                if ((intval($_POST['HEAD_BRANCH']) == 1) && (count($_POST['head_branches_yes']) > 0))
                {
                    foreach ($_POST['head_branches_yes'] as $c)
                    {
                        CIBlockElement::SetPropertyValuesEx($c, 89, array(731 => 0));
                    }
                }
				$arResult["MESSAGE"][] = 'Информация обновлена';
			}
		}
	}
	
	if (isset($_POST['savelimitperiods']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			CIBlockElement::SetPropertyValuesEx($_POST['id'], 89, array(686 => array("VALUE" => array ("TEXT" => json_encode($_POST['limitperiods']), "TYPE" => "text"))));
			$arResult["MESSAGE"][] = 'Информация обновлена';
		}
	}
	
	$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id, true);
	$arResult["INFO"] = false;
	$res = CIBlockElement::GetList(
		array("NAME" => "asc"), 
		array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "ID" => intval($_GET['id']), "PROPERTY_UK" => $agent_array['id']), 
		false, 
		false, 
		array("ID","NAME","ACTIVE", "PROPERTY_AVAILABLE_FOR_AGENT")
	);
	if ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arResult["INFO"] = $arFields;
		$res_2 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"], "ID" => intval($_GET['branch_id'])), 
			false, 
			false, 
			array("ID","NAME","ACTIVE","PROPERTY_FIO","PROPERTY_PHONE","PROPERTY_CITY","PROPERTY_INDEX","PROPERTY_ADRESS","PROPERTY_LIMIT","PROPERTY_IN_1C","PROPERTY_BY_AGENT","PROPERTY_EMAIL","PROPERTY_IN_1C_CODE", "PROPERTY_IN_1C_PREFIX", "PROPERTY_SEND_REPORT", "PROPERTY_LIMITPERIODS", 'PROPERTY_HEAD_BRANCH')
		);
		if ($ob_2 = $res_2->GetNextElement())
		{
			$arFields_2 = $ob_2->GetFields();
			if (strlen($arFields_2['PROPERTY_LIMITPERIODS_VALUE']['TEXT']))
			{
				$arFields_2['LIMITPERIODS'] = json_decode(htmlspecialcharsBack($arFields_2['PROPERTY_LIMITPERIODS_VALUE']['TEXT']), true);
			}
			$arResult["INFO"]["BRANCH"] = $arFields_2;
			$arResult["INFO"]["BRANCH"]["PROPERTY_CITY"] = GetFullNameOfCity($arResult["INFO"]["BRANCH"]["PROPERTY_CITY_VALUE"]);
		}
		$arResult["INFO"]["BRANCHES"] = array();
		$res_3 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
			false, 
			false, 
			array("ID","NAME","ACTIVE","PROPERTY_CITY.NAME","PROPERTY_LIMIT", 'PROPERTY_HEAD_BRANCH')
		);
		while ($ob_3 = $res_3->GetNextElement())
		{
			$arFields_3 = $ob_3->GetFields();	
			$arFields_3["PROPERTY_LIMIT"] = $arFields_3["PROPERTY_LIMIT_VALUE"][$arResult['quarter']];
			$arResult["INFO"]["BRANCHES"][] = $arFields_3;
		}
		$arResult["INFO"]["CONTRACTS"] = array();
		$res_2 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
			false, 
			false, 
			array("ID","NAME","ACTIVE", "PROPERTY_NUMBER", "PROPERTY_DATE", "PROPERTY_LIMIT", "PROPERTY_PERIODS")
		);
		while ($ob_2 = $res_2->GetNextElement())
		{
			$arFields_2 = $ob_2->GetFields();
			$arFields_2['PERIODS'] = false;
			if (strlen($arFields_2['PROPERTY_PERIODS_VALUE']))
			{
				$arFields_2['PERIODS'] = json_decode(htmlspecialcharsBack($arFields_2['PROPERTY_PERIODS_VALUE']), true);
			}
			$arResult["INFO"]["CONTRACTS"][] = $arFields_2;
		}
	}
}

if ($mode == 'add_branch')
{
	if (isset($_POST['add']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (!strlen(NewQuotes($_POST['NAME'])))
			{
				$arResult['ERR_FIELDS']['NAME'] = 'has-error';
			}
			if (!strlen(trim($_POST['CITY'])))
			{
				$arResult['ERR_FIELDS']['CITY'] = 'has-error';
			}
			else
			{
				$city_id = GetCityId($_POST['CITY']);
				if ($city_id == 0)
				{
					$arResult['ERR_FIELDS']['CITY'] = 'has-error';
				}
			}
            if (!strlen(trim($_POST['IN_1C_CODE'])))
            {
                $arResult['ERR_FIELDS']['IN_1C_CODE'] = 'has-error';
            }
			if (count($arResult['ERR_FIELDS']) == 0)
			{
                /*
				$arL = array(
					floatval(str_replace(',','.',$_POST['LIMIT'][0])),
					floatval(str_replace(',','.',$_POST['LIMIT'][1])),
					floatval(str_replace(',','.',$_POST['LIMIT'][2])),
					floatval(str_replace(',','.',$_POST['LIMIT'][3]))
				);
				if ($_POST['NDS'] == 1)
				{
					foreach ($arL as $k => $v)
					{
						$arL[$k] = $v*1.18;
					}
				}
                */
				$el = new CIBlockElement;
				$arLoadProductArray = Array(
					"MODIFIED_BY" => $USER->GetID(), 
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 89,
					"NAME" => NewQuotes($_POST['NAME']),
					"ACTIVE" => $_POST['ACTIVE'],
					"PROPERTY_VALUES" => array(
						631 => $_POST['id'],
						636 => NewQuotes($_POST['FIO']),
						637 => NewQuotes($_POST['PHONE']),
						632 => $city_id,
						638 => NewQuotes($_POST['INDEX']),
						633 => NewQuotes($_POST['ADRESS']),
						// 634 => $arL
                        645 => $_POST['EMAIL'],
                        643 => $_POST['IN_1C'],
                        644 => $_POST['BY_AGENT'],

                        666 => $_POST['IN_1C_CODE'],
                        667 => $_POST['IN_1C_PREFIX'],
                        672 => $_POST['SEND_REPORT'],
                        731 => intval($_POST['HEAD_BRANCH']) 
					)
				);
				if ($branch_id = $el->Add($arLoadProductArray))
				{
                    if ((intval($_POST['HEAD_BRANCH']) == 1) && (count($_POST['head_branches_yes']) > 0))
                    {
                        foreach ($_POST['head_branches_yes'] as $c)
                        {
                            CIBlockElement::SetPropertyValuesEx($c, 89, array(731 => 0));
                        }
                    }
					$_SESSION["MESSAGE"][] = "Филиал ".NewQuotes($_POST['NAME'])." успешно добавлен";
					LocalRedirect($arParams['LINK'].'index.php?mode=customer&id='.$_POST['id']);
				}
			}
		}
	}
	$arResult["INFO"] = false;
	$res = CIBlockElement::GetList(
		array("NAME" => "asc"), 
		array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "ID" => intval($_GET['id']), "PROPERTY_UK" => $agent_array['id']), 
		false, 
		false, 
		array("ID","NAME","ACTIVE", "PROPERTY_AVAILABLE_FOR_AGENT")
	);
	if ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arResult["INFO"] = $arFields;
		$arResult["INFO"]["BRANCHES"] = array();
		$res_3 = CIBlockElement::GetList(
			array("NAME" => "asc"), 
			array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
			false, 
			false, 
			array("ID","NAME","ACTIVE","PROPERTY_CITY.NAME","PROPERTY_LIMIT", 'PROPERTY_HEAD_BRANCH')
		);
		while ($ob_3 = $res_3->GetNextElement())
		{
			$arFields_3 = $ob_3->GetFields();
			$arFields_3["PROPERTY_LIMIT"] = $arFields_3["PROPERTY_LIMIT_VALUE"][$arResult['quarter']];
			$arResult["INFO"]["BRANCHES"][] = $arFields_3;
		}
	}
    $arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id, true);
	$arResult['TITLE'] = GetMessage('TITLE_MODE_ADD_BRANCH');
	$APPLICATION->SetTitle(GetMessage('TITLE_MODE_ADD_BRANCH'));
}

$this->IncludeComponentTemplate($mode);