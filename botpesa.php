<?php

require_once('vendor/autoload.php');

use GuzzleHttp\Client;

class BotPesa
{

/* -----------------------------------------
SEND TELEGRAM MESSAGE
----------------------------------------- */

private function sendMessage($chatId, $text, $apiURL, $parseMode = null)
{
    $client = new Client(['base_uri' => $apiURL]);

    $keyboard = [
        ['Help', 'About']
    ];

    $replyMarkup = json_encode([
        "keyboard" => $keyboard,
        "resize_keyboard" => true,
        "one_time_keyboard" => false
    ]);

    $payload = [
        'query' => [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $replyMarkup
        ]
    ];

    if ($parseMode) {
        $payload['query']['parse_mode'] = $parseMode;
    }

    $client->post('sendMessage', $payload);
}


/* -----------------------------------------
PHONE SANITIZER
----------------------------------------- */

private function sanitizePhone($phone)
{
    $phone = trim($phone);

    // remove spaces, hyphens etc
    $phone = preg_replace('/[^0-9+]/', '', $phone);

    if (preg_match('/^\+2547\d{8}$/', $phone)) {
        return substr($phone, 1);
    }

    if (preg_match('/^2547\d{8}$/', $phone)) {
        return $phone;
    }

    if (preg_match('/^07\d{8}$/', $phone)) {
        return '254' . substr($phone, 1);
    }

    return false;
}


/* -----------------------------------------
HANDLE PAY COMMAND
----------------------------------------- */

public function handle($update, $apiURL, $message)
{
    $chatId = $update->message->chat->id;

    $parts = explode(" ", trim($message));

    if (count($parts) != 3) {

        $this->sendMessage(
            $chatId,
            "❌ Invalid format\n\nUse:\n<pre>pay 0712345678 50</pre>",
            $apiURL,
            'html'
        );

        return;
    }

    $phone = $this->sanitizePhone($parts[1]);

    if (!$phone) {

        $this->sendMessage(
            $chatId,
            "❌ Invalid phone number\n\nAllowed formats:\n<pre>
+254712345678
254712345678
0712345678
</pre>",
            $apiURL,
            'html'
        );

        return;
    }

    $amount = $parts[2];

    if (!is_numeric($amount) || $amount <= 0) {

        $this->sendMessage(
            $chatId,
            "❌ Invalid amount",
            $apiURL
        );

        return;
    }

    $this->sendMessage(
        $chatId,
        "⏳ Initiating M-Pesa STK Push...",
        $apiURL
    );

    $res = $this->STKPushSimulation($phone, $amount);

    $data = json_decode($res);

    if (isset($data->ResponseCode) && $data->ResponseCode == "0") {

        $msg = "✅ STK Push sent\n\nCheck your phone and enter your M-Pesa PIN.";

    } else {

        $msg = "❌ Transaction failed. Try again later.";

    }

    $this->sendMessage($chatId, $msg, $apiURL);
}


/* -----------------------------------------
HELP
----------------------------------------- */

public function help($update, $apiURL)
{
    $text = "
<b>BotPesa Help</b>

To make a payment type:

<pre>pay 0712345678 50</pre>

Formats accepted:

<pre>
+254712345678
254712345678
0712345678
</pre>
";

    $this->sendMessage(
        $update->message->chat->id,
        $text,
        $apiURL,
        'html'
    );
}


/* -----------------------------------------
ABOUT
----------------------------------------- */

public function about($update, $apiURL)
{

$text = "
<b>BotPesa Telegram Bot</b>

This bot allows customers to pay using
<b>Lipa Na M-Pesa STK Push</b>.

Simply type:

<pre>pay phone amount</pre>

Example:

<pre>pay 0712345678 50</pre>
";

$this->sendMessage(
    $update->message->chat->id,
    $text,
    $apiURL,
    'html'
);

}


/* -----------------------------------------
UNKNOWN MESSAGE
----------------------------------------- */

public function unknown($update, $apiURL)
{

$this->sendMessage(
    $update->message->chat->id,
    "I didn't understand that.\nType Help for instructions.",
    $apiURL
);

}


/* -----------------------------------------
MPESA TOKEN
----------------------------------------- */

public static function generateToken()
{

$consumer_key = getenv("consumer_key");
$consumer_secret = getenv("consumer_secret");

$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$credentials = base64_encode($consumer_key . ':' . $consumer_secret);

$curl = curl_init();

curl_setopt_array($curl, [
CURLOPT_URL => $url,
CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials],
CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($curl);

return json_decode($response)->access_token;

}


/* -----------------------------------------
STK PUSH
----------------------------------------- */

public function STKPushSimulation($phone, $amount)
{

$token = self::generateToken();

$url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

$BusinessShortCode = "YOUR_SHORTCODE";
$Passkey = "YOUR_PASSKEY";

$timestamp = date("YmdHis");

$password = base64_encode($BusinessShortCode . $Passkey . $timestamp);

$data = [

'BusinessShortCode' => $BusinessShortCode,
'Password' => $password,
'Timestamp' => $timestamp,
'TransactionType' => "CustomerPayBillOnline",
'Amount' => $amount,
'PartyA' => $phone,
'PartyB' => $BusinessShortCode,
'PhoneNumber' => $phone,
'CallBackURL' => "https://yourdomain.com/callback.php",
'AccountReference' => "BotPesa",
'TransactionDesc' => "Payment"

];

$curl = curl_init();

curl_setopt_array($curl, [

CURLOPT_URL => $url,
CURLOPT_HTTPHEADER => [
'Content-Type:application/json',
'Authorization:Bearer ' . $token
],
CURLOPT_RETURNTRANSFER => true,
CURLOPT_POST => true,
CURLOPT_POSTFIELDS => json_encode($data)

]);

return curl_exec($curl);

}

}
