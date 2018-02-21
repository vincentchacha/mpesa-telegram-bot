<?php
require_once( 'vendor/autoload.php' );
use GuzzleHttp\Client;

class BotPesa
{
        
   
    

public function handle($update,$apiURL,$message)
{
    	$full_message = explode(" ",$message);
    	$contact=$full_message[1];
    	$amount=$full_message[2];
    	
	    $client = new Client( array( 'base_uri' => $apiURL ) );
	    $keyboard = array(array("Help","About"));
	    $resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
	    $reply = json_encode($resp);

            $client->post( 'sendMessage', array( 'query' => array( 'chat_id' => $update->message->chat->id, 'text' =>"Please wait as we try perfom the transaction for you..." ,'reply_markup'=>$reply) ) );
            $res=$this->STKPushSimulation($contact,$amount);
             $data = json_decode($res);
             if($data->ResponseCode=='0')
             {
                 $text= $data->CustomerMessage;
                 $msg =$text .'. Please wait to enter your secret PIN.#Cheers';
             }else
             {
                 $msg = "An error has occured. Please try again and ensure that the info you submit follows the correct format. #Sorry for the issue";
             }
             $client->post( 'sendMessage', array( 'query' => array( 'chat_id' => $update->message->chat->id, 'text' => $msg) ) );
	
}

public function help($update,$apiURL)
{
    $client = new Client( array( 'base_uri' => $apiURL ) );
    $keyboard = array(array("Help","About"));
	   $resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
	   $reply = json_encode($resp);
	   $client->post( 'sendMessage', array( 'query' => array( 'chat_id' => $update->message->chat->id, 'text' => "Ooh I heard you need some help from me.<i> You are so lucky you found the right Ninja</i>. I guess you wanna know how to <code>lipa na mpesa</code>. Its simple just type <i>pay</i> followed by your <i>safaricom mobile number</i> then the <i>amount</i> .e.g  <pre>Pay 254700000000 50</pre>" ,'reply_markup'=>$reply,'parse_mode'=>'html') ) );
}

public function about($update,$apiURL)
{
    $client = new Client( array( 'base_uri' => $apiURL ) );
    $keyboard = array(array("Help","About"));
	   $resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
	   $reply = json_encode($resp);
	   $client->post( 'sendMessage', array( 'query' => array( 'chat_id' => $update->message->chat->id, 'text' => "I am an AI bot that can help you as an <b>MPESA</b> user perfom <pre>Lipa na Mpesa</pre> transactions easily to a certain bussiness. I am not so intelligent as for now  but i can help you a bit. You can use me easily by telling me the transaction parameters in the message field. You will love it. Type <b>Help</b> to get help info or type <b>About</b> to know more about me To try me out by making payments, type pay followed by mobile number then amount to pay like 'pay 254700000000 50' #Cheers" ,'reply_markup'=>$reply,'parse_mode'=>'html') ) ); 
}

public function unknown($update,$apiURL)
{
    $client = new Client( array( 'base_uri' => $apiURL ) );
    $keyboard = array(array("Help","About"));
	   $resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
	   $resp = json_encode($resp);
	   $client->post( 'sendMessage', array( 'query' => array( 'chat_id' => $update->message->chat->id, 'text' => "Sorry i did not understand what you said. Please retype the correct request or click on help to get directions. I will be waiting.." ,'reply_markup'=>$reply) ) );
}
    /**
     * use this function to generate a sandbox token
     * @return mixed
     */
    public static function generateSandBoxToken(){
        $dotenv = new Dotenv\Dotenv(__DIR__);
        $dotenv->load();
        $consumer_key= getenv("consumer_key");
        $consumer_secret= getenv("consumer_secret");
        if(!isset($consumer_key)||!isset($consumer_secret)){
            die("please declare the consumer key and consumer secret as defined in the documentation");
        }
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $credentials = base64_encode($consumer_key.':'.$consumer_secret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);

        return json_decode($curl_response)->access_token;
    }
        /**
     * This is used to generate tokens for the live environment
     * @return mixed
     */
    public static function generateLiveToken(){
        $dotenv = new Dotenv\Dotenv(__DIR__);
        $dotenv->load();
        $consumer_key=getenv("consumer_key");
        $consumer_secret=getenv("consumer_secret");

        if(!isset($consumer_key)||!isset($consumer_secret)){
            die("please declare the consumer key and consumer secret as defined in the documentation");
        }
        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $credentials = base64_encode($consumer_key.':'.$consumer_secret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);

        return json_decode($curl_response)->access_token;


    }
        /**
     * Use this function to initiate an STKPush Simulation
     * @param $BusinessShortCode | The organization shortcode used to receive the transaction.
     * @param $LipaNaMpesaPasskey | The password for encrypting the request. This is generated by base64 encoding BusinessShortcode, Passkey and Timestamp.
     * @param $TransactionType | The transaction type to be used for this request. Only CustomerPayBillOnline is supported.
     * @param $Amount | The amount to be transacted.
     * @param $PartyA | The MSISDN sending the funds.
     * @param $PartyB | The organization shortcode receiving the funds
     * @param $PhoneNumber | The MSISDN sending the funds.
     * @param $CallBackURL | The url to where responses from M-Pesa will be sent to.
     * @param $AccountReference | Used with M-Pesa PayBills.
     * @param $TransactionDesc | A description of the transaction.
     * @param $Remark | Remarks
     * @return mixed|string
     */
    public function STKPushSimulation($contact,$amount){
        $dotenv = new Dotenv\Dotenv(__DIR__);
        $dotenv->load();
        $live=getenv("application_status");
        if( $live =="true"){
            $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            $token=$this->generateLiveToken();
        }elseif ($live=="sandbox"){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            $token=$this->generateSandBoxToken();
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $BusinessShortCode='YOUR_BUSINESS_CODE';
        $LipaNaMpesaPasskey="YOUR LIPA NA MPESA PASS KEY";
        $TransactionType="CustomerPayBillOnline";
        $Amount=$amount;
        $PartyA=$contact;
        $PartyB='PARTY B';
        $PhoneNumber=$contact;
        $CallBackURL="CALLBACK URL";
        $AccountReference="BotPesa Telegram Bot";
        $TransactionDesc="Botpesa Telegram API bot.";
        $Remarks="Transaction made successfully";

        $timestamp='20'.date(    "ymdhis");
        $password=base64_encode($BusinessShortCode.$LipaNaMpesaPasskey.$timestamp);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token));


        $curl_post_data = array(
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
            'TransactionDesc' => $TransactionType,
            'Remark'=> $Remark
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response=curl_exec($curl);
        return $curl_response;


    }
}