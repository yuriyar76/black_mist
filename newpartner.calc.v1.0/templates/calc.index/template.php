
<?/*
echo "Итого тариф: <br>";
if($arResult['TARIF_ITOG']>0){
    dump($arResult['TARIF_ITOG']);
}else{
    echo "Расчет невозможен. Для расчета по этим направлениям просьба обратиться к менеджерам компании по телефонам - +7 495 663-99-18
8 800 55-123-89";
}
*/
use Bitrix\Main\Localization\Loc; ?>
<div class="frame main_block color3">
    <div class="frame-header">
        <p><?= Loc::getMessage("calculate_the_cost") ?></p>

    </div>
    <form id="calc_form" method="post" autocomplete="off">
        <div class="faq">
            ?
            <div class="o">
                <p align="center"><b><?= Loc::getMessage("Where from") ?></b></p>
                <ul>
                    <li>Начните вводить название места, <span>ИЗ</span> которого будет отправлена посылка.</li>
                    <li>Из появившегося списка населенных пунктов выберите нужный.</li>
                </ul>
            </div>
        </div>
        <div id="from_p">
            <label for="delivery_note"><?= Loc::getMessage("WHERE") ?></label>
            <input  required type="text" id="city_0" name="city_0"
                    value="<?=$arResult['SENDER']['FULLNAME']?>"
                    class="autocity" autocomplete="off">
            <span role="status" aria-live="polite" class="ui-helper-hidden-accessible">

        </span>
            <input type="hidden" id="citycode_0" name="citycode_0" value="<?=$arResult['ID_SENDER'];?>">
        </div>
        <div class="faq">
            ?
            <div class="o">
                <p align="center"><b>Заполнение поля "Куда"</b></p>
                <ul>
                    <li>Начните вводить название места, <span>В</span> который будет отправлена посылка.</li>
                    <li>Из появившегося списка населенных пунктов выберите нужный.</li>
                </ul>
            </div>
        </div>
        <div id="to_p">

            <label for="delivery_note"><?= Loc::getMessage("FROM") ?></label>
            <input  required type="text" id="city_1" name="city_1"
                    value="<?=$arResult['RECIPIENT']['FULLNAME']?>"
                    class="autocity" autocomplete="off">
            <span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span>
            <input type="hidden" id="citycode_1" name="citycode_1" value="<?=$arResult['ID_RECIPIENT'];?>">

        </div>
        <div class="faq" style="margin-top:14px;">
            ?
            <div class="o">
                <p align="center"><b><?= Loc::getMessage("PARAMETERS") ?></b></p>
                <ul>
                    <li>Укажите вес в килограммах.</li>
                    <li>Укажите точные размеры упаковки в санитметрах<span>*</span>.</li>
                </ul>
                <p>*<i>В случае превышения объемного веса над фактическим, стоимость рассчитывается по объемному весу (1м3=200кг) </i></p>
            </div>
        </div>
        <table border="0" class="gab">
            <tbody>
            <tr id="calc_th">
                <td><span><?= Loc::getMessage("HIGHT") ?></span></td>
                <td><span><?= Loc::getMessage("LONG") ?></span></td>
                <td><span><?= Loc::getMessage("WIDTH") ?></span></td>
                <td><span><?= Loc::getMessage("WEIGHT") ?></span></td>
                <td style="width:77px;"></td>
            </tr>
            <tr>
                <td><span><?= Loc::getMessage("cm") ?></span></td>
                <td><span><?= Loc::getMessage("cm") ?></span></td>
                <td><span><?= Loc::getMessage("cm") ?></span></td>
                <td><span><?= Loc::getMessage("kg") ?></span></td>
                <td style="width:77px;"></td>
            </tr>
            <?if(isset($arResult['GAB'])):?>
                <? $c = 1;?>
                <? foreach($arResult['GAB'] as $key=>$value):?>
                    <tr id="row<?=$c;?>">
                        <td ><input value="<?=$value['h'];?>" type="number" class="r1"  name="r1[]" min="0"></td>
                        <td  ><input value="<?=$value['l'];?>" type="number" class="r2"  name="r2[]" min="0"></td>
                        <td><input value="<?=$value['w'];?>" type="number" class="r3"  name="r3[]" min="0"></td>
                        <td ><input class="ves" value="<?=$value['ves'];?>" type="text"   name="ves[]"></td>
                        <td style="text-align:right;">
                            <div class="wrbt">
                                <?if($c == 1):?>
                                    <div class="place_add" onClick="return AddNewPlace(<?=$c;?>)" title="Добавить еще место">+</div>
                                <?else:?>
                                    <div class="place_delete" onClick="return DeletePlace(<?=$c;?>)" title="Удалить место">-</div>
                                <?endif;?>
                                <div class="place_add_copy" onClick="return CopyPlace(<?=$c;?>)" title="Добавить копированием">
                                    <i class="fa fa-clone" aria-hidden="true"></i>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <? $c++;?>
                <?endforeach;?>
            <?else:?>
                <tr id="row1">
                    <td  width="100"><input type="number" class="r1"  name="r1[]" min="0"></td>
                    <td  width="100"><input type="number" class="r2"  name="r2[]" min="0"></td>
                    <td  width="100"><input type="number" class="r3"  name="r3[]" min="0"></td>
                    <td  width="100"><input type="text" class="ves"  name="ves[]" value="1.00"></td>
                    <td style="text-align:right;">
                        <div class="wrbt">
                            <!--<div class="place_add" onClick="return AddNewPlace('1')" title="Добавить еще место">+</div>-->
                            <div class="place_add_copy" onClick="return CopyPlace('1')"  title="Добавить место копированием">
                                <i class="fa fa-clone" aria-hidden="true"></i>
                            </div>
                        </div>
                    </td>
                </tr>
            <?endif;?>
            </tbody>
        </table>
        <p align="center">
            <button type="submit" name="calc_sub" class="btn"  id="ok"></button>
        </p>
    </form>
</div>
