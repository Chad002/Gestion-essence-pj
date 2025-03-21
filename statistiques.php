<?php
include("config.php");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Produits</title>
    <style>
        * { font-family: Arial, sans-serif; }
        body { display: flex; background: whitesmoke; height: 100vh; }

        /* Barre de navigation */
        .sidebar {
            width: 250px;
            height: 100vh;
            /* background: linear-gradient(30deg, #ff001d, #ff2a4e); */
            background: linear-gradient(to right, #3498db, #2980b9);
            padding: 20px;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }
        .sidebar h2 { text-align: center; margin-bottom: 60px; font-size: 27px; }
        .sidebar ul { list-style: none; padding: 0; text-align: center; cursor: pointer; }
        .sidebar ul li { padding: 15px; border-bottom: 2px solid rgba(255, 255, 255, 0.2); font-weight: bold; transition: 0.4s; }
        .sidebar ul li a { color: white; text-decoration: none; display: block; }
        .sidebar ul li:hover { background: rgba(255, 255, 255, 0.4); transform: scale(1.1); }

        .main-content { margin-left: 350px; margin-right: 75px;  padding: 20px; width: 100%; }
        

        .CONTAINER-STATS {
        background-color: #f4f4f9;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        width: 100%;
        }

        .CONTAINER-STATS h2 {
        margin-bottom: 15px;
        color: #333;
        }
    </style>
</head>
<body>

    <!-- Barre de navigation -->
    <div class="sidebar">
        <h2>Station Essence</h2>
        <ul>
            <li><a href="index.php">Tableau de bord</a></li>
            <li><a href="produits.php">PRODUIT</a></li>
            <li><a href="entree.php">ENTREE</a></li>
            <li><a href="achat.php">ACHAT</a></li>
            <li><a href="service.php">SERVICE</a></li>
            <li><a href="entretien.php">ENTRETIEN</a></li>
            <li><a href="statistiques.php">Statistiques</a></li>
        </ul>
    </div>

<!-- Contenu principal -->
<div class="main-content">
    <div class="CONTAINER-STATS">
        <!-- Histogramme des recettes par mois (les 5 derniers mois) -->
        <h2>Histogramme des recettes (5 derniers mois)</h2>
        <canvas id="recetteChart"></canvas>
    </div>
</div>

<!-- Chargement de Chart.js depuis node_modules -->
<script src="node_modules/chart.js/dist/chart.umd.js"></script>

<script>
    const ctx = document.getElementById('recetteChart').getContext('2d');

    <?php
    include('config.php');

    // Requête SQL avec MySQLi
    $sql = "SELECT DATE_FORMAT(dateEntretien, '%Y-%m') AS mois, SUM(S.prix) AS recette 
            FROM ENTRETIEN E 
            JOIN SERVICE S ON E.numServ = S.numServ 
            GROUP BY mois 
            ORDER BY mois DESC 
            LIMIT 5";

    $result = mysqli_query($conn, $sql);

    $mois = [];
    $recettes = [];

    // Vérification de la requête
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_unshift($mois, $row['mois']);
            array_unshift($recettes, $row['recette'] * 5); // Conversion en Ariary
        }
    } else {
        echo "Erreur dans la requête SQL : " . mysqli_error($conn);
    }

    mysqli_close($conn);
    ?>

    // Configuration de l'histogramme avec Chart.js
    // new Chart(ctx, {
    //     type: 'bar',
    //     data: {
    //         labels: <?php echo json_encode($mois); ?>,
    //         datasets: [{
    //             label: 'Recette (Ariary)',
    //             data: <?php echo json_encode($recettes); ?>,
    //             backgroundColor: 'rgba(75, 192, 192, 0.6)',
    //             borderColor: 'rgba(75, 192, 192, 1)',
    //             borderWidth: 1
    //         }]
    //     },
    //     options: {
    //         responsive: true,
    //         scales: {
    //             y: {
    //                 beginAtZero: true
    //             }
    //         }
    //     }
    // });


        new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($mois); ?>,
            datasets: [{
                label: 'Recette (Ariary)',
                data: <?php echo json_encode($recettes); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            // Options pour réduire la largeur des barres
            barPercentage: 0.3, // Réduit la largeur des barres à 50% de la largeur disponible
            categoryPercentage: 0.5 // Réduit l'espace occupé par les barres dans chaque catégorie
        }
    });
</script>


<style>
    .CONTAINER-STATS {
        margin: 20px;
        padding: 20px;
        background-color: #f4f4f9;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        max-width: 800px;
    }

    canvas {
        max-width: 100%;
    }
</style>

    

</body>
</html>