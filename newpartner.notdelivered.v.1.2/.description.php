<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MANIFESTOS_NAME"),
	"DESCRIPTION" => GetMessage("MANIFESTOS_DESC"),
	"ICON" => "/images/banner.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "newpartner",
		"NAME" => GetMessage("NEWPARTNER")
	),
);
?>