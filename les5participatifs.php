<?php
// Inclure la connexion à la base de données
include("config.php");


// Exécuter la requête SQL
$sql = "SELECT nomClient, COUNT(*) AS nombre_entretiens FROM ENTRETIEN GROUP BY nomClient ORDER BY nombre_entretiens DESC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Afficher les résultats dans le bloc CD3
    // echo '<div class="CD3">';
    echo '<h2>Top 5 des clients les plus participatifs (Entretiens)</h2>';
    echo '<ul>';
    while($row = $result->fetch_assoc()) {
        echo '<li><div>' . $row["nomClient"] . '</div><div>' . $row["nombre_entretiens"] . ' entretiens</div></li>';
    }
    echo '</ul>';
    // echo '</div>';
} else {
    // echo '<div class="CD3">Aucun résultat trouvé</div>';
    echo 'Aucun résultat trouvé';
}

?>