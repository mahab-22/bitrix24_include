<?php
    //require_once ('include/common.php');	
	class CRestBD
    {
    
        public static function call($method, $auth_data, $params = [])
        {
            $arPost = [
                'method' => $method,
                'params' => $params
            ];
            $url = $auth_data['client_endpoint'].$arPost['method'].'.json';
            $obCurl = curl_init();
            curl_setopt($obCurl, CURLOPT_URL, $url);
            curl_setopt($obCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($obCurl, CURLOPT_POSTREDIR, 10);
            curl_setopt($obCurl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
            curl_setopt($obCurl, CURLOPT_FOLLOWLOCATION, false ); 
            curl_setopt($obCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($obCurl, CURLOPT_SSL_VERIFYHOST, false);             
            $arPost['params']['auth'] = $auth_data['access_token'];
            $sPostFields = http_build_query($arPost[ 'params' ]);   
            if($sPostFields)
            {
                curl_setopt($obCurl, CURLOPT_POST, true);
                curl_setopt($obCurl, CURLOPT_POSTFIELDS, $sPostFields);
            }
            file_put_contents('/var/www/bitrix24.paykeeper.ru/log/posts.log', print_r($arPost,true),FILE_APPEND); 
            $out = curl_exec($obCurl);
            $out = json_decode($out,true);
            $info = curl_getinfo($obCurl);
            if(curl_errno($obCurl))
            {
                $info[ 'curl_error' ] = curl_error($obCurl);
                return $info[ 'curl_error' ]; // Вернуть ошибку соединения curl
            }    
            curl_close($obCurl); 
            return  $out;
        }

        public static function GetNewAuth($auth_data)
        {
            $url = 'https://oauth.bitrix.info/oauth/token/?';
            $query = [
                'client_id'     => $auth_data[ 'C_REST_CLIENT_ID' ],
                'grant_type'    => 'refresh_token',
                'client_secret' => $auth_data[ 'C_REST_CLIENT_SECRET' ],
                'refresh_token' => $auth_data[ "refresh_token" ],
            ];
            $getFields = http_build_query($query);
            $url .=  $getFields; 

            $obCurl = curl_init();
            curl_setopt($obCurl, CURLOPT_URL, $url);
            curl_setopt($obCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($obCurl, CURLOPT_POSTREDIR, 10);
            curl_setopt($obCurl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
            curl_setopt($obCurl, CURLOPT_FOLLOWLOCATION, 0 ); 
            curl_setopt($obCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($obCurl, CURLOPT_SSL_VERIFYHOST, false);  
            $out = curl_exec($obCurl);
            $out = json_decode($out,true);
            $info = curl_getinfo($obCurl);
            if(curl_errno($obCurl))
            {
                $info[ 'curl_error' ] = curl_error($obCurl);
                return $info[ 'curl_error' ]; // Вернуть ошибку соединения curl
            }
            curl_close($obCurl); 
            return  $out;
        }

    }

?>