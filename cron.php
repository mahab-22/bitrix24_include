<?php

    try {
      $DB = new PDO("mysql:dbname=b24;host=127.0.0.1;charset=UTF8", "b24", "6hyFdcv5");
      $DB->exec("set names utf8");
      $DB->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    } catch (Exception $m) 
    {
      file_put_contents('log.log',print_r("DB connection cant be started",true),FILE_APPEND);
      die ("DB connection can't be started, {$m->getMessage()}\n");
    };
    echo $timestampPlus10Days.PHP_EOL;
    $res = $DB->query("SELECT * FROM servers WHERE (expires+1) >= $timestampPlus10Days");
    while ($row = $res->fetch())
    {
      print_r($row);
    }

echo "Работает!!!!!!!!!!!!!!";
?>