<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	'PARAMETERS' => array(
		'CACHE_TIME'  =>  array('DEFAULT'=>3600),
		'CACHE_TYPE' => array('DEFAULT'=>'N'),
		'MODE' => array('DEFAULT'=>false),
		'STATUS' => array(
			'NAME' => '������� �� ��������',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'Y',
			'PARENT' => 'BASE',
		),
		'STATUS_CHANCEL_NOT' => array(
			'NAME' => '�������, �� ������� ������ �������� �����',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'Y',
			'PARENT' => 'BASE',
		),
		'STATUS_DELEETE_IM' => array(
			'NAME' => '������� �������� ��',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'Y',
			'PARENT' => 'BASE',
		),
		'STATUS_TO_DELIVERY_IM' => array(
			'NAME' => '������� �������� �� �������� ��',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'Y',
			'PARENT' => 'BASE',
		),
		'STATUS_PICKUP_IM' => array(
			'NAME' => '������� ������ ������� ��',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'Y',
			'PARENT' => 'BASE',
		),
		'STATUS_FINAL' => array(
			'NAME' => '��������� ������',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'Y',
			'PARENT' => 'BASE',
		)
	),
);
?>