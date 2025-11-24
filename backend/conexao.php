<?php
$host = "db";
$user = "root";
$pass = "BoiDataBase";
$db = "registro_carros";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["status" => "erro", "mensagem" => "Falha na conexão com o banco."]));
}

$conn->set_charset("utf8");
?>