<?php

/*						  
	Copyright© Arseniy Romanovskiy aka ShamanHead     
	This file is part of Telbot package		  
	Created by ShamanHead				  
	Mail: arsenii.romanovskii85@gmail.com	     	  						  
*/

namespace Telbot;

/*

Class Chat

@method static add(Bot $bot, string $chatId, string $type)
@method static get(Bot $bot, string $chatId)
@method static delete(Bot $bot, string $chatId)
@method static getAll(Bot $bot)

*/

use \PDO as PDO;

class Chat{

	public static function createTable($bot){
		try{
		$DBH = $bot->pdoConnection;

		$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		$tableCreator = $DBH->prepare("CREATE TABLE IF NOT EXISTS telbot_chats(
						id INT(11) AUTO_INCREMENT Primary Key,
						chatId VARCHAR(50) NOT NULL,
						botToken VARCHAR(50) NOT NULL,
						type VARCHAR(100) NOT NULL
						)
						");

		$tableCreator->execute();

		$DBH = null;
		}catch(PDOException $e) {  
		    echo $e->getMessage();  
		}
	}

	public static function add($bot, $chatId, $type){
		if(!$bot->sqlConnectionPosibility) return false;

		self::createTable($bot);

		$DBH = $bot->pdoConnection;

		if(!self::get($bot, $chatId)){
			$addChat = $DBH->prepare("INSERT INTO telbot_chats (chatId, botToken, type) VALUES (:chatId, :botToken, :type)");

			$token = $bot->getToken();

			$addChat->bindParam(':chatId', $chatId);
			$addChat->bindParam(':botToken', $token);
			$addChat->bindParam(':type', $type);

			$addChat->execute();
		}

		$DBH = null;
	}

	public static function get($bot, $chatId){
		if(!$bot->sqlConnectionPosibility) return false;

		$DBH = $bot->pdoConnection;

		$searchChat = $DBH->prepare("SELECT * FROM telbot_chats WHERE botToken = :botToken AND chatId = :chatId");

		$searchChat->bindParam(':chatId', $chatId);
		$searchChat->bindParam(':botToken', $token);

		$searchChat->setFetchMode(PDO::FETCH_ASSOC);

		$token = $bot->getToken();

		$searchChat->execute();

		$DBH = null;

		return $searchChat->fetchAll();
	}

	public static function delete($bot, $chatId) {
		if(!$bot->sqlConnectionPosibility) return false;

		$DBH = $bot->pdoConnection;

		$token = $bot->getToken();

		$deleteChat = $DBH->prepare('DELETE FROM telbot_chats WHERE chatId = :userId AND botToken = :botToken');

		$deleteChat->bindParam(':chatId', $chatId);
		$deleteChat->bindParam(':botToken', $token);

		$deleteChat->execute();

		$DBH = null;
	}

	public static function getAll($bot) {
		if(!$bot->sqlConnectionPosibility) return false;

		$DBH = $bot->pdoConnection;

		$getAllChats = $DBH->prepare("SELECT * FROM telbot_chats WHERE botToken = :botToken");

		$getAllChats->bindParam(':botToken', $token);

		$token = $bot->getToken();

		$getAllChats->setFetchMode(PDO::FETCH_ASSOC);

		$getAllChats->execute();

		return $getAllChats->fetchAll();
	}

}

?>
