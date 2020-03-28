<?php

/*						  
	Copyright© Arseniy Romanovskiy aka ShamanHead     
	This file is part of Telbot package		  
	Created by ShamanHead				  
	Mail: arsenii.romanovskii85@gmail.com	     	  						  
*/

namespace Telbot;

class Inquiry {

	public static function send($bot , $method, $data) {

		$canSended = [
				   'sendmessage',
				   'sendaudio',
				   'sendphoto',
				   'senddocument',
				   'sendanimation',
				   'sendpoll',
				   'sendvenue',
				   'editmessagetext',
				   'editmessagecaption',
				   'editmessagemedia',
				   'editmessagereplymarkup',
				   'getuserprofilephotos', 
		           'getfile',
		           'getchat',
		           'getchatmember',
		           'getchatmemberscount',
		           'getme',
		           'sendchataction',
		           'sendInvoice',
		           'answercallbackquery'
		];

		$url = 'https://api.telegram.org/bot'.$bot->getToken().'/'.$method.'?';
		
		$finded = false;

		for($i = 0;$i < count($canSended);$i++){
			if(strcasecmp($method,$canSended[$i]) == 0){
				$finded = true;
			}
		}

		if($finded == false){
			return new \Error('Unknown telegram method');
		}

		foreach($data as $key=>$value) {
			switch($key) {
				case 'reply_markup':
					$data['reply_markup'] = json_encode($value, JSON_UNESCAPED_UNICODE);
					break;
			}
		}

		self::query($url, $data);
	}

	public static function answerInlineQuery($bot, $data) {

		$url = 'https://api.telegram.org/bot'.$bot->getToken().'/answerInlineQuery?';

		self::query($url, $data);
	}

	protected static function query($url, $postFields, $mode = false) {
		$ch = curl_init();

	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));

	    $data = curl_exec($ch);
	    curl_close($ch);

	    switch ($mode) {
	    	case 'get':
	    		return $data;
	    		break;
	    	
	    	default:
	    		return true;
	    		break;
	    }
	}


}

?>
