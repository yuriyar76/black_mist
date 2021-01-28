<?
foreach ($arResult["MES_TO_SHOT"] as $m) {
	if ($m["PROPERTY_TYPE_ENUM_ID"] != 83) {
	?>
    <p class="mess_<?=$m["PROPERTY_TYPE_ENUM_ID"];?>"><a href="/messages/index.php?mode=detail&id=<?=$m["ID"];?>"><?=$m["PROPERTY_TYPE_VALUE"];?> <span><?=$m["PROPERTY_COMMENT_VALUE"];?></span></a></p>
    <?
	}
	else {
		?>
        <p><?=$m["DETAIL_TEXT"];?></p>
        <?
	}
}
?>