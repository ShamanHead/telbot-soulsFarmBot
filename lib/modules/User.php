<?php

/*						  
	Copyright© Arseniy Romanovskiy aka ShamanHead     
	This file is part of Telbot package		  
	Created by ShamanHead				  
	Mail: arsenii.romanovskii85@gmail.com	     	  						  
*/

namespace Telbot;

use PDO as PDO;

class User{
	public static function createTable($bot) {
		try{
		$DBH = $bot->pdoConnection;

		$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		$tableCreator = $DBH->prepare("CREATE TABLE IF NOT EXISTS telbot_users(
						id INT(11) AUTO_INCREMENT Primary Key,
						userId VARCHAR(50) NOT NULL,
						botToken VARCHAR(50) NOT NULL,
						context VARCHAR(100) NOT NULL
						)
						");

		$tableCreator->execute();

		$DBH = null;
		}catch(PDOException $e) {  
		    echo $e->getMessage();  
		}
	}

	public static function add($bot, $userId) {
		if(!$bot->sqlConnectionPosibility) return false;

		self::createTable($bot);

		$DBH = $bot->pdoConnection;

		if(!self::get($bot, $userId)){
			$addUser = $DBH->prepare("INSERT INTO telbot_users (userId, botToken) VALUES (:userId, :botToken)");

			$token = $bot->getToken();

			$addUser->bindParam(':userId', $userId);
			$addUser->bindParam(':botToken', $token);

			$addUser->execute();
		}

		$DBH = null;
	}

	public static function get($bot, $userId) {
		if(!$bot->sqlConnectionPosibility) return false;

		$DBH = $bot->pdoConnection;

		$searchUser = $DBH->prepare("SELECT * FROM telbot_users WHERE botToken = :botToken AND userId = :userId");

		$searchUser->bindParam(':userId', $userId);
		$searchUser->bindParam(':botToken', $token);

		$searchUser->setFetchMode(PDO::FETCH_ASSOC);

		$token = $bot->getToken();

		$searchUser->execute();

		$DBH = null;

		return $searchUser->fetchAll();
	}

	public static function getAll($bot) {
		if(!$bot->sqlConnectionPosibility) return false;

		$DBH = $bot->pdoConnection;

		$getAllUsers = $DBH->prepare("SELECT * FROM telbot_users WHERE botToken = :botToken");

		$getAllUsers->bindParam(':botToken', $token);

		$token = $bot->getToken();

		$getAllUsers->setFetchMode(PDO::FETCH_ASSOC);

		$getAllUsers->execute();

		return $getAllUsers->fetchAll();
	}

	public static function delete($bot, $userId) {
		if(!$bot->sqlConnectionPosibility) return false;

		$DBH = $bot->pdoConnection;

		$token = $bot->getToken();

		$deleteUser = $DBH->prepare('DELETE FROM telbot_users WHERE userId = :userId AND botToken = :botToken');

		$deleteUser->bindParam(':userId', $userId);
		$deleteUser->bindParam(':botToken', $token);

		$deleteUser->execute();

		$DBH = null;
	}

	public static function sendToAll($bot, $method, $data){
		if(!$bot->sqlConnectionPosibility) return false;

		$allUsers = self::getAll($bot);

		for($i = 0;$i < count($allUsers);$i++){
			$data['chat_id'] = $allUsers[$i]['userId'];
			self::send($bot, $method, $data);
		}

		$DBH = null;
	}
}

?>