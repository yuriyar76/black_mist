<?php
function getData($id_user, $type, $component_id){

    $arFilter = [
        'PROPERTY_966' => $id_user,
        'PROPERTY_967' => $type,
        'IBLOCK_ID' => 114,
        'ACTIVE' => 'Y',
    ];
    $arSelect = [
        "NAME",
        "DATE_CREATE",
        "IBLOCK_ID",
        "ID",
        "PROPERTY_*",
    ];

    $arList = GetInfoArr(false, false, $component_id, $arSelect, $arFilter, false );

    return $arList;
}

