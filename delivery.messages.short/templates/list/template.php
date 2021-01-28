<?
 if ((count($arResult["MES_TO_SHOT"]) > 0) || (count($arResult["OTHER"]) > 0)) {
	?>
    <div class="messages absolute">
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
foreach ($arResult["OTHER"] as $m) {
	echo $m;
}
?>
</div>
<?
}
?>