<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arComponentParameters = array(
	"GROUPS" => array(
		"ADDITIONAL" => array(
    		"NAME" => "�������������",
    		"SORT" => 1000,
    	)
	),
	"PARAMETERS" => array(
		"CACHE_TIME" => Array("DEFAULT"=>"0"),
		"LINK" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "������ �� ������",
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => ""
		)
	)
);
?>
