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
	
<form action="" method="post">
<input type="hidden" name="step1" value="1" />
<input type="hidden" name="name_of_file" value="<?=time();?>">
 <p>Введите наименование прайс-листа: <input type="text" name="user_n_file" value="<?=$_POST['user_n_file'];?>"  class="inp2"></p>
        <p>Введите количество градаций признаков спеццены <span class="red">*</span>: 
        <input type="text" class="inp" name="numdoc" value="<?=intval($_POST['numdoc']);?>" ></p>
        <p>Введите количество градаций признаков по весу<span class="red">**</span>: 
        <input type="text" class="inp" name="numkg" value="<?=intval($_POST['numkg']);?>" ></p> 
   <?=$arResult["STEP_2"];?>
         <p class="red">* Например, при наличии показателй "До 0.5кг" и "от 0.5 до 1кг", значением поля является <b>2</b>.</p>
        <p class="red">** Например, при наличии показателй "Свыше 1кг", "Свыше 10кг" и "Свыше 20кг", значением поля является <b>3</b>.</p>
        <input type="submit" value=" " class="save" />
        </form>