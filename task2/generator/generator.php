<?php
/**
 * Черновой скрипт для генерации базы.
 * Расчитан на запуск из консоли.
 * Генерация 100 000 000 записей прошла приблизительно за 40 минут.
 */
$host = "<host>";
$dbname = "<name>";
$user = "<user>";
$pass = "<pass>";
$DBH = new PDO("mysql:host={$host};dbname={$dbname}", $user, $pass);
$pNumber = 1000;
$placeholders = [];
for($i = 0; $i < $pNumber; $i++) {
    $placeholders[] = "(?, ?, ?)";
}
$placeholders = join(",", $placeholders);

$STH = $DBH->prepare("INSERT INTO users (name, gender, email) VALUES {$placeholders}");

$domains = [
    'ya.ru',
    'yandex.ru',
    'mail.ru',
    'inbox.ru',
    'bk.ru',
    'list.ru',
    'rambler.ru',
    'gmail.com',
    'yahoo.com',
    'hotmail.com',
    'mailinator.com'
];

$num = 1000000;
$rows = [];
for($i = 0; $i < $num; $i++){
    $emailCount = rand(0, 5);
    $name = null;
    $gender = null;
    $emails = [];
    for($j = 0; $j < $emailCount; $j++) {
        $dom = rand(1, count($domains) - 1);
        $emails[] = "{$i}_{$j}@{$domains[$dom]}";
    }
    $gender = $i % 2;
    $name = $i;
    $emails = is_array($emails) ?  @join(",", $emails) : "";

    $rows[] = $name;
    $rows[] = $gender;
    $rows[] = $emails;

    if($i >= $pNumber && $i % 1000 == 0){
        $STH->execute($rows);
        $rows = [];
        echo "cur: ", $i, "\n";
        echo "left: ", $num - $i, "\n";
    }

}