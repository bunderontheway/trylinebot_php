    <?php
    require __DIR__ . '/vendor/autoload.php';

    use \LINE\LINEBot;
    use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
    use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
    use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
    use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
    use \LINE\LINEBot\SignatureValidator as SignatureValidator;

    // set false for production
    $pass_signature = true;

    // set LINE channel_access_token and channel_secret
    $channel_access_token = "pRS1ZCvX53WeG8k/7n0A3jKMGeTeZlzCDDJuDtfp3U5oAYZICv+HprdN0blk9ooVvxj6yH5F/sr5Q+jCWF+d/Y7A0K+MV2LaQjh6Z9XSlFHAL15izz2VYqPGtYFlvE8wAgbV8kCcpghmWcuELgrG1AdB04t89/1O/w1cDnyilFU=";
    $channel_secret = "2952e9151df7c2ff483fe5eae852e298";

    // inisiasi objek bot
    $httpClient = new CurlHTTPClient($channel_access_token);
    $bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

    $configs =  [
        'settings' => ['displayErrorDetails' => true],
    ];
    $app = new Slim\App($configs);

    // buat route untuk url homepage
    $app->get('/', function($req, $res)
    {
      echo "Welcome at Slim Framework";
    });

    // buat route untuk webhook
    $app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
    {
        // get request body and line signature header
        $body        = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

        // log body and signature
        file_put_contents('php://stderr', 'Body: '.$body);

        if($pass_signature === false)
        {
            // is LINE_SIGNATURE exists in request header?
            if(empty($signature)){
                return $response->withStatus(400, 'Signature not set');
            }

            // is this request comes from LINE?
            if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
                return $response->withStatus(400, 'Invalid signature');
            }
        }

        // kode aplikasi nanti disini
    	$data = json_decode($body, true);
	if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                {
                    // send same message as reply to user
                    //$result = $bot->replyText($event['replyToken'], $event['bales']['balesan satu']);
		    $result = $bot->replyText($replyToken, 'ini pesan balesan');
     		    //$textMessageBuilder = new TextMessageBuilder('ini pesan balasan');
		    //$bot->replyMessage($replyToken, $textMessageBuilder);


                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
     
                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }
            }
        } 
    }

    });

//PUSH MESSAGE
    $app->get('/pushmessage', function($req, $res) use ($bot)
    {
        // send push message to user
        $userId = 'Ub0208a183ccb685ffb52a0b6c804aeb4';
        $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
        $result = $bot->pushMessage($userId, $textMessageBuilder);

        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
    });

//MULTICAST MESSAGE
    $app->get('/multicast', function($req, $res) use ($bot)
    {
        // list of users
       // $userList = [
         //   'Ub0208a183ccb685ffb52a0b6c804aeb4',
           // 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            //'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            //'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            //'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'];
     
        // send multicast message to user
        $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
        $result = $bot->multicast($userList, $textMessageBuilder);
       
        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
    });


    $app->get('/profile/{userId}', function($req, $res) use ($bot)
    {
        // get user profile
        $route  = $req->getAttribute('route');
        $userId = $route->getArgument('userId');
        $result = $bot->getProfile($userId);
                 
        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
    });
    $app->run();
