<?php


class IndexComponent
{
    public  function __construct()
    {
        CModule::IncludeModule("iblock");
        ini_set("soap.wsdl_cache_enabled", "0" );
        ini_set("default_socket_timeout", "300");
    }
}