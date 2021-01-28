<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule("currency");
$u_id = $USER->GetID();
$arResult["CURRENT_USER"] = $u_id;

$agent_array = GetCurrentAgent($u_id);
$agent_id = $agent_array['id'];
$componentPage = false;

/**************************************************/
/************************УК************************/ 
/**************************************************/

if ($agent_array['type'] == 51)
{
	$modes = array("default");
	if (in_array($_GET['mode'],$modes))
	{
		$mode = $_GET['mode'];
	}
	else
	{
		if ($arParams['MODE'])
		{
			$mode = $arParams['MODE'];
		}
		else
		{
			$mode = $modes[0];
		}
	}
	$componentPage = "upr_".$mode;
	
	if ($mode == 'default')
	{
		$arResult["TITLE"] = 'Распределение по курьерам';
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
				if (strlen(trim($_POST['number'])) && strlen(trim($_POST['date'])))
				{
					$res = CIBlockElement::GetList(array("id" => "desc"), Array("IBLOCK_ID"=>42, "PROPERTY_N_ZAKAZ_IN"=>trim($_POST['number'])), false, Array("nTopCount"=>1),array("ID"));
					if($ob = $res->GetNextElement())
					{
						$arFields = $ob->GetFields();
						$st = 43;
						$st_sh = 71;
						CIBlockElement::SetPropertyValuesEx($arFields['ID'], 42, array(203 => $st, 229 => $st_sh));
						$history_id = AddToHistory($arFields['ID'], $agent_id, $u_id, $st, '', $_POST['date']);
						$short_history_id = AddToShortHistory($arFields['ID'], $u_id, $st_sh, '', $_POST['date']);
						$arResult["MESSAGE"][] = 'Заказ '.trim($_POST['number']).' выдан курьеру на маршрут';
						unset($_POST);
					}
					else
					{
						$arResult["ERRORS"][] = 'Заказ '.trim($_POST['number']).' не найден';
					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Не заполнены все поля';
				}
			}
		}
	}
}

/***************************************************/
/***********************АГЕНТ***********************/
/***************************************************/

if ($agent_array['type'] == 53)
{
	$modes = array("courier","default","print_orders");
	if (in_array($_GET['mode'],$modes)) $mode = $_GET['mode'];
	else {
		if ($arParams['MODE']) $mode = $arParams['MODE'];
		else $mode = 'default';
	}
	$arResult["MENU"] = array(
		'default'=> 'Список курьеров'
	);
	unset($arResult["MENU"][$mode]);
	$componentPage = "agent_".$mode;
	
	/***************************************************/
	if ($mode == 'default')
	{
		$arResult["TITLE"] = "Список курьеров";
		if (isset($_POST['add_courier'])) {
			if (strlen($_POST['fio']) <= 3)
				$arResult["ERRORS"][] = 'ФИО курьера должно быть больше трех символов';
			else {
				$res = CIBlockElement::GetList(array("ID"=>"desc"), array("IBLOCK_ID"=>43), false, array("nTopCount"=>1), array("ID", "PROPERTY_ID_IN"));
				if($ob = $res->GetNextElement()) {
					$arFields = $ob->GetFields();
					$max_id = intval($arFields["PROPERTY_ID_IN_VALUE"]);
				}
				$max_id++;
				$max_id_5 = str_pad($max_id,5,'0',STR_PAD_LEFT);
				
				$el = new CIBlockElement;
				$PROP = array();
				$PROP[205] = $agent_id;
				$PROP[341] = $max_id_5;
				$arLoadProductArray = Array(
						"MODIFIED_BY"    => $u_id, 
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID"      => 43,
						"PROPERTY_VALUES"=> $PROP,
						"NAME" => $_POST['fio'],
						"ACTIVE"         => "Y");
						if ($PRODUCT_ID = $el->Add($arLoadProductArray))
							$arResult['MESSAGE'][] = 'Курьер №'.$max_id_5.' <b>'.$_POST['fio'].'</b> успешно добавлен в систему';
						else
							$arResult['ERRORS'][] = $el->LAST_ERROR;
			}
		}

		if (isset($_POST['applay'])) {
			foreach ($_POST['curr_id'] as $id) {
				if ($_POST['action'] == 1) {
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
					$arResult['MESSAGE'][] = 'Курьер №'.$_POST['id_in'][$id].' успешно активирован';
				}
				if ($_POST['action'] == 2) {
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
					$arResult['MESSAGE'][] = 'Курьер №'.$_POST['id_in'][$id].' успешно деактивирован';
				}
				if ($_POST['action'] == 3) {
					if (CIBlockElement::Delete($id))
						$arResult['MESSAGE'][] = 'Курьер №'.$_POST['id_in'][$id].' успешно удален';
				}
				/*
				if ($_POST['action'] == 4) {
					CIBlockElement::SetPropertyValuesEx($id, false, array(241=>floatval($_POST['summ_account'][$id] - $_POST['give_to_cur'][$id])));
					$arResult['MESSAGE'][] = 'Курьеру №'.$_POST['id_in'][$id].' выдано '.floatval($_POST['give_to_cur'][$id]).' руб.';
				}
				*/
			}
		}

		$arResult["LIST"] = TheListOfCouriers($agent_id);
		foreach ($arResult["LIST"] as $k => $c) {
			$packs = PackagesOfCourier($c["ID"],43);
			$arResult["LIST"][$k]["COUNT"] = count($packs);
			$arResult["LIST"][$k]["PACKS"] = array();
			foreach ($packs as $pp) {
				$arResult["LIST"][$k]["PACKS"][$pp["ID"]] = nZakaz($pp["PROPERTY_N_ZAKAZ_VALUE"]);
			}
		}
	}
	
	/***************************************************/
	if ($mode == 'courier') {	
			$id = intval($_REQUEST['id']);
			if ($id > 0) {
				if(isset($_POST['save'])) {
					$el = new CIBlockElement;
					$PRODUCT_ID = $el->Update($id, array("NAME"=>$_POST['fio'],"ACTIVE"=>$_POST['active'],"MODIFIED_BY"=> $u_id));
					CIBlockElement::SetPropertyValuesEx($id, false, array(241=>floatval($_POST['summ_now'] - $_POST['account'])));
					$arResult['MESSAGE'][] = 'Данные курьера №'.$_POST['id_in_c'].' успешно изменены';
				}
				
				if (isset($_POST['apply'])) {
					$summ_upload = 0;
					foreach ($_POST['operation'] as $p => $oper) {
						if (intval($oper) > 0) {
							if ($oper == '001') {
								CIBlockElement::SetPropertyValuesEx($p, false, array(203 => 44,229=>72,218=>61,267=>$_POST['date_delivery'][$p]));
								$add_to_period = AddElementToPeriod($p, 284, $u_id);
								$history_id = AddToHistory($p,$agent_id,$u_id,44,$_POST['fio'][$p]);
								$short_history_id = AddToShortHistory($p,$u_id,72,$_POST['fio'][$p]);
								$txt = '<p>'.$_POST['date_delivery'][$p].' заказ №'.$_POST['id_in'][$p].' доставлен курьером '.$_POST['cur_fio'].'.</p>';
								$el_0 = new CIBlockElement;
								$arLoadProductArray_0 = Array(
									"MODIFIED_BY"    => $u_id,
									"IBLOCK_ID"      => 50,
									"DETAIL_TEXT" => $txt,
									"DETAIL_TEXT_TYPE" => "html",
									"PROPERTY_VALUES"=> array(234=>2197189,235=>$_POST['shop'][$p],236=>152), "NAME" => "Заказ №".$_POST['id_in'][$p]." доставлен");
									$qw = $el_0->Add($arLoadProductArray_0);
								SendMessageMailNew($_POST['shop'][$p],$agent_array['id'],114,166,array(
									"ID" => $_POST['id_in'][$p],
									"DATE_RES"=> $_POST['date_delivery'][$p],
									"ID_MESS"=>$qw,
									"FROM"=> 'курьером '.$_POST['cur_fio'],
									"RESIVER"=>$_POST['fio'][$p]));
								$trans_id = AddTransaction(101,'',$agent_array['id'],$u_id,$_POST['date_delivery'][$p],$_POST['summ'][$p],'','Оплата от получателя',$p);
								$summ_upload = $summ_upload + $_POST['summ'][$p];
								$arResult['MESSAGE'][] = 'Заказ №'.$_POST['id_in'][$p].' успешно доставлен';
							}
							else {
								CIBlockElement::SetPropertyValuesEx($p, false, array(203=>40,229=>69,204=>false));
								$history_id = AddToHistory($p,$agent_id,$u_id,40,$arParams["STATUS"][$oper]);
								$short_history_id = AddToShortHistory($p,$u_id,69,$arParams["STATUS"][$oper]);
								$arResult['MESSAGE'][] = 'Заказ №'.$_POST['id_in'][$p].' перемещен на склад';
							}
						}
					}
					if ($summ_upload > 0) {
						$db_props = CIBlockElement::GetProperty(40, $agent_array['id'], array("sort" => "asc"), Array("CODE"=>"ACCOUNT"));
						if($ar_props = $db_props->Fetch())
							$summ_agent = $ar_props["VALUE"];
						else
							$summ_agent = 0;
						$summ_agent = $summ_agent + $summ_upload;
						CIBlockElement::SetPropertyValuesEx($agent_array['id'], false, array(219=>$summ_agent));
						$arResult['MESSAGE'][] = GetMessage("MESS_BALANCE", array("#SUMM#"=>CurrencyFormat($summ_agent,"RUU")));
					}
				}
				$cur_arr = TheListOfCouriers($agent_id,$id);
				$arResult["courier"] = $cur_arr[0];
				$arResult["TITLE"] = 'Курьер №'.$arResult["courier"]["PROPERTY_ID_IN_VALUE"];
				$arResult["COURIER"]["PACKS"] = PackagesOfCourier($id,43);
			}
			else $arResult["TITLE"] = 'Курьер не найден';
	}
	
	/***************************************************/
	if ($mode == 'print_orders') {
		$id = intval($_GET['id']);
		if ($id > 0) {
			$cur_arr = TheListOfCouriers($agent_id,$id);
			if (count($cur_arr) == 1) {
				$arResult['COURIER'] = $cur_arr[0];
				$arResult["TITLE"] = 'Список доставок на '.date('d.m.Y H:i:s');
				$arResult["AGENT_NAME"] = strlen($agent_array['legal_name'])? $agent_array['legal_name'] : $agent_array['name'];
				$arResult["COURIER"]["PACKS"] = PackagesOfCourier($id,43);
				$stats_full = array();
				foreach ($arParams['STATUS'] as $k => $v) {
					$stats_full[] = $k;
				}
				$arResult["STATUS"] = array();
				$db_enum_list = CIBlockProperty::GetPropertyEnum(203, array("sort"=>"asc"), array("IBLOCK_ID"=>42));
				while ($ar_enum_list = $db_enum_list->GetNext()) {
					if (in_array($ar_enum_list["ID"],$stats_full)) {
						$arResult["STATUS"][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
					}
				}
			}
			else $arResult['COURIER'] = false;
		}
	}
}


$arResult["PATH"] = $this->GetPath();
$this->IncludeComponentTemplate($componentPage);
?>