<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule('iblock');

if (isset($_GET['file_id'])) {
	/*$arGroups = $USER->GetUserGroupArray();
	echo '<pre>';
	print_r($arGroups);
	echo '</pre>'; */
	$f_id = intval($_GET['file_id']);
	if ($f_id > 0) {
		
		/*
		
		$f_array = CFile::MakeFileArray($f_id);
	
       	header("Cache-Control: public");
         header("Content-Description: File Transfer");
         header("Content-Disposition: attachment; filename=".$f_array["name"]);
         header("Content-Type: ".$f_array["type"]);
         header("Content-Transfer-Encoding: binary");
		 header("Content-Length: ".$f_array["size"]); 
		  readfile($f_array["tmp_name"]); 
		


		 header("Pragma: public"); // required
   		 header("Expires: 0");
   		 header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
   		 header("Cache-Control: private",false); // required for certain browsers
   		 header("Content-Type: ".$f_array["type"]);
   		 header("Content-Disposition: attachment; filename=\"".$f_array["name"]."\";" );
   		 header("Content-Transfer-Encoding: binary");
   		 header("Content-Length: ".$f_array["size"]);
   		 ob_clean();
   		 flush();
   		 readfile($f_array["tmp_name"]); 
	
*/

	}
}

$id = intval($_GET['id']);

$mode = $_GET['mode'];
if ($mode != 'detail')
	$mode = '';

$arResult["ERRORS"] = array();


$filter = array("IBLOCK_ID"=>52,"ACTIVE"=>"Y");

if (strlen($mode) && ($id > 0)) { //список
	$filter["ID"] = $id;
}


$res = CIBlockElement::GetList(array("created"=>"DESC"), $filter, false, false, array("ID","NAME","PROPERTY_FILE","PROPERTY_VERSION","DATE_CREATE","DETAIL_TEXT","IBLOCK_SECTION_ID"));
while ($ob = $res->GetNextElement()) {
	$a = $ob->GetFields();
	$a["DATE_CREATE"] = ConvertDateTime($a["DATE_CREATE"], "DD.MM.YYYY");
	 $a["FILE"] = CFile::GetPath($a["PROPERTY_FILE_VALUE"]);
	if ($a["IBLOCK_SECTION_ID"] == 2368)
		$arFields[] = $a;
	if ($a["IBLOCK_SECTION_ID"] == 2369)
		$arFields2[] = $a;
	$arFieldsAll[] = $a;
}

if (strlen($mode)) {
	if (count($arFieldsAll) == 1) {
	$arResult["ELEMENT"] = $arFieldsAll[0];
	if ($arResult["ELEMENT"]["IBLOCK_SECTION_ID"] == 2368)
		$arResult["TITLE"] = "Версия ПО \"Служба доставки\" ".$arResult["ELEMENT"]["NAME"];
	else $arResult["TITLE"] = $arResult["ELEMENT"]["NAME"];
	}
	else {
		$arResult["ELEMENT"] = false;
		$arResult["TITLE"] = "Элемент не найден";
	}
}

$arResult["LIST"] = array();
$arResult["LIST"] = $arFields;
$arResult["LIST2"] = $arFields2;

$this->IncludeComponentTemplate();
