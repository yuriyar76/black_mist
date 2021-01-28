<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$currentip = GetSettingValue(683);
$currentlink = GetSettingValue(704);
$login1c = GetSettingValue(705);
$pass1c = GetSettingValue(706);
if (!strlen(trim($currentip)))
{
	?>
    <div class="alert alert-dismissable alert-danger fade in" role="alert">
    	Не указан IP для подключения к 1с<br>
    	Пожалуйста, обратитесь к администратору
	</div>
    <?
}
else
{
	function GetEvents($nakl_id)
	{
		$ar = array();
		$res_2 = CIBlockElement::GetList(
			array('PROPERTY_DATE' => 'ASC'), 
			array('IBLOCK_ID' => 30, 'PROPERTY_NUM' => $nakl_id), 
			false, 
			false, 
			array('ID', 'PROPERTY_NUM', 'PROPERTY_EVENT', 'PROPERTY_DATE', 'PROPERTY_DESC', 'PROPERTY_INN', 'PROPERTY_EVENT_DELIVERY')
		);
		while ($ob_2 = $res_2->GetNextElement())
		{
			$ar[] = $ob_2->GetFields();
		}
		return $ar;
	}
	
	function IsDeliverd($arTracks, $dost)
	{
		$res = false;
		foreach ($arTracks as $t)
		{
			if ($t['PROPERTY_EVENT_DELIVERY_VALUE'] == $dost)
			{
				$res = true;
			}
		}
		return $res;
	}
	
	function GetINNs($nakl_id)
	{
		$arINN = array();
		$res_2 = CIBlockElement::GetList(
			array('ID' => 'ASC'), 
			array('IBLOCK_ID' => 29, 'PROPERTY_NUMBER' => $nakl_id), 
			false, 
			false, 
			array('PROPERTY_INN')
		);
		while ($ob_2 = $res_2->GetNextElement())
		{
			$ar = $ob_2->GetFields();
			if (!in_array($ar['PROPERTY_INN_VALUE'], $arINN))
			{
				$arINN[] = $ar['PROPERTY_INN_VALUE'];
			}
		}
		return $arINN;
	}
	
	$arResult["CURRENT_USER"] = $USER->GetID();
	$rsUser = CUser::GetByID($arResult["CURRENT_USER"]);
	$arUser = $rsUser->Fetch();
	
	$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
	
	$arResult['OPEN'] = false;
	$arResult['AGENT_INFO'] = GetCompany($agent_id);
	if (strlen($arResult['AGENT_INFO']['PROPERTY_INN_VALUE']))
	{
		$arResult['OPEN'] = true;
		$arResult['INN'] = $arResult['AGENT_INFO']['PROPERTY_INN_VALUE'];
	}
	else
	{
		$arResult["ERRORS"][] = 'Не указан ИНН компании';
	}
	
	$arResult['USER'] = intval($arUser['UF_USER_SHARING']);
	$arResult['USER'] = ($arResult['USER'] == 0) ? 9025282 : $arResult['USER'];
	
	$arResult['INN'] = strlen(trim($_POST['correct_inn'])) ? trim($_POST['correct_inn']) : $arResult['INN'];
	
	/*
	$arResult['USER'] = intval($arUser['UF_USER_SHARING']);
	
	if ($arResult['USER'] == 0)
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}
	else
	{
		$db_props = CIBlockElement::GetProperty(17, $arResult['USER'], array("sort" => "asc"), Array("CODE"=>"INN"));
		if($ar_props = $db_props->Fetch())
		{
			$arResult['INN'] = $ar_props["VALUE"];
		}
		else
		{
			$arResult['INN'] = '';
		}
	}
	*/
	
	$arResult['NUMBER'] = '';
	$arResult['NUMBER_ID'] = 0;
	$arResult['TRACKING'] = array();
	$arResult['ISSUE'] = false;
	$arResult['EVENTS'] = array(
		$arParams['DOST_CODE'] => GetMessage('DOST_CODE_TEXT'),
		$arParams['ISKL_CODE'] => GetMessage('ISKL_CODE_TEXT'),
		$arParams['DEBIT_CODE'] => GetMessage('DEBIT_CODE_TEXT')
	);
	$arResult['SITUATION'] = array();
	
	
	
	
	/*
	$res_3 = CIBlockElement::GetList(
		array('sort' => 'asc'), 
		array('IBLOCK_ID' => 20, 'ACTIVE' => 'Y'), 
		false, 
		false, 
		array('ID', 'NAME')
	);
	while ($ob_3 = $res_3->GetNextElement())
	{
		$a = $ob_3->GetFields();
		$arResult['EVENTS'][$a['ID']] = $a['NAME'];
	}
	*/
	$res_4 = CIBlockElement::GetList(
		array('NAME' => 'ASC'), 
		array('IBLOCK_ID' => 19, 'ACTIVE' => 'Y'), 
		false, 
		false, 
		array('ID', 'NAME')
	);
	while ($ob_4 = $res_4->GetNextElement())
	{
		$a = $ob_4->GetFields();
		$arResult['SITUATION'][$a['ID']] = $a['NAME'];
	}
	
	if (isset($_POST['search']))
	{
		if (($_POST["rand"] == $_SESSION[$_POST["key_session"]]) && (intval($_POST['json']) != 1))
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (strlen(trim($_POST['number'])))
			{
				$arResult['NUMBER'] = trim($_POST['number']);
				$res = CIBlockElement::GetList(
					array('PROPERTY_DATE' => 'ASC'), 
					array("IBLOCK_ID" => 28, "ACTIVE"=>"Y", "NAME" => trim($_POST['number'])), 
					false, 
					array("nTopCount" => 1), 
					array('ID', 'PROPERTY_NUM')
				);
				if($ob = $res->GetNextElement())
				{
					$arFields = $ob->GetFields();
					$arResult['ISSUE'] = true;
					$arResult['NUMBER_ID'] = $arFields['ID'];
				}
				else
				{
					$arResult["ERRORS"][] = GetMessage('ERR_NOT_FIND');
				}
			}
			else
			{
				$arResult["ERRORS"][] = GetMessage('ERR_NO_NUMBER');
			}
		}
	
			$arResult["JSON"] = array(
			//	'errors' => $arResult["ERRORS"],
				'issue' => ($arResult['ISSUE']) ? 1 : 0,
				'number_id' => $arResult['NUMBER_ID']
			);
	}
	
	if (isset($_POST['add']))
	{
		$arResult['ISSUE'] = true;
		$arResult['NUMBER'] = $_POST['number'];
		$arResult['NUMBER_ID'] = $_POST['number_id'];
		if (!strlen($_POST['date']))
		{
			$arResult["ERRORS"][] = GetMessage('ERR_NO_DATE');
		}
		if (intval($_POST['event']) == 15680)
		{
			$iskl = true;
			if (intval($_POST['situation']) == 0)
			{
				$arResult["ERRORS"][] = GetMessage('ERR_NO_SITUATION');
			}
		}
		else
		{
			$iskl = false;
			if (!strlen(trim($_POST['descr'])))
			{
				$arResult["ERRORS"][] = GetMessage('ERR_NO_DESCRIPTION');
			}
		}
		if (count($arResult["ERRORS"]) == 0)
		{
			$event = $arResult['EVENTS'][$_POST['event']];
			if ($iskl)
			{
				$descr = $arResult['SITUATION'][$_POST['situation']];
			}
			else
			{
				if (intval($_POST['json']) == 1)
				{
					$descr = iconv('utf-8','windows-1251',trim($_POST['descr']));
				}
				else
				{
					$descr = trim($_POST['descr']);
				}
			}
			$props = array(
				'NUM' => $arResult['NUMBER_ID'],
				'INN' => $arResult['INN'],
				'EVENT' => $event,
				'DATE' => $_POST['date'],
				'DESC' => $descr,
				'UPLOADING' => false,
				'EVENT_DELIVERY' => $_POST['event']
			);
			$arr_to_add = array(
				'IBLOCK_ID' => 30,
				'NAME' => '#'.$arResult['NUMBER'].' - '.$_POST['date'].' - '.$event,
				'PROPERTY_VALUES' => $props
			);
			foreach ($props as $k => $v)
			{
				$arResult["JSON"]['event'][$k] = iconv('windows-1251','utf-8',$v);
			}
			$e_to_add = new CIBlockElement;
			if ($e_to_add->Add($arr_to_add))
			{
				$arResult["JSON"]['add_event'] = 1;
				$arResult["MESSAGE"][] = GetMessage('SECC_ADD');
			}
			$res_5 = CIBlockElement::GetList(
				array('id' => 'asc'), 
				array("IBLOCK_ID" => 29, 'PROPERTY_NUMBER' => $arResult['NUMBER_ID'], 'TYPE' => 2), 
				false, 
				array("nTopCount" => 1), 
				array('ID', 'NAME')
			);
			if($ob_5 = $res_5->GetNextElement())
			{
				$arFields = $ob_5->GetFields();
				$zip_file = $arParams['SERVER_PATH'].$arFields['NAME'];
				if (is_file($zip_file))
				{
					$xml_file = false;
					include_once($arParams['SERVER_PATH'].'/bitrix/_black_mist/dUnzip2.inc.php');
					$p = time().'_'.rand(10000, 99999);
					$extr_dir = $arParams['SERVER_PATH'].'/app/f/extracts/'.date('Y');
					if (!file_exists($extr_dir))
					{
						mkdir($extr_dir);
					}
					$extr_dir .= '/'.date('m');
					if (!file_exists($extr_dir))
					{
						mkdir($extr_dir);
					}
					$extr_dir .= '/'.date('d');
					if (!file_exists($extr_dir))
					{
						mkdir($extr_dir);
					}
					$extr_dir .= '/'.$arResult['USER'];
					if (!file_exists($extr_dir))
					{
						mkdir($extr_dir);
					}
					$extr_dir .= '/'.$p;
					if (!file_exists($extr_dir))
					{
						mkdir($extr_dir);
					}
					$zip = new dUnzip2($zip_file);
					$list = $zip->getList();
					$zip->unzipAll($extr_dir);
					foreach ($list as $fileName => $zippedFile)
					{
						$xml_file = $extr_dir.'/'.$fileName;
					}
					if (is_file($xml_file))
					{
						$text = '';
						$handle = fopen($xml_file, "r");
						while (!feof($handle))
						{
							$buffer = fgets($handle, 4096);
							$text .= iconv('utf-8', 'windows-1251//IGNORE', $buffer); 
						}
						fclose($handle);
						$text = eregi_replace("<!DOCTYPE[^>]{1,}>", "", $text);
						$text = eregi_replace("<"."\?XML[^>]{1,}\?".">", "", $text);
						if (strlen($text) <= 0)
						{
							$arResult["ERRORS"][] = GetMessage('ERR_READING');
						}
						else
						{
							$text_status_begin = strpos($text, '<Статусы>');
							$text_status_end = strpos($text, '</Статусы>');
							$doc_has_status = true;
							if ( ($text_status_begin === false) || ($text_status_end === false) )
							{
								$doc_has_status = false;
							}
							if ($doc_has_status)
							{
								$text_status_end += strlen('</Статусы>');
								$status_text = '';
								for ($i=$text_status_begin; $i<$text_status_end; $i++)
								{
									$status_text .= $text[$i];
								}
							}
							$arTracking = GetEvents($arResult['NUMBER_ID']);
							$new_status_text = '';
							foreach ($arTracking as $el)
							{
								$new_status_text .= "\t\t\t" . '<Статус>' . "\r\n";
								$new_status_text .= "\t\t\t\t" . '<Партнер>'.$el['PROPERTY_INN_VALUE'].'</Партнер>' . "\r\n";
								$new_status_text .= "\t\t\t\t" . '<Событие>'.$el['PROPERTY_EVENT_VALUE'].'</Событие>' . "\r\n";
								$new_status_text .= "\t\t\t\t" . '<ЗначениеРеквизита>' . "\r\n";
								$new_status_text .= "\t\t\t\t\t" . '<Наименование>ДатаСобытия</Наименование>' . "\r\n";
								$new_status_text .= "\t\t\t\t\t" . '<Значение>'.$el['PROPERTY_DATE_VALUE'].'</Значение>' . "\r\n";
								$new_status_text .= "\t\t\t\t" . '</ЗначениеРеквизита>' . "\r\n";
								$new_status_text .= "\t\t\t\t" . '<ЗначениеРеквизита>' . "\r\n";
								$new_status_text .= "\t\t\t\t\t" . '<Наименование>КодИсключительнойСитуации</Наименование>' . "\r\n";
								$new_status_text .= "\t\t\t\t\t" . '<Значение>'.$el['PROPERTY_DESC_VALUE'].'</Значение>' . "\r\n";
								$new_status_text .= "\t\t\t\t" . '</ЗначениеРеквизита>' . "\r\n";
								$new_status_text .= "\t\t\t" . '</Статус>' . "\r\n";
							}
							if (strlen($new_status_text) > 0)
							{
								$new_status_text = '<Статусы>' . "\r\n" . $new_status_text . "\t\t" . '</Статусы>';
							}
							$text = str_replace($status_text, $new_status_text, $text);
							/*$text = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n" . $text;*/
							$text = '<?xml version="1.0" encoding="UTF-8"?>'.$text;
							$text = iconv('windows-1251', 'utf-8//IGNORE', $text);
							if (file_put_contents($xml_file, $text) === false)
							{
								$arResult["ERRORS"][] = GetMessage('ERR_WRITING');
							}
							else
							{
								include_once($arParams['SERVER_PATH'].'/bitrix/_black_mist/dZip.inc.php');
								$upload_dir = $arParams['SERVER_PATH'].'/app/f/archives/'.date('Y');
								if (!file_exists($upload_dir))
								{
									mkdir($upload_dir);
								}
								$upload_dir .= '/'.date('m');
								if (!file_exists($upload_dir))
								{
									mkdir($upload_dir);
								}
								$upload_dir .= '/'.date('d');
								if (!file_exists($upload_dir))
								{
									mkdir($upload_dir);
								}
								$upload_dir .= '/'.$arResult['USER'];
								if (!file_exists($upload_dir))
								{
									mkdir($upload_dir);
								}
								$abc = str_shuffle('qwertyuiopasdfghjklzxcvbnm1234567890');
								$t = '';
								$r = '';
								$n = strlen($abc)-1;
								for ($i=0; $i<12; $i++)
								{
									$r .= $abc[rand(0, $n)];
								}
								$doc_file_name = date('His').'_id_'.$arResult['NUMBER_ID'].'_'.$r.'.zip';
								$zip_file = $upload_dir.'/'.$doc_file_name;
								$newzip = new dZip($zip_file);
								$newzip->addFile($xml_file, $fileName);
								$newzip->save();
								$file_path = str_replace($arParams['SERVER_PATH'], '', $zip_file);
								$arResult['INN_FOR'] = GetINNs($arResult['NUMBER_ID']);
								$arr_to_add = array(
									'IBLOCK_ID' => 29,
									'NAME' => $file_path,
									'PROPERTY_VALUES' => array(
										'NUMBER' => $arResult['NUMBER_ID'],
										'UPLOADING' => false,
										'USER' =>  $arResult['USER'],
										'TIME' => time(),
										'TYPE' => '2',
										'INN' => $arResult['INN_FOR']
									)
								);
								$e = new CIBlockElement;
								if($e->Add($arr_to_add))
								{
									$arResult["JSON"]['yes'] = 1;
									// $arResult["MESSAGE"][] = GetMessage('SECC_ADD_FILE');
								}
							}
	
						}
					}
					else
					{
						$arResult["ERRORS"][] = GetMessage('ERR_UNPACK_FILE');
					}
				}
				else
				{
					$arResult["ERRORS"][] = GetMessage('ERR_NO_FILE');
				}
			}
			else
			{
				$arResult["ERRORS"][] = GetMessage('ERR_NO_FILE_INFO');
			}
			$_POST = array();
		}
		else
		{
			foreach($arResult["ERRORS"] as $r)
			{
				$arResult["JSON"]["ERRORS"][] = iconv('windows-1251','utf-8',$r);
			}
		}
	}
	
	if (isset($_POST['addto1c']))
	{
		error_reporting(E_ERROR);
		ini_set("soap.wsdl_cache_enabled", "0" );
		$link = "http://".$currentip.$currentlink;
		$curl = curl_init();
		curl_setopt_array($curl, array(    
			CURLOPT_URL => $link,
			CURLOPT_HEADER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_NOBODY => true,
			CURLOPT_TIMEOUT => 5));
		
		$header = explode("\n", curl_exec($curl));
		curl_close($curl);
		if (strlen(trim($header[0])))
		{
			$arResult['ISSUE'] = true;
			if (is_array($_POST['number'])) 
			{
				foreach ($_POST['number'] as $n)
				{
					$arResult['NUMBER'][] = iconv('utf-8','windows-1251',$n);
				}
			}
			else
			{
				$arResult['NUMBER'] = iconv('utf-8','windows-1251',$_POST['number']);
			}
			if (!strlen($_POST['date']))
			{
				$arResult["ERRORS"][] = GetMessage('ERR_NO_DATE');
			}
			if (intval($_POST['event']) == 15680)
			{
				$iskl = true;
				if (intval($_POST['situation']) == 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_NO_SITUATION');
				}
			}
			else
			{
				$iskl = false;
				if (!strlen(trim($_POST['descr'])))
				{
					$arResult["ERRORS"][] = GetMessage('ERR_NO_DESCRIPTION');
				}
			}
			if (count($arResult["ERRORS"]) == 0)
			{
				$event = $arResult['EVENTS'][$_POST['event']];
				if ($iskl)
				{
					$descr = $arResult['SITUATION'][$_POST['situation']];
				}
				else
				{
					if (intval($_POST['json']) == 1)
					{
						$descr = iconv('utf-8','windows-1251',trim($_POST['descr']));
					}
					else
					{
						$descr = trim($_POST['descr']);
					}
				}
				
				$arJsn = array();
				$a_f = fopen($arParams['SERVER_PATH'].'/app/logs/app_pods/log.txt', 'a+');
				if (is_array($arResult['NUMBER']))
				{
					foreach ($arResult['NUMBER'] as $n)
					{
						$props = array(
							'ID_EVENT' => 0,
							'NUMBER' => $n,
							"DATE" => $_POST['date'],
							'EVENT' => $event,
							'DESCRIPTION' => $descr,
							'INN' => $arResult['INN']
						);
						
						$ar_a_f = array();
						foreach ($props as $k => $v)
						{
							$ar_a_f[] = $k.': '.$v;
						}
						$str_a_f = implode(', ',$ar_a_f);
						fwrite($a_f, date('d.m.Y H:i:s').' USER: '.$arResult["CURRENT_USER"].', AGENT: '.$arResult['AGENT_INFO']['NAME'].', '.$str_a_f."\r\n");
						$ar = array();
						foreach ($props as $k => $v)
						{
							$ar[$k] = iconv('windows-1251','utf-8', $v);
						}
						$arJsn[] = $ar;
					}
				}
				else
				{
					$props = array(
						'ID_EVENT' => 0,
						'NUMBER' => $arResult['NUMBER'],
						"DATE" => $_POST['date'],
						'EVENT' => $event,
						'DESCRIPTION' => $descr,
						'INN' => $arResult['INN']
					);
					$ar_a_f = array();
					foreach ($props as $k => $v)
					{
						$ar_a_f[] = $k.': '.$v;
					}
					$str_a_f = implode(', ',$ar_a_f);
					fwrite($a_f, date('d.m.Y H:i:s').' USER: '.$arResult["CURRENT_USER"].', AGENT: '.$arResult['AGENT_INFO']['NAME'].', '.$str_a_f."\r\n");
					$ar = array();
					foreach ($props as $k => $v)
					{
						$ar[$k] = iconv('windows-1251','utf-8', $v);
					}
					$arJsn[] = $ar;
				}
				$arParamsJson['ListOfDocs'] = json_encode($arJsn);
                //fwrite($a_f, $arParamsJson['ListOfDocs']."\r\n");
				$client = new SoapClient($link,  array('login' => $login1c, 'password' => $pass1c));
				$result = $client->SetDD($arParamsJson);
				$mResult = $result->return;
	
				$obj = json_decode($mResult, true);
				$arNakls = array();
				 foreach ($obj as $v)
					{
                        /*
						$arNakls[iconv('utf-8', 'windows-1251',$v['number'])] = array(
							'status' => (iconv('utf-8', 'windows-1251',$v['status']) == 'true') ? 'Y' : 'N',
							'comment' => iconv('utf-8', 'windows-1251',$v['comment'])
						);
                        */
                        $arNakls[$v['number']] = array(
							'status' => (iconv('utf-8', 'windows-1251',$v['status']) == 'true') ? 'Y' : 'N',
							'comment' => $v['comment']
						);
						fwrite($a_f, iconv('utf-8', 'windows-1251',$v['number']).' '.iconv('utf-8', 'windows-1251',$arNakls[$v['number']]['status']).' '.iconv('utf-8', 'windows-1251',$arNakls[$v['number']]['comment'])."\r\n");
					}
				fwrite($a_f, "\r\n");
				fclose($a_f);	
				$arResult["JSON"]["RESULT"] = $arNakls;
			}
			else
			{
				foreach($arResult["ERRORS"] as $r)
				{
					$arResult["JSON"]["ERRORS"][] = iconv('windows-1251','utf-8',$r);
				}
			}	
		}
		else
		{
			$arResult["JSON"]["ERRORS"][] = iconv('windows-1251','utf-8', 'Нет соединения с сервером');
		}
	}
	
	if ($arResult['ISSUE'])
	{
		$arResult['TRACKING'] = GetEvents($arResult['NUMBER_ID']);
		$arResult['IS_DELIVERD'] = IsDeliverd($arResult['TRACKING'], $arParams['DOST_CODE']);
	}
}

$this->IncludeComponentTemplate();
?>