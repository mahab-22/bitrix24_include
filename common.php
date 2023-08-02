<?php

function Error ($msg) {
  $result = array (
    "result"=>"fail",
    "msg"=>"$msg"
  );
  file_put_contents(__DIR__."/../log/incoming.log", 'Answer to '.$_SERVER['REMOTE_ADDR'].' '.print_r($result,true), FILE_APPEND);
  die(json_encode($result));
}

function DieWithHTTPError ($short_message, $description, $code = 520)
{
  global $REPORT_ERROR_AS_HTTP_200;

  if ($REPORT_ERROR_AS_HTTP_200)
      $code = "200 ERR";

  header("HTTP/1.1 $code $short_message");
  error_log("$short_message: ".$description);
  Error($description);
}

function StartDB()                                                                                                                   
{
  try {
    $DB = new PDO("mysql:dbname=b24;host=127.0.0.1;charset=UTF8", "b24", "6hyFdcv5");
    $DB->exec("set names utf8");
    $DB->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  } catch (Exception $m) 
  {
    file_put_contents('log.log',print_r("DB connection cant be started",true),FILE_APPEND);
    die ("DB connection can't be started, {$m->getMessage()}\n");
  }
  try {
    $sql_add_server =       "call add_server( :pk_url,
                                              :pk_secret_seed,  
                                              :b24_url,
                                              :access_token,
                                              :expires,
                                              :expires_app,
                                              :refresh_token,
                                              :app_token,
                                              :member_id,
                                              :rest_client_id,
                                              :rest_client_secret,
                                              :shop_key,
                                              :handler,
                                              :paysystems_text,
                                              :reserv_1,
                                              :reserv_2
                                            )";
    $sth_add_server = $DB->prepare($sql_add_server, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    $sql_update_server = "call update_server( :pk_url,
                                              :pk_secret_seed,
                                              :b24_url,
                                              :access_token,
                                              :expires,
                                              :expires_app,
                                              :refresh_token,
                                              :app_token,
                                              :member_id,
                                              :rest_client_id,
                                              :rest_client_secret,
                                              :shop_key,
                                              :handler,
                                              :paysystems_text,
                                              :reserv_1,
                                              :reserv_2
                                            )";
    $sth_update_server = $DB->prepare($sql_update_server, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sql_get_server_by_id = "call get_server_by_id(:server_id)";
    $sth_get_server_by_id = $DB->prepare($sql_get_server_by_id, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sql_update_server_is_installed_paysystem = "call update_server_is_installed_paysystem(:server_id,:install_handler,:install_paysystem)";
    $sth_update_server_is_installed_paysystem = $DB->prepare($sql_update_server_is_installed_paysystem, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sql_get_server_by_login = "call get_server_by_login(:login)";
    $sth_get_server_by_login = $DB->prepare($sql_get_server_by_login, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sql_add_transaction = "call add_transaction(:server_id,:orderid,:phone,:email,:sum,:cart)";
    $sth_add_transaction = $DB->prepare($sql_add_transaction, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sql_update_transaction = "call update_transaction(:transaction_id,:status)";
    $sth_update_transaction = $DB->prepare($sql_update_transaction, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sql_get_transaction = "call get_transaction(:transaction_id)";
    $sth_get_transaction = $DB->prepare($sql_get_transaction, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sql_get_server_by_id = "call get_server_by_id(:server_id)";
    $sth_get_server_by_id = $DB->prepare($sql_get_server_by_id, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sql_token_update = "call token_update(:server_id, :acces_token,:refresh_token, :expires)";
    $sth_token_update = $DB->prepare($sql_token_update, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));    
    $sql_get_server_by_url = "call get_server_by_url(:b24_url)";
    $sth_get_server_by_url = $DB->prepare($sql_get_server_by_url, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));    
    $sql_get_auth_more_10_days = "call get_auth_more_10_days(:timestamp)";
    $sth_get_auth_more_10_days = $DB->prepare($sql_get_auth_more_10_days, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
  } catch (Exception $e)
  {
    file_put_contents('log.log',print_r("Statement couldn't be prepared",true),FILE_APPEND);
    DieWithHTTPError("Statement couldn't be prepared", "Statement preparing failed, {$e->getMessage()}");
  };

  return array(
    "get_server_by_login"                     =>  $sth_get_server_by_login,
    "add_transaction"                         =>  $sth_add_transaction,
    "add_server"                              =>  $sth_add_server,
    "update_transaction"                      =>  $sth_update_transaction,
    "get_transaction"                         =>  $sth_get_transaction,
    "update_server_is_installed_paysystem"    =>  $sth_update_server_is_installed_paysystem,
    "get_server_by_id"                        =>  $sth_get_server_by_id,
    "token_update"                            =>  $sth_token_update,
    "get_server_by_url"                       =>  $sth_get_server_by_url,
    "update_server"                           =>  $sth_update_server,
    "get_auth_more_10_days"                   =>  $sth_get_auth_more_10_days
  );
}


function GetServerByURL ($sth,$b24_url)
{
  try 
  {
    $sth->execute(array(":b24_url"=>$b24_url));
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    return $result;
    
  } 
  catch (Exception $e)
  {
    DieWithHTTPError("Get transaction failed", "Couldn't get transaction, {$e->getMessage()}");
  }
}

function TokenUpdate ($sth,$server_id, $acces_token, $refresh_token, $expires)
{
  try {
    $sth->execute(array(
          ":server_id" => $server_id,
          ":acces_token"=>$acces_token,
          ":refresh_token" =>$refresh_token,
          ":expires" => $expires
          ));
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    if (is_array($result)) {
      if (count($result)>1)
        throw new Exception("More than one server associated with this login");
      return $result[0];
    }else
        throw new Exception("No servers found for this login");
  } catch (Exception $e)
  {
    DieWithHTTPError("Get server failed", "Couldn't get server, {$e->getMessage()}");
  }
}
function GetServerByLogin ($sth,$login)
{
  try {
    $sth->execute(array(
          ":login"=>$login
          ));
    $server = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    if (is_array($server)) {
      if (count($server)>1)
        throw new Exception("More than one server associated with this login");
      return $server[0];
    }else
        throw new Exception("No servers found for this login");
  } catch (Exception $e)
  {
    DieWithHTTPError("Get server failed", "Couldn't get server, {$e->getMessage()}");
  }
}

function GetServerById ($sth,$server_id)
{
  try {
    $sth->execute(array(
          ":server_id"=>$server_id
          ));
    $server = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    if (is_array($server)) {
      if (count($server)>1)
        throw new Exception("More than one server associated with this id");
      return $server[0];
    }else
        throw new Exception("No servers found for this id");
  } catch (Exception $e)
  {
    DieWithHTTPError("Get server failed", "Couldn't get server, {$e->getMessage()}");
  }
}

function GetTransaction ($sth,$transaction_id)
{
  try {
    $sth->execute(array(
          ":transaction_id"=>$transaction_id
          ));
    $transaction = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    if (is_array($transaction)) {
      if (count($transaction)>1)
        throw new Exception("More than one transaction associated with this id");
      return $transaction[0];
    }else
        throw new Exception("No transactions found for this id");
  } catch (Exception $e)
  {
    DieWithHTTPError("Get transaction failed", "Couldn't get transaction, {$e->getMessage()}");
  }
}

function UpdateTransaction($sth,$transaction_id,$status)
{
  try {
    $sth->execute(array(
          ":transaction_id"=>$transaction_id,
          ":status"=>$status
          ));
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    return $result;
  } catch (Exception $e)
  {
    DieWithHTTPError("Update transaction failed", "Couldn't update transaction, {$e->getMessage()}");
  }
}

function AddTransaction($sth, $server_id,$orderid,$phone,$email,$sum,$cart)
{
  try {
    $sth->execute(array(
          ":server_id"=>$server_id,
          ":orderid"=>$orderid,
          ":phone"=>$phone,
          ":email"=>$email,
          ":sum"=>$sum,
          ":cart"=>$cart));
    $res = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    return $res[0]['Result'];
  } catch (Exception $e)
  {
    DieWithHTTPError("Transaction insert failed", "Couldn't insert new transaction, {$e->getMessage()}");
  }
}
function UpdateServer($sth,$pk_url, $pk_secret_seed,$b24_url, $access_token,$expires,$expires_app,
  $refresh_token,$app_token,$member_id,$rest_client_id,$rest_client_secret,$shop_key, $handler_id, $paysystems_text, $reserv_1, $reserv_2)
{
  try {
    $sth->execute(array(
          ":pk_url"=>$pk_url,
          ":pk_secret_seed"=>$pk_secret_seed,
          ":b24_url"=>$b24_url,
          ":access_token"=>$access_token,
          ":expires"=>$expires,
          ":expires_app"=>$expires_app,
          ":refresh_token"=>$refresh_token,
          ":app_token"=>$app_token,
          ":member_id"=>$member_id,
          ":rest_client_id"=>$rest_client_id,
          ":rest_client_secret"=>$rest_client_secret,
          ":shop_key"=>$shop_key,
          ":handler"=>$handler_id,
          ":paysystems_text"=>$paysystems_text,
          ":reserv_1"=>$reserv_1,
          ":reserv_2"=>$reserv_2
        ));
 
    $res = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    return $res[0]['Result'];
  } catch (Exception $e)
  {
    DieWithHTTPError("Add Aplication update failed", "Couldn't update new client, {$e->getMessage()}");
  }
}
function AddServer($sth,$pk_url, $pk_secret_seed,$b24_url, $access_token,$expires,$expires_app,
$refresh_token,$app_token,$member_id,$rest_client_id,$rest_client_secret,$shop_key, $handler_id,$paysystems_text, $reserv_1, $reserv_2)
{
  try {
    $sth->execute(array(
          ":pk_url"=>$pk_url,
          ":pk_secret_seed"=>$pk_secret_seed,
          ":b24_url"=>$b24_url,
          ":access_token"=>$access_token,
          ":expires"=>$expires,
          ":expires_app"=>$expires_app,
          ":refresh_token"=>$refresh_token,
          ":app_token"=>$app_token,
          ":member_id"=>$member_id,
          ":rest_client_id"=>$rest_client_id,
          ":rest_client_secret"=>$rest_client_secret,
          ":shop_key"=>$shop_key,
          ":handler"=>$handler_id,
          ":paysystems_text"=>$paysystems_text,
          ":reserv_1"=>$reserv_1,
          ":reserv_2"=>$reserv_2
        ));
    $res = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    return $res[0]['Result'];
  } catch (Exception $e)
  {
    DieWithHTTPError("Add Aplication insert failed", "Couldn't insert new client, {$e->getMessage()}");
  }
}

function UpdateServerIsInstalledPaysystem($sth,$server_id)
{
    try {
        $sth->execute(array(
            ":server_id"=>$server_id,
            ":install_handler" => 1,
            ":install_paysystem" => 1
        ));
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $sth->closeCursor();
        return $result;
    } catch (Exception $e)
    {
        DieWithHTTPError("Update transaction failed", "Couldn't update transaction, {$e->getMessage()}");
    }
}

function GetPkCart($cart_data)
{
  $cart = array();
  foreach($cart_data["Items"] as $item) {
    $name = $item["Name"];
    $name = str_replace("\n ", "", $name);                                                                                      
    $name = str_replace("\r ", "", $name);
    $name = str_replace("'", "`", $name);
    $quantity = $item["Quantity"];
    $sum = $item["Sum"];
    if ($quantity <= 0)
      $quantity = 1;
    $price = $sum/$quantity;
    $tax = GetTax($item["Tax"]);
    $cart[] = array(
        "name" => $name,
        "price" => number_format($price, 2, ".", ""),
        "quantity" => $quantity,
        "sum" => number_format($sum, 2, ".", ""),
        "tax" => $tax
      );
  }
  return $cart;
}

function GetTax($tax_rate)
{
  switch($tax_rate) {
    case "Vat0":
      return "vat0";
      break;
    case "Vat10":
      return "vat10";
      break;
    case "Vat18":
      return "vat18";
      break;
    case "Vat20":
      return "vat20";
      break;
    default:
      return "none";
      break;
  }
}

function ke($a, $b)
{
  $array = $a;
  $key = $b;
  if (!is_array($a))
  {
    $array = $b;
    $key = $a;
  };

  if (!is_array($array))
    return NULL;
  return (array_key_exists($key, $array))?$array[$key]:NULL;
}

function either ()
{
  for ($i=0; $i<func_num_args(); $i++)
  {
    $a = func_get_arg($i);
    if ($a != NULL)
      return $a;
  }
  return NULL;
}

function GetRandomString($length, $mode)
{
  switch($mode) {
    case 'login':
      $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
      break;
    case 'password':
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-@_%';
      break;
  }
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}
function authDataFromDB($sth, $server_id)
{
  $server_info = GetServerById($sth["get_server_by_id"],$server_id);

  if (is_array($server_info)) 
  {
    $auth_data = [];
    $auth_data['access_token']          =   $server_info['access_token'] ;
    $auth_data['expires']               =   $server_info['expires'] ;
    $auth_data['b24_url']                =   $server_info['b24_url'];
    $auth_data['refresh_token']         =   $server_info['refresh_token'];
    $auth_data['application_token']     =   $server_info['app_token'];
    $auth_data['C_REST_CLIENT_ID']      =   $server_info['rest_client_id'];
    $auth_data['C_REST_CLIENT_SECRET']  =   $server_info['rest_client_secret'];
    $auth_data['client_endpoint']       =   (substr($server_info['b24_url'], -1)=='/')?$server_info['b24_url'] . 'rest/':$server_info['b24_url'] . '/rest/';
    $auth_data['pk_secret_seed']        =   $server_info['pk_secret_seed'] ;
    $auth_data["server_id"]             =   $server_info["server_id"];
    $auth_data["shop_key"]              =   $server_info["shop_key"];
    $auth_data["handler"]               =   $server_info["handler"];
    $auth_data["paysystems_text"]       =   $server_info["paysystems_text"];
    $auth_data["reserv_1"]              =   $server_info["reserv_1"];
    $auth_data["reserv_2"]              =   $server_info["reserv_2"];

    return $auth_data;
  } 
  else
  {
    return false;
  }
}
function generate_string($strength = 4) {
  $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $input_length = strlen($input);
  $random_string = '';
  for($i = 0; $i < $strength; $i++) {
      $random_character = $input[mt_rand(0, $input_length - 1)];
      $random_string .= $random_character;
  }

  return $random_string;
}
function GetAuthMore10Days($sth, $timestamp)
{
  try 
  {
    $sth->execute(array(":timestamp"=>$timestamp));
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    return $result;
  } 
  catch (Exception $e)
  {
    DieWithHTTPError("GetAuthMore10Days failed", "Couldn't get data GetAuthMore10Days, {$e->getMessage()}");
  }
}