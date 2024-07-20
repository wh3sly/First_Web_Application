<?php
	session_start();
	// Redirect the user to login page if he is not logged in.
	$id_user;
	if(!isset($_SESSION['valid'])){
		header('Location: ../forms/login.php');
		exit();
	}
	else{
       $id_user = $_SESSION['id'];
	}
	require_once('inc/config/constants.php');
	require_once('inc/config/db.php');
	require('functions.php');
	require_once('inc/header.html');

	


/*********************************************************** Produit ***************************************************** */
$message = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addproduit'])){
	$idproduit = $_POST['idproduit'];
	$nomproduit = $_POST['nomproduit'];
	$date_achat = $_POST['date_achat'];
	$date_expir = $_POST['date_expir'];
	$prix = $_POST['prix'];
	$categorie = $_POST['categorie'];
	$quantite = $_POST['quantite'];
	$magasin = $_POST['magasin'];

	if(empty($idproduit) || empty($nomproduit) || empty($date_achat) || empty($date_expir) || empty($prix) || empty($categorie) || empty($quantite) || empty($magasin)){
		$message = '<div class="alert alert-danger">Tous les champs sont obligatoires !</div>';
	}else{
		$test = addproduit($id_user,$idproduit,$nomproduit,$date_achat,$date_expir,$prix,$quantite,$magasin,$categorie);

		if($test == 1){
			$message = '<div class="alert alert-danger">Ce produit est existe déja</div>';
		}elseif($test == 2){
			$message = '<div class="alert alert-success">Produit ajouté avec succès.</div>';
		}elseif($test == 3){
            $message = '<div class="alert alert-danger">Error SQL !</div>';
		}
	}

}
/******************************************************************************************************************************** */







/***********************************************************   Categorie *************************************************************** */

//ajouter categorie
$message2 = '';
$message3 = '';
$message4 = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addcategorie'])){
	$nomcategorie = $_POST['nomcategorie'];
	if(empty($nomcategorie)){
      $message2 = '<div class="alert alert-danger">Veuillez entrer le nom de categorie! </div>';
	}else{
		$test = addcategorie($nomcategorie,$id_user);
		
		if($test == 1){
			$message2 = '<div class="alert alert-danger">Cette categorie est existe déja</div>';
		}elseif($test == 2){
            $message2 = '<div class="alert alert-success">Categorie ajouté avec succès.</div>';
		}elseif($test == 3){
            $message2 = '<div class="alert alert-danger">Error SQL !</div>';
		}
	}
}


//modifier & supprimer categorie
$modifiercategorie =0 ;
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['idcategorie'])){
	$id=$_POST['idcategorie'];
	$quantitecategorie=$_POST['quantitecategorie'];
	$action = $_POST['action'];
    switch($action){
		case 'modifiercategorie':
			$modifiercategorie = 1;

			$sql = "SELECT * FROM categorie
			        WHERE id=:id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':id',$id,PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$_SESSION['n_m']=$row['nom'];
			$_SESSION['id_m']=$row['id'];
		
		break;

		case 'supprimercategorie':
			try {
				if($quantitecategorie == 0){
                $sql =  "DELETE FROM categorie WHERE id=:id";
                $stmt = $conn->prepare($sql);
                $stmt -> bindParam(':id',$id,PDO::PARAM_INT);
                $stmt->execute();
                $message4 = '<div class="alert alert-success">Categorie supprimer avec succès.</div>';
			    }else{
					$message4 = '<div class="alert alert-danger">Suppression de categorie a echoué ! [nombre de produits > 0].</div>';
				}
            }catch(PDOException $e){
                $message4 = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            }
		break;
	}
}

if(isset($_SESSION['n_m']) && $_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['modifycategorie'])){
    $id=$_SESSION['id_m'];
    $nomcategorie = $_POST['nomcategorie'];

    if(empty($nomcategorie)){
        $message3 = '<div class="alert alert-danger">Veuillez entrer le nom de categorie</div>';
    }else{
        try{
            $sql = "UPDATE categorie 
                    SET nom=:nom
                    WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom',$nomcategorie,PDO::PARAM_STR);
            $stmt->bindParam(':id',$id,PDO::PARAM_INT);
            $stmt->execute();
            $message3 = '<div class="alert alert-success">Categorie modifié avec succès.</div>';
        }catch(PDOException $e){
            $message3 = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }      
}
/***************************************************************************************************************************** */




/************************************************************** stock ************************************************ */
$message5 ='';
$message6 = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updatequantite'])){
	$id = $_POST['idproduit'];
	$quantite = $_POST['quantiteupdate'];
    
	
	$stmt = updatequantiteProduit($id,$id_user,$quantite);
	if($stmt){
		$message5 = '<div class="alert alert-success">Produit modifié avec succès.</div>';
	}
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['supprimerproduit'])){
	$idproduit = $_POST['idproduit'];
    $nomproduit = $_POST['nomupdate'];
	$nomcategorie = $_POST['nomcategorie'];
	$test = supprimerproduit($idproduit,$id_user,$nomproduit,$nomcategorie);
	if($test){
		$message6 = '<div class="alert alert-success">Produit supprimé avec succès.</div>';
	}else{
		$message6 = '<div class="alert alert-danger">ERROR !</div>';
	}
}
/*************************************************************************************************************************** */



/************************************************************* charts *************************************************** */
    try {
// Requête SQL pour récupérer les produits et leurs quantités achetées
        $sql = "SELECT nom, SUM(quantite) AS total_quantite FROM achat GROUP BY nom ORDER BY total_quantite DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Initialisation du tableau des données
        $dataPoints1 = array();

        // Récupération des résultats de la requête et construction des données pour le graphe
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dataPoints1[] = array("label" => $row['nom'], "y" => $row['total_quantite']);
        }

// Requête SQL pour récupérer les articles les plus chers par catégorie
        $sql = "SELECT categorie, nom, prix FROM achat ORDER BY prix DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Initialisation du tableau des données
        $dataPoints2 = array();

        // Récupération des résultats de la requête et construction des données pour le graphe
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Vérifie si la catégorie existe déjà dans les données
            if (!isset($dataPoints2[$row['categorie']])) {
                // Si la catégorie n'existe pas encore, ajoutez-la avec l'article actuel comme article le plus cher
                $dataPoints2[$row['categorie']] = array("label" => $row['nom'], "y" => $row['prix']);
            }
        }

// Requête SQL pour récupérer les dépenses par période
    $sql3 = "SELECT 
                DATE_FORMAT(date_achat, '%Y-%m-%d') AS period, 
                SUM(prix * quantite) AS total_depenses 
             FROM achat 
             GROUP BY period 
             ORDER BY period";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute();
    $dataPoints3 = array();
    while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
        $dataPoints3[] = array("label" => $row['period'], "y" => $row['total_depenses']);
    }

    } catch(PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
/********************************************************************************************** */


?>
  <body>
<?php
	require 'inc/navigation.php';
	require 'inc/footer.php';
?>
    <!-- Page Content -->
    <div class="container-fluid">
	  <div class="row">
		<div class="col-lg-2">
		<h1 class="my-4"></h1>
			<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">

			  <a class="nav-link active" id="v-pills-dashbord-tab" data-toggle="pill" href="#v-pills-dashbord" role="tab" aria-controls="v-pills-dashbord" aria-selected="true"><i class="fas fa-home"></i> Dashbord</a>

			  <a class="nav-link" id="v-pills-achat-tab" data-toggle="pill" href="#v-pills-achat" role="tab" aria-controls="v-pills-achat" aria-selected="false"><i class="fas fa-shopping-cart"></i> Achats</a>

			  <a class="nav-link" id="v-pills-categorie-tab" data-toggle="pill" href="#v-pills-categorie" role="tab" aria-controls="v-pills-categorie" aria-selected="false"><i class="fab fa-windows"></i> Categories</a>

			  <a class="nav-link" id="v-pills-stock-tab" data-toggle="pill" href="#v-pills-stock" role="tab" aria-controls="v-pills-stock" aria-selected="false"><i class="bi bi-box"></i> Stock</a>

			  <a class="nav-link" id="v-pills-analyse-tab" data-toggle="pill" href="#v-pills-analyse" role="tab" aria-controls="v-pills-analyse" aria-selected="false"><i class="bi bi-bar-chart"></i> Analyses Graphiques</a>

			  <a class="nav-link" id="v-pills-alert-tab" data-toggle="pill" href="#v-pills-alert" role="tab" aria-controls="v-pills-alert" aria-selected="false"><i class="bi bi-bell"></i> Notifications</a>

			</div>
		</div>
		 <div class="col-lg-10">
			<div class="tab-content" id="v-pills-tabContent">

	<!-- Dashbord -->
    <div class="tab-pane fade show active" id="v-pills-dashbord" role="tabpanel" aria-labelledby="v-pills-dashbord-tab">
    <div class="card card-outline-secondary my-4">
        <div class="card-header"><i class="fas fa-home"></i> Dashbord</div>
        <div class="container-fluid">
            <div class="container-fluid my-4">
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <div class="card">
                            <div class="card-icon bg-primary text-white d-flex justify-content-center align-items-center p-4">
							<i class="fas fa-shopping-cart fa-4x"></i>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <h5 class="card-title" style="color: black;font-weight: bold;font-size: 35px;"><?php echo nbreproduits($id_user)?></h5>
                                <p class="card-text" style="color: gray;">Produits</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="card">
                            <div class="card-icon bg-success text-white d-flex justify-content-center align-items-start p-4 ">
							<i class="fab fa-windows fa-4x"></i>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <h5 class="card-title" style="color: black;font-weight: bold;font-size: 35px;"><?php echo nbrecategories($id_user)?></h5>
                                <p class="card-text" style="color: gray;">Categories</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card mt-3">
                            <div class="card-icon bg-danger text-white d-flex justify-content-center align-items-start p-4">
							<i class="fas fa-dollar-sign fa-4x"></i>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <h5 class="card-title" style="color: black;font-weight: bold;font-size: 35px;"><?php echo depenses($id_user) ?> Dhs</h5>
                                <p class="card-text" style="color: gray;">Dépenses</p>
                            </div>
                        </div>
                    </div>
					<div class="col">
                        <div class="card mt-3">
                            <div class="card-icon bg-warning text-white d-flex justify-content-center align-items-start p-4">
							<i class="fas fa-box fa-4x"></i></i>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <h5 class="card-title" style="color: black;font-weight: bold;font-size: 35px;"><?php echo nbretotaleproduits($id_user) ?></h5>
                                <p class="card-text" style="color: gray;">Stock</p>
                            </div>
                        </div>
                    </div>    
                </div>
            </div>
        </div>
    </div>
    </div>






            <!-- achat -->
			  <div class="tab-pane fade" id="v-pills-achat" role="tabpanel" aria-labelledby="v-pills-achat-tab">
				<div class="card card-outline-secondary my-4">
				  <div class="card-header"><i class="fas fa-shopping-cart"></i> Entregistrer l'achat</div>
				  <div class="card-body">
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#itemDetailsTab">Achat</a>
						</li>
						<!--
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#itemImageTab">Upload Image</a>
						</li>
                        -->
					</ul>
					
					
					<div class="tab-content">
						<div id="itemDetailsTab" class="container-fluid tab-pane active">
							<br>
							<div id="itemDetailsMessage"><?php echo $message; ?></div>
							<form action="user.php" method="post">
							  <div class="form-row">
								<div class="form-group col-md-3" style="display:inline-block">
								  <label for="nomproduit">Nom de produit<span class="requiredIcon">*</span></label>
								  <input type="text" class="form-control" name="nomproduit" id="nomproduit" autocomplete="off">
								  <div id="itemDetailsItemNumberSuggestionsDiv" class="customListDivWidth"></div>
								</div>
								<div class="form-group col-md-3">
								  <label for="itemDetailsProductID">Produit ID</label>
								  <input type="number" class="form-control invTooltip"  min="1"  id="idproduit" name="idproduit">
								</div>
							  </div>
							  <div class="form-row">
								  <div class="form-group col-md-3">
									<label for="date_achat">Date achat<span class="requiredIcon">*</span></label>
									<input type="date" class="form-control datepicker" id="date_achat" name="date_achat">
									<div id="itemDetailsItemNameSuggestionsDiv" class="customListDivWidth"></div>
								  </div>
								  <div class="form-group col-md-3">
									<label for="date_expir">Date d'expiration<span class="requiredIcon">*</span></label>
									<input type="date" class="form-control datepicker" id="date_expir" name="date_expir">
									<div id="itemDetailsItemNameSuggestionsDiv" class="customListDivWidth"></div>
								  </div>
								  <div class="form-group col-md-3">
									<label for="categorie">Categorie</label>
									<select id="categorie" name="categorie" class="form-control chosenSelect">
										<?php
										  $categories = affichecategorie($id_user);
                                          foreach($categories as $categorie){
											$nomcategorie = $categorie['nom'];
										?>
										    <option value="<?php echo $nomcategorie ?>"><?php echo $nomcategorie ?></option>
										<?php
										  }
										?>
									</select>
								  </div>
							  </div>
							  <div class="form-row">
								<div class="form-group col-md-3">
								  <label for="prix">Prix<span class="requiredIcon">*</span></label>
								  <input type="text" class="form-control" value="0.00" name="prix" id="prix">
								</div>
								<div class="form-group col-md-3">
								  <label for="quantite1">Quantité<span class="requiredIcon">*</span></label>
								  <input type="number" class="form-control" value="0" min="1" name="quantite" id="quantite">
								</div>
								<div class="form-group col-md-4">
								  <label for="magasin">Magasin<span class="requiredIcon">*</span></label>
								  <input type="text" class="form-control" name="magasin" id="magasin">
								</div>
							  </div>
							  <button type="submit" name="addproduit" class="btn btn-success">Add Produit</button>
							  <button type="reset" class="btn" id="itemClear">Clear</button>
							</form>
						</div>
					</div>
				  </div> 
				</div>
			  </div>
             


			<!-- Categorie -->
			 <div class="tab-pane fade" id="v-pills-categorie" role="tabpanel" aria-labelledby="v-pills-categorie-tab">
				<div class="card card-outline-secondary my-4">
				  <div class="card-header"><i class="fab fa-windows"></i> Détails des categories</div>
				  <div class="card-body">
				    <ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#ajoutercategorie">Ajouter une categorie</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#categories">Categories</a>
						</li>   
					</ul>

					<div class="tab-content">
					   <div id="ajoutercategorie" class="container-fluid tab-pane active"><br>
					    <div id="categoieDetailsMessage">
							 <?php echo $message2; ?>
						</div>
					    <form action="" method="post">
					    <div class="form-row">
						<div class="form-group col-md-3">
						  <label for="nomcategorie">Nom de categorie<span class="requiredIcon">*</span></label>
						  <input type="text" class="form-control" id="nomcategorie" name="nomcategorie" autocomplete="off">
						  <div id="purchaseDetailsItemNumberSuggestionsDiv" class="customListDivWidth"></div>
						</div>
						<div class="form-group col-md-3">
							<label for="CategorieID">Categorie ID</label>
							<input class="form-control invTooltip" type="number" readonly  id="CategorieID" name="CategorieID" title="This will be auto-generated when you add a new item">
						</div>
					    </div>
					    <button type="submit" name="addcategorie" class="btn btn-success">Add Categorie</button>
					    <button type="reset" class="btn">Clear</button>
					    </form>
				        </div>

						<div id="categories" class="container-fluid tab-pane"><br>
                        <div id="categoieDetailsMessage"><?php $categories=affichecategorie($id_user); ?></div>
						<div id="categoieDetailsMessage"><?php echo $message3; ?></div>
						<div id="categoieDetailsMessage"><?php echo $message4; ?></div>
						<?php
                            if(isset($_SESSION['n_m']) && $modifiercategorie==1){
						 ?>
						 <form  action="" method="post">
                                        <div class="form-row">
                                            <div class="form-group col-md-3" style="display:inline-block">
                                                <label for="nomcategorie">Nom <span class="requiredIcon">*</span></label>
                                            <input type="text" class="form-control" name="nomcategorie" id="nomcategorie" autocomplete="off"  value="<?php echo $_SESSION['n_m']?>">
                                                <div id="itemDetailsItemNumberSuggestionsDiv" class="customListDivWidth"></div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="CategorieID">ID de categorie</label>
                                                <input class="form-control invTooltip" type="number" readonly  id="CategorieID" name="CategorieID" title="This will be auto-generated when you add a new user">
                                            </div>
                                        </div>
                                        <button type="submit" name="modifycategorie" class="btn btn-success">Modifier</button>
                         </form>
						 <?php
							}
						?>
                         <div class="table-responsive" id="itemDetailsTableDiv">
                             <div class="container-fluid py-5">
                                 <div class="row py-5">
                                     <div class="col-lg-12 mx-auto">
                                         <div class="card rounded shadow border-0">
                                             <div class="card-body p-5 bg-white rounded">
                                                 <div class="table-responsive">
                                                     <!-- <div id="categorieDetailsMessage"><?php //echo $message?></div> -->
                                                     <table id="example" style="width:100%" class="table table-striped table-bordered">
                                                         <thead>
                                                         <tr>
                                                             <th>ID</th>
                                                             <th>Nom de categorie</th>
                                                             <th>Quantité</th>
                                                             <th>Action</th>
                                                         </tr>
                                                         </thead>
                                                         <tbody>
															<?php
															  foreach($categories as $row){
															?>				
                                                         <tr>
                                                             <td><?php echo $row['id']?></td>
                                                             <td><?php echo $row['nom']?></td>
                                                             <td><?php echo $row['quantite']?></td>
                                                             <td>
                                                                <form action="user.php" method="post">
																<input type="hidden" name="idcategorie" value="<?php echo $row['id']; ?>">
																 <input type="hidden" name="quantitecategorie" value="<?php echo $row['quantite']; ?>">
                                                                 <button type="submit" class="btn btn-primary btn-sm" name="action" value="modifiercategorie">Modifier</button>
                                                                 <button type="submit" class="btn btn-danger btn-sm" name="action" value="supprimercategorie">Supprimer</button>
                                                                </form>
                                                             </td>
                                                         </tr>
														    <?php
                                                              }
															?>
                                                         </tbody>
                                                     </table>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                        </div>
				    </div>
			      </div>
			    </div>
		      </div>









			<!-- stock -->
			<div class="tab-pane fade" id="v-pills-stock" role="tabpanel" aria-labelledby="v-pills-stock-tab">
			  <div class="card card-outline-secondary my-4">
				<div class="card-header"><i class="bi bi-box"></i> Details de stock</div>
				<div class="card-body">
				    <ul class="nav nav-tabs" role="tablist">
                        <?php
						   $non_categorie = '';
						   $i=0;
                           $categories = categories($id_user);
						   if(count($categories) > 0){
							  
							 foreach($categories as $categorie){
							 if($i==0){
						 ?>
                             <li class="nav-item">
							 <a class="nav-link active" data-toggle="tab" href="#<?php echo $categorie[0] ?>"><?php echo $categorie[0] ?></a>
						     </li> 
						 <?php
						     $i++;
							 }else{
                         ?>
						     <li class="nav-item">
							 <a class="nav-link" data-toggle="tab" href="#<?php echo $categorie[0] ?>"><?php echo $categorie[0] ?></a>
						     </li> 
						 <?php                         
							 }							 
							 }
						   }else{
							 $non_categorie = '<div class="alert alert-danger">Le Stock est vide !</div>';
						   }
						?>					
					</ul>
					
				 <div class="tab-content">
					<!-- categorie -->
					<?php
					     $i=0;
                         $categories = categories($id_user);

						 if(count($categories) > 0){
						    foreach($categories as $categorie){
							  $activeclass = ($i==0) ? 'active' : '';
							  $tableId = strtolower(str_replace(' ', '-', $categorie[0]));
					?>
					<div id="<?php echo $categorie[0] ?>" class="container-fluid tab-pane <?php echo $activeclass ?>"><br>
					     <div id="stockDetailsMessage"><?php echo $message5; echo $message6; ?></div>

						 <div class="table-responsive" id="itemDetailsTableDiv">
                             <div class="container-fluid py-5">
                                 <div class="row py-5">
                                     <div class="col-lg-12 mx-auto">
                                         <div class="card rounded shadow border-0">
                                             <div class="card-body p-5 bg-white rounded">
                                                 <div class="table-responsive">
                                                     <!-- <div id="categorieDetailsMessage"><?php //echo $message?></div> -->
													 
												 <?php
												      $nbreproduits=testproduits($categorie[0],$id_user);
                                                      if($nbreproduits[0] > 0){
												  ?>
                                                     <table id="<?php echo $tableId ?>-table" style="width:100%" class="table table-striped table-bordered">
                                                         <thead>
                                                         <tr>
                                                             <th>ID</th>
                                                             <th>Nom de produit</th>
															 <th>Date d'achat</th>
															 <th>Date d'expiration</th>
															 <th>Prix</th>
                                                             <th>Quantité</th>
															 <th>Magasin</th>
                                                             <th>Action</th>
                                                         </tr>
                                                         </thead>
                                                         <tbody>
														 <?php
                                                            $produits = produits($categorie[0],$id_user);
															foreach($produits as $produit){
														 ?>
                                                         <tr>
                                                             <td><?php echo $produit['id'] ?></td>
                                                             <td><?php echo $produit['nom'] ?></td>
                                                             <td><?php echo $produit['date_achat'] ?></td>
															 <td><?php echo $produit['date_expir'] ?></td>
															 <td><?php echo $produit['prix'] ?></td>
															 <td>
                                                             <form action="" method="post" class="form-inline">
                                                               <div class="form-group mr-2">
                                                               <input type="number" class="form-control" min="1" value="<?php echo $produit['quantite'] ?>" name="quantiteupdate">
															   <input type="hidden" name="idproduit" value="<?php echo $produit['id'] ?>"> 
                                                               </div>
                                                               <button type="submit" class="btn btn-primary btn-sm" name="updatequantite">Modifier</button>
                                                             </form>
                                                             </td>
															 <td><?php echo $produit['magasin'] ?></td>
                                                             <td>
                                                                <form action="" method="post">
																 <input type="hidden" name="nomupdate" value="<?php echo $produit['nom'] ?>">
																 <input type="hidden" name="idproduit" value="<?php echo $produit['id'] ?>"> 
																 <input type="hidden" name="nomcategorie" value="<?php echo $produit['categorie'] ?>"> 
                                                                 <button type="submit" class="btn btn-danger btn-sm" name="supprimerproduit">Supprimer</button>
                                                                </form>
                                                             </td>
                                                         </tr>
														 <?php
													        }
														 ?>
                                                         </tbody>
                                                     </table>
												 <?php
												      $i++;
													  }else{
														 echo '<div class="alert alert-danger">Pas de produits dans cette categorie !</div>';
													  }
												 ?>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
					</div>

					 <?php
						 }
					     }else{
							echo $non_categorie;
						 }
					?>
				 </div>
				</div> 
			  </div>
			</div>
			    



		<!-- analyse -->
		<div class="tab-pane fade" id="v-pills-analyse" role="tabpanel" aria-labelledby="v-pills-analyse-tab">
            <div class="card card-outline-secondary my-4">
                 <div class="card-header"><i class="bi bi-bar-chart"></i> Statistiques</div>
				 <div class="container-fluid">

        <!-- Premier graphique (Produits par Nombre d'Achats) -->
        <div class="chart-container" style="margin-bottom: 30px;">
            <div id="chartContainer1" style="height: 370px; width: 100%;"></div>
        </div>

        <!-- Deuxième graphique (Articles les Plus Chers par Catégorie) -->
        <div class="chart-container"  style="margin-bottom: 30px;">
            <div id="chartContainer2" style="height: 370px; width: 100%;"></div>
        </div>

        <!-- Troisième graphique (Dépenses par Période) -->
        <div class="chart-container" style="margin-bottom: 30px;">
            <div id="chartContainer3" style="height: 370px; width: 100%;"></div>
        </div>


    <script>
        window.onload = function () {
            // Premier graphique (Produits par Nombre d'Achats)
            var chart1 = new CanvasJS.Chart("chartContainer1", {
                animationEnabled: true,
                exportEnabled: true,
                theme: "light1",
                title:{
                    text: "Produits plus achetés"
                },
                axisY:{
                    includeZero: true
                },
                data: [{
                    type: "column",
                    indexLabelFontColor: "#5A5757",
                    indexLabelPlacement: "outside",   
                    dataPoints: <?php echo json_encode($dataPoints1, JSON_NUMERIC_CHECK); ?>
                }]
            });
            chart1.render();

            // Deuxième graphique (Articles les Plus Chers par Catégorie)
            var chart2 = new CanvasJS.Chart("chartContainer2", {
                animationEnabled: true,
                exportEnabled: true,
                title:{
                    text: "Articles les Plus Chers par Catégorie"
                },
                axisY:{
                    title: "Prix"
                },
                data: [{
                    type: "column",
                    indexLabelFontColor: "#5A5757",
                    indexLabelPlacement: "outside",   
                    dataPoints: <?php echo json_encode(array_values($dataPoints2), JSON_NUMERIC_CHECK); ?>
                }]
            });
            chart2.render();
            // Troisième graphique (Dépenses par Période)
            var chart3 = new CanvasJS.Chart("chartContainer3", {
                animationEnabled: true,
                exportEnabled: true,
                theme: "light1",
                title:{
                    text: "Dépenses par Période"
                },
                axisY:{
                    title: "Dépenses"
                },
                data: [{
                    type: "line",
                    indexLabelFontColor: "#5A5757",
                    indexLabelPlacement: "outside",   
                    dataPoints: <?php echo json_encode($dataPoints3, JSON_NUMERIC_CHECK); ?>
                }]
            });
            chart3.render();
        }
    </script>
				 </div>
            </div>
		</div>




		<!-- alerte -->
		<div class="tab-pane fade" id="v-pills-alert" role="tabpanel" aria-labelledby="v-pills-alert-tab">
            <div class="card card-outline-secondary my-4">
                 <div class="card-header"><i class="bi bi-bell"></i> Notifications</div>
				 <div class="container-fluid">

				 <?php
$stockbas = alerte_stockbas($id_user);
$date_expir = alerte_dateexpiration($id_user);
$date_expir_ = alerte_dateexpiration_($id_user);

// Vérifier s'il n'y a pas d'alertes
if (empty($stockbas) && empty($date_expir) && empty($date_expir_)) {
    echo '<div class="container mt-5">';
    echo '<div class="alert alert-info" role="alert">';
    echo 'Il n\'y a pas d\'alertes pour le moment.';
    echo '</div>';
    echo '</div>';
} else {
    // Afficher les alertes si elles existent
    if ($stockbas) {
        foreach ($stockbas as $nom) {
            ?>
            <div class="container mt-5">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mr-2"></i>
                    <strong> Attention!</strong> Le niveau de stock du produit <strong><?php echo $nom[0] ?></strong> est bas.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <?php
        }
    }

    if ($date_expir) {
        foreach ($date_expir as $nom) {
            ?>
            <div class="container mt-5">
                <div class="alert alert-dark-orange alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mr-2"></i>
                    <strong> Attention!</strong> Le produit <strong><?php echo $nom[0] ?></strong> a une date d'expiration proche.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <?php
        }
    }

    if ($date_expir_) {
        foreach ($date_expir_ as $nom) {
            ?>
            <div class="container mt-5">
                <div class="alert alert-dark-red alert-dismissible fade show" role="alert">
                    <i class="bi bi-x-circle-fill"></i>
                    <strong> Attention!</strong> Le produit <strong><?php echo $nom[0] ?></strong> a une date d'expiration qui est passée.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <?php
        }
    }
}
?>
				         </div>
                     </div>
		         </div>
			 </div>
		 </div>
	  </div>
    </div>

	<style>
        .alert-dark-orange {
            background-color: #FF8C00; 
            color: white;
			
        }
		.alert-dark-red {
            background-color: rgb(255, 45, 45); 
            color: white;
        }
    </style>
    <script>
        $(document).ready(function() {
            <?php
            foreach ($categories as $categorie) {
                $tableId = strtolower(str_replace(' ', '-', $categorie[0]));
                ?>
                $('#<?php echo $tableId; ?>-table').DataTable();
                <?php
            }
            ?>
			$('#example').DataTable();
        });
    </script>
	<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to show a notification
        function showNotification() {
            if (Notification.permission === 'granted') {
                new Notification('Attention! Le niveau de stock du produit "fromage" est bas.');
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('Attention! Le niveau de stock du produit "fromage" est bas.');
                    } else {
                        // Fallback to Bootstrap alert
                        document.getElementById('notification').style.display = 'block';
                    }
                });
            } else {
                // Fallback to Bootstrap alert
                document.getElementById('notification').style.display = 'block';
            }
        }

        // Call the function to show notification
        showNotification();
    });
</script>

<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
<?php
require 'inc/footer1.php';
?>
</body>
</html>
