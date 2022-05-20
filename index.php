<?php
const VK_KEY = ''; //токен сообщества или пользователя
const CONFIRM_STR = ''; //ключ авторизации сообщества, который вы получили
const VERSION = ''; //ваша версия используемого api

require_once('vendor/autoload.php');
require_once('DB.php');

use DigitalStar\vk_api\vk_api;

$db = new DataBase();

$vk = vk_api::create(VK_KEY, VERSION)->setConfirm(CONFIRM_STR);

$data = $vk->initVars($id, $message, $payload, $user_id, $type); //инициализация переменных

//Определение роли человека
$role = GetRole($user_id);

//user
define('create_ticket', $vk->buttonText('Создать тикет 📝', 'green', ["command" => "create_ticket"]));
define('example_ticket', $vk->buttonText('Пример тикета 📃', 'white', ["command" => "example_ticket"]));
define('rules_ticket', $vk->buttonText('Правила составления тикета 🗒', 'red', ["command" => "rules_ticket"]));
define('faq_ticket', $vk->buttonText('F.A.Q.', 'white', ["command" => "faq_ticket"]));
define('my_ticket', $vk->buttonText('Мои тикеты ✅', 'blue', ["command" => "my_ticket"]));

//admins
define('take_ticket_admin', $vk->buttonText('Взять первый тикет в очереди', 'green', ["command" => "take_ticket_admin"]));
define('all_ticket_admin', $vk->buttonText('Все тикеты', 'blue', ["command" => "all_ticket_admin"]));
define('operators_ticket_admin', $vk->buttonText('Операторы', 'white', ["command" => "operators_ticket_admin"]));
define('admins_ticket_admin', $vk->buttonText('Администраторы', 'white', ["command" => "admins_ticket_admin"]));
define('add_operator', $vk->buttonText('Добавить оператора', 'red', ["command" => "add_operator"]));
define('add_admin', $vk->buttonText('Добавить админа', 'red', ["command" => "add_admin"]));
define('close_work', $vk->buttonText('Закончить работу', 'red', ["command" => "close_work"]));

//tickets action
define('close_ticket_admin', $vk->buttonText('Закрыть тикет ✅', 'green', ['command' => "close_ticket_admin"]));
define('dicline_ticket_admin', $vk->buttonText('Отклонить тикет ❌', 'red', ['command' => "dicline_ticket_admin"]));
define('close_ticket', $vk->buttonText('Закрыть тикет ✅', 'green', ['command' => "close_ticket"]));

//ticket menu
define('take_ticket_id', $vk->buttonText('Взять тикет по номеру', 'green', ["command" => "take_ticket_id"]));
define('opened_ticket_id', $vk->buttonText('Тикеты со статусом «Открыт»', 'blue', ["command" => "opened_ticket_id"]));
define('closed_ticket_id', $vk->buttonText('Тикеты со статусом «Закрыт»', 'green', ["command" => "closed_ticket_id"]));
define('decline_ticket_id', $vk->buttonText('Тикеты со статусом «Отклонен»', 'red', ["command" => "decline_ticket_id"]));
define('admin_menu', $vk->buttonText('Назад в меню', 'white', ["command" => "admin_menu"]));

////FOUNDER PANEL
if($message == "ADMIN START"){
    switch($role){
        case 'none':
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        case 'founder':
            $db->query("UPDATE `admins` SET `is_active` = 'true' WHERE `user` = '$user_id';");
            $vk->sendButton($user_id,
                "Здравствуйте, %full%. Вы авторизованы как: Основатель",
                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        case 'admin':
            $db->query("UPDATE `admins` SET `is_active` = 'true' WHERE `user_id` = '$user_id';");
            $vk->sendButton($user_id,
                "Здравствуйте, %full%. Вы авторизованы как: Администратор",
                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        case 'operator':
            $db->query("UPDATE `admins` SET `is_active` = 'true' WHERE `user_id` = '$user_id';");
            $vk->sendButton($user_id,
                "Здравствуйте, %full%. Вы авторизованы как: Оператор",
                $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        default:
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );+
            exit;
    }
}

if ($payload) { //если пришел payload
    //Пользователь!
    if($payload['command'] == 'create_ticket'){ // Создать тикет
        if($db->query("INSERT INTO tickets (message_id, user_id, message, status) VALUES ('$id', '$user_id', '', 'formation');")){
            $vk->sendButton($user_id, "Создайте тикет ОДНИМ сообщением так, как это написано в примере. Постарайтесь максимально описать суть проблемы, чтобы мы могли быстрее помочь Вам!");
            exit;
        }
    }
    if($payload['command'] == 'start'){
        $vk->sendButton($user_id, "Здравствуйте, %full%.<br>Выберите пунк меню технической поддержки NetherCraft Project", $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]], $inline = false, $one_time = true, $params = []);
    }
    if($payload['command'] == 'example_ticket') { // пример тикета
        $vk->sendButton($user_id,
            "Пример тикета:<br>1. Ваш никнейм на сервере<br>2. Тема вопроса<br>3. Дата и время появления проблемы<br>4. Описание проблемы<br>5. Скриншот ошибки(если имеется)<br>6. Квитанция оплаты(если проблема связанна с платежами)",
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    }
    if($payload['command'] == 'rules_ticket'){ // Правила составления тикета
        $vk->sendButton($user_id,
            "При составлении тикета запрещается:<br><br>- Оскробления<br>- Использование нецензурной брани<br>- Составлять тикет касаемо игровых моментов(все подобные вопросы или жалобы создаются на форуме)<br>- Составлять тикет, не связанный с вопросами или ошибками оплаты, а также с проблемами аккаунта<br><br>Администрация проекта NetherCraft Project оставляет за собой права игнорировать и блокировать пользователей в сообществе, нарушающих данные правила.",
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    }
    if($payload['command'] == 'faq_ticket'){ // FAQ
        $vk->sendButton($user_id,
            "❗ Наиболее часто задаваемые вопросы ❗<br>
❓ Какое среднее время ожидание ответа?
✅ Оператор отвечает на вопрос в зависимости от загруженности и времени суток. Мы стараемся отвечать на вопросы как можно быстрее. В рабочее время(10:00 - 24:00) мы страемся отвечать на тикеты в течение 2-х часов.

❓ Можно ли задавать вопросы касаемо игровых моментов?
✅ Нет, нельзя. Для этого существует форум forum.nethercraft.fun, в нем необходимо создать тему в топике, связанный с Вашим вопросом или жалобой.

❓ Кто отвечает на тикеты, которые я отправил?
✅ На тикеты отвечает персонал. В зависимости от типа вопроса персонал либо решает проблему самостоятельно, либо обращается к администраторам, уполномеченные на решение проблем более высокого уровня.

❓ Как я пойму, что мне ответил оператор?
✅ С Вами свяжется оператор в данном диалоге.

❓ Можно ли сообщать данные банковской карты, переводить деньги персоналу, если они попросили Вас сделать это?
✅ Нет, ни в коем случае не сообщайте личные данные банковских карт и не переводите деньги, если Вас об этом просят. Если возникла подобная ситуация, просьба обратиться в личные сообщения главному администратору vk.com/zytia.
",
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    }

    if($payload['command'] == 'my_ticket'){ // Мои тикеты
        $tickets = @mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `user_id` = '$user_id';"));
        if(empty($tickets)){
            $vk->sendButton($user_id,
                "Вы не создавали тикеты.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
        $message = 'Ваши тикеты:<br><br>';
        foreach($tickets as $key){
            switch ($key['4']){
                case 'opened':
                    $status = "Открыт";
                    break;
                case 'closed':
                    $status = "Закрыт";
                    break;
                case 'dicline':
                    $status = "Отклонен";
                    break;
                case 'formation':
                    $status = "Формируется";
                    break;
                default:
                    $status = "Неизвестно";
                    break;
            }
            $message .= 'Номер тикета: '.$key['0'].'<br>'.'Сообщение: '.$key['3'].'<br>'.'Статус: '.$status.'<br><br>';
        }
        $vk->sendButton($user_id,
            $message,
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    }

    //Администратор!
    if($payload['command'] == "take_ticket_admin"){ // взять тикет админу
        if($role != 'none'){
            $data = @mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `admin_id` = '' AND `status` = 'opened';"));
            if(empty($data)){
                switch($role){
                    case 'founder':
                        $vk->sendButton($user_id,
                            "Открытых тикетов не найдено.",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'admin':
                        $vk->sendButton($user_id,
                            "Открытых тикетов не найдено.",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'operator':
                        $vk->sendButton($user_id,
                            "Открытых тикетов не найдено.",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    default:
                        $vk->sendButton($user_id,
                            "У вас нет прав для выполнения данной команды.",
                            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                }
            }
            $temp = (int)$data['0']['0'];
            if($db->query("UPDATE `tickets` SET `admin_id` = '$user_id', `is_working` = 'true' WHERE `id` = '$temp';")){
                $vk->sendButton($user_id,
                    'Вы приняли тикет номер '.$data['0']['0']. '.<br><br>Ситуация пользователя: <br>'. $data['0']['3'].'<br><br>Для ответа пишите в чате.',
                    $buttons = [[close_ticket_admin], [dicline_ticket_admin]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                $vk->sendButton($data['0']['2'],
                    '✅ Ваш тикет номер '.$data['0']['0'].' принял '.GetRoleRus(GetRole($user_id)) . ' ' . GetName($vk, $user_id) . '. Сейчас он работает над Вашей проблемой, ожидайте и будьте на связи 😉',
                    $buttons = [[close_ticket]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                exit;
            }
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    if($payload["command"] == 'close_ticket'){ // закрыть тикет со стороны пользователя
        $data = @mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `user_id` = '$user_id' AND `is_working` = 'true';"));
        if (empty($data)) {
            $vk->sendButton($user_id,
                "У вас нет активных тикетов.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        } else {
            $temp_id = $data['0']['0'];
            if ($db->query("UPDATE `tickets` SET `status` = 'closed', `is_working` = 'false' WHERE `id` = '$temp_id';")) {
                $vk->sendButton($user_id,
                    "Вы закрыли тикет ✅",
                    $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                switch (GetRole($data['0']['5'])) {
                    case 'founder':
                        $vk->sendButton($data['0']['5'],
                            "Пользователь закрыл тикет ✅",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'admin':
                        $vk->sendButton($data['0']['5'],
                            "Пользователь закрыл тикет ✅",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'operator':
                        $vk->sendButton($data['0']['5'],
                            "Пользователь закрыл тикет ✅",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    default:
                        $vk->sendButton($user_id,
                            "У вас нет прав для выполнения данной команды.",
                            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                }
            }
        }
    }

    if($payload['command'] == 'close_ticket_admin'){ // закрыть тикет со стороны админа
        if(GetRole($user_id) != 'none'){
            $data = @mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `admin_id` = '$user_id' AND `is_working` = 'true';"));
            if(empty($data)) {
                switch ($role) {
                    case 'founder':
                        $vk->sendButton($user_id,
                            "У вас нет активных тикетов",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'admin':
                        $vk->sendButton($user_id,
                            "У вас нет активных тикетов",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'operator':
                        $vk->sendButton($user_id,
                            "У вас нет активных тикетов",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    default:
                        $vk->sendButton($user_id,
                            "У вас нет прав для выполнения данной команды.",
                            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                }
            } else {
                $temp_id = $data['0']['0'];
                if ($db->query("UPDATE `tickets` SET `status` = 'closed', `is_working` = 'false' WHERE `id` = '$temp_id';")) {
                    $name = $vk->userInfo($data['0']['5']);
                    $vk->sendButton($data['0']['2'],
                        GetRoleRus(GetRole($data['0']['5'])) . ' ' . GetName($vk, $data['0']['5']) .' закрыл Ваш тикет.',
                        $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    switch(GetRole($data['0']['5'])) {
                        case 'founder':
                            $vk->sendButton($user_id,
                                "Вы закрыли тикет ✅",
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                        case 'admin':
                            $vk->sendButton($user_id,
                                "Вы закрыли тикет ✅",
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                        case 'operator':
                            $vk->sendButton($user_id,
                                "Вы закрыли тикет ✅",
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                        default:
                            $vk->sendButton($user_id,
                                "У вас нет прав для выполнения данной команды.",
                                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                    }
                }
            }
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    if($payload['command'] == 'dicline_ticket_admin'){ // отклонить тикет админ
        if(GetRole($user_id) != 'none'){
            $data = @mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `admin_id` = '$user_id' AND `is_working` = 'true';"));
            if(empty($data)) {
                switch ($role) {
                    case 'founder':
                        $vk->sendButton($user_id,
                            "У вас нет активных тикетов",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'admin':
                        $vk->sendButton($user_id,
                            "У вас нет активных тикетов",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'operator':
                        $vk->sendButton($user_id,
                            "У вас нет активных тикетов",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    default:
                        $vk->sendButton($user_id,
                            "У вас нет прав для выполнения данной команды.",
                            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                }
            } else {
                $temp_id = $data['0']['0'];
                if ($db->query("UPDATE `tickets` SET `status` = 'dicline', `is_working` = 'false' WHERE `id` = '$temp_id';")) {
                    $vk->sendButton($data['0']['2'],
                        '❌ '.GetRoleRus(GetRole($data['0']['5'])) . ' ' . GetName($vk, $data['0']['5']) . ' отклонил Ваш тикет за нарушение правил.',
                        $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    switch (GetRole($data['0']['5'])) {
                        case 'founder':
                            $vk->sendButton($user_id,
                                "Вы отклонили тикет ❌",
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                        case 'admin':
                            $vk->sendButton($user_id,
                                "Вы отклонили тикет ❌",
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                        case 'operator':
                            $vk->sendButton($user_id,
                                "Вы отклонили тикет ❌",
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                        default:
                            $vk->sendButton($user_id,
                                "У вас нет прав для выполнения данной команды.",
                                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                    }
                }
            }
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    //Кнопка Все тикеты
    if($payload['command'] == 'all_ticket_admin'){
        if($role != 'none'){
            $vk->sendButton($user_id,
                "Выберите пункт меню.",
                $buttons = [[take_ticket_id], [opened_ticket_id], [closed_ticket_id], [decline_ticket_id], [admin_menu]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    //Тикеты со статусом «Открыт»
    if($payload['command'] == 'opened_ticket_id'){
        if($role != 'none'){
            $ticket = mysqli_fetch_all($db->query_select("*", "tickets", "status", "opened"));
            if(empty($ticket)){
                $vk->sendButton($user_id,
                    "Открытые тикеты отсутствуют",
                    $buttons = [[take_ticket_id], [opened_ticket_id], [closed_ticket_id], [decline_ticket_id], [admin_menu]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                exit;
            }
            $message = '';
            foreach ($ticket as $data){
                switch ($data['4']){
                    case 'opened':
                        $status = "Открыт";
                        break;
                    case 'closed':
                        $status = "Закрыт";
                        break;
                    case 'dicline':
                        $status = "Отклонен";
                        break;
                    case 'formation':
                        $status = "Формируется";
                        break;
                    default:
                        $status = "Неизвестно";
                        break;
                }
                $message .= 'Номер тикета: '.$data['0'].'<br>'.'Сообщение: '.$data['3'].'<br>'.'Статус: '.$status.'<br><br>';
            }
            $vk->sendButton($user_id,
                "Открытые тикеты:<br>".$message,
                $buttons = [[take_ticket_id], [opened_ticket_id], [closed_ticket_id], [decline_ticket_id], [admin_menu]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    //Тикеты со статусом «Закрыт»
    if($payload['command'] == 'closed_ticket_id'){
        if($role != 'none'){
            $ticket = mysqli_fetch_all($db->query_select("*", "tickets", "status", "closed"));
            if(empty($ticket)){
                $vk->sendButton($user_id,
                    "Закрытые тикеты отсутствуют",
                    $buttons = [[take_ticket_id], [opened_ticket_id], [closed_ticket_id], [decline_ticket_id], [admin_menu]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                exit;
            }
            $message = '';
            foreach ($ticket as $data){
                switch ($data['4']){
                    case 'opened':
                        $status = "Открыт";
                        break;
                    case 'closed':
                        $status = "Закрыт";
                        break;
                    case 'dicline':
                        $status = "Отклонен";
                        break;
                    case 'formation':
                        $status = "Формируется";
                        break;
                    default:
                        $status = "Неизвестно";
                        break;
                }
                $message .= 'Номер тикета: '.$data['0'].'<br>'.'Сообщение: '.$data['3'].'<br>'.'Статус: '.$status.'<br><br>';
            }
            $vk->sendButton($user_id,
                "Закрытые тикеты:<br>".$message,
                $buttons = [[take_ticket_id], [opened_ticket_id], [closed_ticket_id], [decline_ticket_id], [admin_menu]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    //Тикеты со статусом «Отклонен»
    if($payload['command'] == 'decline_ticket_id'){
        if($role != 'none'){
            $ticket = mysqli_fetch_all($db->query_select("*", "tickets", "status", "dicline"));
            if(empty($ticket)){
                $vk->sendButton($user_id,
                    "Отклоненные тикеты отсутствуют",
                    $buttons = [[take_ticket_id], [opened_ticket_id], [closed_ticket_id], [decline_ticket_id], [admin_menu]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                exit;
            }
            $message = '';
            foreach ($ticket as $data){
                switch ($data['4']){
                    case 'opened':
                        $status = "Открыт";
                        break;
                    case 'closed':
                        $status = "Закрыт";
                        break;
                    case 'dicline':
                        $status = "Отклонен";
                        break;
                    case 'formation':
                        $status = "Формируется";
                        break;
                    default:
                        $status = "Неизвестно";
                        break;
                }
                $message .= 'Номер тикета: '.$data['0'].'<br>'.'Сообщение: '.$data['3'].'<br>'.'Статус: '.$status.'<br><br>';
            }
            $vk->sendButton($user_id,
                "Отклоненные тикеты:<br>".$message,
                $buttons = [[take_ticket_id], [opened_ticket_id], [closed_ticket_id], [decline_ticket_id], [admin_menu]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    //Главное меню админа
    if($payload['command'] == 'admin_menu'){
        if($role != 'none') {
            switch ($role) {
                case 'founder':
                    $vk->sendButton($user_id,
                        "Главное меню",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                case 'admin':
                    $vk->sendButton($user_id,
                        "Главное меню",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                case 'operator':
                    $vk->sendButton($user_id,
                        "Главное меню",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                default:
                    $vk->sendButton($user_id,
                        "У вас нет прав для выполнения данной команды.",
                        $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
            }
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    //Взять тикет по id
    if($payload['command'] == 'take_ticket_id'){
        if($role != 'none') {
            if ($db->query("INSERT INTO temp_messages (user_id, status) VALUES ('$user_id', 'formation');")) {
                $vk->sendButton($user_id, "Введите номер тикета(только цифры), который Вы хотите взять. Пример: 110");
                exit;
            }
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    //Добавить админа
    if($payload['command'] == 'add_admin'){
        if($role != 'none') {
            if($role == 'founder') {
                if ($db->query("INSERT INTO temp_messages (user_id, admin_status) VALUES ('$user_id', 'formation');")) {
                    $vk->sendButton($user_id, "Введите числовой id пользователя(строка браузера после vk.com/id...), которого вы хотите добавить. Пример: 1252125");
                    exit;
                }
            }
            else{
                switch($role){
                    case 'admin':
                        $vk->sendButton($user_id,
                            "У вас нет прав для выполнения данной команды.",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'operator':
                        $vk->sendButton($user_id,
                            "У вас нет прав для выполнения данной команды.",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                }
            }
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }
    //Добавить оператора
    if($payload['command'] == 'add_operator'){
        if($role != 'none') {
            if($role == 'admin' or $role == 'founder') {
                if ($db->query("INSERT INTO temp_messages (user_id, operator_status) VALUES ('$user_id', 'formation');")) {
                    $vk->sendButton($user_id, "Введите числовой id пользователя(строка браузера после vk.com/id...), которого вы хотите добавить. Пример: 1252125");
                    exit;
                }
            }
            else{
                $vk->sendButton($user_id,
                    "У вас нет прав для выполнения данной команды.",
                    $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                exit;
            }
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }
    //Вывести операторов
    if($payload['command'] == 'operators_ticket_admin'){
        if($role != 'none') {
            if($role == 'admin' or $role == 'founder') {
                $data = @mysqli_fetch_all($db->query_select("*", "admins", "role", "operator"));
                if(!empty($data)){
                    switch($role){
                        case 'founder':
                            $message = 'Список операторов:<br><br>';
                            foreach ($data as $roles) {
                                $message .= "Имя и Фамилия оператора: " . GetName($vk, $roles['1']) .
                                    "<br>Ссылка на профиль: vk.com/id" . $roles['1'] . "<br><br>";
                            }
                            $vk->sendButton($user_id,
                                $message,
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                        case 'admin':
                            $message = 'Список операторов:<br><br>';
                            foreach ($data as $roles) {
                                $message .= "Имя и Фамилия оператора: " . GetName($vk, $roles['1']) .
                                    "<br>Ссылка на профиль: vk.com/id" . $roles['1'] . "<br><br>";
                            }
                            $vk->sendButton($user_id,
                                $message,
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                    }
                }
                else{
                    switch($role){
                        case 'founder':
                            $vk->sendButton($user_id,
                                "Список операторов пуст.",
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                        case 'admin':
                            $message = 'Список операторов:<br><br>';
                            foreach ($data as $roles) {
                                $message .= "Имя и Фамилия оператора: " . GetName($vk, $roles['1']) .
                                    "<br>Ссылка на профиль: vk.com/id" . $roles['1'] . "<br><br>";
                            }
                            $vk->sendButton($user_id,
                                "Список операторов пуст.",
                                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                                $inline = false,
                                $one_time = false,
                                $params = []
                            );
                            exit;
                    }
                }
            }
            else{
                $vk->sendButton($user_id,
                    "У вас нет прав для выполнения данной команды.",
                    $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                exit;
            }
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }

    //Вывести админов
    if($payload['command'] == 'admins_ticket_admin'){
        if($role != 'none') {
            if($role == 'founder') {
                $data = @mysqli_fetch_all($db->query_select("*", "admins", "role", "admin"));
                if(!empty($data)){
                    $message = 'Список администраторов:<br><br>';
                    foreach ($data as $roles) {
                        $message .= "Имя и Фамилия администратора: " . GetName($vk, $roles['1']) .
                            "<br>Ссылка на профиль: vk.com/id" . $roles['1'] . "<br><br>";
                    }
                    $vk->sendButton($user_id,
                        $message,
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                }
                else {
                    $vk->sendButton($user_id,
                        "Список администраторов пуст.",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                }
            }
            else {
                switch($role){
                    case 'admin':
                        $vk->sendButton($user_id,
                            "У вас нет прав для выполнения данной команды.",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                    case 'operator':
                        $vk->sendButton($user_id,
                            "У вас нет прав для выполнения данной команды.",
                            $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                            $inline = false,
                            $one_time = false,
                            $params = []
                        );
                        exit;
                }
                $vk->sendButton($user_id,
                    "У вас нет прав для выполнения данной команды.",
                    $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                    $inline = false,
                    $one_time = false,
                    $params = []
                );
                exit;
            }
        }
        else {
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }
    if($payload['command'] == 'close_work'){ // завершение работы
        if($role != 'none') {
            $db->query("UPDATE `admins` SET `is_active` = 'false' WHERE `user` = '$user_id';");
            $vk->sendButton($user_id,
                "Вы завершили работу, уведомления Вам больше приходить не будут.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
        else{
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }
}
//Обработка сообщения по номеру тикета
if(!empty(mysqli_fetch_all($db->query("SELECT * FROM `temp_messages` WHERE `user_id` = '$user_id' AND `status` = 'formation'")))) {
    if($role != 'none') {
        $db->query("DELETE FROM `temp_messages` WHERE `user_id` = '$user_id'");
        $data = @mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `id` = '$message' AND `status` = 'opened';"));
        if(empty($data)){
            switch($role){
                case 'founder':
                    $vk->sendButton($user_id,
                        "Такого тикета не существует!",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                case 'admin':
                    $vk->sendButton($user_id,
                        "Такого тикета не существует!",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                case 'operator':
                    $vk->sendButton($user_id,
                        "Такого тикета не существует!",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                default:
                    $vk->sendButton($user_id,
                        "У вас нет прав для выполнения данной команды.",
                        $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
            }
        }
        if($db->query("UPDATE `tickets` SET `admin_id` = '$user_id', `is_working` = 'true' WHERE `id` = '$message';")){
            $vk->sendButton($user_id,
                'Вы приняли тикет номер '.$data['0']['0']. '.<br><br>Ситуация пользователя: <br>'. $data['0']['3'].'<br><br>Для ответа пишите в чате.',
                $buttons = [[close_ticket_admin], [dicline_ticket_admin]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            $vk->sendButton($data['0']['2'],
                '✅ Ваш тикет номер '.$data['0']['0'].' принял '.GetRoleRus(GetRole($user_id)) . ' ' . GetName($vk, $user_id) . '. Сейчас он работает над Вашей проблемой, ожидайте и будьте на связи 😉',
                $buttons = [[close_ticket]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
    }
    else{
        $db->query("DELETE FROM `temp_messages` WHERE `user_id` = '$user_id'");
        $vk->sendButton($user_id,
            "У вас нет прав для выполнения данной команды.",
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    }
}

//Ответ админа
if(!empty(@mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `admin_id` = '$user_id' AND `is_working` = 'true';")))){
    $data = @mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `admin_id` = '$user_id' AND `is_working` = 'true';"));
    $vk->sendButton($data['0']['2'],
        "➡ Сообщение от ".GetRoleRus(GetRole($data['0']['5'])) . ' ' . GetName($vk, $data['0']['5']) . ":<br><br>💬 " . $message,
        $buttons = [[close_ticket]],
        $inline = false,
        $one_time = false,
        $params = []
    );
}

//Ответ пользователя
if(!empty(@mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `user_id` = '$user_id' AND `is_working` = 'true';")))){
    $data = @mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `user_id` = '$user_id' AND `is_working` = 'true';"));
    $vk->sendButton($data['0']['5'],
        "Сообщение от пользователя: <br><br>" . $message,
        $buttons = [[close_ticket_admin], [dicline_ticket_admin]],
        $inline = false,
        $one_time = false,
        $params = []
    );
}

//формирование тикета, ввод текста
if (!empty(mysqli_fetch_all($db->query("SELECT * FROM `tickets` WHERE `user_id` = '$user_id' AND `status` = 'formation'")))) { // Если в базе данных у пишущего пользователя открыт тикет со статусом формируется, то принять это сообщение и отправить ему сообщение об успешном принятии тикета
    $message = @mysql_escape_mimic($message);
    if ($db->query("UPDATE `tickets` SET `message` = '$message', `status` = 'opened' WHERE `user_id` = '$user_id' AND `status` = 'formation';")) {
        $vk->sendButton($user_id,
            "✅ Ваш тикет принят в обработку! Ожидайте ответ оператора",
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    } else {
        $vk->sendButton($user_id,
            "❌ Ошибка отправки тикета, повторите попытку позже",
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    }
}

//Добавление админа
if(!empty(mysqli_fetch_all($db->query("SELECT * FROM `temp_messages` WHERE `user_id` = '$user_id' AND `admin_status` = 'formation'")))) {
    if($role != 'none') {
        $db->query("DELETE FROM `temp_messages` WHERE `user_id` = '$user_id'");
        if($role != 'founder'){
            switch($role){
                case 'admin':
                    $vk->sendButton($user_id,
                        "У вас нет прав для выполнения данной команды.",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                case 'operator':
                    $vk->sendButton($user_id,
                        "У вас нет прав для выполнения данной команды.",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
            }
        }
        $message = @mysql_escape_mimic($message);
        if($db->query("INSERT INTO admins (user, role) VALUES ('$message', 'admin');")){
            $vk->sendButton($user_id,
                "Администратор успешно добавлен",
                $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                $inline = false,
                $one_time = false,
                $params = []
            );
        }
    }
    else{
        $db->query("DELETE FROM `temp_messages` WHERE `user_id` = '$user_id'");
        $vk->sendButton($user_id,
            "У вас нет прав для выполнения данной команды.",
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    }
}

//добавление оператора
if(!empty(mysqli_fetch_all($db->query("SELECT * FROM `temp_messages` WHERE `user_id` = '$user_id' AND `operator_status` = 'formation'")))) {
    if($role != 'none') {
        $db->query("DELETE FROM `temp_messages` WHERE `user_id` = '$user_id'");
        if($role != 'founder' and $role != 'admin'){
            $vk->sendButton($user_id,
                "У вас нет прав для выполнения данной команды.",
                $buttons = [[take_ticket_admin], [all_ticket_admin], [close_work]],
                $inline = false,
                $one_time = false,
                $params = []
            );
            exit;
        }
        $message = @mysql_escape_mimic($message);
        if($db->query("INSERT INTO admins (user, role) VALUES ('$message', 'operator');")){
            switch ($role){
                case 'founder':
                    $vk->sendButton($user_id,
                        "Оператор успешно добавлен.",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin, admins_ticket_admin], [add_operator, add_admin], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
                case 'admin':
                    $vk->sendButton($user_id,
                        "Оператор успешно добавлен.",
                        $buttons = [[take_ticket_admin], [all_ticket_admin], [operators_ticket_admin], [add_operator], [close_work]],
                        $inline = false,
                        $one_time = false,
                        $params = []
                    );
                    exit;
            }

        }
    }
    else{
        $db->query("DELETE FROM `temp_messages` WHERE `user_id` = '$user_id'");
        $vk->sendButton($user_id,
            "У вас нет прав для выполнения данной команды.",
            $buttons = [[create_ticket], [my_ticket], [rules_ticket], [example_ticket, faq_ticket]],
            $inline = false,
            $one_time = false,
            $params = []
        );
        exit;
    }
}

function mysql_escape_mimic($inp) {
    if(is_array($inp))
        return array_map(__METHOD__, $inp);

    if(!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }

    return $inp;
}

function GetRole($user_id){
    $db = new DataBase();
    $data = @mysqli_fetch_all($db->query_select("*", "admins", "user", $user_id));
    if(empty($data)){
        return 'none';
    }
    else{
        return $data['0']['2'];
    }
}

function GetRoleRus($role){
    switch($role){
        case 'founder':
            return "Основатель";
        case 'admin':
            return "Администратор";
        case 'operator':
            return "Оператор";
        default:
            return "Сотрудник";
    }
}

function GetName($vk, $user_id){
    $user = $vk->userInfo($user_id);
    return $user["first_name"] .' '. $user["last_name"];
}