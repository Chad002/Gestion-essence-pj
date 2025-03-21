<?php
session_start();
include 'config.php';

// Générer numéro de service
function genererNumeroService($conn) {
    $result = $conn->query("SELECT MAX(numServ) AS dernier FROM SERVICE");
    $row = $result->fetch_assoc();
    $dernier = $row['dernier'];

    if ($dernier) {
        $num = intval(substr($dernier, 1)) + 1;
        return 'S' . str_pad($num, 4, '0', STR_PAD_LEFT);
    } else {
        return 'S0001';
    }
}

// Ajouter service
if (isset($_POST['ajouterService'])) {
    $numServ = genererNumeroService($conn);
    $service = $_POST['service'];
    $prix = $_POST['prix'];

    $sql = "INSERT INTO SERVICE (numServ, service, prix) VALUES ('$numServ', '$service', $prix)";
    
    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Service ajouté !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Erreur : ' . $conn->error;
        $_SESSION['message_type'] = 'error';
    }
    header("Location: service.php");
    exit();
}

// Modifier service
if (isset($_POST['modifierService'])) {
    $numServ = $_POST['editNunServ'];
    $service = $_POST['editService'];
    $prix = $_POST['editPrix'];

    $sql = "UPDATE SERVICE SET service='$service', prix=$prix WHERE numServ='$numServ'";
    
    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Service modifié !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Erreur : ' . $conn->error;
        $_SESSION['message_type'] = 'error';
    }
    header("Location: service.php");
    exit();
}

// Supprimer service
if (isset($_GET['supprimer'])) {
    $numServ = $_GET['supprimer'];
    $conn->query("DELETE FROM SERVICE WHERE numServ='$numServ'");
    header("Location: service.php");
    exit();
}

// Récupérer services
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM SERVICE" . (!empty($search) ? " WHERE service LIKE '%$search%'" : "");
$services = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Services</title>
    <style>
        /* Styles identiques à achat.php */
        * { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { 
            display: flex; 
            background: whitesmoke; 
            height: 100vh; 
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(to right, #3498db, #2980b9);
            padding: 20px;
            color: white;
            position: fixed;
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

        .main-content { 
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }

        .alert {
            position: fixed;
            top: 40%;
            right: 37%;
            box-shadow: 0 0 5px rgba(0,0,0,0.5);
            width: 230px;
            height: 230px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            justify-content: center;
            background: white;
            color:rgb(0, 216, 50);
            font-weight: 500;
            z-index: 1000;

        }

        .alert-success {
            background: white;
            color:rgb(0, 216, 50);
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .search-bar { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 20px;
            align-items: center;
        }

        .search-input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            font-size: 16px;
        }

        .add-btn {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white; 
            padding: 10px 20px;
            border: none; 
            cursor: pointer;
            border-radius: 5px; 
            font-size: 16px;
            transition: 0.4s;
        }
        .add-btn:hover {
            transform: scale(0.9);
        }


        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
        }

        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: center; 
        }

        th { 
            background: black; 
            color: white; 
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:nth-child(odd) {
            background: #e8e8e8;
        }

        .modal {
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%;
            background: rgba(0,0,0,0.5); 
            justify-content: center; 
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 25px; 
            border-radius: 10px; 
            width: 400px; 
        }
        .edit-btn, .delete-btn {
            cursor: pointer; padding: 8px;
            border: none; color: white;
            border-radius: 5px; margin: 8px;
            transition: 0.4s;
        }

        .edit-btn { background: linear-gradient(to right, #f1c40f, #f39c12); }
        .delete-btn { background: linear-gradient(to right, #e74c3c, #c0392b); }
        .edit-btn:hover, .delete-btn:hover { transform: scale(0.9); }
    </style>
</head>
<body>
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
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                <?= $_SESSION['message'] ?>
            </div>
            <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <h1>Gestion des Services</h1><br><br>
        
        <div class="search-bar">
            <button class="add-btn" onclick="openModal('add')">+ Ajouter Service</button>
            <form method="GET" style="margin-left: auto;">
                <input type="text" 
                       name="search" 
                       class="search-input"
                       placeholder="Rechercher..."
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="add-btn">🔍 Rechercher</button>
                <button type="button" onclick="window.location.href='service.php'" class="add-btn">🔄 Réinitialiser</button>
            </form>
        </div>

        <table>
            <tr>
                <th>Numéro</th>
                <th>Nom</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $services->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["numServ"]) ?></td>
                    <td><?= htmlspecialchars($row["service"]) ?></td>
                    <td><?= htmlspecialchars($row["prix"]) ?> Ar</td>
                    <td>
                        <button onclick="openEditModal(
                            '<?= $row['numServ'] ?>',
                            '<?= $row['service'] ?>',
                            '<?= $row['prix'] ?>'
                        )" class="edit-btn">
                            ✏️
                        </button>
                            <button onclick="confirmDelete('<?= $row['numServ'] ?>')" class="delete-btn">🗑</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Modale Ajout -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <h2>Nouveau Service</h2><br><br>
            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label>Nom du service :</label>
                    <input type="text" name="service" required style="width: 100%; padding: 8px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Prix :</label>
                    <input type="number" name="prix" required style="width: 100%; padding: 8px;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="ajouterService" class="add-btn">Enregistrer</button>
                    <button type="button" onclick="closeModal('add')" class="add-btn">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Édition -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h2>Modifier Service</h2><br><br>
            <form method="POST">
                <input type="hidden" name="editNunServ" id="editNunServ">
                
                <div style="margin-bottom: 15px;">
                    <label>Nom du service :</label>
                    <input type="text" name="editService" id="editService" required style="width: 100%; padding: 8px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Prix :</label>
                    <input type="number" name="editPrix" id="editPrix" required style="width: 100%; padding: 8px;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="modifierService" class="add-btn">Enregistrer</button>
                    <button type="button" onclick="closeModal('edit')" class="add-btn">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fonctions identiques à achat.php
        function openModal(type) {
            document.getElementById(type + 'Modal').style.display = 'flex';
        }

        function closeModal(type) {
            document.getElementById(type + 'Modal').style.display = 'none';
        }

        function confirmDelete(numServ) {
            if (confirm("Supprimer ce service ?")) {
                window.location.href = 'service.php?supprimer=' + numServ;
            }
        }

        function openEditModal(numServ, service, prix) {
            document.getElementById('editNunServ').value = numServ;
            document.getElementById('editService').value = service;
            document.getElementById('editPrix').value = prix;
            openModal('edit');
        }

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 3000);
    </script>
</body>
</html>