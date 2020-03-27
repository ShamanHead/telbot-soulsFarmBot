<?php

use \Telbot\Inquiry as Inquiry;

function findWeaponById($weaponId){
	global $bot;
	global $DBH;
	$findWeapon = $DBH->prepare('SELECT * FROM weapons WHERE id = :weaponId');
	$findWeapon->bindParam(':weaponId', $weaponId);
	$findWeapon->execute();
	return $findWeapon->fetch();
}

function findWeaponToSell($userLevel){
	global $bot;
	global $DBH;
	$findWeapon = $DBH->prepare('SELECT * FROM weapons WHERE level <= :userLevel');
	$findWeapon->bindParam(':userLevel', $userLevel);
	$findWeapon->execute();
	$weapon = $findWeapon->fetchAll();
	return $weapon[array_key_last($weapon)];
}

function setWeapon($weaponId){
	global $userId;
	global $bot;
	global $DBH;
	$setWeapon = $DBH->prepare('UPDATE users SET weaponId = :weaponId, weaponLevel = 0 WHERE userId = :userId');
	$setWeapon->bindParam(':weaponId', $weaponId);
	$setWeapon->bindParam(':userId', $userId);
	$setWeapon->execute();
	$searchWeapon = $DBH->prepare('SELECT * FROM weapons WHERE id = :weaponId');
	$searchWeapon->bindParam(':weaponId', $weaponId);
	$searchWeapon->execute();
	$weapon = $searchWeapon->fetch();
	Inquiry::send($bot, 'sendMessage', [
		'chat_id' => $userId,
		'text' => 'Вы получили новое оружие: '.$weapon['name']
	]);
}

function getWeaponLevel(){
	global $bot;
	global $DBH;
	$getWeaponLevel = $DBH->prepare('SELECT level FROM weapons WHERE id = :weaponId');
	$getWeaponLevel->bindParam(':weaponId', getUser()['weaponId']);
	$getWeaponLevel->execute();
	return ($getWeaponLevel->fetch())['level'];
}

?>