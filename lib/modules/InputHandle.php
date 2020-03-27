<?php

/*						  
	Copyright© Arseniy Romanovskiy aka ShamanHead     
	This file is part of Telbot package		  
	Created by ShamanHead				  
	Mail: arsenii.romanovskii85@gmail.com	     	  						  
*/

namespace Telbot;

/*

Class InputHandle - represents data from telegram answer.

@method getUpdateId() - returns an update id of telegram answer
@method getQueryType() - returns a query type of telegram answer
@method newChatMember() - returns true if in chat has come new member
@method getMessageText() - returns a text of telegram answer
@method 

*/

class InputHandle{

	private $inputData;
	private $queryType;
	private $updateId;

	function __construct(){
		$inputData = json_decode(file_get_contents('php://input'));
		if(isset($inputData->message)){
			$this->inputData = $inputData->message;
			$this->queryType = 'message';
		}else if(isset($inputData->callback_query)){
			$this->inputData = $inputData->callback_query;
			$this->queryType = 'callback_query';
		}else if(isset($inputData->inline_query)){
			$this->inputData = $inputData->inline_query;
			$this->queryType = 'inline_query';
		}
		$this->inputData->updateId = $inputData->update_id;
	}

	function __clone(){}

	public function getUpdateId(){
		return $this->inputData->updateId;
	}

	public function getQueryType(){
		return $this->queryType;
	}

	public function newChatMember(){
		if(isset($this->inputData->new_chat_member)){
			return $this->inputData->new_chat_member;
		}else{
			return false;
		}
	}

	public function getMessageText(){
		switch($this->queryType){
			case 'inline_query':
				return false;
				break;
			case 'callback_query':
				return $this->inputData->message->text;
				break;
			case 'message':
				return $this->inputData->text;
				break;
		}
	}

	public function getChatId(){
		switch($this->queryType){
			case 'inline_query':
				return false;
				break;
			case 'callback_query':
				return $this->inputData->message->chat->id;
				break;
			case 'message':
				return $this->inputData->chat->id;
		}
	}

	public function getInstance(){
		return $this->inputData;
	}

	public function getCallbackData(){
		if($this->queryType != 'callback_query') return false;
		return $this->inputData->data;
	}

	public function getCallBackQueryId(){
		if($this->queryType != 'callback_query') return false;
		return $this->inputData->id;
	}

	public function getInlineQueryId(){
		if($this->queryType != 'inline_query') return false;
		return $this->inputData->id;
	}

	public function getUserId(){
		return $this->inputData->from->id;
	}

	public function getChatType(){
		return $this->inputData->chat->type;
	}

	public function getChat(){
		return $this->inputData->chat;
	}

	public function getDate(){
		return $this->inputData->date;
	}

	public function getEntities(){
		return $this->inputData->entities;
	}


}

?>