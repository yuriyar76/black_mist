<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>

<div class="new_menu">
    <ul>
        <li class="active"><a href="javascript:void(0);"><?=$arResult["TITLE"];?></a></li>
        <?
        foreach ($arResult["MENU"] as $k => $v)
		{
			?>
			<li><a href="index.php?mode=<?=$k?>"><?=$v?></a></li>
			<?
		}
        ?>
    </ul>
</div>
<?

if (count($arResult["ERRORS"]) > 0) echo '
	<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
if (count($arResult["MESSAGE"]) > 0) echo '
	<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';

if (intval($arResult["COMPANY"]["ID"]) > 0)
{
	?>
    <form name="iblock_add" action="" method="post" enctype="multipart/form-data">
    	<table class="borders" cellpadding="3" border="0">
        	<tbody>
				<?
                foreach ($arResult['ALL_FIELDS'] as $code => $field)
				{
					if(in_array($arResult["COMPANY_TYPE"],$field['for_types']))
					{
						?>
						<tr>
                        	<td><?=$field['label'];?><? echo (in_array($arResult["COMPANY_TYPE"],$field['reqv'])) ? '<span class="red">*</span>' : ''; ?></td>
                            <td>
								<?
								if (($field['type'] == 'text')||($field['type'] == 'inn')||($field['type'] == 'email'))
								{
									?>
									<input type="text" name="<?=$code;?>" value="<?=$arResult["COMPANY"][$code];?>">
									<?
								}
								elseif ($field['type'] == 'phone')
								{
									?>
									<input type="text" name="<?=$code;?>" value="<?=$arResult["COMPANY"][$code];?>" class="maskphone">
									<?
								}
								elseif ($field['type'] == 'city')
								{
									?>
									<input type="text" name="<?=$code;?>" value="<?=$arResult["COMPANY"][$code];?>" class="autocomplete_city">
									<?
								}
								elseif ($field['type'] == 'select')
								{
									?>
									<select name="<?=$code;?>" size="1">
									<?
									foreach ($field['values'] as $k => $v) {
										if ($arResult["COMPANY"][$code] == $k) $ch = 'selected'; else $ch = '';
										?>
										<option value="<?=$k;?>" <?=$ch;?>><?=$v;?></option>
										<?
									}
									?>
									</select>
									<?
								}
								?>
							</td>
						</tr>
						<?
					}
				}
				if (count($arResult["MAIL_SETS"]) > 0)
				{
					?>
					<tr valign="top">
                    	<td><?=GetMessage("LABEL_MAIL_SETS");?></td>
                        <td>
							<?
							 foreach ($arResult["MAIL_SETS"] as $k => $v)
							 {
								 ?>
								 <input type="checkbox" name="mail_sets[]" value="<?=$k;?>" <? echo (isset($arResult["COMPANY"]["PROPERTY_MAIL_SETTINGS_VALUE"][$k])) ? ' checked' : ''; ?>> 
								 <?=$v;?> <br>
								 <?
							}
							?>
						</td>
					</tr>
					<?
				}
				?>
			</tbody>
		</table>
		<input type="submit" name="save" value="<?=GetMessage("IBLOCK_FORM_SUBMIT");?>">
	</form>
	<?
	if (($arResult["COMPANY_TYPE"] == 51) && ($USER->IsAdmin()))
	{
		?>
        <div style="margin-top:10px; border-top:1px dashed #ccc;"></div>
        <form action="" method="post">
        	<select name="company_id" size="1">
            	<?
				foreach ($arResult["ALL_UKS"] as $k => $v)
				{
					?>
                    <option value="<?=$k?>"><?=$v;?></option>
                    <?
				}
				?>
            </select>
        	<input type="submit" name="change_company" value="<?=GetMessage("CHANGE_COMPANY_BTN");?>">
        </form>
        <?
	}
}
?>
