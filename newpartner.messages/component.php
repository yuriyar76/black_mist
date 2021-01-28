<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$currentip = GetSettingValue(683);
if (!strlen(trim($currentip))) 
{
	?>
    <div class="alert alert-dismissable alert-danger fade in" role="alert"> Не указан IP для подключения к 1с
        <br> Пожалуйста, обратитесь к администратору </div>
    <?
}
else
{
    $url = 'http://'.$currentip.'/sd_msk/ws/DashboardExchange.1cws?wsdl';
	$curl = curl_init();
	curl_setopt_array($curl, array(    
		CURLOPT_URL => $url,
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_NOBODY => true,
		CURLOPT_TIMEOUT => 10));
	
	$header = explode("\n", curl_exec($curl));
	curl_close($curl);
	if ((strlen($header[0])) && ($header[0] != 'HTTP/1.1 500 Internal Server Error') && ($header[0] != 'HTTP/1.1 401 Unauthorized'))
	{
        ini_set("soap.wsdl_cache_enabled", "0" );
		$arResult['OPEN'] = true;
		$arResult['ADMIN_AGENT'] = false;
        $modes = array(
			'inbox'
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
        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        $arResult['USER_NAME'] = $USER->GetFullName();
        $agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
        $arResult['AGENT'] = GetCompany($agent_id);
        if ($agent_type == 51)
        {
            $arResult['ADMIN_AGENT'] = true;
        }
        if ($mode == 'inbox')
        {
            $mess = ListOfMasages(0, $agent_id, '', 0, 0, 92, false);
            $arResult["MESS"] = array();
            foreach ($mess as $m)
            {
                if (!isset( $arResult["MESS"][$m["PROPERTY_COMMENT_VALUE"]]['NEW']))
                {
                     $arResult["MESS"][$m["PROPERTY_COMMENT_VALUE"]]['NEW'] = 0;
                }
                if (!isset( $arResult["MESS"][$m["PROPERTY_COMMENT_VALUE"]]['NEW_IDS']))
                {
                    $arResult["MESS"][$m["PROPERTY_COMMENT_VALUE"]]['NEW_IDS'] = array();
                }
                $arResult["MESS"][$m["PROPERTY_COMMENT_VALUE"]]['MESSAGES'][] = $m;
                if ($m["ACTIVE"] == 'Y')
                {
                    $arResult["MESS"][$m["PROPERTY_COMMENT_VALUE"]]['NEW_IDS'][] = $m['ID'];
                     $arResult["MESS"][$m["PROPERTY_COMMENT_VALUE"]]['NEW']++;
                }
            }
        }
    }
    else
    {
        $mode = 'close';
    }
}
$this->IncludeComponentTemplate($mode);


/*

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

*/