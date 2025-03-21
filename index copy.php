<?php
// Inclure la connexion à la base de données
include("config.php");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Station Essence</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            display: flex;
        }

        /* Barre de navigation */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(to right, #3498db, #2980b9);    
            padding: 20px;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 60px;
            font-size: 27px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            text-align: center;
            cursor: pointer;
        }

        .sidebar ul li {
            padding: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            font-weight: bold;
            transition: 0.4s;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar ul li:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: scale(1.1);
        }

        /* Contenu principal */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        /* DASHBOARD */

        .CONTAINERS-DASHBOARDS{
            display: grid;
            /* grid-template-columns: 1fr 1fr;  */
            grid-template-columns: 2fr 1fr; 
            gap: 20px;
            padding: 20px;
            background: #f4f4f9;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }
        .CD2, .CD3, .CD4{
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
        }
        .CD1 h2{
            margin-bottom: 15px;
            /* color: #c0392b; */
            color: #333;
        }
        .CD1{
            grid-column: 1 / 3;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
        }
        #signalement-critique{
            color: #333;
            font-weight: bold;
            /* background: #ffebeb; */
            padding: 10px;
            border-radius: 5px;
            /* border: 1px solid #e74c3c; */
        }
        #signalement-critique ul {
            color: red;
            list-style: none;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 20px;
            /* background:rgb(128, 128, 128); */
        }
        #signalement-critique ul li{
            /* background: red; */
            background: #ffebeb;
            margin-top: 5px;
            margin-bottom: 5px;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }

        .CD2 p{
            font-size: 1.8em;  
            font-weight: bold;
            color: #56bc70;
            margin-top: 10px;
        }
        .CD2{
            display: flex;
            justify-content: space-between;
        }
        .money-icon {
            width: 50px; /* Taille de l'icône */
            height: 50px;
        }



        .CD3 h2 {
            margin-top: 0;
        }
        .CD3 ul {
            list-style-type: none;
            padding: 0;
        }
        .CD3 ul li {
            background-color: #fff;
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    <!-- <div class="main-content">
        <h1>Tableau de bord</h1>
        <p>Hello</p>
    </div> -->

        <div class="main-content">
            <div class="CONTAINERS-DASHBOARDS">
                <div class="CD1">   
                    <h2>Produits en Stock Critique</h2>
                    <div id="signalement-critique">
                        <?php include 'signalement_critique.php'; ?>
                    </div>
                </div>


                <div class="CD2">
                    <div>
                        <h3 style="color: #333; font-size: 15px">Recette Totale Accumulée</h3>
                        <p>
                            <?php
                                $requete = "SELECT SUM(prix) AS recette_totale FROM SERVICE s 
                                JOIN ENTRETIEN e ON s.numServ = e.numServ";
                                $resultat = mysqli_query($conn, $requete);
                                $data = mysqli_fetch_assoc($resultat);

                                // echo "Recette totale : " . ($data['recette_totale'] ? $data['recette_totale'] . " ARIARY" : "0 ARIARY");
                                echo ($data['recette_totale'] ? $data['recette_totale'] . " Ariary" : "0 Ariary");
                            ?>
                        </p>
                    </div>
                        

                    <div>
                        <svg
                            class="money-icon"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 64 64"
                        >
                            <circle cx="32" cy="32" r="30" stroke="#9dfd9d" stroke-width="4" fill="#9dfd9d" />
                            <circle cx="32" cy="32" r="24" fill="#9dfd9d" />
                            <text
                                x="32"
                                y="38"
                                font-size="29"
                                font-weight="bold"
                                text-anchor="middle"
                                fill="#00dc3d"
                            >
                            $
                            </text>
                        </svg>
                    </div>    
                </div>
                <div class="CD3">
                    <?php include 'les5participatifs.php'; ?>
                </div>
                <div class="CD4"></div>
            </div>
        </div>

</body>
</html>