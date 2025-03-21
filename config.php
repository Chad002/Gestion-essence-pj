<?php

$host = "localhost";
$user = "root"; 
$password = ""; 
$database = "station_essence";

// Connexion à la base de données
$conn = new mysqli($host, $user, $password, $database);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Définir l'encodage des caractères pour éviter les erreurs d'affichage
$conn->set_charset("utf8");

?>