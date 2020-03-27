<?php

use \Telbot\Inquiry as Inquiry;

function newPotion($potionId){
	global $userId;
	global $bot;
	global $DBH;
	$searchPotion = $DBH->prepare('SELECT * FROM potions WHERE id = :potionId');
	$searchPotion->bindParam(':potionId', $potionId);
	$searchPotion->execute();
	$potion = $searchPotion->fetch();
	Inquiry::send($bot, 'sendMessage', [
		'chat_id' => $userId,
		'text' => 'Вы получили новый предмет: '.$potion['name']
	]);
	$addPotion = $DBH->prepare('INSERT INTO inventory (userId, itemId) VALUES (:userId, :potionId)');
	$addPotion->bindParam(':userId', $userId);
	$addPotion->bindParam(':potionId', $potion['id']);
	$addPotion->execute();
}

function findPotionInInventory($potionId){
	global $userId;
	global $bot;
	global $DBH;
	$searchPotion = $DBH->prepare('SELECT * FROM potions WHERE id = :potionId');
	$searchPotion->bindParam(':potionId', $potionId);
	$searchPotion->execute();
	$potion = $searchPotion->fetch();
	return $potion;
}

?>