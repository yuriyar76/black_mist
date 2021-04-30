<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arComponentParameters = array(
	"GROUPS" => array(
		"ADDITIONAL" => array(
    		"NAME" => "Дополнительно",
    		"SORT" => 1000,
    	)
	),
	"PARAMETERS" => array(
		"CACHE_TIME" => Array("DEFAULT"=>"0"),
		"TYPE" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "Тип заявок",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				53 => 'Агентские',
				242 => 'Клиентские'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
		"REGISTRATION" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "Регистрация заявок",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				1 => 'По отправке',
				2 => 'По созданию'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
		"LINK" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "Ссылка на раздел",
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
		'RESTRICTION_SENDERS' => array(
			'NAME' => 'Создание заявок без добавления отправителя',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "ADDITIONAL"
		)
	)
);
?>
