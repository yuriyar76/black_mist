<?php


class IndexComponent
{
    const IBLOCK_Z = 113;
    const IBLOCK_I = 83;

    public  function __construct()
    {
        global $USER;
        CModule::IncludeModule("iblock");
        ini_set("soap.wsdl_cache_enabled", "0" );
        ini_set("default_socket_timeout", "300");
    }
}