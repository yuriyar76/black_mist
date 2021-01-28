<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}?>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-danger fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["ERRORS"]);?>
    </div>
    <?
}
if (count($arResult["MESSAGE"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-success fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["MESSAGE"]);?>
    </div>
    <?
}
if (count($arResult["WARNINGS"]) > 0)
{
	?>
    <div class="alert alert-dismissable alert-warning fade in" role="alert">
    	<button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
		<?=implode('</br>',$arResult["WARNINGS"]);?>
    </div>
    <?
}
if ($arResult['OPEN']) :?>
<div class="row">
	<div class="col-md-12">
		<div class="btn-group">
			<div class="btn-group" role="group">
				<a href="<?=$arParams['LINK'];?>index.php?mode=add" class="btn btn-warning" id="new_btn"><span class="glyphicon glyphicon-file" aria-hidden="true"></span> Новая накладная</a>
			</div>
			<div class="btn-group" role="group">
				<a href="<?=$arParams['LINK'];?>index.php" class="btn btn-default" data-toggle="tooltip" data-placement="bottom"  title="Список накладных">
				<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Список накладных
			</a>
			</div>
		</div>

	</div>
</div>
<div class="row">
	<div class="col-md-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-md-4">
		<div class="well">
			<form method="post" action="" enctype="multipart/form-data">
				<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
				<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
				<div class="form-group">
					<label for="fileupload">Файл XML</label>
					<input type="file" id="fileupload" name="fileupload">
				</div>
				<button type="submit" class="btn btn-primary" name="upload">Загрузить</button>
			</form>
		</div>

        <div class="well">
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="rand" value="<?=random_int(100000,999999);?>">
                <input type="hidden" name="key_session" value="key_session_<?=random_int(100000,999999);?>">
                <div class="form-group">
                    <label for="fileupload_ex">Файл EXCEL</label>
                    <input type="file" id="fileupload_ex" name="fileupload_ex">
                </div>
                <?php if($arResult['CURRENT_CLIENT'] == ID_ABSOLUT):?>
                    <a href="/upload/exel-blanks/absinvoices.xlsx" class="btn btn-default"
                       data-toggle="tooltip" data-placement="bottom"
                       title="Скачать образец файла в формате excel">
                        <small>Скачать образец файла</small>
                        <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                    </a>
                <?php else:?>
                    <a href="/upload/exel-blanks/invoices.xlsx" class="btn btn-default"
                       data-toggle="tooltip" data-placement="bottom"
                       title="Скачать образец файла в формате excel">
                        <small>Скачать образец файла</small>
                        <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                    </a>
                <?php endif;?>

                <button type="submit" class="btn btn-primary" name="upload_ex">Загрузить</button>
            </form>
        </div>

	</div>
	<div class="col-md-8">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                   aria-controls="home" aria-selected="true"><strong>Файл для загрузки в формате XML.</strong></a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                   aria-controls="profile" aria-selected="false"><strong>Файл для загрузки в формате EXCEL.</strong></a>
            </li>

        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <br>
                <p><a href="/upload/iblock/f50/upload_example.xml" target="_blank">Пример структуры файла для загрузки</a>, где:</p>
                <p>
                    <strong>Sheeper</strong> - секция отправителя<br>
                    <strong>ShipperFIO</strong> - Фамилия отправителя<br>
                    <strong>ShipperPhone</strong> - телефон отправителя<br>
                    <strong>ShipperCompany</strong> - компания отправителя<br>
                    <strong>ShipperCity</strong> - населенный пункт отправителя (точное соответствие перечню населенных пунктов)<br>
                    <strong>ShipperZip</strong> - индекс отправителя<br>
                    <strong>ShipperAddress</strong> - адрес отправителя
                </p>
                <p>
                    <strong>Invoice</strong> - секция описания отправления<br>
                    <strong>ConsigneeFIO</strong> - Фамилия получателя<br>
                    <strong>ConsigneePhone</strong> - Телефон получателя<br>
                    <strong>ConsigneeCompany</strong> - Компания получателя<br>
                    <strong>ConsigneeCity</strong> - населенный пункт получателя  (точное соответствие перечню населенных пунктов)<br>
                    <strong>ConsigneeZip</strong> - Индекс получателя<br>
                    <strong>ConsigneeAddress</strong> - Адрес получателя<br>
                    <strong>Weight</strong> - Вес отправления, кг<br>
                    <strong>Length</strong> - Длина отправления, см<br>
                    <strong>Height</strong> - Высота отправления, см<br>
                    <strong>Width</strong> - Ширина отправления, см<br>
                    <strong>Places</strong> - Количество мест, шт.<br>
                    <strong>PackDescription</strong> - Описание отдельных мест отправления<br>
                    <strong>TypePyas</strong> - Кто оплачивает (Отправитель, Получатель, Другой)<br>
                    <strong>TypePyasDescription</strong> - Кто оплачивает, расшифровка значения "Другой"<br>
                    <strong>Cost</strong> - К оплате, руб.<br>
                    <strong>CodCost</strong> - Сумма наложенного платежа, руб.<br>
                    <strong>DeclaredCost</strong> - Объявленная стоимость, руб.<br>
                    <strong>Payment</strong> - Тип оплаты (Наличными, По счету, Банковской картой)<br>
                    <strong>TypeDelivery</strong> - Тип доставки (Экспресс, Стандарт, Эконом)<br>
                    <strong>TypePack</strong> - Тип отправления (Документы, Не документы)<br>
                    <strong>WhoDelivery</strong> - Доставить (По адресу, До востребования, Лично в руки)<br>
                    <strong>DateDelivery</strong> - Доставить в дату (в формате ДД.ММ.ГГГ)<br>
                    <strong>TimeDelivery</strong> - Доставить до часа (в формате ЧЧ:ММ)<br>
                    <strong>SpecDelivery</strong> - Специальные инструкции<br>
                    <strong>Good</strong> - Описание товаров отправления, где <strong>Name</strong> - наименование, <strong>Amount</strong> - количество, <strong>Price</strong> - цена, <strong>Sum</strong> - сумма, <strong>SumNDS</strong> - сумма НДС, <strong>PersentNDS</strong> - ставка НДС
                </p>
            </div>
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <br>
                <p><strong>Описание основных полей файла соответствует описанию в разделе XML.</strong></p>
                <p><strong>Правила добавления товаров в отправления:</strong></p>
                <p> 1. Всего в файле предусмотрено добавление 6 мест товаров (GoodName-GoodPersentNDS5);</p>
                <p> 2. Поля товара -</p>
                <ul>
                    <li>GoodName - название,</li>
                    <li> GoodAmount - количество,</li>
                    <li>GoodPrice - цена,</li>
                    <li>GoodSum - сумма,</li>
                    <li> GoodSumNDS - сумма НДС,</li>
                    <li>GoodPersentNDS - ставка НДС</li>
                </ul>
                <p>К полям каждого следующего товара прибавляется цифровой постфикс (GoodName2 - GoodPersentNDS2).
                    Если необходимо добавить еще неограниченное количество товаров, в файл-образец необходимо добавить
                    нужное количество полей (GoodName<span style="color:red">Цифра</span> - GoodPersentNDS<span
                        style="color:red">Цифра</span>) с соответствующим цифровым постфиксом.</p>
                <p>3. PackDescription - поля для описания отправления. Всего предусмотрено 6 описаний.
                    При необходимости добавления еще неограниченного числа описаний, нужно добавить в файл поля по
                    аналогии с товарами. </p>



            </div>
        </div>

        <script>
            $('#home-tab').tab('show')
        </script>
	</div>
</div>
<?/*if ($arResult["FILE_ID"]) :?>
			<form method="post" action="">
				<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
				<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
				<input type="hidden" name="fileid" value="6839">
				<button type="submit" class="btn btn-primary" name="upload">Продолжить</button>
			</form>
<?endif;*/?>
<?endif;?>