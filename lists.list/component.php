<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLL_MODULE_NOT_INSTALLED"));
	return;
}
$IBLOCK_ID = intval($arParams["~IBLOCK_ID"]);
if(isset($_GET["list_section_id"]))
	$section_id = intval($_GET["list_section_id"]);
else
	$section_id = intval($arParams["~SECTION_ID"]);

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	$IBLOCK_ID,
	$arParams["~SOCNET_GROUP_ID"]
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CC_BLL_WRONG_IBLOCK_TYPE"));
		return;
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CC_BLL_WRONG_IBLOCK"));
		return;
	case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
		ShowError(GetMessage("CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED"));
		return;
	default:
		ShowError(GetMessage("CC_BLL_UNKNOWN_ERROR"));
		return;
	}
}
elseif(
	$lists_perm < CListPermissions::CAN_READ
	&& !(
		CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "element_read")
		|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $section_id, "section_element_bind")
	)
)
{
	ShowError(GetMessage("CC_BLL_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] = ($lists_perm >= CListPermissions::IS_ADMIN)
			|| CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit");
$arResult["IBLOCK_PERM"] = $lists_perm;
$arResult["USER_GROUPS"] = $USER->GetUserGroupArray();
$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));
$arResult["~IBLOCK"] = $arIBlock;
$arResult["IBLOCK"] = htmlspecialcharsex($arIBlock);
$arResult["IBLOCK_ID"] = $arIBlock["ID"];


/****************определение агента****************/
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$u_id = $USER->GetID();

$agent_array = GetCurrentAgent($u_id);
$arResult['agent_array'] = $agent_array;
$agent_id = $agent_array['id'];

$arResult["ShowOnlyMyProducts"] = true;
if  ($agent_array['type'] == 51)
{
	$arResult["ShowOnlyMyProducts"] = false;
}

/****************фиксаци€ остатков*****************/
if (isset($_POST['fix'])) {
	if ($_POST['rand']==$_SESSION[$_POST['key_session']]) {
		$_POST = array();
		$arResult['ERRORS'][] = GetMessage("DOUBLE_ERROR");
	}
	else {
		$_SESSION[$_POST['key_session']] = $_POST['rand'];
		$goods = GetGoodsOfShop($_POST['shop']);
		if (count($goods) > 0) {
			$max = GetMaxIDIN(72,5,true,388,$_POST['shop']);
			$el = new CIBlockElement;
			$arLoadProductArray = array(
				"MODIFIED_BY"    => $USER->GetID(), 
				"IBLOCK_SECTION_ID" => false,  
				"IBLOCK_ID"      => 72,
				"PROPERTY_VALUES"=> array(
					388 => $_POST['shop'],
					389 => $max
					),
				"NAME"           => GetMessage("FIX_TITLE",array("#ID#"=>$max)),
				"ACTIVE"         => "Y"
			);
			if($fix_id = $el->Add($arLoadProductArray)) {
				$goods = GetGoodsOfShop($_POST['shop']);
				foreach ($goods as $g) {
					$el_2 = new CIBlockElement;
					$arLoadProductArray_2 = array(
						"MODIFIED_BY"    => $USER->GetID(), 
						"IBLOCK_SECTION_ID" => false,  
						"IBLOCK_ID"      => 73,
						"PROPERTY_VALUES"=> array(
							385 => $fix_id,
							386 => $g["ID"],
							387 => $g["PROPERTY_299_VALUE"]
						),
						"NAME" => htmlspecialcharsBack($g['NAME']),
						"ACTIVE" => "Y"
					);
					$fix_id_el = $el_2->Add($arLoadProductArray_2);
				}
				$arResult["MESSAGE"][] = GetMessage("FIX_SUCCESS");
			}
			else {
				$arResult['ERRORS'][] = $el->LAST_ERROR;
			}
		}
	}
}


if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
else
	$arParams["SOCNET_GROUP_ID"] = "";

$arResult["GRID_ID"] = "lists_list_elements_".$arResult["IBLOCK_ID"];

$arResult["ANY_SECTION"] = isset($_GET["list_section_id"]) && strlen($_GET["list_section_id"]) == 0;
$arResult["SECTION"] = false;
$arResult["SECTION_ID"] = false;
$arResult["PARENT_SECTION_ID"] = false;
$arResult["SECTIONS"] = array();
$arResult["LIST_SECTIONS"] = array("0" => GetMessage("CC_BLL_UPPER_LEVEL"));
$arResult["~LIST_SECTIONS"] = array("0" => GetMessage("CC_BLL_UPPER_LEVEL"));
$arResult["SECTION_PATH"] = array();

$rsSections = CIBlockSection::GetList(
	array("left_margin" => "asc"),
	array(
		"IBLOCK_ID" => $arIBlock["ID"],
		"GLOBAL_ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => ($lists_perm >= CListPermissions::CAN_READ? "N": "Y"),
	)
);
while($arSection = $rsSections->GetNext())
{
	if($section_id && !$arResult["SECTION"])
	{
		while(count($arResult["SECTION_PATH"]) && $arSection["DEPTH_LEVEL"] <= $arResult["SECTION_PATH"][count($arResult["SECTION_PATH"])-1]["DEPTH_LEVEL"])
			array_pop($arResult["SECTION_PATH"]);

		if(!count($arResult["SECTION_PATH"])|| $arSection["DEPTH_LEVEL"] > $arResult["SECTION_PATH"][count($arResult["SECTION_PATH"])-1]["DEPTH_LEVEL"])
			array_push($arResult["SECTION_PATH"], $arSection);
	}

	if($arSection["ID"] == $section_id)
	{
		$arResult["SECTION"] = $arSection;
		$arResult["SECTION_ID"] = $arSection["ID"];
		$arResult["PARENT_SECTION_ID"] = $arSection["IBLOCK_SECTION_ID"];
	}

	$arResult["SECTIONS"][$arSection["ID"]] = array(
		"ID" => $arSection["ID"],
		"NAME"=>$arSection["NAME"],
		"LIST_URL"=>str_replace(
			array("#list_id#", "#section_id#", "#group_id#"),
			array($arSection["IBLOCK_ID"], $arSection["ID"], $arParams["SOCNET_GROUP_ID"]),
			$arParams['LIST_URL']
		),
	);

	$arResult["LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];
	$arResult["~LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["~NAME"];
}

foreach($arResult["SECTION_PATH"] as $i => $arSection)
{
	$arResult["SECTION_PATH"][$i] = array(
		"NAME" => htmlspecialcharsex($arSection["NAME"]),
		"URL" => str_replace(
			array("#list_id#", "#section_id#", "#group_id#"),
			array($arIBlock["ID"], intval($arSection["ID"]), $arParams["SOCNET_GROUP_ID"]),
			$arParams["LIST_URL"]
		),
	);
}

$arResult["~LISTS_URL"] = str_replace(
	array("#group_id#"),
	array($arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialcharsbx($arResult["~LISTS_URL"]);

$arResult["~LIST_EDIT_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_EDIT_URL"]
);
$arResult["LIST_EDIT_URL"] = htmlspecialcharsbx($arResult["~LIST_EDIT_URL"]);

$arResult["~LIST_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
);
$arResult["LIST_URL"] = htmlspecialcharsbx($arResult["~LIST_URL"]);

$arResult["~LIST_SECTION_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_SECTIONS_URL"]
);
$arResult["LIST_SECTION_URL"] = htmlspecialcharsbx($arResult["~LIST_SECTION_URL"]);

$arResult["~LIST_PARENT_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["PARENT_SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
);
$arResult["LIST_PARENT_URL"] = htmlspecialcharsbx($arResult["~LIST_PARENT_URL"]);

$arResult["~BIZPROC_WORKFLOW_ADMIN_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~BIZPROC_WORKFLOW_ADMIN_URL"]
);
$arResult["BIZPROC_WORKFLOW_ADMIN_URL"] = htmlspecialcharsbx($arResult["~BIZPROC_WORKFLOW_ADMIN_URL"]);

$obList = new CList($arIBlock["ID"]);

//Form submitted
if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
	&& (
		isset($_POST["action_button_".$arResult["GRID_ID"]])
	)
	
)
{
	$obSection = new CIBlockSection;
	$obElement = new CIBlockElement;

	/*Build filter*/
	$arFilter = array(
		"IBLOCK_ID" => $arIBlock["ID"],
		"CHECK_PERMISSIONS" => ($arParams["CAN_EDIT"] || $arParams["SOCNET_GROUP_ID"]? "N": "Y"), //This cancels iblock permissions for trusted users
	);

	if($_POST["action_all_rows_".$arResult["GRID_ID"]] == "Y")
	{
		if(!$arResult["ANY_SECTION"])
			$arFilter["SECTION_ID"] = $arResult["SECTION_ID"];
	}
	else
	{
		$arFilter["=ID"] = $_POST["ID"];
	}

	/*Take action*/
	
	
	if($_POST["action_button_".$arResult["GRID_ID"]]  == "section")
	{
		if(
			$lists_perm >= CListPermissions::CAN_WRITE
			|| CIBlockSectionRights::UserHasRightTo($arIBlock["ID"], $_POST["section_to_move"], "section_element_bind")
		)
		{
			$rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
			while($arElement = $rsElements->Fetch())
				$obElement->SetElementSection(
					$arElement["ID"],
					array($_POST["section_to_move"]),
					false,
					($arIBlock["RIGHTS_MODE"]=="E"? $arIBlock["ID"]: 0)
				);
		}
	}
	elseif($_POST["action_button_".$arResult["GRID_ID"]]  == "inpackage") {
		$goods = array();
		if(is_array($_POST["ID"])) {
			foreach ($_POST["ID"] as $good) {
				$db_props = CIBlockElement::GetProperty(62, $good, array("sort" => "asc"), Array("ID"=>295));
				if($ar_props = $db_props->Fetch())
					$shop = $ar_props["VALUE"];
				$res = CIBlockElement::GetByID($good);
				if($ar_res = $res->GetNext()) {
					$goods[$shop]['NAMES'][$good] = $name_of_good = $ar_res['NAME'];
				}
				$db_props = CIBlockElement::GetProperty(62, $good, array("sort" => "asc"), Array("ID"=>299));
				if($ar_props = $db_props->Fetch())
					$count_of_good = intval($ar_props["VALUE"]);
				else $count_of_good = 0;
				
				if ($count_of_good > 0) {
					$db_props = CIBlockElement::GetProperty(62, $good, array("sort" => "asc"), Array("ID"=>296));
					if($ar_props = $db_props->Fetch())
						$goods[$shop]['WEIGHT'][$good] = $ar_props["VALUE"];
					else $goods[$shop]['WEIGHT'][$good] = 0;
					$db_props = CIBlockElement::GetProperty(62, $good, array("sort" => "asc"), Array("ID"=>297));
					if($ar_props = $db_props->Fetch())
						$goods[$shop]['COST'][$good] = $ar_props["VALUE"];
					else $goods[$shop]['COST'][$good] = 0;
					$db_props = CIBlockElement::GetProperty(62, $good, array("sort" => "asc"), Array("ID"=>294));
					if($ar_props = $db_props->Fetch())
						$goods[$shop]['ARTICLE'][$good] = $ar_props["VALUE"];
					else $goods[$shop]['ARTICLE'][$good] = '';
					
					$goods[$shop]['GOODS'][] = $good;
				}
				else {
					ShowError(GetMessage("COUNT_0",array('#NAME#'=>$name_of_good)));
				}
			}
			
			foreach ($goods as $shop => $v) {
				//ищем открытый заказ магазина
				$res = CIBlockElement::GetList(array("ID"=>"desc"), array("IBLOCK_ID"=>42, "PROPERTY_213"=>$shop,"PROPERTY_203"=>116,"CREATED_USER_ID"=>$u_id), false, array("nTopCount"=>1), array("ID"));
				if($ob = $res->GetNextElement()) {
					$arFields = $ob->GetFields();
					$ord_id = $arFields["ID"];
					$goods_in_order = array();
					$res2 = CIBlockElement::GetList(array("ID"=>"desc"), array("IBLOCK_ID"=>63, "PROPERTY_302"=>$ord_id), false, false, array("ID","PROPERTY_300"));
					while ($ob2 = $res2->GetNextElement()) {
						$arFields2 = $ob2->GetFields();
						$g_id = $arFields2["PROPERTY_300_VALUE"];
						if (in_array($g_id,$v['GOODS'])) {
							$goods_in_order[] = $g_id;
						}
					}
					
					$goods_in_order_names = array();
					foreach ($v['GOODS'] as $g) {
						if (!in_array($g, $goods_in_order)) {
							$el = new CIBlockElement;
							$PROP = array();
							$PROP[300] = $g;
							$PROP[301] = 1;
							$PROP[302] = $ord_id;
							$PROP[360] = $v['COST'][$g];
							$PROP[361] = $v['ARTICLE'][$g];
							$PROP[362] = $v['WEIGHT'][$g];
							$arLoadProductArray = Array(
								"MODIFIED_BY"    => $u_id,
								"IBLOCK_SECTION_ID" => false,
								"PROPERTY_VALUES" => $PROP,
								"IBLOCK_ID"      => 63,
								"NAME" => $v['NAMES'][$g],
								"ACTIVE"         => "Y");
							$GOOD_ID = $el->Add($arLoadProductArray);
						}
					}
					//пересчитываем стоимость и вес
					RecalculationWeightAndCost($ord_id);
					
				}
				else {
					$max_id_5 = GetMaxIDIN(42,5,true,213,$shop);
					$name_of_order = MakeOrderId($shop, $max_id_5);
					
					$el = new CIBlockElement;
					$PROP = array();
					$PROP[306] = $max_id_5;
					$PROP[213] = $shop;
					$PROP[402] = $name_of_order;
					$PROP[198] = $PROP[199] = $PROP[197] = $PROP[250] = $PROP[231] = $PROP[347] = $PROP[247] = $PROP[248] = $PROP[249] = $PROP[240] = $PROP[266] = 0;
					$PROP[203] = 116;
					$PROP[229] = 117;
					$PROP[232] = 1;
					$PROP[218] = 60;
					$PROP[257] = 91;
					$PROP[346] = GetMessage("CASH");
					$PROP[499] = $shop;
					
					$arLoadProductArray = Array(
						"MODIFIED_BY"    => $u_id,
						"IBLOCK_SECTION_ID" => false,
						"PROPERTY_VALUES" => $PROP,
						"IBLOCK_ID"      => 42,
						"NAME" => GetMessage("ORDER_NAME",array("#ID#"=>$name_of_order)),
						"ACTIVE"         => "Y");
					$PACKAGE_ID = $el->Add($arLoadProductArray);
					unset($arLoadProductArray);
					
					foreach ($v['GOODS'] as $g) {
						$el = new CIBlockElement;
						$PROP = array();
						$PROP[300] = $g;
						$PROP[301] = 1;
						$PROP[302] = $PACKAGE_ID;
						$PROP[360] = $v['COST'][$g];
						$PROP[361] = $v['ARTICLE'][$g];
						$PROP[362] = $v['WEIGHT'][$g];
						$arLoadProductArray = Array(
							"MODIFIED_BY"    => $u_id,
							"IBLOCK_SECTION_ID" => false,
							"PROPERTY_VALUES" => $PROP,
							"IBLOCK_ID"      => 63,
							"NAME" => $goods[$shop]['NAMES'][$g],
							"ACTIVE"         => "Y");
						$GOOD_ID = $el->Add($arLoadProductArray);
					}
					//расчитываем стоимость, вес и стоимость доставки
					RecalculationWeightAndCost($PACKAGE_ID);
					
					//добавление истории
					$history_id = AddToHistory($PACKAGE_ID,$agent_id,$USER->GetID(),116,'');
					$short_history_id = AddToShortHistory($PACKAGE_ID,$USER->GetID(),117);
				}
			}
		}
	}
	elseif($_POST["action_button_".$arResult["GRID_ID"]] == "delete" && isset($_POST["ID"]) && is_array($_POST["ID"]))
	{
		$rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
		while($arElement = $rsElements->Fetch())
		{
			if(
				$lists_perm >= CListPermissions::CAN_WRITE
				|| CIBlockElementRights::UserHasRightTo($arIBlock["ID"], $arElement["ID"], "element_delete")
			)
				$obElement->Delete($arElement["ID"]);
		}
	}

	if(!isset($_POST["AJAX_CALL"]))
		LocalRedirect($arResult["~LIST_URL"]);
}
$grid_options = new CGridOptions($arResult["GRID_ID"]);
$grid_columns = $grid_options->GetVisibleColumns();
$grid_sort = $grid_options->GetSorting(array("sort"=>array("name"=>"asc")));

if($arResult["IBLOCK"]["BIZPROC"]=="Y" && CModule::IncludeModule('bizproc'))
{
	$arDocumentTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$arResult["IBLOCK_ID"]));
	$arResult["BIZPROC"] = "Y";
}
else
{
	$arDocumentTemplates = array();
	$arResult["BIZPROC"] = "N";
}

/* FIELDS */
$arResult["ELEMENTS_HEADERS"] = array();
$arSelect = array("ID", "IBLOCK_ID");
$arProperties = array();

$arResult["FIELDS"] = $arListFields = $obList->GetFields();
foreach($arListFields as $FIELD_ID => $arField)
{
	if(!count($grid_columns) || in_array($FIELD_ID, $grid_columns))
	{
		if(substr($FIELD_ID, 0, 9) == "PROPERTY_")
			$arProperties[] = $FIELD_ID;
		else
			$arSelect[] = $FIELD_ID;
	}

	if($FIELD_ID == "CREATED_BY")
		$arSelect[] = "CREATED_USER_NAME";

	if($FIELD_ID == "MODIFIED_BY")
		$arSelect[] = "USER_NAME";

	$arResult["ELEMENTS_HEADERS"][] = array(
		"id" => $FIELD_ID,
		"name" => htmlspecialcharsex($arField["NAME"]),
		"default" => true,
		"sort" => $arField["MULTIPLE"]=="Y"? "": $FIELD_ID,
	);
}

//скрываем необходимые пол€ от »ћ
//if ($arResult["ShowOnlyMyProducts"]) {
	unset($arListFields["PROPERTY_295"]);
//}

if(!count($grid_columns) || in_array("IBLOCK_SECTION_ID", $grid_columns))
{
	$arSelect[] = "IBLOCK_SECTION_ID";
}
$arResult["ELEMENTS_HEADERS"][] = array(
	"id" => "IBLOCK_SECTION_ID",
	"name" => GetMessage("CC_BLL_COLUMN_SECTION"),
	"default" => true,
	"sort" => false,
);

if(count($arDocumentTemplates) > 0)
{
	$arSelect[] = "CREATED_BY";
	$arResult["ELEMENTS_HEADERS"][] = array(
		"id" => "BIZPROC",
		"name" => GetMessage("CC_BLL_COLUMN_BIZPROC"),
		"default" => true,
		"sort" => false,
	);
}

//скрываем колонки от »ћ
if ($arResult["ShowOnlyMyProducts"]) {
	$delete_columns = array("PROPERTY_295","IBLOCK_SECTION_ID");
	$delete_index = array();
	foreach ($arResult["ELEMENTS_HEADERS"] as $k => $v) {
		if (in_array($v["id"],$delete_columns)) $delete_index[] = $k;
	}
	foreach ($delete_index as $k) {
		unset($arResult["ELEMENTS_HEADERS"][$k]);
	}
}

/* FILTER */
$sections = array('' => GetMessage("CC_BLL_ANY"));
foreach($arResult["~LIST_SECTIONS"] as $id => $name)
	$sections[$id] = $name;
	$arResult["FILTER"] = array();
/*
$arResult["FILTER"] = array(
	array(
		"id" => "list_section_id",
		"name" => GetMessage("CC_BLL_SECTION"),
		"type" => "list",
		"items" => $sections,
		"filtered" => $arResult["SECTION_ID"] !== false,
		"value" => $arResult["SECTION_ID"],
	),
);
*/
$i = 1;

$arFilterable = array();
$arCustomFilter = array();
$arDateFilter = array();

foreach($arListFields as $FIELD_ID => $arField)
{
	if(
		$arField["TYPE"] == "ACTIVE_FROM"
		|| $arField["TYPE"] == "ACTIVE_TO"
	)
	{
		$arResult["FILTER"][$i] = array(
			"id" => "DATE_".$FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "date",
		);
		$arFilterable["DATE_".$FIELD_ID] = "";
		$arDateFilter["DATE_".$FIELD_ID] = true;
	}
	elseif(
		$arField["TYPE"] == "DATE_CREATE"
		|| $arField["TYPE"] == "TIMESTAMP_X"
	)
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "date",
		);
		$arFilterable[$FIELD_ID] = "";
		$arDateFilter[$FIELD_ID] = true;
	}
	elseif($arField["TYPE"] == "PREVIEW_PICTURE" || $arField["TYPE"] == "DETAIL_PICTURE")
	{
	}
	elseif(is_array($arField["PROPERTY_USER_TYPE"]) && array_key_exists("GetPublicFilterHTML", $arField["PROPERTY_USER_TYPE"]))
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "custom",
			"enable_settings" => false,
			"value" => call_user_func_array($arField["PROPERTY_USER_TYPE"]["GetPublicFilterHTML"], array(
				$arField,
				array(
					"VALUE"=>$FIELD_ID,
					"FORM_NAME"=>"filter_".$arResult["GRID_ID"],
				),
			)),
		);
		$arFilterable[$FIELD_ID] = "";
		if(array_key_exists("AddFilterFields", $arField["PROPERTY_USER_TYPE"]))
			$arCustomFilter[$FIELD_ID] = array(
				"callback" => $arField["PROPERTY_USER_TYPE"]["AddFilterFields"],
				"filter" => &$arResult["FILTER"][$i],
			);
	}
	elseif($arField["TYPE"] == "F")
	{
	}
	elseif($arField["TYPE"] == "SORT" || $arField["TYPE"] == "N")
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "number",
		);
		$arFilterable[$FIELD_ID] = "";
	}
	elseif($arField["TYPE"] == "G")
	{
		$items = array();
		$prop_secs = CIBlockSection::GetList(array("left_margin" => "asc"), array("IBLOCK_ID" => $arField["LINK_IBLOCK_ID"]));
		while($ar_sec = $prop_secs->Fetch())
			$items[$ar_sec["ID"]] = str_repeat(". ", $ar_sec["DEPTH_LEVEL"]-1).$ar_sec["NAME"];

		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "list",
			"items" => $items,
			"params" => array("size"=>5, "multiple"=>"multiple"),
			"valign" => "top",
		);
		$arFilterable[$FIELD_ID] = "";
	}
	elseif($arField["TYPE"] == "E")
	{
		//Should be handled in template
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "E",
			"value" => $arField,
		);
		$arFilterable[$FIELD_ID] = "";
	}
	elseif($arField["TYPE"] == "L")
	{
		$items = array();
		$prop_enums = CIBlockProperty::GetPropertyEnum($arField["ID"]);
		while($ar_enum = $prop_enums->Fetch())
			$items[$ar_enum["ID"]] = $ar_enum["VALUE"];

		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "list",
			"items" => $items,
			"params" => array("size"=>5, "multiple"=>"multiple"),
			"valign" => "top",
		);
		$arFilterable[$FIELD_ID] = "";
	}
	elseif(in_array($arField["TYPE"], array("S", "S:HTML", "NAME", "DETAIL_TEXT", "PREVIEW_TEXT")))
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
		);
		$arFilterable[$FIELD_ID] = "?";
	}
	else
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
		);
		$arFilterable[$FIELD_ID] = "";
	}

	$i++;
}

$arFilter = array();
$grid_filter = $grid_options->GetFilter($arResult["FILTER"]);
foreach($grid_filter as $key => $value)
{
	if(substr($key, -5) == "_from")
	{
		$op = ">=";
		$new_key = substr($key, 0, -5);
	}
	elseif(substr($key, -3) == "_to")
	{
		$op = "<=";
		$new_key = substr($key, 0, -3);
		if(array_key_exists($new_key, $arDateFilter))
		{
			if(!preg_match("/\\d\\d:\\d\\d:\\d\\d\$/", $value))
				$value .= " 23:59:59";
		}
	}
	else
	{
		$op = "";
		$new_key = $key;
	}

	if(array_key_exists($new_key, $arFilterable))
	{
		if($op == "")
			$op = $arFilterable[$new_key];
		$arFilter[$op.$new_key] = $value;
	}
}

foreach($arCustomFilter as $FIELD_ID => $arCallback)
{
	call_user_func_array($arCallback["callback"], array(
		$arListFields[$FIELD_ID],
		array("VALUE" => $FIELD_ID),
		&$arFilter,
		&$filtered,
	));
	$arCallback["filter"]["filtered"] = $filtered;
}

$arFilter["IBLOCK_ID"] = $arIBlock["ID"];
$arFilter["CHECK_PERMISSIONS"] = ($lists_perm >= CListPermissions::CAN_READ? "N": "Y");
if(!$arResult["ANY_SECTION"])
	$arFilter["SECTION_ID"] = $arResult["SECTION_ID"];
	
if ($arResult["ShowOnlyMyProducts"]) {
	$arFilter["PROPERTY_295"] = $agent_array['id'];
}

if (!strlen($arFilter['>=PROPERTY_299'])) {
	if ($_GET['in_presence'] == 'Y') {
		$arFilter['>PROPERTY_299'] = 0;
	}
}

$rsElements = CIBlockElement::GetList(
	$grid_sort["sort"], $arFilter, false, $grid_options->GetNavParams(), $arSelect
);

if ($arResult["BIZPROC"] == "Y")
{
	$arUserGroupsForBP = CUser::GetUserGroup($GLOBALS["USER"]->GetID());
	$arDocumentStatesForBP = CBPWorkflowTemplateLoader::GetDocumentTypeStates(array("iblock", "CIBlockDocument", "iblock_".$arIBlock["ID"]));
}
else
{
	$arUserGroupsForBP = array();
}

$arResult["ELEMENTS_CAN_DELETE"] = array();
$arResult["ELEMENTS_CAN_MOVE"] = array();
$arResult["ELEMENTS_ROWS"] = array();
while($obElement = $rsElements->GetNextElement())
{
	$data = $obElement->GetFields();

	$aCols = array();

	if(!empty($arProperties))
	{
		$rsProp = CIBlockElement::GetProperty($arIBlock["ID"], $data["ID"]);
		while($arProp = $rsProp->Fetch())
		{
			$FIELD_ID = "PROPERTY_".$arProp["ID"];
			if(in_array($FIELD_ID, $arProperties))
			{
				$arField = $arResult["FIELDS"][$FIELD_ID];

				if(!isset($data[$FIELD_ID]))
					$data[$FIELD_ID] = array();

				if(is_array($arField["PROPERTY_USER_TYPE"]) && is_array($arField["PROPERTY_USER_TYPE"]["GetPublicViewHTML"]))
				{
					$data[$FIELD_ID][] = call_user_func_array($arField["PROPERTY_USER_TYPE"]["GetPublicViewHTML"], array(
						$arField,
						array("VALUE" => $arProp["VALUE"]),
						array(),
					));
				}
				elseif($arField["PROPERTY_TYPE"] == "L")
				{
					$data[$FIELD_ID][] = htmlspecialcharsex($arProp["VALUE_ENUM"]);
				}
				else
				{
					$data[$FIELD_ID][] = htmlspecialcharsex($arProp["VALUE"]);
				}
			}
		}
	}

	if(isset($data["CREATED_BY"]))
		$data["CREATED_BY"] = "[".$data["CREATED_BY"]."] ".$data["CREATED_USER_NAME"];

	if(isset($data["MODIFIED_BY"]))
		$data["MODIFIED_BY"] = "[".$data["MODIFIED_BY"]."] ".$data["USER_NAME"];

	if(isset($data["ACTIVE_FROM"]))
		$data['ACTIVE_FROM'] = FormatDateFromDB($data['ACTIVE_FROM']);
	if(isset($data["ACTIVE_TO"]))
		$data['ACTIVE_TO'] = FormatDateFromDB($data['ACTIVE_TO']);
	if(isset($data["DATE_CREATE"]))
		$data['DATE_CREATE'] = FormatDateFromDB($data['DATE_CREATE']);
	if(isset($data["TIMESTAMP_X"]))
		$data['TIMESTAMP_X'] = FormatDateFromDB($data['TIMESTAMP_X']);


	$arUserGroupsForBPTmp = $arUserGroupsForBP;
	if ($GLOBALS["USER"]->GetID() == $data["CREATED_BY"])
		$arUserGroupsForBPTmp[] = "Author";

	$arBPStart = array();
	foreach($arDocumentTemplates as $arWorkflowTemplate)
	{
		if (CBPDocument::CanUserOperateDocument(
				CBPCanUserOperateOperation::StartWorkflow,
				$GLOBALS["USER"]->GetID(),
				array("iblock", "CIBlockDocument", intval($data["~ID"])),
				array("IBlockId" => $arIBlock["ID"], "AllUserGroups" => $arUserGroupsForBPTmp, "DocumentStates" => $arDocumentStatesForBP, "WorkflowId" => $arWorkflowTemplate["ID"])
			))
		{
			$url = CHTTP::urlAddParams(str_replace(
				array("#list_id#", "#section_id#", "#element_id#", "#workflow_template_id#", "#group_id#"),
				array($arIBlock["ID"], intval($arResult["SECTION_ID"]), intval($data["~ID"]), $arWorkflowTemplate["ID"], $arParams["SOCNET_GROUP_ID"]),
				$arParams["BIZPROC_WORKFLOW_START_URL"]
			), array("workflow_template_id" => $arWorkflowTemplate["ID"]));
			$url .= ((strpos($url, "?") === false) ? "?" : "&").bitrix_sessid_get();
			$arBPStart[] = array(
				"TEXT" => $arWorkflowTemplate["NAME"],
				"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
			);
		}
	}

	$url = str_replace(
		array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
		array($arIBlock["ID"], intval($arResult["SECTION_ID"]), intval($data["~ID"]), $arParams["SOCNET_GROUP_ID"]),
		$arParams["LIST_ELEMENT_URL"]
	);
	if($arResult["ANY_SECTION"])
		$url = CHTTP::urlAddParams($url, array("list_section_id" => ""));

	$aActions = array();

	if(
		$lists_perm >= CListPermissions::CAN_WRITE
		|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $data["~ID"], "element_edit")
	)
	{
		$aActions[] = array(
			"ICONCLASS" => "edit",
			"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_EDIT"),
			"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
			"DEFAULT" => true,
		);
		$arResult["ELEMENTS_CAN_MOVE"][] = $data["ID"];
	}
	else
	{
		$aActions[] = array(
			"ICONCLASS" => "view",
			"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_VIEW"),
			"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
			"DEFAULT" => true,
		);
	}

	if(
		count($arBPStart)
		&& (
			$lists_perm >= CListPermissions::CAN_BIZPROC
			|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $data["~ID"], "element_bizproc_start")
		)
	)
		$aActions[] = array(
			"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_START_BP"),
			"MENU" => $arBPStart,
		);

	if(
		$lists_perm >= CListPermissions::CAN_WRITE
		|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $data["~ID"], "element_delete")
	)
	{
		$aActions[] = array("SEPARATOR" => true);
		$aActions[] = array(
			"ICONCLASS" => "delete",
			"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_DELETE"),
			"ONCLICK" => "bxGrid_".$arResult["GRID_ID"].".DeleteItem('".$data["ID"]."', '".GetMessage("CC_BLL_ELEMENT_ACTION_MENU_DELETE_CONF")."')",
		);
		$arResult["ELEMENTS_CAN_DELETE"][] = $data["ID"];
	}

	$arResult["ELEMENTS_ROWS"][] = array(
		"id" => $data["ID"],
		"data" => $data,
		"actions" => $aActions,
		"columns" => $aCols,
	);
}

$rsElements->bShowAll = true;
$arResult["NAV_OBJECT"] = $rsElements;
$arResult["SORT"] = $grid_sort["sort"];

$arResult["LIST_NEW_ELEMENT_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
	array($arIBlock["ID"], intval($arResult["SECTION_ID"]), 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["LIST_ELEMENT_URL"]
);
if($arResult["ANY_SECTION"])
	$arResult["LIST_NEW_ELEMENT_URL"] = CHTTP::urlAddParams($arResult["LIST_NEW_ELEMENT_URL"], array("list_section_id" => ""));

$APPLICATION->SetTitle(GetMessage("CC_BLL_TITLE", array("#NAME#" => $arResult["IBLOCK"]["NAME"])));

$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
), array("list_section_id" => "")));

foreach($arResult["SECTION_PATH"] as $arPath)
{
	$APPLICATION->AddChainItem($arPath["NAME"], $arPath["URL"]);
}

$u_id = $USER->GetID();
$agent_array = GetCurrentAgent($u_id);
if ($agent_array['type'] == 52) {
	$arResult["TITLE"] = GetMessage("TTL_SHOP");
	$templ = 'shop_template';
}
else {
	$res = CIBlockElement::GetByID($_SESSION['CURRNET_SHOP']);
	if($ar_res = $res->GetNext())
	$arResult["TITLE"] = GetMessage("TTL",array("#NAME#"=>$ar_res['NAME']));
	$templ = 'template';
}

if ($_GET['print'] == 'Y')
	$componentPage = 'template_print';
else $componentPage = $templ;



$this->IncludeComponentTemplate($componentPage);
?>