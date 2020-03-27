<?php

use \Telbot\Inquiry as Inquiry;

function findDungeonByLevel($level){
	global $bot;
	global $DBH;
	global $userId;
	$result = [];
	$getAllDungeons = $DBH->prepare('SELECT * FROM dungeons');
	$getAllDungeons->execute();
	$allDungeons = $getAllDungeons->fetchAll();
	for($i=0;$i < count($allDungeons);$i++){
		$levels = explode("-", $allDungeons[$i]['level']);
		$first = intval($levels[0]);
		$second = intval($levels[1]);
		if($level >= $first && $level <= $second){
			array_push($result, $allDungeons[$i]);
		}
	}
	return $result;
}

function findDungeonById($dungeonId){
	global $bot;
	global $DBH;
	global $userId;
	$getDungeon = $DBH->prepare('SELECT * FROM dungeons WHERE id = :dungeonId');
	$getDungeon->bindParam(':dungeonId', $dungeonId);
	$getDungeon->execute();
	return $getDungeon->fetch();
}

function spawnRandomMob($dungeonId){
	global $bot;
	global $DBH;
	global $userId;
	$getAllMobsCount = $DBH->prepare('SELECT COUNT(id) FROM dungeon_mobs WHERE dungeonId = :dungeonId');
	$getAllMobsCount->bindParam(':dungeonId', $dungeonId);
	$getAllMobsCount->execute();
	$mobsCount = $getAllMobsCount->fetch()[0];

	$randomNumber = rand(1,$mobsCount);

	$getMobs = $DBH->prepare('SELECT * FROM dungeon_mobs WHERE dungeonId = :dungeonId');
	$getMobs->bindParam(':dungeonId', $dungeonId);
	$getMobs->execute();
	$allMobs = $getMobs->fetchAll();

	return $allMobs[$randomNumber-1];
}

?>