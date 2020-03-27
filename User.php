<?php

use \Telbot\Inquiry as Inquiry;

require_once('User.php');

function getUser(){
	global $userId;
	global $bot;
	global $DBH;
	$getUser = $DBH->prepare('SELECT * FROM users WHERE userId = :userId');
	$getUser->bindParam(':userId', $userId);
	$getUser->execute();
	$user = $getUser->fetch();
	return $user;
}

function findNickName($nickName){
	global $userId;
	global $bot;
	global $DBH;
	$getNickName = $DBH->prepare('SELECT * FROM users WHERE nickname = :nickName');
	$getNickName->bindParam(':nickName', $nickName);
	$getNickName->execute();
	$user = $getNickName->fetch();
	return $user;
}

function getMoney(){
	global $userId;
	global $bot;
	global $DBH;
	$getMoney = $DBH->prepare('SELECT money FROM users WHERE userId = :userId');
	$getMoney->bindParam(':userId', $userId);
	$getMoney->execute();
	return ($getMoney->fetch())['money'];
}

function getLevel(){
	global $userId;
	global $bot;
	global $DBH;
	$getLevel = $DBH->prepare('SELECT level FROM users WHERE userId = :userId');
	$getLevel->bindParam(':userId', $userId);
	$getLevel->execute();
	return ($getLevel->fetch())['level'];
}

function addExp($count){
	global $userId;
	global $bot;
	global $DBH;
	$addExp = $DBH->prepare('UPDATE users SET exp = exp + :count WHERE userId = :userId');
	$addExp->bindParam(':count', $count);
	$addExp->bindParam(':userId', $userId);
	$addExp->execute();
	Inquiry::send($bot, 'sendMessage', [
		'chat_id' => $userId,
		'text' => 'Вам было добавлено '.$count.' единицы опыта.'
	]);
}

function addMoney($count){
	global $userId;
	global $bot;
	global $DBH;
	$addMoney = $DBH->prepare('UPDATE users SET money = money + :count WHERE userId = :userId');
	$addMoney->bindParam(':userId', $userId);
	$addMoney->bindParam(':count', $count);
	$addMoney->execute();
	Inquiry::send($bot, 'sendMessage', [
		'chat_id' => $userId,
		'text' => 'На ваш счет были добавлены деньги в размере '.$count.' монет'
	]);
}

function subMoney($count){
	global $userId;
	global $bot;
	global $DBH;
	$subMoney = $DBH->prepare('UPDATE users SET money = money - :count WHERE userId = :userId');
	$subMoney->bindParam(':userId', $userId);
	$subMoney->bindParam(':count', $count);
	$subMoney->execute();
	Inquiry::send($bot, 'sendMessage', [
		'chat_id' => $userId,
		'text' => 'С вашего счета были сняты деньги в размере '.abs($count).' монет'
	]);
}

function increaseWeaponLevel(){
	global $userId;
	global $bot;
	global $DBH;
	$increaseWeaponLevel = $DBH->prepare('UPDATE users SET weaponLevel = weaponLevel + 1, money = money - '.((getUser()['weaponLevel'] + 1 * getWeaponLevel()) * 10).' WHERE userId = :userId');
	$increaseWeaponLevel->bindParam(':userId', $userId);
	$increaseWeaponLevel->execute();
	Inquiry::send($bot, 'sendMessage', [
			'chat_id' => $userId,
			'text' => 'Вы улучшили уровень своего оружия до '.getUser()['weaponLevel']
	]);
}

function increaseLevel(){
	global $userId;
	global $bot;
	global $DBH;
	$increaseLevel = $DBH->prepare('UPDATE users SET level = level + 1, exp = 0 WHERE userId = :userId');
	$increaseLevel->bindParam(':userId', $userId);
	$increaseLevel->execute();
}

?>