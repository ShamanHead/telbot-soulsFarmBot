<?php

require_once('lib/modules/Bot.php');
require_once('lib/modules/Inquiry.php');
require_once('lib/modules/Context.php');
require_once('lib/modules/User.php');
require_once('lib/modules/Utils.php');
require_once('lib/modules/InputHandle.php');
require_once('lib/modules/Chat.php');

use \Telbot\Context as Context;
use \Telbot\Inquiry as Inquiry;
use \Telbot\Bot as Bot;
use \Telbot\User as User;
use \Telbot\Utils as Utils;
use \Telbot\InputHandle as InputHandle;
use \Telbot\Chat as Chat;

$data = json_decode(file_get_contents('php://input'));
$bot = new Bot('1009655071:AAEoB3-DO74_FLnc4K9osmrYHf3XEGHVc-g');
$ih = new InputHandle();
$bot->enableSql();
$DBH = new PDO('mysql:host=us-cdbr-iron-east-05.cleardb.net;charset=utf8;dbname=heroku_75918dcf01cbce3', 'be76b1edb6932d', '38023b9d');
$bot->externalPdo($DBH);

$userId = $ih->getUserId();
$message = $ih->getMessageText();

// if(!Chat::get($bot, $ih->getChatId())) Chat::add($bot, $ih->getChatId(), $ih->getChatType());

require_once('User.php');
require_once('Weapon.php');
require_once('Potion.php');
require_once('Dungeon.php');
require_once('Adventure.php');

$mainKeyboard = Utils::buildKeyboard([[['🏘Город'], ['💪Мой персонаж'], ['🧟‍♀️Подземелье']]], true, true);
$cityKeyboard = Utils::buildKeyboard([[['🛠Кузница']], [['❌Выйти из города']]], true, true);
$user = getUser();
$levelFormula = $user['level'] * 15;
$weapon = findWeaponById($user['weaponId']);
$weaponDamage = $weapon['damage'] + ($user['weaponLevel'] * ($weapon['damage'] * 0.1));

function registrationStart(){
	global $userId;
	global $DBH;
	global $ih;
	global $bot;
	$userExistQuery = $DBH->prepare('SELECT * FROM users WHERE userId = :userId');
	$userExistQuery->bindParam(':userId', $userId);
	$userExistQuery->execute();
	$userExist = $userExistQuery->fetchAll();
	if(!$userExist){
		Inquiry::send($bot, 'sendMessage' , [
			'chat_id' => $ih->getChatId(),
			'text' => 'Хммм, я не вижу тебя в списке героев, но я вижу пыл в твоих глазах.Да, ты хочешь уничтожить зло!Назовись, герой!'
		]);
		$addUser = $DBH->prepare('INSERT INTO users (userId) VALUES(:userId)');
		$addUser->bindParam(':userId', $userId);
		$addUser->execute();
		Context::write($bot, $userId, 'registration');
		die();
	}
}

function registrationEnd(){
	global $userId;
	global $DBH;
	global $message;
	$UpdateUser = $DBH->prepare('UPDATE users SET nickname = :nickname WHERE userId = :userId');
	$UpdateUser->bindParam(':nickname', $message);
	$UpdateUser->bindParam(':userId', $userId);
	$UpdateUser->execute();
}

if($user['exp'] >= $levelFormula && $user){
	Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => 'Вы достигли нового уровня!'
					]);
	increaseLevel();
}
if(Context::read($bot, $userId) == 'in_adventure' && ($ih->getCallBackData() != 'quit_adventure' && $ih->getCallBackData() != 'continue_adventure' && $ih->getCallBackData() != 'adventure_attack')){
		Inquiry::send($bot, 'sendMessage' , [
			'chat_id' => $ih->getChatId(),
			'text' => 'Вы не можете сделать этого находясь в подземелии!'
		]);
		die();
}
switch($ih->getQueryType()){
	case 'callback_query':
		if(preg_match("/start_new_dungeon_([1-9]+)/",$ih->getCallBackData())){
			Context::write($bot, $userId, 'in_adventure');
			$matches;
			preg_match("/start_new_dungeon_([1-9]+)/",$ih->getCallBackData(), $matches);
			$dungeon = findDungeonById($matches[1]);
			Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => 'Вы вошли в подземелие...'
					]);
			newAdventure($matches[1]);
			$hp = $user['level'] * 10;
			$expErning = findDungeonById($matches[1])['exp'];
				sleep(3);
				$mob = spawnRandomMob($matches[1]);
				$mobHealth = $mob['hp'];
				$mob['damage'] = round($mob['damage'],1, PHP_ROUND_HALF_UP);
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => "Вы увидели, как что-то вдалеке движется.\nЭтим что-то оказался $mob[name].\nУ него ".$mobHealth." хп и $mob[damage] урона.\nЧто будете делать?"
					]);
				$turn =0;
				$hp -= $mob['damage'];
				updateAdventure(0, 0, $hp, 0, $mob['id'], $mobHealth);
				Inquiry::send($bot, 'sendMessage' , [
								'chat_id' => $ih->getChatId(),
								'text' => "Монстр ранил вас!У вас осталось $hp хп.\nЧто будете делать?",
								'reply_markup' => Utils::buildInlineKeyboard([[['Атаковать', 'adventure_attack']]])
							]);
		}
		switch($ih->getCallBackData()){
			case 'buy_weapon':
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => 'Ты уверен?Это покупка обойдется тебе в '.findWeaponToSell($user['level'])['cost'].' монет.',
						'reply_markup' => Utils::buildInlineKeyboard([[['Да, абсолютно.', 'buy_weapon_confirm'], ['Нет, еще подумаю.', 'buy_weapon_cancel']]])
					]);
			break;
			case 'buy_weapon_confirm':
			if($weapon['level'] <= findWeaponToSell($user['level'])['level'] && getMoney() >= findWeaponToSell($user['level'])['cost']){
				subMoney(findWeaponToSell($user['level'])['cost']);
				setWeapon(findWeaponToSell($user['level'])['id']);
			}else{
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => 'Данный предмет недоступен к покупке.'
					]);
			}
			break;
			case 'buy_weapon_cancel':
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => 'Окей, тогда как будешь уверен, приходи!'
					]);
			break;
			case 'upgrade_weapon':
				if($user['money'] >= ($user['weaponLevel'] + 1 * getWeaponLevel()) * 10 && $user['weaponLevel'] < $weapon['maxLevel']){
					increaseWeaponLevel();
				}else{
					Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => 'У вас не хватает денег на улучшение данного предмета либо данный предмет достиг своего максимального уровня.'
						]);
				}
			break;
			case 'buy_weapon_catalog':
				if($weapon['level'] <= findWeaponToSell($user['level'])['level'] && getMoney() >= findWeaponToSell($user['level'])['cost']){
					$weaponToSell = findWeaponToSell($user['level']);
					$keyboard = Utils::buildInlineKeyboard([[['Купить '.$weaponToSell['name'].'('.$weaponToSell['cost'].' монет)', 'buy_weapon']]]);
					Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => 'Что хотите приобрести?',
						'reply_markup' => $keyboard
					]);
				}else{
					Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => 'Что хотите приобрести?'
					]);
				}
			break;
			case 'max_weapon_level':
				Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => 'Ваше оружие достигло максимального уровня и не требует улучшений.'
						]);
			break;
			case 'continue_adventure':
				if(!Context::read($bot, $userId) == 'in_adventure'){
					Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => 'Вы не в подземелии.'
						]);
					die();
				}
				Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => 'Вы пошли дальше в подземелие'
						]);
				$adventure = findAdventure();
				$mob = spawnRandomMob($adventure['dungeonId']);
				if(findDungeonById($adventure['dungeonId'])['type'] == 2 && $adventure['mobs_died']){
					$mob['hp'] = $mob['hp'] / 0.3;
					$mob['damage'] = $mob['damage'] / 0.3;
					$mob['money'] = $mob['damage'] / 0.3;
					$expErning = findDungeonById($adventure['dungeonId'])['exp'] / 0.3;
					Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => 'Сила монстров и дропа увеличилась на 30%!'
						]);
				}
				$mobHealth = $mob['hp'];
				$expErning = findDungeonById($adventure['dungeonId'])['exp'];
				$hp = $adventure['hp'];
				$hp -= $mob['damage'];
				$hp = round($hp,1, PHP_ROUND_HALF_UP);
				$mobHealth = round($mobHealth,1, PHP_ROUND_HALF_UP);
				$mob['damage'] = round($mob['damage'],1, PHP_ROUND_HALF_UP);
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => "Вы увидели, как что-то вдалеке движется.\nЭтим что-то оказался $mob[name].\nУ него ".$mobHealth." хп и $mob[damage] урона.\nЧто будете делать?"
					]);
				updateAdventure(0, 0, $hp, 1, $mob['id'], $mobHealth);
				Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => "Моб атаковал вас.У вас осталось ".$hp." хп.\nЧто будете делать дальше?",
							'reply_markup' => Utils::buildInlineKeyboard([[['Атаковать' , 'adventure_attack']]])
						]);
			break;
			case 'adventure_attack':
				if(!Context::read($bot, $userId) == 'in_adventure'){
					Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => 'Вы не в подземелии.'
						]);
					die();
				}
				$adventure = findAdventure();
				$hp = $adventure['hp'];
				$mob = findMob($adventure['mobId']);
				$mobHealth = $adventure['mobHp'];
				$expErning = findDungeonById($adventure['dungeonId'])['exp'];
				$hp = round($hp,1, PHP_ROUND_HALF_UP);
				$mobHealth = round($mobHealth,1, PHP_ROUND_HALF_UP);
				$mob['damage'] = round($mob['damage'],1, PHP_ROUND_HALF_UP);
				$mobHealth -= $weaponDamage;
				if($mobHealth > 0){
					Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => 'Вы атаковали монстра.У него осталось '.$mobHealth.' хп'
						]);
					$hp -= $mob['damage'];
					if($hp < 0){
						Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => 'Вы умерли.'
						]);
						deleteAdventure();
						Context::delete($bot, $userId);
						die();
					}
					updateAdventure(0, 0, $hp, 0, $adventure['mobId'], $mobHealth);
					Inquiry::send($bot, 'sendMessage' , [
								'chat_id' => $ih->getChatId(),
								'text' => "Моб атаковал вас.У вас осталось ".$hp." хп.\nЧто будете делать дальше?",
								'reply_markup' => Utils::buildInlineKeyboard([[['Атаковать' , 'adventure_attack']]])
							]);
				}else{
					updateAdventure($mob['money'], $expErning, $hp,1, 0,  0);
					Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => "Вы убили монстра.У вас осталось $hp хп.",
							'reply_markup' => Utils::buildInlineKeyboard([[['Продолжить путь', 'continue_adventure']], [['Покинуть подземелие', 'quit_adventure']]])
						]);
				}
			break;
			case 'quit_adventure':
				$adventure = findAdventure();
				if($adventure){
					addMoney($adventure['money']);
					addExp($adventure['exp']);
					deleteAdventure();
					Context::delete($bot, $userId);
				}else{
					Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => "Неизвестная ошибка"
						]);
				}
			break;
		}
		break;
	case 'message':
		registrationStart();
		switch($ih->getMessageText()){
			case '/kb':
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => "Клавиатура была создана.",
						'reply_markup' => $mainKeyboard
					]);
			break;
			case '/help':
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => "Добро пожаловать в гильдию охотников.Чем я могу вам помочь?",
						'reply_markup' => Utils::buildInlineKeyboard([[['Пока работаем над этим...', 'work']]])
					]);
			break;
			case '/test':
				Inquiry::send($bot, 'sendMessage' , [
								'chat_id' => $ih->getChatId(),
								'text' => print_r(findDungeonByLevel($user['level']), true)
							]);
			break;
			case '🛠Кузница':
				$weapon = findWeaponToSell($user['level']);
				if($user['weaponLevel'] >= $weapon['maxLevel']){
					$upgrade = [['У вашего оружия максимальный уровень', 'max_weapon_level']];
				}else{
					$upgrade = [['Улучшение оружия('.(($user['weaponLevel'] + 1 * getWeaponLevel()) * 10).' монет)', 'upgrade_weapon']];
				}
					Inquiry::send($bot, 'sendMessage' , [
							'chat_id' => $ih->getChatId(),
							'text' => "Привет, я Джо!Добро пожаловать в кузницу.Здесь ты можешь приобрести оружие и броню за хорошую цену!.",
							'reply_markup' => Utils::buildInlineKeyboard([[["Покупка аммуниции", 'buy_weapon_catalog']], $upgrade])
						]);
			break;
			case '🏘Город':
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => "Добро пожаловать в КейТаун!",
						'reply_markup' => $cityKeyboard
					]);
			break;
			case '💪Мой персонаж':
				switch($weapon['rare']){
					case '1':
						$weapon['rare'] = 'Обычный';
					break;
					case '2':
						$weapon['rare'] = 'Редкий';
					break;
					case '3':
						$weapon['rare'] = 'Древний';
					break;
					case '4':
						$weapon['rare'] = 'Мифический';
					break;
					case '5':
						$weapon['rare'] = 'Легендарный';
					break;
				}
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => "~~~~~~Мой персонаж~~~~~~\nПрозвище: $user[nickname]\nУровень: $user[level]\nКол-во монет: $user[money]\nОружие: $weapon[name]\nРедкость: $weapon[rare]\nУровень оружия: $user[weaponLevel]\nСуммарный урон: $weaponDamage\nОпыт: $user[exp]\nДо следующего уровня: ".($levelFormula - $user['exp']),
					]);
			break;
			case '🧟‍♀️Подземелье':
				$dungeons = findDungeonByLevel($user['level']);
				$keyboard = [[]];
				for($i = 0;$i < count($dungeons);$i++){
					array_push($keyboard[0],[$dungeons[$i]['name'].'('.$dungeons[$i]['level'].' уровни)', 'start_new_dungeon_'.$dungeons[$i]['id']]);
				}
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => "Выберите подзмелье.",
						'reply_markup' => Utils::buildInlineKeyboard($keyboard, true, true)
					]);
			break;
			case '❌Выйти из города':
				Inquiry::send($bot, 'sendMessage' , [
						'chat_id' => $ih->getChatId(),
						'text' => "Вы вышли из города.",
						'reply_markup' => $mainKeyboard
					]);
			break;
			default:
			if(!Context::read($bot, $ih->getUserId())){
				Inquiry::send($bot, 'sendMessage' , [
					'chat_id' => $ih->getChatId(),
					'text' => "Извини, но я не понял, что ты хотел сказать.\nЕсли хочешь помощи, напиши /help."
				]);
			}
			switch(Context::read($bot, $userId)){
				case 'registration':
				if(!findNickName($message)){
					Inquiry::send($bot, 'sendMessage', [
						'chat_id' => $userId,
						'text' => 'Отлично, '.$message."!\nТеперь ты полноправный герой!Держи карточку охотника на демонов.\nС помощью нее ты сможешь посещать подземелья 1-5 уровня!\nТакже я дал тебе еще немного предметов, необходимых для первого похода в подземелье.Почитать о них ты сможешь у себя в инвентаре.И да!Не забудь заглянуть в кузницу и купить меч на те деньги, что я тебе дал.",
							'reply_markup' => $cityKeyboard
					]);
					sleep(1);
					sleep(1);
					addMoney(5);
					registrationEnd();
					Context::delete($bot, $userId);

				}else{
					Inquiry::send($bot, 'sendMessage', [
						'chat_id' => $userId,
						'text' => "Извини, но охотник с таким именем уже существует.\nПридумай прозвище получше этого!."
					]);
					Context::write($bot, $userId, 'registration');
				}
				break;
			}
			break;
		}
		break;
}
?>