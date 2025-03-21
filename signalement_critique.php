<?php
// Connexion à la base de données
include 'config.php';

$query = "SELECT Design, stock FROM PRODUIT WHERE stock < 10";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    // echo "<ul style='color: green;'>";
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        // echo "<li> ⚠️ Produit : " . $row['Design'] . " - Stock restant : " . $row['stock'] . " litres</li>";
        echo "<li><div>⚠️</div><div>" . $row['Design'] . "</div><div>" . $row['stock'] . " litres</div></li>";
    }
    echo "</ul>";
} else {
    echo "Aucun produit en stock critique.";
}
?>
