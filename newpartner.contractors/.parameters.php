<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"CACHE_TIME" => Array("DEFAULT"=>"0"),
		"TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => "���",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				259 => '�����������',
				260 => '����������'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
		"TYPE_WHO" => array(
			"PARENT" => "BASE",
			"NAME" => "��� �����������",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				53 => '������',
				242 => '�������'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
	)
);
?>
