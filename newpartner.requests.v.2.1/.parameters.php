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
		"TYPE" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "��� ������",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				53 => '���������',
				242 => '����������'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
		"REGISTRATION" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "����������� ������",
			"TYPE" => "LIST",
			"VALUES" => array(
				0 => "",
				1 => '�� ��������',
				2 => '�� ��������'
			),
			"MULTIPLE" => "N",
			"DEFAULT" => array()
		),
		"LINK" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "������ �� ������",
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => ""
		),
		"MODE" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => "�����",
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => ""
		),
		'RESTRICTION_SENDERS' => array(
			'NAME' => '�������� ������ ��� ���������� �����������',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "ADDITIONAL"
		)
	)
);
?>
