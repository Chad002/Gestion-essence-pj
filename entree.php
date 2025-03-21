<?php
include("config.php");


// Supprimer une entrée
if (isset($_GET["delete"])) {
    $numEntree = $_GET["delete"];

    // Récupérer la quantité à soustraire du stock
    $result = $conn->query("SELECT stockEntree, numProd FROM ENTREE WHERE numEntree = '$numEntree'");
    $row = $result->fetch_assoc();
    $stockEntree = $row["stockEntree"];
    $numProd = $row["numProd"];

    // Supprimer l'entrée
    $conn->query("DELETE FROM ENTREE WHERE numEntree = '$numEntree'");

    // Mettre à jour le stock du produit concerné
    $conn->query("UPDATE PRODUIT SET stock = stock - $stockEntree WHERE numProd = '$numProd'");

    header("Location: entree.php");
    exit();
}

// Modifier une entrée
if (isset($_POST["modifier"])) {
    $numEntree = $_POST["editNumEntree"];
    $newStockEntree = $_POST["editStockEntree"];
    $dateEntree = $_POST["editDateEntree"];
    $numProd = $_POST["editNumProd"];

    // Récupérer l'ancienne valeur du stock
    $result = $conn->query("SELECT stockEntree, numProd FROM ENTREE WHERE numEntree = '$numEntree'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $oldStockEntree = $row["stockEntree"];
        $numProd = $row["numProd"];

        // Calculer la différence de stock
        $stockDifference = $newStockEntree - $oldStockEntree;

        // Mettre à jour l'entrée
        $conn->query("UPDATE ENTREE SET stockEntree='$newStockEntree', dateEntree='$dateEntree', numProd='$numProd' WHERE numEntree='$numEntree'");

        // Mettre à jour le stock du produit
        $conn->query("UPDATE PRODUIT SET stock = stock + $stockDifference WHERE numProd = '$numProd'");

        header("Location: entree.php");
        exit();
    } else {
        echo "Erreur : Entrée non trouvée.";
    }
}

// Fonction pour générer un numéro d'entrée unique (E001, E002, ...)
function generateEntryNumber($conn) {
    $result = $conn->query("SELECT MAX(numEntree) AS maxNum FROM ENTREE");
    $row = $result->fetch_assoc();
    $maxNum = $row["maxNum"];

    if ($maxNum) {
        $number = intval(substr($maxNum, 1)) + 1;
    } else {
        $number = 1;
    }
    return "E" . str_pad($number, 3, "0", STR_PAD_LEFT);
}

// Ajouter une entrée
if (isset($_POST["ajouter"])) {
    $numEntree = generateEntryNumber($conn);
    $dateEntree = $_POST["dateEntree"];
    $stockEntree = $_POST["stockEntree"];
    $numProd = $_POST["numProd"];

    $sql = "INSERT INTO ENTREE (numEntree, dateEntree, stockEntree, numProd) VALUES ('$numEntree', '$dateEntree', '$stockEntree', '$numProd')";
    $conn->query($sql);

    $conn->query("UPDATE PRODUIT SET stock = stock + $stockEntree WHERE numProd = '$numProd'");

    header("Location: entree.php");
    exit();
}

$result = $conn->query("SELECT * FROM ENTREE");
$produits = $conn->query("SELECT numProd FROM PRODUIT");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Entrées</title>
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


        .main-content {margin-left: 350px; margin-right: 75px;  padding: 20px; width: 100%; }
        .add-btn {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white; padding: 10px 20px;
            border: none; cursor: pointer;
            border-radius: 5px; font-size: 16px;
            transition: 0.4s;
        }
        .add-btn:hover { transform: scale(0.9); }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; text-align: center; padding: 10px; }
        th { background: black; color: white; }
        tr:nth-child(even) { background: white; }
        tr:nth-child(odd) { background: #e8e8e8; }





        .edit-btn, .delete-btn {
            cursor: pointer; padding: 8px;
            border: none; color: white;
            border-radius: 5px; margin: 8px;
            transition: 0.4s;
        }
        .edit-btn { background: linear-gradient(to right, #f1c40f, #f39c12); }
        .delete-btn { background: linear-gradient(to right, #e74c3c, #c0392b); }
        .edit-btn:hover, .delete-btn:hover { transform: scale(0.9); }




        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); justify-content: center; align-items: center;
        }
        .modal-content {
            background: linear-gradient(to right, #2c3e50, #34495e);
            padding: 20px; border-radius: 10px; color: white; 
            width: 400px; 
            height: 400px;
            text-align: center;
        }
        .modal-content h2{
            color: white;
            padding-bottom: 20px;
        }
        .modal-content input {
            padding: 10px; margin-top: 10px;
            width: 80%; border-radius: 5px;
            border: 1px solid #ddd; text-align: center;
        }
        .modal-content .selectss{
            padding: 10px; margin-top: 10px;
            width: 80%; border-radius: 5px;
            border: 1px solid #ddd; text-align: center;
        }
        .btnAddSuppr{
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white; padding: 10px 20px;
            border: none; cursor: pointer;
            border-radius: 5px; font-size: 16px;
            transition: 0.4s;
        }
        .btnAddSuppr:hover { transform: scale(0.9); }
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

    <div class="main-content">
        <h1>Gestion des Entrées</h1>
        <button class="add-btn" onclick="openModal('add')">+ Ajouter Entrée</button>

        <table>
            <tr>
                <th>Numéro</th>
                <th>Date</th>
                <th>Numéro Produit</th>
                <th>Stock Entré</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row["numEntree"] ?></td>
                    <td><?= $row["dateEntree"] ?></td>
                    <td><?= $row["numProd"] ?></td>
                    <td><?= $row["stockEntree"] ?></td>
                    <td>
    <button class="edit-btn" onclick="openEditModal('<?= $row['numEntree'] ?>', '<?= $row['stockEntree'] ?>', '<?= $row['dateEntree'] ?>', '<?= $row['numProd'] ?>')">✏️</button>
    <button class="delete-btn" onclick="confirmDelete('<?= $row['numEntree'] ?>')">🗑</button>
</td>

                </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Modale d'ajout -->
    <div class="modal" id="modal-add">
        <div class="modal-content">
            <h2>Ajouter une Entrée</h2>
            <form method="POST">
                <label>Date :</label><br>
                <input type="date" name="dateEntree" required><br><br>

                <label>Stock Entré :</label>
                <input type="number" name="stockEntree" required><br><br>

                <label>Numéro Produit :</label>
                <select name="numProd" required class="selectss">
                    <?php while ($prod = $produits->fetch_assoc()) { ?>
                        <option value="<?= $prod["numProd"] ?>"><?= $prod["numProd"] ?></option>
                    <?php } ?>
                </select><br><br>

                <button type="submit" name="ajouter" class="btnAddSuppr">Ajouter</button>
                <button type="button" onclick="closeModal('add')" class="btnAddSuppr">Annuler</button>
            </form>
        </div>
    </div>




            <!-- EDIT MODALE -->

    <div class="modal" id="editModal">
        <div class="modal-content">
            <h2>Modifier une Entrée</h2>
            <form method="POST">

                <label>Date :</label><br>
                <input type="date" name="editDateEntree" id="editDateEntree" required><br><br>
                <label>Stock Entré :</label>
                <input type="hidden" name="editNumEntree" id="editNumEntree"><br><br>
                <input type="number" name="editStockEntree" id="editStockEntree" placeholder="Stock Entrée" required><br><br>
                <label>Numéro Produit :</label>
                <!-- <select name="editNumProd" id="editNumProd" class="selectss" required>
                    //<?php
                    //$produits = $conn->query("SELECT numProd FROM PRODUIT");
                    //while ($prod = $produits->fetch_assoc()) {
                        //echo "<option value='{$prod['numProd']}'>{$prod['numProd']}</option>";
                   // }
                    ?>
                </select> -->
                <br><br>
                <div class="div_boutton">
                    <button type="submit" name="modifier" class="add-btn">Modifier</button>
                    <button type="button" onclick="closeModal2('Modal')" class="btnAddSuppr">Annuler</button>
                </div>
            </form>
        </div>
    </div>



    <script>
        function openModal(id) {
            document.getElementById('modal-' + id).style.display = "flex";
        }

        function closeModal(id) {
            document.getElementById('modal-' + id).style.display = "none";
        }
        function closeModal2(id) {
            document.getElementById('edit' + id).style.display = "none";
        }

        function confirmDelete(numEntree) {
            if (confirm("Voulez-vous vraiment supprimer cette entrée ?")) {
                window.location.href = "entree.php?delete=" + numEntree;
            }
        }



        
        function openEditModal(numEntree, stockEntree, dateEntree, numProd) {
            document.getElementById("editModal").style.display = "flex";
            document.getElementById("editNumEntree").value = numEntree;
            document.getElementById("editStockEntree").value = stockEntree;
            document.getElementById("editDateEntree").value = dateEntree;
            document.getElementById("editNumProd").value = numProd;
        }


    </script>

</body>
</html>