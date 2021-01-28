<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/black_mist/delivery.packages/functions.php');

$modes = array("list","create","add_city","delete_city","edit","edit_structure");
if (in_array($_GET['state'],$modes)) $mode = $_GET['state'];
else $mode = 'list';


$time = date('Y-m-d H:i:s');
$arResult['global_types'] = array();
$del_string = $arParams["TYPES_DELIVERY"];
$del_array = explode(',',$del_string);
foreach ($del_array as $v)
	$arResult['global_types'][] = trim($v);

$arResult['size_types'] = sizeof($arResult['global_types']);

$name0 = iconv('windows-1251','utf-8','�����������');
$name1 = iconv('windows-1251','utf-8','����������');
$name2 = iconv('windows-1251','utf-8','����������');
$name3 = iconv('windows-1251','utf-8','������');
$name4 = iconv('windows-1251','utf-8','�����');
$name5 = iconv('windows-1251','utf-8','���������');
$name6 = iconv('windows-1251','utf-8','������');
$name7 = iconv('windows-1251','utf-8','�����������');
$name8 = iconv('windows-1251','utf-8','�����');
$name11 = iconv('windows-1251','utf-8','���');
$name12 = iconv('windows-1251','utf-8','�������');
$name13 = iconv('windows-1251','utf-8','��');
$name14 = iconv('windows-1251','utf-8','�����������');

CModule::IncludeModule("iblock");

$arResult["ERRORS"] = array();

//������ �����-������
if ($mode == 'list') {
	$APPLICATION->SetTitle(GetMessage("MESS_1"));
	if (isset($_POST['save'])) {
		foreach($_POST['name'] as $k => $v) {
			if (in_array($k,$_POST['del_file'])) {
				CIBlockElement::Delete($k);
				$arResult["MESSAGE"][] = '�����-���� �'.$k.' ������� ������';
			}
			else {
				$el = new CIBlockElement;
				$res = $el->Update($k, array("MODIFIED_BY"=>$USER->GetID(),"NAME"=>$v,"SORT"=>$_POST['sort'][$k],"ACTIVE"=>$_POST['active'][$k]));
				$arResult["MESSAGE"][] = '�����-���� �'.$k.' ������� �������';
			}
		
		}
	}
	
	if (isset($_POST['upload'])) {
			if ($_FILES["file_price"]["type"] != 'text/xml') $arResult["ERRORS"][] = '�����-���� ������ ���� � ������� XML';
			else {
				$name_f = time();
				$arr_file= array(
					"name" => $name_f.'.xml',
					"size" => $_FILES["file_price"]["size"],
					"tmp_name" => $_FILES["file_price"]["tmp_name"],
					"type" => "",
					"old_file" => "",
					"del" => "N",
					"MODULE_ID" => $arParams["IBLOCK_ID"]);
					$fid = CFile::SaveFile($_FILES["file_price"], "prices_delivery-russia");
					if (strlen($fid)>0) {
						$path = CFile::GetPath($fid);
						$text = file_get_contents($_SERVER["DOCUMENT_ROOT"].$path);
						$res3 = simplexml_load_string($text);
						$productNames = $res3->xpath('/'.$name0.'/'.$name3.'/'.$name4);
						$productNames02 = $res3->xpath('/'.$name0);
						$pokaz2 = (array)$productNames02[0];
						$name_of_current2 = iconv('utf-8','windows-1251',$pokaz2['@attributes'][$name14]);
						$el = new CIBlockElement;
						$price_id_bd = $el->Add(array("MODIFIED_BY"=>$USER->GetID(),"IBLOCK_SECTION_ID" => false,"IBLOCK_ID"=>$arParams["IBLOCK_ID"],"NAME" =>$name_of_current2,"SORT"=>0,"ACTIVE"=>"N","PROPERTY_VALUES"=>array("USER"=>$arParams["COMPANY_ID"],"FILE"=>$fid)));
						$VALUES_c = array();
						for ($i=0;$i<sizeof($productNames);$i++) {
							$city = (array)$productNames[$i];
							$ind_city_utf = $city['@attributes'][$name4];
							$ind_city = intval(str_replace('%C2%A0','',urlencode($ind_city_utf)));
							$VALUES_c[] = $ind_city;
						}
						CIBlockElement::SetPropertyValues($price_id_bd, $arParams["IBLOCK_ID"], $VALUES_c,"CITIES");
						$arResult["MESSAGE"][] = '�����-���� "'.$name_of_current2.'" ������� ��������';
					}
					else $arResult["ERRORS"][] = "������ �������� �����";
		}
	}
	
	$arResult["LIST"] = array();
	$res = CIBlockElement::GetList(Array("sort"=>"asc"), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"PROPERTY_USER"=>$arParams["COMPANY_ID"]), false, false, array("ID", "NAME","PROPERTY_FILE","ACTIVE","DATE_CREATE"));
	while($res_0 = $res->GetNext()) {
		$arResult["LIST"][] = $res_0;
	}
}

if ($mode == 'create') {
	$APPLICATION->SetTitle(GetMessage("MESS_2"));
	if (isset($_POST['step1'])) {
		if (!strlen($_POST['user_n_file']))
			$arResult["ERRORS"][] = '�� ������� ������������ �����-�����';
		if (intval($_POST['numkg']) < 0)
			$arResult["ERRORS"][] = '���������� �������� �� ���� �� ����� ���� ������ 0';
		if (count($arResult["ERRORS"]) == 0) {
			//�������� ��� � ��������� ����� ������
			$arResult["STEP_2"] = '
			<input type="hidden" name="step2" value="1" />
			<p>&nbsp;</p><p>��������� ���: <input type="text" class="inp" name="start" value="'.$_POST['start'].'" /> ��</p>
			<p>����������:</p>';
			$numdoc = intval($_POST['numdoc']);
			if ($numdoc > 0) {
				for ($i=0;$i<$numdoc;$i++) {
					$doc_name = 'doc'.$i;
					$arResult["STEP_2"] .= '<p>�������� �� <input type="text" value="'.$_POST[$doc_name].'" name="'.$doc_name.'" class="inp"> ��</p>';
				}
			}
			$numkg = intval($_POST['numkg']);
			if ($numkg > 0) {
				for ($i=0;$i<$numkg;$i++) {
					$kg_name = 'kg'.$i;
					$arResult["STEP_2"] .= '<p>����� <input type="text" value="'.$_POST[$kg_name].'" name="'.$kg_name.'" class="inp"> ��</p>';
				}
			}
		}
		else $arResult["STEP_2"] = '';
		//�������� ������� ����
		if (isset($_POST['step2'])) {
			if (floatval($_POST['start']) < 0)
				$arResult["ERRORS"][] = '��������� ��� ������ ���� ������ ���� ����� 0';
			if (count($arResult["ERRORS"]) == 0) {
				//���������������� �������� ������ � ������������� �� ������ �� ���������� �������
				$name_of_file = $_POST['name_of_file'];
				$global_file = $_SERVER["DOCUMENT_ROOT"].'/upload/iblock/prices/'.$name_of_file.'.xml';
				$path_to_bd = '/upload/prices/'.$name_of_file.'.xml';
				/********* �������� �����-����� *************/
				$numkg = intval($_POST['numkg']);
				$doc_name = intval($_POST['numdoc']);
				$doc = new DOMDocument("1.0","windows-1251");
				$doc->formatOutput = true;
				$root = $doc->createElement(iconv('windows-1251','utf-8','�����������'));
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����������'));
				$root->appendChild($a1);
				$text = $doc->createTextNode('0.1');
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
				$root->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$arParams['COMPANY_NAME']));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����������'));
				$root->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['user_n_file']));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
				$root->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$arParams['COMPANY_INN']));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������������'));
				$root->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$time));
				$text = $a1->appendChild($text);
		
				// ����������
				$root1 = $doc->createElement(iconv('windows-1251','utf-8','����������'));
				$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
				$atr = $root1->appendChild($atr);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','1'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','0'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','���.���� ��������'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','3'));
				$text = $a1->appendChild($text);
				$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
				$atr = $root1->appendChild($atr);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','2'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','0'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','����.���� ��������'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','4'));
				$text = $a1->appendChild($text);
				$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
				$atr = $root1->appendChild($atr);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','3'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',intval($_POST['start'])));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','��������� ���('.intval($_POST['start']).'��)'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','1'));
				$text = $a1->appendChild($text);
				for ($i=0;$i<$numkg;$i++) {
					$kod = $i+4;
					$name = 'kg'.$i;
					$znach = '����� '.$_POST[$name].'��';
					$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
					$atr = $root1->appendChild($atr);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$kod));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST[$name]));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$znach));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8','0'));
					$text = $a1->appendChild($text);
				}
				//���������
				for ($i=0;$i<$numdoc;$i++) {
					$kod = $i+4+$numkg;
					$name = 'doc'.$i;
					$znach = '�� '.$_POST[$name].'��';
					$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
					$atr = $root1->appendChild($atr);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$kod));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST[$name]));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$znach));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8','2'));
					$text = $a1->appendChild($text);
				}
				if ($arParams["PERSENTS"] == "Y") {
					//ID %% �.�.
					$kod = 4+$numkg+$numdoc;
					$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
					$atr = $root1->appendChild($atr);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$kod));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',0));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8','ID % � �.�.'));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8','5'));
					$text = $a1->appendChild($text);
					$kod++;
					$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
					$atr = $root1->appendChild($atr);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$kod));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',0));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8','ID % ��� �.�.'));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8','6'));
					$text = $a1->appendChild($text);
				}
				$root->appendChild($root1);
				//������
				$root2 = $doc->createElement(iconv('windows-1251','utf-8','������'));
				$root->appendChild($root2);
				//�����-����
				$root3 = $doc->createElement(iconv('windows-1251','utf-8','���������'));
				$root->appendChild($root3); 
				$root = $doc->appendChild($root);
				$doc->save($global_file);
				
				$el = new CIBlockElement;
				$price_id_bd = $el->Add(array("MODIFIED_BY"=>$USER->GetID(),"IBLOCK_SECTION_ID" => false,"IBLOCK_ID"=>$arParams["IBLOCK_ID"],"NAME" =>$_POST['user_n_file'],"SORT"=>0,"PROPERTY_VALUES"=>array("USER"=>$arParams["COMPANY_ID"],"FILE"=>CFile::MakeFileArray($global_file))));
				$link = $APPLICATION->GetCurPageParam("state=addcity&price_id=".$price_id_bd, array("state"));
				$arResult["MESSAGE"][] = '�����-���� ������� ������, ��� ���������� ������� ���������, ����������, �� <a href="'.$APPLICATION->GetCurPageParam("state=add_city&price_id=".$price_id_bd, array("state")).'">������.</a>';
				LocalRedirect($link);
			}
		}
	}
}


if ($mode == 'add_city') {
	$APPLICATION->SetTitle(GetMessage("MESS_3"));
	$price_id_bd = intval($_GET['price_id']);
	$res = CIBlockElement::GetList(Array("sort"=>"asc"), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"PROPERTY_USER"=>$arParams["COMPANY_ID"],"ID"=>$price_id_bd), false, false, array("PROPERTY_FILE","NAME"));
	while($res_0 = $res->GetNext()) {
		$result = $res_0;
	}
	$path_to_bd = CFile::GetPath($result["PROPERTY_FILE_VALUE"]);
	$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
	
	//���� ��� �����-�����
	if ($arParams["SAME_CITIES"] == "Y") $same_cities = true; else $same_cities = false;
	$same_prices = array();
	if ($same_cities) {
		$res = CIBlockElement::GetList(Array("sort"=>"asc"), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"PROPERTY_USER"=>$arParams["COMPANY_ID"],"!ID"=>$price_id_bd), false, false, array("PROPERTY_FILE","ID"));
		while($res_0 = $res->GetNext()) {
			$p = CFile::GetPath($res_0["PROPERTY_FILE_VALUE"]);
			$gl = $_SERVER["DOCUMENT_ROOT"].$p;
			if (is_file($gl)) {
				$same_prices[$res_0["ID"]] = $gl;
			}
		}
	}
	
	if (is_file($global_file)) {
		$APPLICATION->SetTitle(GetMessage("MESS_4",array("#NAME#"=>$result["NAME"])));
		/***********���������� ������ � �����-����***********/
		if (isset($_POST['add_c']) && strlen($_POST['city0'])) {
			$city_id = GetCityId($_POST['city0']);
			if ($city_id > 0) {
				$dom  = new domDocument('1.0','Windows-1251');
				$dom->load($global_file);
				$dom->formatOutput = true;
				
				$xpath = new DOMXPath ($dom); 
				$parent = $xpath->query ('//'.$name0.''); 
				$next = $xpath->query ('//'.$name0.'/'.$name5);
				$next2 = $xpath->query ('//'.$name0.'/'.$name5.'/'.$name6);
				$next3 = $xpath->query ('//'.$name0.'/'.$name3);
				$next4 = $xpath->query ('//'.$name0.'/'.$name3.'/'.$name4);
				
				$atr = $dom->createElement(iconv('windows-1251','utf-8','�����'));
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode($city_id);
				$text = $a1->appendChild($text);
				$next3->item(0)->insertBefore($atr, $next4->item(0));
				//$next3->appendChild($atr); 
	
				$massiv2 = $_POST['result'];
				foreach($massiv2 as $key => $value) {
					for($j=0;$j<$arResult['size_types'];$j++) {
						$type_name = $arResult['global_types'][$j];
						$massiv3 = $massiv2[$key][$type_name];
						foreach($massiv3 as $k => $zn) {
							if ($zn != '') {
								$atr = $dom->createElement(iconv('windows-1251','utf-8','������'));
								$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����'));
								$atr->appendChild($a1);
								$text = $dom->createTextNode($city_id);
								$text = $a1->appendChild($text);
								$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','������'));
								$atr->appendChild($a1);
								$text = $dom->createTextNode(iconv('windows-1251','utf-8',$time));
								$text = $a1->appendChild($text);
								$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','����������'));
								$atr->appendChild($a1);
								$text = $dom->createTextNode(iconv('windows-1251','utf-8',$k));
								$text = $a1->appendChild($text);
								$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����'));
								$atr->appendChild($a1);
								$text = $dom->createTextNode(iconv('windows-1251','utf-8',$zn));
								$text = $a1->appendChild($text);
								$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����������'));
								$atr->appendChild($a1);
								$text = $dom->createTextNode(iconv('windows-1251','utf-8',$type_name));
								$text = $a1->appendChild($text);
								$next->item(0)->insertBefore($atr, $next2->item(0)); 
							}
						}
					}
					if ($arParams["PERSENTS"] == "Y") {
						$atr = $dom->createElement(iconv('windows-1251','utf-8','������'));
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode($city_id);
						$text = $a1->appendChild($text);
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','������'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode(iconv('windows-1251','utf-8',$time));
						$text = $a1->appendChild($text);
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','����������'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode(iconv('windows-1251','utf-8',$_POST['persent_1']));
						$text = $a1->appendChild($text);
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode(iconv('windows-1251','utf-8',$value['��������'][$_POST['persent_1']]));
						$text = $a1->appendChild($text);
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����������'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode(iconv('windows-1251','utf-8','��������'));
						$text = $a1->appendChild($text);
						$next->item(0)->insertBefore($atr, $next2->item(0)); 
						$atr = $dom->createElement(iconv('windows-1251','utf-8','������'));
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode($city_id);
						$text = $a1->appendChild($text);
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','������'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode(iconv('windows-1251','utf-8',$time));
						$text = $a1->appendChild($text);
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','����������'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode(iconv('windows-1251','utf-8',$_POST['persent_2']));
						$text = $a1->appendChild($text);
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode(iconv('windows-1251','utf-8',$value['��������'][$_POST['persent_2']]));
						$text = $a1->appendChild($text);
						$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����������'));
						$atr->appendChild($a1);
						$text = $dom->createTextNode(iconv('windows-1251','utf-8','��������'));
						$text = $a1->appendChild($text);
						$next->item(0)->insertBefore($atr, $next2->item(0)); 
					}
				}
				$dom->save($global_file);
				$VALUES_c = array();
				$res = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $price_id_bd, "sort", "asc", array("CODE" => "CITIES"));
				while ($ob = $res->GetNext()) {
        			$VALUES_c[] = $ob['VALUE'];
				}
				$VALUES_c[] = $city_id;
				CIBlockElement::SetPropertyValues($price_id_bd, $arParams["IBLOCK_ID"], $VALUES_c,"CITIES");
				
				//��������� ������ �� ��� ������
				foreach ($same_prices as $k => $v) {
					$dom  = new domDocument('1.0','Windows-1251');
					$dom->load($v);
					$dom->formatOutput = true;
					$xpath = new DOMXPath ($dom); 
					$parent = $xpath->query ('//'.$name0.''); 
					$next = $xpath->query ('//'.$name0.'/'.$name5);
					$next2 = $xpath->query ('//'.$name0.'/'.$name5.'/'.$name6);
					$next3 = $xpath->query ('//'.$name0.'/'.$name3);
					$next4 = $xpath->query ('//'.$name0.'/'.$name3.'/'.$name4);
					$atr = $dom->createElement(iconv('windows-1251','utf-8','�����'));
					$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�����'));
					$atr->appendChild($a1);
					$text = $dom->createTextNode($city_id);
					$text = $a1->appendChild($text);
					$next3->item(0)->insertBefore($atr, $next4->item(0));
					$dom->save($v);
					CIBlockElement::SetPropertyValues($k, $arParams["IBLOCK_ID"], $VALUES_c,"CITIES");
				}
				
			}
			else {
				$arResult["ERRORS"][] = '����� �� ������';
			}
		}
		$arResult["HTML"] = read_price($global_file);
		$arResult["NUMKG"] = sizeof($arResult["HTML"]["WEIGHT"]);
		$arResult["NUMDOC"] = sizeof($arResult["HTML"]["DOCS"]);
	}
	else {
		$arResult["ERRORS"][] = '�����-���� �� ������';
	}
}

if ($mode == "delete_city") {
	$APPLICATION->SetTitle(GetMessage("MESS_5"));
	$price_id_bd = intval($_GET['price_id']);
	$res = CIBlockElement::GetList(Array("sort"=>"asc"), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"PROPERTY_USER"=>$arParams["COMPANY_ID"],"ID"=>$price_id_bd), false, false, array("PROPERTY_FILE","NAME"));
	while($res_0 = $res->GetNext()) {
		$result = $res_0;
	}
	$path_to_bd = CFile::GetPath($result["PROPERTY_FILE_VALUE"]);
	$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
	$file_name = $result["NAME"];
	
	
	//���� ��� �����-�����, ���� ���������� �� ����������
	if ($arParams["SAME_CITIES"] == "Y") $same_cities = true; else $same_cities = false;
	$same_prices = array();
	if ($same_cities) {
		$res = CIBlockElement::GetList(Array("sort"=>"asc"), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"PROPERTY_USER"=>$arParams["COMPANY_ID"],"!ID"=>$price_id_bd), false, false, array("PROPERTY_FILE","ID"));
		while($res_0 = $res->GetNext()) {
			$p = CFile::GetPath($res_0["PROPERTY_FILE_VALUE"]);
			$gl = $_SERVER["DOCUMENT_ROOT"].$p;
			if (is_file($gl)) {
				$same_prices[$res_0["ID"]] = $gl;
			}
		}
	}
	
	if (is_file($global_file)) {
		if (isset($_POST['save'])) {
			$res = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $price_id_bd, "sort", "asc", array("CODE" => "CITIES"));
			while ($ob = $res->GetNext()) {
				$VALUES_c[$ob['VALUE']] = $ob['VALUE'];
			}
			foreach ($_POST['city_del'] as $key) {
				//�������� �������
				$dom  = new domDocument('1.0','Windows-1251');
				$dom->load($global_file);
				$xpath= new DOMXpath($dom);
				$conv_src = iconv('windows-1251','utf-8','@�����="').$key.'"';
				$next = $xpath->query ('//'.$name0.'/'.$name3.'/'.$name4.'['.$conv_src.']');
				foreach($next as $photo) {
					$photo->parentNode->removeChild($photo);
				}
				$next2 = $xpath->query ('//'.$name0.'/'.$name5.'/'.$name6.'['.$conv_src.']');
				foreach($next2 as $photo) {
					$photo->parentNode->removeChild($photo);
				} 
				$dom->save($global_file);
				
				//��������� ��� ���� �������, ���� ����������
				foreach ($same_prices as $k => $v) {
					$dom  = new domDocument('1.0','Windows-1251');
					$dom->load($v);
					$xpath= new DOMXpath($dom);
					$conv_src = iconv('windows-1251','utf-8','@�����="').$key.'"';
					$next = $xpath->query ('//'.$name0.'/'.$name3.'/'.$name4.'['.$conv_src.']');
					foreach($next as $photo) {
						$photo->parentNode->removeChild($photo);
					}
					$next2 = $xpath->query ('//'.$name0.'/'.$name5.'/'.$name6.'['.$conv_src.']');
					foreach($next2 as $photo) {
						$photo->parentNode->removeChild($photo);
					} 
					$dom->save($v);
				}
				
				unset($VALUES_c[$key]);
			}
			CIBlockElement::SetPropertyValues($price_id_bd, $arParams["IBLOCK_ID"], $VALUES_c,"CITIES");
			//��������� ��� ���� �������, ���� ����������
			foreach ($same_prices as $k => $v) {
				CIBlockElement::SetPropertyValues($k, $arParams["IBLOCK_ID"], $VALUES_c,"CITIES");
			}
			
			$arResult["MESSAGE"][] = '�����-���� ������� �������';
		}
		$APPLICATION->SetTitle(GetMessage("MESS_9",array("#NAME#"=>$file_name)));
		$arResult["HTML"] = read_price($global_file);
	}
	else {
		$arResult["ERRORS"][] = '�����-���� �� ������';
	}
}

if ($mode == "edit") {
	$APPLICATION->SetTitle(GetMessage("MESS_6"));
	$price_id_bd = intval($_GET['price_id']);
	$res = CIBlockElement::GetList(Array("sort"=>"asc"), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"PROPERTY_USER"=>$arParams["COMPANY_ID"],"ID"=>$price_id_bd), false, false, array("PROPERTY_FILE","NAME"));
	while($res_0 = $res->GetNext()) {
		$result = $res_0;
	}
	$path_to_bd = CFile::GetPath($result["PROPERTY_FILE_VALUE"]);
	$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
	$file_name = $result["NAME"];
	if (is_file($global_file)) {
		////���������� ������
		if(isset($_POST['save'])) {
			$numkg = sizeof($_POST['weight']);
			$numdoc = sizeof($_POST['docs']);
			$result = $_POST['result'];
			$doc = new DOMDocument("1.0","windows-1251");
			$doc->formatOutput = true;
			$root = $doc->createElement(iconv('windows-1251','utf-8','�����������'));
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����������'));
			$root->appendChild($a1);
			$text = $doc->createTextNode('0.1');
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
			$root->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8',$arParams["COMPANY_NAME"]));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����������'));
			$root->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8',$file_name));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
			$root->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8',$arParams["COMPANY_INN"]));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������������'));
			$root->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8',$time));
			$text = $a1->appendChild($text);
			// ����������
			$root1 = $doc->createElement(iconv('windows-1251','utf-8','����������'));
			$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
			$atr = $root1->appendChild($atr);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['min_index']));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8','0'));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8','���.���� ��������'));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8','3'));
			$text = $a1->appendChild($text);
			$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
			$atr = $root1->appendChild($atr);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['max_index']));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8','0'));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8','����.���� ��������'));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8','4'));
			$text = $a1->appendChild($text);
			$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
			$atr = $root1->appendChild($atr);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['start_index']));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['start_value']));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8','��������� ���('.$_POST['start_value'].'��)'));
			$text = $a1->appendChild($text);
			$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
			$atr->appendChild($a1);
			$text = $doc->createTextNode(iconv('windows-1251','utf-8','1'));
			$text = $a1->appendChild($text);
			//���
			foreach ($_POST['weight'] as $kod => $value) {
				$name = $_POST['weight'][$kod];
				$znach = '����� '.$name.'��';
				$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
				$atr = $root1->appendChild($atr);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$kod));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$name));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$znach));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','0'));
				$text = $a1->appendChild($text);
			}
			//��������
			foreach ($_POST['docs'] as $kod => $value) {
				$name = $_POST['docs'][$kod];
				$znach = '�� '.$_POST[$name].'��';
				$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
				$atr = $root1->appendChild($atr);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$kod));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$name));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$znach));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','2'));
				$text = $a1->appendChild($text);
			}
			if ($arParams["PERSENTS"] == "Y") {
				//ID %% �.�.
				$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
				$atr = $root1->appendChild($atr);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['persent_1']));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','0'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','ID % � �.�.'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','5'));
				$text = $a1->appendChild($text);
				$atr = $doc->createElement(iconv('windows-1251','utf-8','����������'));
				$atr = $root1->appendChild($atr);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['persent_2']));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','0'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','ID % ��� �.�.'));
				$text = $a1->appendChild($text);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8','6'));
				$text = $a1->appendChild($text);
			}
			$root->appendChild($root1);
			//������
			$root2 = $doc->createElement(iconv('windows-1251','utf-8','������'));
			foreach ($result as $city_id => $value) {
				$atr = $doc->createElement(iconv('windows-1251','utf-8','�����'));
				$atr = $root2->appendChild($atr);
				$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����'));
				$atr->appendChild($a1);
				$text = $doc->createTextNode(iconv('windows-1251','utf-8',$city_id));
				$text = $a1->appendChild($text);
			}
			$root->appendChild($root2);
			//�����-����
			$root3 = $doc->createElement(iconv('windows-1251','utf-8','���������'));
			$flag = true;
			foreach ($result as $city_id => $value) {
				for($j=0;$j<$arResult['size_types'];$j++) {
					$name_type = $arResult['global_types'][$j];
					$massiv = $value[$name_type];
					foreach ($massiv as $k => $zn) {
						if ($zn != '') {
							$atr = $doc->createElement(iconv('windows-1251','utf-8','������'));
							$atr = $root3->appendChild($atr);
							$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����'));
							$atr->appendChild($a1);
							$text = $doc->createTextNode($city_id);
							$text = $a1->appendChild($text);
							$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','������'));
							$atr->appendChild($a1);
							$text = $doc->createTextNode(iconv('windows-1251','utf-8',$time));
							$text = $a1->appendChild($text);
							$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
							$atr->appendChild($a1);
							$text = $doc->createTextNode(iconv('windows-1251','utf-8',$k));
							$text = $a1->appendChild($text);
							$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����'));
							$atr->appendChild($a1);
							$text = $doc->createTextNode(iconv('windows-1251','utf-8',$zn));
							$text = $a1->appendChild($text);
							$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����������'));
							$atr->appendChild($a1);
							$text = $doc->createTextNode(iconv('windows-1251','utf-8',$name_type));
							$text = $a1->appendChild($text);
						}
						else {
							$flag = false;
						}
					}
				}
				if ($arParams["PERSENTS"] == "Y") {
					//%% 1
					$atr = $doc->createElement(iconv('windows-1251','utf-8','������'));
					$atr = $root3->appendChild($atr);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode($city_id);
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$time));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['persent_1']));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$value['��������'][$_POST['persent_1']]));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8','��������'));
					$text = $a1->appendChild($text);
					//%% 2
					$atr = $doc->createElement(iconv('windows-1251','utf-8','������'));
					$atr = $root3->appendChild($atr);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode($city_id);
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$time));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','����������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$_POST['persent_2']));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8',$value['��������'][$_POST['persent_2']]));
					$text = $a1->appendChild($text);
					$a1 = $doc->createAttribute(iconv('windows-1251','utf-8','�����������'));
					$atr->appendChild($a1);
					$text = $doc->createTextNode(iconv('windows-1251','utf-8','��������'));
					$text = $a1->appendChild($text);
				}
				
			}
			$root->appendChild($root3); 
			$root = $doc->appendChild($root);
			if ($arParams["ALL_ORDERS"] == "Y") {
				if ($flag) {
					$doc->save($global_file);
					$arResult["MESSAGE"][] = '�����-���� ������� �������';
				}
				else {
					$arResult["ERRORS"][] = '�� ��������� ���� ��� ��������� �����';
				}
			}
			else {
				$doc->save($global_file);
				$arResult["MESSAGE"][] = '�����-���� ������� �������';
			}
		}
		
		$APPLICATION->SetTitle(GetMessage("MESS_8",array("#NAME#"=>$file_name)));
		$arResult["HTML"] = read_price($global_file);
		$arResult["NUMKG"] = sizeof($arResult["HTML"]["WEIGHT"]);
		$arResult["NUMDOC"] = sizeof($arResult["HTML"]["DOCS"]);

	}
	else {
		$arResult["ERRORS"][] = '�����-���� �� ������';
	}
}

if ($mode == "edit_structure") {
	$APPLICATION->SetTitle(GetMessage("MESS_7"));
	$price_id_bd = intval($_GET['price_id']);
	$res = CIBlockElement::GetList(Array("sort"=>"asc"), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"PROPERTY_USER"=>$arParams["COMPANY_ID"],"ID"=>$price_id_bd), false, false, array("PROPERTY_FILE","NAME"));
	while($res_0 = $res->GetNext()) {
		$result = $res_0;
	}
	$path_to_bd = CFile::GetPath($result["PROPERTY_FILE_VALUE"]);
	$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
	$file_name = $result["NAME"];
	if (is_file($global_file)) {
		if (isset($_POST['save'])) {
			$dom  = new domDocument('1.0','Windows-1251');
			$dom->load($global_file);
			$dom->formatOutput = true;
			$xpath = new DOMXPath ($dom);
			$parent = $xpath->query ('//'.$name0.''); 
			$next = $xpath->query ('//'.$name0.'/'.$name1);
			$next2 = $xpath->query ('//'.$name0.'/'.$name1.'/'.$name2);
			///��������� ������������
			foreach($parent as $p) {
			$p->setAttribute($name14, iconv('windows-1251','utf-8',$_POST['user_n_file']));
			}
			//��������� ���������� ��������
			$strt_ind = htmlspecialchars($_POST['start_index']);
			$conv_src = iconv('windows-1251','utf-8','@���="'.$strt_ind.'"');
			$next3 = $xpath->query ('//'.$name0.'/'.$name1.'/'.$name2.'['.$conv_src.']');
			foreach($next3 as $photo) {
				$photo->setAttribute($name13, intval($_POST['start_value']));
				$photo->setAttribute($name2, iconv('windows-1251','utf-8','��������� ���('.intval($_POST['start_value']).') ��'));
			}
			//�������� ������� 
			foreach ($_POST['delete_yes'] as $k => $v) {
				$conv_src = iconv('windows-1251','utf-8','@���="').$v.'"';
				$conv_src2 = iconv('windows-1251','utf-8','@����������="').$v.'"';
				$next = $xpath->query ('//'.$name0.'/'.$name1.'/'.$name2.'['.$conv_src.']');
				foreach($next as $photo) { $photo->parentNode->removeChild($photo); }
				$next = $xpath->query ('//'.$name0.'/'.$name5.'/'.$name6.'['.$conv_src2.']');
				foreach($next as $photo) { $photo->parentNode->removeChild($photo); }
			}
			//��������� �������� ��
			foreach ($_POST['edit_yes'] as $k => $v) {
				$conv_src = iconv('windows-1251','utf-8','@���="').$v.'"';
				$next = $xpath->query ('//'.$name0.'/'.$name1.'/'.$name2.'['.$conv_src.']');
				foreach($next as $photo) { $photo->setAttribute($name13, floatval(str_replace(',','.',$_POST['weight_value_'.$v]))); }
			}
			//���������� ����� �����
			$mm_kod = $_POST['max_kod'];
			if ((intval($_POST['add_kg']) > 0) or (intval($_POST['add_doc']) > 0)) {
				for ($i=0;$i<$_POST['add_kg'];$i++) {
					$arResult['msg1'] .= '
				<tr><td>�����</td><td><input type="text" name="new_weight_'.$i.'" value="'.floatval(str_replace(',','.',$_POST['new_weight_'.$i])).'" class="inp"></td><td colspan="2">��</td></tr>';
				if (intval($_POST['new_weight_'.$i]) > 0) {
				$mm_kod++; 
				$atr = $dom->createElement(iconv('windows-1251','utf-8','����������'));
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode($mm_kod);
				$text = $a1->appendChild($text);
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode(intval($_POST['new_weight_'.$i]));
				$text = $a1->appendChild($text);
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode(iconv('windows-1251','utf-8','����� '.floatval(str_replace(',','.',$_POST['new_weight_'.$i])).'��'));
				$text = $a1->appendChild($text);
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode(0);
				$text = $a1->appendChild($text);
				$next->item(0)->insertBefore($atr, $next2->item(0));
				$arResult['result_add_kg']++; }
				}
				for ($i=0;$i<$_POST['add_doc'];$i++) { 
					$arResult['msg2'] .= '
						<tr><td>��</td><td><input type="text" name="new_docs_'.$i.'" value="'.floatval(str_replace(',','.',$_POST['new_docs_'.$i])).'"class="inp"></td><td colspan="2">��</td></tr>';
				if ($_POST['new_docs_'.$i] != '') {
				$mm_kod++;
				$atr = $dom->createElement(iconv('windows-1251','utf-8','����������'));
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','���'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode($mm_kod);
				$text = $a1->appendChild($text);
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','��'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode(iconv('windows-1251','utf-8',$_POST['new_docs_'.$i]));
				$text = $a1->appendChild($text);
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','����������'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode(iconv('windows-1251','utf-8','�� '.floatval(str_replace(',','.',$_POST['new_docs_'.$i])).'��'));
				$text = $a1->appendChild($text);
				$a1 = $dom->createAttribute(iconv('windows-1251','utf-8','�������'));
				$atr->appendChild($a1);
				$text = $dom->createTextNode(2);
				$text = $a1->appendChild($text);
				$next->item(0)->insertBefore($atr, $next2->item(0));
				$arResult['result_add_docs']++; }
				}
			}
			 $dom->save($global_file);
			 $el = new CIBlockElement;
			 $res = $el->Update($price_id_bd, array("MODIFIED_BY"=>$USER->GetID(),"NAME"=>$_POST['user_n_file']));
			 $file_name = $_POST['user_n_file'];
			 $arResult["MESSAGE"][] = '��������� �����-����� ������� ��������';
		}
		$APPLICATION->SetTitle(GetMessage("MESS_10",array("#NAME#"=>$file_name)));
		$arResult["HTML"] = read_price($global_file);
		$arResult["HTML"]["NAME"] = $file_name;
	}
	else {
		$arResult["ERRORS"][] = '�����-���� �� ������';
	}
}

$this->IncludeComponentTemplate($mode);

?>