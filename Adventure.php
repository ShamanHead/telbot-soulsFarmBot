<?php

function newAdventure($dungeonId){
	global $bot;
	global $DBH;
	global $userId;
	$newAdventure = $DBH->prepare('INSERT adventures (dungeonId, userId) VALUES (:dungeonId, :userId)');
	$newAdventure->bindParam(':dungeonId', $dungeonId);
	$newAdventure->bindParam(':userId', $userId);
	$newAdventure->execute();
}

function updateAdventure($money, $exp, $hp, $mobsDied, $mobId, $mobHp){
	try{
			global $bot;
	global $DBH;
	global $userId;
	$updateAdventure = $DBH->prepare('UPDATE adventures SET money = money + :count, exp = exp + :exp , hp = :hp , mobs_died = mobs_died + :mobsDied, mobId = :mobId, mobHp = :mobHp WHERE userId = :userId');
	$updateAdventure->bindParam(':count', $money);
	$updateAdventure->bindParam(':exp', $exp);
	$updateAdventure->bindParam(':hp', $hp);
	$updateAdventure->bindParam(':mobsDied', $mobsDied);
	$updateAdventure->bindParam(':userId', $userId);
	$updateAdventure->bindParam(':mobId', $mobId);
	$updateAdventure->bindParam(':mobHp', $mobHp);
	$updateAdventure->execute();
	}catch(PDOException $e){
		echo $e;
	}
}

function findAdventure(){
	global $bot;
	global $DBH;
	global $userId;
	$findAdventure = $DBH->prepare('SELECT * FROM adventures WHERE userId = :userId');
	$findAdventure->bindParam(':userId', $userId);
	$findAdventure->execute();
	return $findAdventure->fetch();
}

function deleteAdventure(){
	global $bot;
	global $DBH;
	global $userId;
	$deleteAdventure = $DBH->prepare('DELETE FROM adventures WHERE userId = :userId');
	$deleteAdventure->bindParam(':userId', $userId);
	$deleteAdventure->execute();
}
  
function findMob($mobId){
	global $bot;
	global $DBH;
	global $userId;
	$findMob = $DBH->prepare('SELECT * FROM dungeon_mobs WHERE id = :mobId');
	$findMob->bindParam(':mobId', $mobId);
	$findMob->execute();
	return $findMob->fetch();
}

?>