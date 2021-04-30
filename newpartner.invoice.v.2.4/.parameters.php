<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arComponentParameters = array(
	"GROUPS" => array(
		"ADDITIONAL" => array(
    		"NAME" => "Дополнительно",
    		"SORT" => 10,
    	),
		"LINKS" => array(
    		"NAME" => "Ссылки",
    		"SORT" => 20,
    	)
	),
	"PARAMETERS" => array(
		"CACHE_TIME" => Array("DEFAULT"=>"0"),
		"TYPE" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "Тип накладных",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				53 => 'Агентские',
				242 => 'Клиентские'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
		"LINK" => array(
			"PARENT" => "LINKS",
			"NAME" => "Ссылка на список",
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => ""
		),
		"MODE" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "Режим",
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => ""
		),
	)
);
?>
