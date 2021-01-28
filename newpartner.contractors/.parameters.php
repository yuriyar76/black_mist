<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"CACHE_TIME" => Array("DEFAULT"=>"0"),
		"TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => "Тип",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				259 => 'Отправители',
				260 => 'Получатели'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
		"TYPE_WHO" => array(
			"PARENT" => "BASE",
			"NAME" => "Чьи контрагенты",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				53 => 'Агента',
				242 => 'Клиента'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
	)
);
?>
