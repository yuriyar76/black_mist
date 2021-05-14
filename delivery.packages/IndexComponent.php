<?php


class IndexComponent
{
    const IBLOCK_Z = 113;
    const IBLOCK_I = 83;
    public $user = [];

    public  function __construct()
    {
        CModule::IncludeModule("iblock");
        CModule::IncludeModule("main");
        ini_set("soap.wsdl_cache_enabled", "0" );
        ini_set("default_socket_timeout", "300");
        global $USER;
        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        if(!empty($arUser)){
            $this->user["USER"]["USER_ID"] = $USER->GetID();
            $this->user["USER"]['USER_NAME'] = $USER->GetFullName();
            $agent_id = (int)$arUser["UF_COMPANY_RU_POST"];
            $this->user["USER"]['COMPANY'] = NPAllFunc::GetCompany($agent_id);
            $this->user["USER"]['UK_ID'] = $this->user["USER"]['COMPANY']['PROPERTY_UK_VALUE'];
            $this->user["USER"]['SETTINGS_ID'] = $this->user["USER"]['COMPANY']['PROPERTY_SETTINGS_ID'];
        }

    }

}