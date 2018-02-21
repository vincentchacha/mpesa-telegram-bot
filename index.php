<?php
require_once( 'botpesa.php' );

$botpesa=new BotPesa;
    $apiKey = 'YOUR BOT API KEY'; //talk to bot father
	$apiURL = 'https://api.telegram.org/bot' . $apiKey . '/'; 
	
	
	$update = json_decode( file_get_contents( 'php://input' ) );
	$text = $update->message->text;
	
	if ( preg_match('#^pay#i',  $text) === 1)
	{
	   
	    $botpesa->handle($update,$apiURL,$text);
	}
			elseif( $text == '/start')
	{
	    $botpesa->about($update,$apiURL);
	}
	elseif( $text == 'Help')
	{
	    $botpesa->help($update,$apiURL);
	}
		elseif( $text == 'About')
	{
	    $botpesa->about($update,$apiURL);
	}
	else{
	    $botpesa->unknown($update,$apiURL);
	}

?>