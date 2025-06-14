<?php

function send_tg_msg($txt)
{  
global $tg_config;
$params=[
'chat_id'=>$tg_config['tgr_user'],
'text'=> $txt
];
$req_uri="https://api.telegram.org/bot".$tg_config['tgr_key']."/sendMessage?".http_build_query($params);
file_get_contents($req_uri);
}


?>
