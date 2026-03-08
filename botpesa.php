<?php
require_once('vendor/autoload.php');
use GuzzleHttp\Client;

class BotPesa
{
    private function sendMessage($chatId, $text, $apiURL, $parseMode = null)
    {
        $client = new Client(['base_uri' => $apiURL]);
        $keyboard = [['Help', 'About']];
        $replyMarkup = json_encode([
            "keyboard" => $keyboard,
            "resize_keyboard" => true,
            "one_time_keyboard" => true
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

    public function handle($update, $apiURL, $message)
    {
        $full_message = explode(" ", $message);
        $contact = $full_message[1] ?? null;
        $amount = $full_message[2] ?? null;

        $this->sendMessage($update->message->chat->id, "Please wait as we try to perform the transaction for you...", $apiURL);

        $res = $this->STKPushSimulation($contact, $amount);
        $data = json_decode($res);

        if ($data->ResponseCode == '0') {
            $msg = $data->CustomerMessage . '. Please wait to enter your secret PIN. #Cheers';
        } else {
            $msg = "An error has occurred. Please try again and ensure the info you submit follows the correct format. #Sorry for the issue";
        }

        $this->sendMessage($update->message->chat->id, $msg, $apiURL);
    }

    public function help($update, $apiURL)
    {
        $text = "Ooh I heard you need some help from me. <i>You are so lucky you found the right Ninja</i>. I guess you want to know how to <code>lipa na mpesa</code>. It's simple: type <i>pay</i> followed by your <i>Safaricom mobile number</i> then the <i>amount</i>. e.g. <pre>Pay 254700000000 50</pre>";
        $this->sendMessage($update->message->chat->id, $text, $apiURL, 'html');
    }

    public function about($update, $apiURL)
    {
        $text = "I am an AI bot that can help you as an <b>MPESA</b> user perform <pre>Lipa na Mpesa</pre> transactions easily. I am not very intelligent yet, but I can help you a bit. Use me by telling me the transaction parameters in the message field. Type <b>Help</b> to get instructions or <b>About</b> to know more. To make a payment, type 'pay' followed by mobile number and amount like 'pay 254700000000 50' #Cheers";
        $this->sendMessage($update->message->chat->id, $text, $apiURL, 'html');
    }

    public function unknown($update, $apiURL)
    {
        $text = "Sorry, I did not understand what you said. Please retype the correct request or click on Help to get directions. I will be waiting...";
        $this->sendMessage($update->message->chat->id, $text, $apiURL);
    }

    public static function generateSandBoxToken()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $consumer_key = getenv("consumer_key");
        $consumer_secret = getenv("consumer_secret");

        if (!$consumer_key || !$consumer_secret) {
            die("Please declare the consumer key and consumer secret as defined in the documentation");
        }

        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($curl);
        return json_decode($response)->access_token;
    }

    public static function generateLiveToken()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $consumer_key = getenv("consumer_key");
        $consumer_secret = getenv("consumer_secret");

        if (!$consumer_key || !$consumer_secret) {
            die("Please declare the consumer key and consumer secret as defined in the documentation");
        }

        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($curl);
        return json_decode($response)->access_token;
    }

    public function STKPushSimulation($contact, $amount)
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $live = getenv("application_status");

        if ($live === "true") {
            $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            $token = $this->generateLiveToken();
        } elseif ($live === "sandbox") {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            $token = $this->generateSandBoxToken();
        } else {
            return json_encode(["Message" => "invalid application status"]);
        }

        $BusinessShortCode = 'YOUR_BUSINESS_CODE';
        $LipaNaMpesaPasskey = "YOUR_LIPA_NA_MPESA_PASS_KEY";
        $TransactionType = "CustomerPayBillOnline";
        $PartyA = $contact;
        $PartyB = 'PARTY_B';
        $PhoneNumber = $contact;
        $CallBackURL = "CALLBACK_URL";
        $AccountReference = "BotPesa Telegram Bot";
        $TransactionDesc = "Botpesa Telegram API bot.";
        $Remark = "Transaction made successfully";
        $Amount = $amount;

        $timestamp = '20' . date("ymdhis");
        $password = base64_encode($BusinessShortCode . $LipaNaMpesaPasskey . $timestamp);

        $curl_post_data = [
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $TransactionType,
            'Amount' => $Amount,
            'PartyA' => $PartyA,
            'PartyB' => $PartyB,
            'PhoneNumber' => $PhoneNumber,
            'CallBackURL' => $CallBackURL,
            'AccountReference' => $AccountReference,
            'TransactionDesc' => $TransactionDesc,
            'Remark' => $Remark
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Authorization:Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($curl_post_data)
        ]);

        return curl_exec($curl);
    }
}
