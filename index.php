<?php
/**
 *  titolo: Jamendo_searchbot
 *  autore: Matteo Enna
 *  licenza GPL3
 **/
 
    define('BOT_TOKEN', '[token-telegram]');
    define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
    
    define('CLIENT_ID_J', '[client-jamenda]');
    define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
    
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);
    $chatID = $update["message"]["chat"]["id"];
    
    $bvn=array(
        'Benvenuto su Jamendo Search Bot, ',      
        'il bot telegram per cercare le canzoni libere presenti su Jamenda.',
        'il bot è realizzato da @matteoenna'
    );
    
    $benvenuto=implode(chr(10),$bvn);    
    
    $message_text = $update["message"]["text"];
        
    if(!$message_text){
        $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode('Formato non riconosciuto.');
        file_get_contents($sendto);
        die;
    }
        
    if($message_text=="/start"){
        $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($benvenuto);
        file_get_contents($sendto);
        
    }elseif($message_text=="/help"){
        
        $benvenuto .=chr(10).'invia un messaggio con il contenuto che vuoi ricercare.';
        $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($benvenuto);
        file_get_contents($sendto);
    }elseif(strpos($message_text,'/radio')!==FALSE){
        $id = substr($message_text,6);
        if ($id == 'all') {
            $url_radio = 'https://api.jamendo.com/v3.0/radios/?client_id='.CLIENT_ID_J.'&format=jsonpretty&limit=30';
            $result = file_get_contents($url_radio);
            $update = json_decode($result, true);
            $res = $update['results'];
            foreach ($res as $track) {
                $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode('/radio'.$track['name']);
                file_get_contents($sendto);
                
            }
        } else {
            $url_radio = 'https://api.jamendo.com/v3.0/radios/stream/?client_id='.CLIENT_ID_J.'&format=jsonpretty&name='.$id;
            $result = file_get_contents($url_radio);
            $update = json_decode($result, true);
            $res = $update['results'];
            
            $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($res[0]['dispname']);
            file_get_contents($sendto);
            
            $sendto =API_URL."sendPhoto?chat_id=".$chatID."&photo=".urlencode($res[0]['playingnow']['track_image']);
            file_get_contents($sendto);
            
            $name = $res[0]['playingnow']['track_name'].' - '.$res[0]['playingnow']['artist_name'];
            
            $url_track = 'https://api.jamendo.com/v3.0/albums/tracks/?client_id='.CLIENT_ID_J.'&track_id='.$res[0]['playingnow']['track_id'];
            $result = file_get_contents($url_track);
            $update = json_decode($result, true);
            $res = $update['results'][0];
            
            $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($name.chr(10).$res["tracks"][0]['audio']);
            file_get_contents($sendto);
        }
        die();    
    } else {
        if(strpos($message_text,'/info')!==FALSE) {
            
            $id = substr($message_text,5);
            
            $url_i = 'https://api.jamendo.com/v3.0/tracks/?client_id='.CLIENT_ID_J.'&format=json&id='.$id;
        
            $result = file_get_contents($url_i);
            $update = json_decode($result, true);
            
            $res = $update['results'][0];
                       
            $resultate = array(
                'Titolo: '.$res['name'],
                'Artista: '.$res['artist_name'],
                'Album: '.$res['album_name'],
                'Licenza: '.$res['license_ccurl']
            );
            
            $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode(implode(chr(10),$resultate));
            file_get_contents($sendto);
            die;
        }
        $url_s = 'https://api.jamendo.com/v3.0/tracks/?client_id='.CLIENT_ID_J.'&format=json&search='.$message_text;
        
        $result = file_get_contents($url_s);
        $update = json_decode($result, true);
        
        $res = $update['results'];
                        
        $acapo=chr(10);
        
        $i = 0;
        
        foreach ($res as $track) {
            
            $response = $track['name'].' '.$track['audio'].$acapo."Informazioni sulla traccia /info".$track['id']; 
            
            $sendto =API_URL."sendPhoto?chat_id=".$chatID."&photo=".urlencode($track['image']);
            file_get_contents($sendto);
            
            $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($response);
            file_get_contents($sendto);
            
            $i++;
            
            if($i==9){
                die;
            }
            
        }
        
    }
?>
