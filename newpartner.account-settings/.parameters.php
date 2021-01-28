<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"GROUPS" => array(
		"ADDITIONAL" => array(
    		"NAME" => GetMessage("GROUP_ADDITIONAL_TITLE"),
    		"SORT" => 1000,
    	)
	),
	"PARAMETERS" => array(
		"CACHE_TIME" => Array("DEFAULT"=>"0"),
		"TYPE" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("PARAMETER_TYPE_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				53 => GetMessage("PARAMETER_TYPE_1"),
				242 => GetMessage("PARAMETER_TYPE_2"),
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
	)
);
?>
