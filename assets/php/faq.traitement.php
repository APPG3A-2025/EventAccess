<?php

require_once 'Connexion.php';

$servername = "localhost";
$username = "root";
$password = "root";

try {
  $bdd = new PDO("mysql:host=$servername;dbname=event_access", $username, $password);
  
  $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

if (isset($_POST["ok"])){
    
    $qst = $_POST["qst"];
    $mail=$_POST["mail"];
   


    $requete = $bdd -> prepare("INSERT INTO Qst VALUES (0, :qst, :mail)") ;
    $requete ->execute(
        array(
            "qst" => $qst,
            "mail" => $mail,
        )
        );

   
}                   
?>