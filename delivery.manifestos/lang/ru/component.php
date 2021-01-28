<?
$MESS["INBOX_TTL"] = "Входящие манифесты";
$MESS["OUTBOX_TTL"] = "Исходящие манифесты";
$MESS["LIST_TTL"] = "Ожидаемые манифесты";
$MESS["MANIFESTS_TO_SEND_TTL"] = "Манифесты на отправку";

$MESS["MANIFESTO_ADOPTED"] = "<a href=\"/manifesty/index.php?mode=manifest&id=#ID#\">Манифест №#ID_IN#</a> успешно принят";
$MESS["MANIFESTO_REMOVED"] = "Манифест №#ID# успешно удален";
$MESS["MANIFESTO_REMOVED_HISTORY"] = "Манифест удален";

$MESS["ORDER_ACCEPTED"] = "<a href=\"/warehouse/index.php?mode=package&id=#ID#\" target=\"_blank\">Заказ №#ID_IN#</a> успешно принят на склад";

$MESS["MANIFESTO"] = "Манифест #ID#";
$MESS['FROM_1C_TTL'] = 'Выгрузка из 1с';
$MESS['ERR_NO_FOLDER'] = 'Папка дня для выгрузки отсутствует';
$MESS['ERR_NO_FILES'] = 'Файлы для загрузки отсутствуют, выполните обмен данными с 1с';

$MESS['WARN_NO_MANIFESTOS'] = 'Манифесты отсутствуют';
$MESS['WARN_ORDER_NOT_CHANGE'] = 'Заказ №#ORDER# имеет статус #STATE# (#STATE_ID#) и не изменен';
$MESS['ORDER_STATE_44'] = '<a href="/warehouse/index.php?mode=package&id=#ID#" target="_blank">Заказ №#ORDER#</a> имеет статус #STATE# (#STATE_ID#) и включен в манифест, добавлена история';
$MESS['ORDER_STATE_56'] = '<a href="/warehouse/index.php?mode=package&id=#ID#" target="_blank">Заказ №#ORDER#</a> имеет статус #STATE# (#STATE_ID#) и включен в манифест, добавлена история, изменен статус#TEXT#';

$MESS['ORDER_STATE'] = '<a href="/warehouse/index.php?mode=package&id=#ID#" target="_blank">Заказ №#ORDER#</a> включен в манифест #MAN#, изменен статус на #TEXT#';

$MESS['MAN_CREATE'] = 'Манифест <a href="index.php?mode=manifest&id=#ID#&back_url=from1c">#NAME#</a> агенту #AGENT# успешно создан';

$MESS["ERR_REPEATED_FORM"] = "Повторная отправка данных";
$MESS['NO_MANIFESTO'] = 'Манифест не найден';

$MESS["LINK_TO_MESS"] = "<p><a href=\"http://dms.newpartner.ru/messages/index.php?mode=detail&id=#ID#\" target=\"_blank\">Ссылка на данное сообщение в системе DMS</a></p>";

$MESS["SEND_MANIFEST_TTL"] = "Отправлен манифест";

$MESS["MESS_LINE"] = "<p>=====================================================================</p>";
$MESS["MESS_AUTOMATICALLY"] = "<p><i>Письмо сгенерировано автоматически и не требует ответа</i></p>";
$MESS['MESS_CARRIER_DOC'] = '<br>Перевозочный документ: #NAME#';
$MESS['MESS_CARRIER'] = '<br>Перевозчик: #NAME#';