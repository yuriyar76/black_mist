<?
if (count($arResult["ERRORS"]) > 0) 
	echo '
		<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';

if (count($arResult["MESSAGE"]) > 0) 
	echo '
		<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
else {
	if (count($arResult["WARNINGS"]) > 0) 
	echo '
		<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}

 if (count($arResult["LIST"]) > 0):
 ?>
 <form action="" method="post" enctype="multipart/form-data">
 <table cellpadding="5" cellspacing="0" border="1" style="border-collapse:collapse;" class="list"><thead>
 <tr><td><?=GetMessage("MESS_1");?></td>
 <td><?=GetMessage("MESS_2");?></td>
 <td><?=GetMessage("MESS_3");?></td>
 <td><?=GetMessage("MESS_4");?></td>
 <td><?=GetMessage("MESS_5");?></td>
 <td><?=GetMessage("MESS_6");?></td>
 <td colspan="4"><?=GetMessage("MESS_7");?></td>
 <td><?=GetMessage("MESS_8");?></td></tr>
 </thead><tbody>
 <?
 foreach($arResult["LIST"] as $r):
 ?>
 <tr><td><?=$r["ID"];?></td><td><input type="text" value="<?=$r["NAME"];?>" name="name[<?=$r["ID"];?>]"></td>
 <td><?=$r["DATE_CREATE"];?></td>
 <td><input type="text" value="<?=$r["SORT"];?>" name="sort[<?=$r["ID"];?>]" style="width:60px;"></td><td>
 <select name="active[<?=$r["ID"];?>]" size="1">
 <option value="Y" <? $y = ($r["ACTIVE"] == 'Y') ? 'selected' :  ''?> <?=$y;?>><?=GetMessage("MESS_12");?></option>
  <option value="N" <? $y = ($r["ACTIVE"] == 'N') ? 'selected' :  ''?> <?=$y;?>><?=GetMessage("MESS_13");?></option>
  </select>
 </td><td><a href="<?=CFile::GetPath($r["PROPERTY_FILE_VALUE"]);?>" target="_blank"><?=GetMessage("MESS_14");?></a></td>
 <td>
 <a href="<?=$APPLICATION->GetCurPageParam("state=edit&price_id=".$r['ID'], array("state"));?>"><?=GetMessage("MESS_15");?></a></td>
  <td><a href="<?=$APPLICATION->GetCurPageParam("state=edit_structure&price_id=".$r['ID'], array("state"));?>"><?=GetMessage("MESS_16");?></a></td>
 <td><a href="<?=$APPLICATION->GetCurPageParam("state=add_city&price_id=".$r['ID'], array("state"));?>"><?=GetMessage("MESS_17");?></a></td>
 <td><a href="<?=$APPLICATION->GetCurPageParam("state=delete_city&price_id=".$r['ID'], array("state"));?>"><?=GetMessage("MESS_18");?></a></td>
 <td style="text-align:center;"><input type="checkbox" name="del_file[]" value="<?=$r["ID"];?>"></td>
 </tr>
 <?
 endforeach;
 ?>
 </tbody></table>
 <br>
 <input type="submit" name="save" value=" " class="save">
 </form>
 <?
endif;
?>
<br>
<p><a href="<?=$APPLICATION->GetCurPageParam("state=create", array("state"));?>"><?=GetMessage("MESS_10");?></a></p>

<?
if($arParams["UPLOAD"] == "Y") {
	?>
    <form action="" method="post" enctype="multipart/form-data"><?=GetMessage("MESS_11");?> <?=CFile::InputFile("file_price", 20);?> <input type="submit" name="upload" value="<?=GetMessage("MESS_9");?>" class="upload" /> </form>
    <?
}
?>