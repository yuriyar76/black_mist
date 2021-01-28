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
?>
<script type="text/javascript">
function setChecked(obj) 
   {
   var str = document.getElementById("text").innerHTML;
   str = (str == "מעלועטעü" ? "סםÿעü" : "מעלועטעü");
   document.getElementById("text").innerHTML = str;
   
   var check = document.getElementsByName("city_del[]");
   for (var i=0; i<check.length; i++) 
      {
      check[i].checked = obj.checked;
      }
   }
</script>
<form action="" method="post">
<p>
      <input type="checkbox" name="set" onclick="setChecked(this)" /> 
      <span id="text">מעלועטעü</span> גסו
</p>
<?
foreach ($arResult["HTML"]["CITIES"] as $key => $value) {
	?>
    <input type="checkbox" name="city_del[]" value="<?=$key;?>" id="city_del<?=$key;?>"> <label for="city_del<?=$key;?>"><?=$value;?></label><br>
    <?
}
?>
<input type="submit" value=" " class="save" name="save">
</form>