<?php
require_once('inc/config/constants.php');
require_once('inc/config/db.php');

function nbreusers(){
    global $conn;
    try{
	$sql = "SELECT count(*) FROM users";
	$stmt = $conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_NUM);
    $n = $result[0];

    return $n;
    }catch(PDOException $e){
        echo "Error : ".$e->getMessage();
    }
}

function nbreproduits($id_user){
    global $conn;
    try{
	$sql = "SELECT count(*) FROM produit
            WHERE id_user=:id_user";
	$stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_NUM);
    $n = $result[0];

    return $n;
    }catch(PDOException $e){
        echo "Error : ".$e->getMessage();
    }
}

function nbretotaleproduits($id_user){
    global $conn;
    try{
	$sql = "SELECT SUM(quantite) FROM achat
            WHERE id_user=:id_user";
	$stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_NUM);
    $n = $result[0];
    if($n == null){
        return 0;
    }
    return $n;
    }catch(PDOException $e){
        echo "Error : ".$e->getMessage();
    }
}
function nbrecategories($id_user){
    global $conn;
    try{
	$sql = "SELECT count(*) FROM categorie
            WHERE id_user=:id_user";
	$stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_NUM);
    $n = $result[0];

    return $n;
    }catch(PDOException $e){
        echo "Error : ".$e->getMessage();
    }
}


function depenses($id_user){
    global $conn;
    try{

    if(nbreproduits($id_user) == 0){
        return 0;
    }else{
        $sql = "SELECT SUM(depenses.prixtotale) FROM 
                (SELECT prix * quantite as prixtotale from achat where id_user=:id_user ) as depenses";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_NUM);

        $n = $row[0];
        return $n;
    }
    }catch(PDOException $e){
        echo "Error : ".$e->getMessage();
    }
}

function addproduit($id_user,$idproduit,$nomproduit,$date_achat,$date_expir,$prix,$quantite,$magasin,$categorie){
     global $conn;
     
     $test = "SELECT * FROM achat
              WHERE id_user=:id_user AND id=:idproduit AND nom=:nomproduit AND date_achat=:date_achat AND date_expir=:date_expir AND magasin=:magasin AND categorie=:categorie ";
     try{
        $stmt = $conn->prepare($test);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->bindParam(':idproduit',$idproduit,PDO::PARAM_INT);
            $stmt->bindParam(':nomproduit',$nomproduit,PDO::PARAM_STR);
            $stmt->bindParam(':date_achat',$date_achat,PDO::PARAM_STR);
            $stmt->bindParam(':date_expir',$date_expir,PDO::PARAM_STR);
            $stmt->bindParam(':magasin',$magasin,PDO::PARAM_STR);
            $stmt->bindParam(':categorie',$categorie,PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row == false){
            $sql = "INSERT INTO achat (id,id_user,nom,date_achat,date_expir,prix,quantite,magasin,categorie) VALUES (:idproduit,:id_user,:nomproduit,:date_achat,:date_expir,:prix,:quantite,:magasin,:categorie)";
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':idproduit',$idproduit,PDO::PARAM_INT); 
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->bindParam(':nomproduit',$nomproduit,PDO::PARAM_STR);
            $stmt->bindParam(':date_achat',$date_achat,PDO::PARAM_STR);
            $stmt->bindParam(':date_expir',$date_expir,PDO::PARAM_STR);
            $stmt->bindParam(':magasin',$magasin,PDO::PARAM_STR);
            $stmt->bindParam(':categorie',$categorie,PDO::PARAM_STR);
            $stmt->bindParam(':prix',$prix,PDO::PARAM_STR);
            $stmt->bindParam(':quantite',$quantite,PDO::PARAM_INT);
            $stmt->execute();
            
            //maj categorie
            $sql = "UPDATE categorie 
                    SET quantite = quantite + 1
                    WHERE nom=:nomcategorie AND id_user=:id_user";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->bindParam(':nomcategorie',$categorie,PDO::PARAM_STR);
            $stmt->execute();

            updateProduit($id_user,$idproduit,$nomproduit,$prix);

            $message = 2;
            return $message;

        }
        else{
            $message = 1;
            return $message;
        }
     }catch(PDOException $e){
            $message = 3;
            return $message;
     }
}

function updateProduit($id_user,$id_achat,$nomproduit, $prix) {
    global $conn;

    try {
        // Vérifier si le produit existe déjà pour cet achat
        $stmt = $conn->prepare("SELECT * FROM produit WHERE nom_achat = :nomproduit AND id_user=:id_user");
        $stmt->bindParam(':nomproduit', $nomproduit, PDO::PARAM_STR);
        $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            //min des prix
            $stmt = $conn->prepare("SELECT id,prix FROM achat WHERE nom = :nom AND id_user=:id_user ORDER BY prix ASC LIMIT 1");
            $stmt->bindParam(':nom', $nomproduit, PDO::PARAM_STR);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_minprix = $row['id'];
            $minprix = $row['prix'];
            
            //max des prix
            $stmt = $conn->prepare("SELECT id,prix FROM achat WHERE nom = :nom AND id_user=:id_user ORDER BY prix DESC LIMIT 1");
            $stmt->bindParam(':nom', $nomproduit, PDO::PARAM_STR);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $maxprix = $row['prix'];


            // Mettre à jour les prix si nécessaire
            $sql = "UPDATE produit SET id_achat=:id_minprix ,prix_pluseleve = :maxprix, prix_moinseleve = :minprix WHERE nom_achat = :nomproduit AND id_user=:id_user";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->bindParam(':nomproduit', $nomproduit, PDO::PARAM_STR);
            $stmt->bindParam(':id_minprix', $id_minprix, PDO::PARAM_INT);
            $stmt->bindParam(':minprix', $minprix, PDO::PARAM_STR);
            $stmt->bindParam(':maxprix', $maxprix, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            // Insertion dans la table produit
            $stmt = $conn->prepare("INSERT INTO produit (id_achat,id_user,nom_achat, prix_pluseleve, prix_moinseleve) VALUES (:id_achat,:id_user,:nom_achat, :prix, :prix)");
            $stmt->bindParam(':id_achat',$id_achat,PDO::PARAM_INT);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->bindParam(':nom_achat', $nomproduit, PDO::PARAM_STR);
            $stmt->bindParam(':prix', $prix, PDO::PARAM_STR);
            $stmt->execute();
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        
    }
}

function addcategorie($nomcategorie,$id_user){
    global $conn;
    
    $test = "SELECT * FROM categorie
             WHERE nom=:nomcategorie AND id_user=:id_user";
    try{
       $stmt = $conn->prepare($test);
           $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
           $stmt->bindParam(':nomcategorie',$nomcategorie,PDO::PARAM_STR);
       $stmt->execute();
       $row = $stmt->fetch(PDO::FETCH_ASSOC);
       if($row){
           $message = 1;
           return $message;
       }else{
            $sql = "INSERT INTO categorie (id_user,nom,quantite)
                   VALUES (:id_user,:nomcategorie,:quantite)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->bindParam(':nomcategorie',$nomcategorie,PDO::PARAM_STR);
            $stmt->bindValue(':quantite',0,PDO::PARAM_INT);
            $stmt->execute();

            $message = 2;
            return $message;
       }
    }catch(PDOException $e){
           $message = 3;
           return $message;
    }
}

function affichecategorie($id_user){
    global $conn;
    $sql = "SELECT * FROM categorie
            WHERE id_user=:id_user";
    try{
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $categories;
    }catch(PDOException $e){
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>' ;
    }
}



function  categories($id_user){
    global $conn;

    try{

        $sql = "SELECT nom FROM categorie
                WHERE id_user=:id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam('id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();

        $categories = $stmt->fetchAll(PDO::FETCH_NUM);
        return $categories;

    }catch(PDOException $e){
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>' ;
    }
}

function testproduits($categorie,$id_user){
    global $conn;

    try{
     $sql = "SELECT count(*) from achat
             WHERE categorie=:categorie AND id_user=:id_user";
     $stmt = $conn->prepare($sql);
     $stmt->bindParam(':categorie',$categorie,PDO::PARAM_STR);
     $stmt->bindParam('id_user',$id_user,PDO::PARAM_INT);
     $stmt->execute();
     $nbre = $stmt->fetch(PDO::FETCH_NUM);
            
     return $nbre;
    }catch(PDOException $e){
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>' ;
    }   
}

function produits($categorie,$id_user){
    global $conn;

    try{
     $sql = "SELECT * from achat
             WHERE categorie=:categorie AND id_user=:id_user";
     $stmt = $conn->prepare($sql);
     $stmt->bindParam(':categorie',$categorie,PDO::PARAM_STR);
     $stmt->bindParam('id_user',$id_user,PDO::PARAM_INT);
     $stmt->execute();
     $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
     return $produits;
    }catch(PDOException $e){
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>' ;
    }
}

function updatequantiteProduit($idproduit,$id_user,$quantite){
    global $conn;
    try{
        $sql = "UPDATE achat 
                SET quantite=:quantite
                WHERE id=:idproduit AND id_user=:id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':quantite',$quantite,PDO::PARAM_INT);
        $stmt->bindParam(':idproduit',$idproduit,PDO::PARAM_INT);
        $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }catch(PDOException $e){
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>' ;
    }
}

function supprimerproduit($idproduit,$id_user,$nomproduit,$nomcategorie){
    global $conn;
    
    try{
        $test = "SELECT * FROM produit
                 WHERE id_achat=:idproduit AND id_user=:id_user";
        $stmt = $conn->prepare($test);
        $stmt->bindParam('idproduit',$idproduit,PDO::PARAM_INT);
        $stmt->bindParam('id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($result){
        $sql = "UPDATE categorie
                SET quantite = quantite - 1 
                WHERE id_user=:id_user AND nom=:nomcategorie";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam('id_user',$id_user,PDO::PARAM_INT);
        $stmt->bindParam('nomcategorie',$nomcategorie,PDO::PARAM_STR);
        $stmt->execute(); 


        $sql = "DELETE FROM  produit
                WHERE id_achat=:idproduit AND id_user=:id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam('idproduit',$idproduit,PDO::PARAM_INT);
        $stmt->bindParam('id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();       

        $sql = "DELETE FROM  achat
                WHERE id=:idproduit AND id_user=:id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam('idproduit',$idproduit,PDO::PARAM_INT);
        $stmt->bindParam('id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();

        updateProduitsupprimer($nomproduit,$id_user);

        }
        else{
        
        $sql = "DELETE FROM  achat
                WHERE id=:idproduit AND id_user=:id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam('idproduit',$idproduit,PDO::PARAM_INT);
        $stmt->bindParam('id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();
        }

        return true;
    }catch(PDOException $e){
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>' ;
    }
}

function updateProduitsupprimer($nomproduit,$id_user){
    global $conn;

    try {
        // Vérifier si le produit existe
        $stmt = $conn->prepare("SELECT * FROM achat WHERE nom = :nom AND id_user=:id_user");
        $stmt->bindParam(':nom', $nomproduit, PDO::PARAM_STR);
        $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            //min des prix
            $stmt = $conn->prepare("SELECT id,prix FROM achat WHERE nom = :nom AND id_user=:id_user ORDER BY prix ASC LIMIT 1");
            $stmt->bindParam(':nom', $nomproduit, PDO::PARAM_STR);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_minprix = $row['id'];
            $minprix = $row['prix'];
            
            //max des prix
            $stmt = $conn->prepare("SELECT id,prix FROM achat WHERE nom = :nom AND id_user=:id_user ORDER BY prix DESC LIMIT 1");
            $stmt->bindParam(':nom', $nomproduit, PDO::PARAM_STR);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $maxprix = $row['prix'];


            // Mettre à jour les prix si nécessaire
            $sql = "UPDATE produit SET id_achat=:id_minprix ,prix_pluseleve = :maxprix, prix_moinseleve = :minprix WHERE nom_achat = :nomproduit AND id_user=:id_user";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user',$id_user,PDO::PARAM_INT);
            $stmt->bindParam(':nomproduit', $nomproduit, PDO::PARAM_STR);
            $stmt->bindParam(':id_minprix', $id_minprix, PDO::PARAM_INT);
            $stmt->bindParam(':minprix', $minprix, PDO::PARAM_STR);
            $stmt->bindParam(':maxprix', $maxprix, PDO::PARAM_STR);
            $stmt->execute();
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        
    }
}


function alerte_stockbas($id_user){
     global $conn;

     try{

        $sql = "SELECT nom FROM achat
                WHERE quantite = 1 AND id_user=:id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id_user',$id_user,PDO::PARAM_INT);
        $stmt->execute();

        $noms = $stmt->fetchAll(PDO::FETCH_NUM);
        return $noms;
     }catch(PDOException $e){
        echo "Error: " . $e->getMessage();        
     }
}


function alerte_dateexpiration($id_user) {
    global $conn;

    try {
        $sql = "SELECT nom FROM achat
                WHERE date_expir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                AND id_user = :id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();

        $noms = $stmt->fetchAll(PDO::FETCH_NUM);
        return $noms;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();        
    }
}

function alerte_dateexpiration_($id_user) {
    global $conn;

    try {
        $sql = "SELECT nom FROM achat
                WHERE date_expir <= CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                AND id_user = :id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();

        $noms = $stmt->fetchAll(PDO::FETCH_NUM);
        return $noms;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();        
    }
}
?>