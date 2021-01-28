<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("INVOICE_NAME"),
	"DESCRIPTION" => GetMessage("INVOICE_DESC"),
	"ICON" => "/images/banner.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "newpartner",
		"NAME" => GetMessage("NEWPARTNER")
	),
);
?>