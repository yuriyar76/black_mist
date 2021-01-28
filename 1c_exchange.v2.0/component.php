<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams["INTERVAL"] = intval($arParams["INTERVAL"]);

if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);
	
if(!is_array($arParams["SITE_LIST"]))
	$arParams["SITE_LIST"] = array();
	
$arParams["FILE_SIZE_LIMIT"] = intval($arParams["FILE_SIZE_LIMIT"]);
if($arParams["FILE_SIZE_LIMIT"] < 1)
	$arParams["FILE_SIZE_LIMIT"] = 200*1024;
	
$arParams["USE_CRC"] = $arParams["USE_CRC"]!="N";
$arParams["USE_ZIP"] = $arParams["USE_ZIP"]!="N";

if($arParams["INTERVAL"] <= 0)
	@set_time_limit(0);
	
$start_time = time();

$bUSER_HAVE_ACCESS = false;

if(isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$bUSER_HAVE_ACCESS = $GLOBALS["USER"]->IsAdmin();
	if(!$bUSER_HAVE_ACCESS)
	{
		$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
		foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
		{
			if(in_array($PERM, $arUserGroupArray))
			{
				$bUSER_HAVE_ACCESS = true;
				break;
			}
		}
	}
}

$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas()
		&& !isset($_GET["mode"])
		&& is_object($GLOBALS["USER"])
		&& $GLOBALS["USER"]->IsAdmin();

if(!$bDesignMode)
{
	if(!isset($_GET["mode"]))
		return;
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
}



$DIR_NAME = "";

ob_start();

if($_GET["mode"] == "checkauth" && $USER->IsAuthorized())
{
	echo "success\n";
	echo session_name()."\n";
	echo session_id() ."\n";
}
elseif(!$USER->IsAuthorized())
{
	echo "failure\n",GetMessage("CC_BSC1_ERROR_AUTHORIZE");
}
elseif(!$bUSER_HAVE_ACCESS)
{
	echo "failure\n",GetMessage("CC_BSC1_PERMISSION_DENIED");
}
elseif(!CModule::IncludeModule("iblock"))
{
	echo "failure\n",GetMessage("CC_BSC1_ERROR_MODULE");
}
else
{
	$arUserID_n = $GLOBALS["USER"]->GetID();

	$DIR_NAME = "/app/f/v2/".date('Y');
	if (!file_exists($_SERVER['DOCUMENT_ROOT'].$DIR_NAME))
	{
		mkdir($_SERVER['DOCUMENT_ROOT'].$DIR_NAME);
	}
	$DIR_NAME .= '/'.date('m');
	if (!file_exists($_SERVER['DOCUMENT_ROOT'].$DIR_NAME))
	{
		mkdir($_SERVER['DOCUMENT_ROOT'].$DIR_NAME);
	}
	$DIR_NAME .= '/'.date('d');
	if (!file_exists($_SERVER['DOCUMENT_ROOT'].$DIR_NAME))
	{
		mkdir($_SERVER['DOCUMENT_ROOT'].$DIR_NAME);
	}
	$DIR_NAME .= '/'.$bUserINN;
	if (!file_exists($_SERVER['DOCUMENT_ROOT'].$DIR_NAME))
	{
		mkdir($_SERVER['DOCUMENT_ROOT'].$DIR_NAME);
	}
	$ABS_FILE_NAME = false;
	$WORK_DIR_NAME = false;
	if(isset($_GET["filename"]) && (strlen($_GET["filename"])>0))
	{
		$filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "", $_GET["filename"]);
		$filename = trim(str_replace("\\", "/", trim($filename)), "/");
	
		$io = CBXVirtualIo::GetInstance();
		$bBadFile = HasScriptExtension($filename)
			|| IsFileUnsafe($filename)
			|| !$io->ValidatePathString("/".$filename)
		;
	
		if(!$bBadFile)
		{
			$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"].$DIR_NAME, "/".$filename);
			if((strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename))
			{
				$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$DIR_NAME.$FILE_NAME;
				$WORK_DIR_NAME = substr($ABS_FILE_NAME, 0, strrpos($ABS_FILE_NAME, "/")+1);
			}
		}
	}
	if (($_GET["mode"] == "file") && $ABS_FILE_NAME)
	{
		if(function_exists("file_get_contents"))
			$DATA = file_get_contents("php://input");
		elseif(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
			$DATA = &$GLOBALS["HTTP_RAW_POST_DATA"];
		else
			$DATA = false;
	
		$DATA_LEN = defined("BX_UTF")? mb_strlen($DATA, 'latin1'): strlen($DATA);
		if(isset($DATA) && $DATA !== false)
		{
			CheckDirPath($ABS_FILE_NAME);
			if($fp = fopen($ABS_FILE_NAME, "ab"))
			{
				$result = fwrite($fp, $DATA);
				if($result === $DATA_LEN)
				{
					echo "success\n";
					if($_SESSION["BX_CML2_IMPORT"]["zip"])
						$_SESSION["BX_CML2_IMPORT"]["zip"] = $ABS_FILE_NAME;
				}
				else
				{
					echo "failure\n",GetMessage("CC_BSC1_ERROR_FILE_WRITE", array("#FILE_NAME#"=>$FILE_NAME));
				}
			}
			else
			{
				echo "failure\n",GetMessage("CC_BSC1_ERROR_FILE_OPEN", array("#FILE_NAME#"=>$FILE_NAME));
			}
			fclose($fp);
		}
		else
		{
			echo "failure\n",GetMessage("CC_BSC1_ERROR_HTTP_READ");
		}
	}
	
	elseif (($_GET["mode"] == "import") && $_SESSION["BX_CML2_IMPORT"]["zip"])
	{
		if(!array_key_exists("last_zip_entry", $_SESSION["BX_CML2_IMPORT"]))
			$_SESSION["BX_CML2_IMPORT"]["last_zip_entry"] = "";
	
		$result = CIBlockXMLFile::UnZip($_SESSION["BX_CML2_IMPORT"]["zip"], $_SESSION["BX_CML2_IMPORT"]["last_zip_entry"]);
		if ($result===false)
		{
			echo "failure\n",GetMessage("CC_BSC1_ZIP_ERROR");
		}
		elseif($result===true)
		{
			$_SESSION["BX_CML2_IMPORT"]["zip"] = false;
			echo "progress\n".GetMessage("CC_BSC1_ZIP_DONE");
		}
		else
		{
			$_SESSION["BX_CML2_IMPORT"]["last_zip_entry"] = $result;
			echo "progress\n".GetMessage("CC_BSC1_ZIP_PROGRESS");
		}
	}
	
	elseif (($_GET["mode"] == "import") && $ABS_FILE_NAME)
	{
		if ($xml = simplexml_load_file($ABS_FILE_NAME))
		{
			$name01 = iconv('windows-1251','utf-8',"Накладная");
			$name02 = iconv('windows-1251','utf-8',"Номер");
			$name06 = iconv('windows-1251','utf-8',"Статусы");
			$name07 = iconv('windows-1251','utf-8',"Статус");
			$name08 = iconv('windows-1251','utf-8',"Партнер");
			$name09 = iconv('windows-1251','utf-8',"Событие");
			$name10 = iconv('windows-1251','utf-8',"ЗначениеРеквизита");
			$name11 = iconv('windows-1251','utf-8',"Наименование");
			$name12 = iconv('windows-1251','utf-8',"Значение");
			$to_bd = array();
			$delete_nakl = array();
			foreach($xml->$name01 as $xmlNakl)
			{
				foreach ($xmlNakl->$name06->$name07 as $xmlStat)
				{
					$ardata_NUMBER = trim(iconv('utf-8','windows-1251',(string)$xmlNakl->$name02));
					$ardata_EVENT = iconv('utf-8','windows-1251',(string)$xmlStat->$name09);
					$ardata_DATE = $ardata_COMMENT = '';
					foreach ($xmlStat->$name10 as $xmlReqv)
					{
						$nameReqv = iconv('utf-8','windows-1251',(string)$xmlReqv->$name11);
						if ($nameReqv == 'ДатаСобытия')
						{
							$ardata_DATE = iconv('utf-8','windows-1251',(string)$xmlReqv->$name12);
						}
						if ($nameReqv == 'КодИсключительнойСитуации')
						{
							$ardata_COMMENT = iconv('utf-8','windows-1251',(string)$xmlReqv->$name12);
						}
					}
					$to_bd[] = "(NULL, '".$ardata_NUMBER."', '".$arUserID_n."', '".$ardata_EVENT."', '".ConvertDateTime($ardata_DATE, "YYYY-MM-DD HH:MI:SS")."', '".$ardata_COMMENT."', '".date('Y-m-d H:i:s')."')";
				}
				$delete_nakl[] = "'".$ardata_NUMBER."'";
			}
			if (count($delete_nakl) > 0)
			{
				$sql = "DELETE FROM `".$arParams["TABLE_NAME"]."` WHERE  `OVERHEAD` IN (".implode(", ",$delete_nakl).") AND `USER_ID` = ".$arUserID_n;
				$rs = $DB->Query($sql);
			}
			if (count($to_bd) > 0)
			{
				$sql = "INSERT INTO `".$arParams["TABLE_NAME"]."` (`ID`, `OVERHEAD`, `USER_ID`, `EVENT`, `DATE`, `DESCRIPTION`, `DATE_CREATE`) VALUES ";
				$sql .= implode(", ", $to_bd);
				$rs = $DB->Query($sql);
			}
		
			echo "success\n",GetMessage("CC_BSC1_IMPORT_SUCCESS");
			$_SESSION["BX_CML2_IMPORT"] = array(
				"zip" => $_SESSION["BX_CML2_IMPORT"]["zip"],
				"NS" => array(
					"STEP" => 0,
				),
				"SECTION_MAP" => false,
				"PRICES_MAP" => false,
			);
				
		}
		else
		{
			echo "failure\n",GetMessage("ERR_READ_FILE");
		}
	}
	
	elseif ($_GET["mode"] == "init")
	{
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].$DIR_NAME."/");
		if(!is_dir($_SERVER["DOCUMENT_ROOT"].$DIR_NAME))
		{
			echo "failure\n",GetMessage("CC_BSC1_ERROR_INIT");
		}
		else
		{
			$_SESSION["BX_CML2_IMPORT"] = array(
				"zip" => $arParams["USE_ZIP"] && function_exists("zip_open"),
				"NS" => array(
					"STEP" => 0,
				),
				"SECTION_MAP" => false,
				"PRICES_MAP" => false,
			);
			echo "zip=".($_SESSION["BX_CML2_IMPORT"]["zip"]? "yes": "no")."\n";
			echo "file_limit=".$arParams["FILE_SIZE_LIMIT"]."\n";
		}
	}
	else
	{
		echo "failure\n",GetMessage("CC_BSC1_ERROR_UNKNOWN_COMMAND");
	}
}

$contents = ob_get_contents();
ob_end_clean();

if($DIR_NAME != "")
{
	$ht_name = $_SERVER["DOCUMENT_ROOT"].$DIR_NAME."/.htaccess";
	file_put_contents($ht_name, "Deny from All");
	@chmod($ht_name, BX_FILE_PERMISSIONS);
}

if(!$bDesignMode)
{
	if(toUpper(LANG_CHARSET) != "WINDOWS-1251")
		$contents = $APPLICATION->ConvertCharset($contents, LANG_CHARSET, "windows-1251");
	header("Content-Type: text/html; charset=windows-1251");

	echo $contents;
	die();
}
?>