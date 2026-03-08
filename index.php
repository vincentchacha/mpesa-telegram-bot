<?php
require_once('botpesa.php');

$botpesa = new BotPesa;

// Replace with your bot API key
$apiKey = 'YOUR BOT API KEY'; // Talk to BotFather
$apiURL = 'https://api.telegram.org/bot' . $apiKey . '/';

// Get the incoming update from Telegram
$update = json_decode(file_get_contents('php://input'));
$text = $update->message->text;

// Handle different commands
if (preg_match('#^pay#i', $text) === 1) {
    $botpesa->handle($update, $apiURL, $text);
} elseif ($text == '/start') {
    $botpesa->about($update, $apiURL);
} elseif ($text == 'Help') {
    $botpesa->help($update, $apiURL);
} elseif ($text == 'About') {
    $botpesa->about($update, $apiURL);
} else {
    $botpesa->unknown($update, $apiURL);
}
?>
