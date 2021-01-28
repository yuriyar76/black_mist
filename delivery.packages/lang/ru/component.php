<?php
$MESS["MESS_NEW_MAN_CREATE"] = "<a href=\"/manifesty/index.php?mode=manifest&id=#ID#\">Манифест #NUMBER#</a> успешно создан.";
$MESS["MESS_PACK_ADD_TO_MAN"] = "Заказ №#ID# успешно добавлен к манифесту. Агенту за доставку: #SUMM#, вознаграждение за кассовое обслуживание: #PERSENT#";
$MESS["MESS_PACK_ACCEPT"] = '<a href="/warehouse/index.php?mode=package&id=#ID#">Заказ №#NUMBER#</a> успешно принят на склад';
$MESS["MESS_PACK_CHANGE_WEIGHT"] = 'Габариты <a href="/warehouse/index.php?mode=package&id=#ID#">заказа №#NUMBER#</a> изменены. Соимость доставки для магазина составляет #SUMM#';
$MESS["MESS_PACK_NOT_ACCEPT"] = "Заказ №#ID# отклонен";
$MESS["MESS_UPLOAD_LIST"] = "Список заказов успешно загружен";
$MESS["MESS_UPLOAD"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\">Заказ №#NUMBER#</a> успешно загружен";
$MESS["MESS_PACK_GO_DELIVERY"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\">Заказ №#NUMBER#</a> успешно передан на доставку";
$MESS["MESS_PACK_GO_FORM"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\">Заказ №#N_ZAKAZ#</a> успешно передан на формирование";
$MESS["MESS_DELETE_PACK"] = "Заказ №#ID# успешно удален";
$MESS["MESS_PACK_CREATE"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\">Заказ №#N_ZAKAZ#</a> успешно оформлен";
$MESS["MESS_PACK_CHANGE"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\">Заказ №#NUMBER#</a> успешно изменен";
$MESS["MESS_PACK_DELIVERED"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\">Заказ №#NUMBER#</a> успешно доставлен";
$MESS["MESS_BALANCE"] = "Баланс внутреннего счета  составляет #SUMM#";
$MESS["MESS_PERMIT_DELIVERY"] = "Заказ №#ID# успешно выдан на доставку";
$MESS["MESS_TO_PVZ"] = "Заказ №#ID# успешно перемещен в ПВЗ #PVZ#";

$MESS["ERR_OGR_DEMO"] = "При демо-доступе ограничение на загрузку - 15 заказов";
$MESS["ERR_NO_CITY_AGENT"] = "Город заказа №#ID# (#CITY#) отсутствует в прайс-листах выбранного агента";
$MESS["ERR_NO_FILE"] = "Отсутвует файл прайс-листа для заказа №#ID#";
$MESS["ERR_NO_COMMENT"] = "Для отклонения заказа введите комментарий";
$MESS["ERR_COST_FOR_AGENTS"] = "Стоимость доставки <a href=\"/warehouse/index.php?mode=package&id=#ID#\">заказа №#NUMBER#</a> для агента 
	(#summ_shop_ag# = #persent_to_agent# + #summ#) выше, чем для магазина (#summ_shop_s# = #summ_shop# + #rate#)";
$MESS["ERR_XML"] = "Список заказов должен быть в формате XML";
$MESS["ERR_EDIT_1"] = "Страховая сумма заказа №#ID# исправлена на сумму заказа";
$MESS["ERR_EDIT_2"] = "Количество мест заказа №#ID# исправлено на 1";
$MESS["ERR_EDIT_3"] = 'Тип доставки заказа №#ID# исправлен на "самовывоз"';
$MESS["ERR_EDIT_4"] = "Заказ №#ID#: в указанный город доставка отсутствует, произведен запрос города";
$MESS["ERR_EDIT_5"] = "Заказ №#ID#: неверный идентификатор города. Заказ не будет загружен.";
$MESS["ERR_EDIT_6"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\">Заказ №#NUMBER#</a>: в указанный город доставка отсутствует";
$MESS["ERR_UPLOAD"] = "Ошибка загрузки файла";
$MESS["ERR_DRAFT"] = 'Невозможно передать на доставку заказ №#ID# - статус "Черновик"';
$MESS["ERR_STATUS_39"] = 'Невозможно отправить запрос на формирование заказа №#ID#, не содержащего товары. Для передачи заказа в службу доставки, пожалуйста, нажмите кнопку "Передать на доставку."';
$MESS["ERR_STATUS_116"] = 'Невозможно передать на доставку заказ №#ID#, включающий товары. Пожалуйста, отправьте запрос на формирование заказа.';
$MESS["ERR_PACK_DELETE"] = "Ошибка удаления заказа №#ID#";
$MESS["ERR_STRING_1"] = 'Не заполнено поле "Внутренний номер заказа"';
$MESS["ERR_STRING_2"] = 'Поле "Сумма к оплате" некорректно';
$MESS["ERR_STRING_3"] = 'Поле "Страховая стоимость заказа" некорректно';
$MESS["ERR_STRING_4"] = 'Страховая стоимость заказа не может быть больше суммы заказа';
$MESS["ERR_STRING_5"] = 'Поле "Вес" некорректно';
$MESS["ERR_STRING_6"] = 'Поле "Количество мест" некорректно';
$MESS["ERR_STRING_7"] = 'Не заполнено поле "Получатель"';
$MESS["ERR_STRING_8"] = 'Не заполнено поле "Номер телефона"';
$MESS["ERR_STRING_9"] = 'Не заполнено поле "Город назначения"';
$MESS["ERR_STRING_10"] = 'Не заполнено поле "Адрес доставки"';
$MESS["ERR_STRING_11"] = 'Не выбраны условия доставки';
$MESS["ERR_STRING_12"] = 'Поле "Стоимость заказа" некорректно';
$MESS["ERR_STRING_13"] = 'Поле "Стоимость доставки" некорректно';
$MESS["ERR_STRING_14"] = 'Одно или несколько полей "Габариты" некорректно';
$MESS['ERR_STRING_15'] = 'Поле "Агентское вознаграждение" некорректно';
$MESS['ERR_STRING_16'] = 'Поле "Стоимость доставки для магазина" некорректно';
$MESS['ERR_STRING_17'] = 'Поле "Сумма за возврат" некорректно';
$MESS['ERR_STRING_18'] = 'Поле "Стоимость формирования заказа" некорректно';
$MESS["ERR_NO_COURIER"] = "Не выбран курьер";
$MESS["ERR_MAKE_1"] = "В указанный город доставка отсутствует";

$MESS["ERR_NO_COUNT"] = "Товар(ы) №#IDS# отсутствуют на складе в необходимом количестве";

$MESS["TTL_ACCEPTANCE_SHIPMENTS"] = "Принятие заказов";
$MESS["TTL_DISTRIBUTION_AGENTS"] = "Распределение по агентам";
$MESS["TTL_WAREHOUSE"] = "Заказы на складе";
$MESS["TTL_ARCHIVE_SHIPMENTS"] = "Архив заказов";
$MESS["TTL_PACK"] = "Заказ №#ID#";
$MESS["TTL_PACK_OF_SHOP"] = "Заказ №#ID#, &quot;#SHOP#&quot;";
$MESS["TTL_PACK_NOT"] = "Заказ не найден";
$MESS["TTL_LIST_PACK"] = "Список заказов";
$MESS["TTL_MAKE_PACK"] = "Оформление нового заказа";
$MESS["TTL_DEPARTURE_DELIVERY"] = "Заказы у курьеров";
$MESS["TTL_CONSIGNMENT_DELIVERED"] = "Доставленные заказы";
$MESS["TTL_MAKE_PACKAGE_LIST"] = "Заказы на подготовке";
$MESS["TTL_FORMATION"] = "Заказы на формировании";
$MESS["TTL_PODS"] = "Ввод ПОДов";

$MESS["ZAPROS_1"] = "Запрос денежных средств";
$MESS["ZAPROS_2"] = "<p>У вас запрошены денежные средства а размере <strong>#SUMM#</strong></p>";
$MESS["ZAPROS_3"] = "Запрос денежных средств у агента";

$MESS["CITY_NOT"] = "Город назначения отсутствует в системе";
$MESS["CITY_YES"] = '<br>Сохраните заказ для изменения статуса на "Ввод данных"';

$MESS["ORDER_FORMED"] = 'Заказ сформирован';
$MESS["NOT_SELECTED_AGENT"] = 'Не выбран агент';
$MESS["NOT_SELECTED_PACK"] = 'Не выбран ни один заказ';

$MESS["MESS_SAVE_PACKS"] = '<a href="/warehouse/index.php?mode=makepackageofgoods&id=#ID#">Заказ №#NUMBER#</a> успешно сохранен';

$MESS["UK_MENU_1"] = "Принятие заказов";
$MESS["UK_MENU_2"] = "Распределение по агентам";
$MESS["UK_MENU_3"] = "Заказы на складе";
$MESS["UK_MENU_4"] = "Заказы на формировании";
$MESS["UK_MENU_5"] = "Заказы на подготовке";
$MESS["UK_MENU_6"] = "Ввод ПОДов";
$MESS["UK_MENU_7"] = "Архив";
$MESS['UK_MENU_8'] = 'Передача в TopDelivery';
$MESS['UK_MENU_9'] = 'Возвраты';
$MESS['UK_MENU_10'] = 'Заказы в TopDelivery';

$MESS["AGENT_MENU_1"] = "Заказы на складе";
$MESS["AGENT_MENU_2"] = "Заказы у курьеров";
$MESS["AGENT_MENU_3"] = "Доставленные заказы";
$MESS["AGENT_MENU_4"] = "Ввод ПОДов";
$MESS['AGENT_MENU_5'] = 'Возвраты';

$MESS["ERR_REPEATED_FORM"] = "Повторная отправка формы";

$MESS["SUBMIT_FOR_DELIVERY_TEXT"] = '<p>#DATE_SEND# <a href="http://dms.newpartner.ru/warehouse/index.php?mode=package&id=#ID#">заказ №#NUMBER#</a> сформирован и передан на доставку.</p>';
$MESS["SUBMIT_FOR_DELIVERY_NAME"] = "Заказ сформирован и передан на доставку";

$MESS["MESS_DELETE"] = "Заказы успешно удалены";

$MESS["MANIFEST_ID"] = "Манифест №#ID#";

$MESS["ACCESS_DENIED"] = "Доступ запрещен";

$MESS["INCORRECT_STATUS_CANCELLATION"] = "Неверный статус аннулирования <a href=\"/warehouse/index.php?mode=package_edit&id=#ID#\" target=\"_blank\" >заказа №#NUMBER#</a>";
$MESS["INCORRECT_STATUS_DELETE"] = "Неверный статус удаления <a href=\"/warehouse/index.php?mode=package_edit&id=#ID#\" target=\"_blank\" >заказа №#NUMBER#</a>";
$MESS["INCORRECT_STATUS_SEND"] = "Неверный статус перадачи <a href=\"/warehouse/index.php?mode=package_edit&id=#ID#\" target=\"_blank\" >заказа №#NUMBER#</a> на доставку";
$MESS["INCORRECT_STATUS_RETURN"] = "Неверный статус возврата <a href=\"/warehouse/index.php?mode=package_edit&id=#ID#\" target=\"_blank\" >заказа №#NUMBER#</a>";

$MESS["INCORRECT_MESSAGE_TO_UK"] = "<p>#DATE# интернет-магазин <a href=\"http://dms.newpartner.ru/shops/index.php?mode=shop&id=#SHOP_ID#\">#SHOP_NAME#</a> отправил запрос аннулирование заказа №<a href=\"http://dms.newpartner.ru/warehouse/index.php?mode=package&id=#ID#\">#NUMBER#</a>.</p>";
$MESS["INCORRECT_MESSAGE_TO_UK_TITLE"] = "Запрос аннулирования заказа";
$MESS["INCORRECT_SEND_SUCCESS"] = "Запрос на аннулирования <a href=\"/warehouse/index.php?mode=package_edit&id=#ID#\" target=\"_blank\">заказа №#NUMBER#</a> успешно отправлен";

$MESS["RETURN_MESSAGE_TO_UK_TITLE"] = "Запрос возврата заказа";
$MESS["RETURN_SEND_SUCCESS"] = "Запрос возврата <a href=\"/warehouse/index.php?mode=package_edit&id=#ID#\" target=\"_blank\">заказа №#NUMBER#</a> успешно отправлен";

$MESS["ISSUED"] = "#DATE# <a href=\"/warehouse/index.php?mode=package_edit&id=#ID#\" target=\"_blank\">заказ №#NUMBER#</a> выдан получателю";
$MESS["ISSUED_BY_COURIER"] = "<p>#DATE# <a href=\"/warehouse/index.php?mode=package_edit&id=#ID#\" target=\"_blank\">заказ №#NUMBER#</a> выдан получателю курьером #CUR#</p>";
$MESS["ISSUED_BY_COURIER_TITLE"] = "Заказ №#NUMBER# доставлен";
$MESS["BY_COURIER"] = " курьером #FIO#";
$MESS["BY_PVZ"] = " из ПВЗ #PVZ#";

$MESS["TRANS_NAME_1"] = "Оплата получателя";
$MESS["CASH"] = "Наличные";

$MESS["MOVED_TO_WAREHOUSE"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\" target=\"_blank\">Заказ №#NUMBER#</a> перемещен на склад";
$MESS["MARKED_AS_RETURNED"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\" target=\"_blank\">Заказ №#NUMBER#</a> помечен на возврат";

$MESS["FROM"] = "c #TIME#";
$MESS["TO"] = "до #TIME#";

$MESS["PVZ_CHANGE"] = "ПВЗ <a href=\"/warehouse/index.php?mode=package&id=#ID#\" target=\"_blank\">заказа №#NUMBER#</a> изменен";

$MESS["CANCELLATION"] = "Аннулирование №#ID#";
$MESS["CANCELLATION_TEXT"] = "<p>#DATE# <a href=\"/warehouse/index.php?mode=package&id=#ID#\" target=\"_blank\">заказ №#NUMBER#</a> аннулирован.</p>";
$MESS["CANCELLATION_TITLE"] = "Заказ аннулирован";
$MESS["CANCELLATION_SUCCESS"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\" target=\"_blank\">Заказ №#NUMBER#</a> успешно аннулирован";

$MESS["EXCEPTIONAL_SITUATION_TITLE"] = "Исключительная ситуация по заказу №#NUMBER#";
$MESS["EXCEPTIONAL_SITUATION_TEXT"] = "<p>#DATE# исключительная ситуация по <a href=\"/warehouse/index.php?mode=package&id=#ID#\" target=\"_blank\">заказу №#NUMBER#</a>:<strong> #OPER#</strong>.</p>";

$MESS["ADD_CITY_TITLE"] = "Запрос на добавление города";

$MESS["ORDER"] = "Заказ №#NUMBER#";
$MESS["YES"] = "да";
$MESS["NO"] = "нет";
$MESS["LIMIT_OVER"] = "Превышен лимит заказов";

$MESS["MSD_NAME"] = "ООО \"МСД\"";

$MESS["FORMATION_TEXT_WHEN"] = "<strong>Доставить</strong>: #WHEN#<br>";
$MESS["FORMATION_TEXT_COMMENT"] = "<strong>Комментарий к заказу</strong>: #COMMENT#<br>";
$MESS["FORMATION_TEXT_URGENCY"] = "<strong>Срочность заказа</strong>: да, двойной тариф<br>";
$MESS["FORMATION_TITLE"] = "Запрос формирования заказа";
$MESS["FORMATION_INFO"] = " (формируется из забора)";

$MESS["PICKUP"] = "Самовывоз";

$MESS["UPLOAD_FIELD_0"] = "КоммерческаяИнформация";
$MESS["UPLOAD_FIELD_1"] = "Документ";
$MESS["UPLOAD_FIELD_2"] = "Номер";
$MESS["UPLOAD_FIELD_3"] = "Сумма";
$MESS["UPLOAD_FIELD_4"] = "СуммаДоставки";
$MESS["UPLOAD_FIELD_5"] = "СтрахСумма";
$MESS["UPLOAD_FIELD_6"] = "Вес";
$MESS["UPLOAD_FIELD_7"] = "Места";
$MESS["UPLOAD_FIELD_8"] = "Контрагент";
$MESS["UPLOAD_FIELD_9"] = "ФИО";
$MESS["UPLOAD_FIELD_10"] = "Телефон";
$MESS["UPLOAD_FIELD_11"] = "Доставка";
$MESS["UPLOAD_FIELD_12"] = "Тип";
$MESS["UPLOAD_FIELD_13"] = "Город";
$MESS["UPLOAD_FIELD_14"] = "Адрес";
$MESS["UPLOAD_FIELD_15"] = "Длина";
$MESS["UPLOAD_FIELD_16"] = "Ширина";
$MESS["UPLOAD_FIELD_17"] = "Высота";
$MESS["UPLOAD_FIELD_18"] = "Стоимость";
$MESS["UPLOAD_FIELD_19"] = "КассовоеОбслуживание";
$MESS["UPLOAD_FIELD_20"] = "Комментарий";
$MESS["UPLOAD_FIELD_21"] = "Срочный";
$MESS["UPLOAD_FIELD_22"] = "ДатаВремя";

$MESS["ERR_INPUT_CITY"] = "Не заполнены поля Отправиттель-Получатель";


$MESS["LINK_TO_MESS"] = "<p><a href=\"http://dms.newpartner.ru/messages/index.php?mode=detail&id=#ID#\" target=\"_blank\">Ссылка на данное сообщение в системе DMS</a></p>";

$MESS["RECEIPT_TITLE"] = "Поступление заказов";

$MESS["NEW_ORDER_BTN"] = "Новый заказ";
$MESS["UPLOAD_ORDERS_BTN"] = "Загрузить заказы";

$MESS['ERR_NO_ORDERS_CHECK'] = 'Не выбрано ни одного заказа';
