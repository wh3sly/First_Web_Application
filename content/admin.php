<?php
session_start();
$id_user;
if(!isset($_SESSION['valid'])){
    header('Location: ../forms/login.php');
    exit();
}else{
    $id_user = $_SESSION['id'];
}

require_once('inc/config/constants.php');
require_once('inc/config/db.php');
require_once('inc/header.html');
require('functions.php');

$message = '';
$message1 ='';
$message2 ='';
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['ajouter'])){
    $username = $_POST['utilisateurNom'];
    $password = $_POST['utilisateurMotdepasse'];
    $usertype = $_POST['utilisateurType'];
    $email = $_POST['utilisateurEmail'];

    if(empty($username) || empty($password) || empty($usertype) || empty($email)){
        $message1 = '<div class="alert alert-danger">Tous les champs sont obligatoires !</div>';
    }else{

        
        $test ="SELECT * FROM users 
                WHERE email=:email AND password=:password";
        try{
            $stmt = $conn->prepare($test);
            $stmt->bindParam(':email',$email,PDO::PARAM_STR);
            $stmt->bindParam(':password',$password,PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(count($row)>0){
                $message1 = '<div class="alert alert-danger">Ce utilisateur est existe déja</div>';
            }else{
                $test=1;
            }
        }catch(PDOException $e){
            $message1 = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
        
        if($test==1){
        try{
            $sql = "INSERT INTO users (username,password,email,usertype) VALUES (:username,:password,:email,:usertype)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username',$username,PDO::PARAM_STR);
            $stmt->bindParam(':password',$password,PDO::PARAM_STR);
            $stmt->bindParam(':email',$email,PDO::PARAM_STR);
            $stmt->bindParam(':usertype',$usertype,PDO::PARAM_STR);
            $stmt->execute();
            $message1 = '<div class="alert alert-success">Utilisateur ajouté avec succès.</div>';
        }catch(PDOException $e){
            $message1 = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
        }
    }
}

$modifier = 0;

if($_SERVER['REQUEST_METHOD'] =='POST' && isset($_POST['action']) && isset($_POST['id'])){
    $id = $_POST['id'];
    $action = $_POST['action'];
    switch($action){
        case 'modifier' : 
              $modifier = 1;
              $sql = "SELECT * FROM users
                      WHERE id=:id";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bindParam(':id',$id,PDO::PARAM_INT);
                                        $stmt->execute();
                                        $row= $stmt->fetch(PDO::FETCH_NUM);
                                        $_SESSION['id_m']=$row[0];
                                        $_SESSION['u_m']=$row[1];
                                        $_SESSION['p_m']=$row[2];
                                        $_SESSION['e_m']=$row[3];

        break;

        case 'supprimer' :
            try {
                $sql =  "DELETE FROM users WHERE id=:id";
                $stmt = $conn->prepare($sql);
                $stmt -> bindParam(':id',$id,PDO::PARAM_INT);
                $stmt->execute();
                $message = '<div class="alert alert-success">Utilisateur supprimer avec succès.</div>';
            }catch(PDOException $e){
                $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            }
        break;
    }
}

if(isset($_SESSION['id_m']) && $_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['modify'])){
    $id=$_SESSION['id_m'];
    $username = $_POST['utilisateurNom'];
    $password = $_POST['utilisateurMotdepasse'];
    $usertype = $_POST['utilisateurType'];
    $email = $_POST['utilisateurEmail'];

    if(empty($username) || empty($password) || empty($usertype) || empty($email)){
        $message2 = '<div class="alert alert-danger">Tous les champs sont obligatoires !</div>';
    }else{
        try{
            $sql = "UPDATE users 
                    SET username=:username,password=:password,email=:email,usertype=:usertype
                    WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username',$username,PDO::PARAM_STR);
            $stmt->bindParam(':password',$password,PDO::PARAM_STR);
            $stmt->bindParam(':email',$email,PDO::PARAM_STR);
            $stmt->bindParam(':usertype',$usertype,PDO::PARAM_STR);
            $stmt->bindParam(':id',$id,PDO::PARAM_INT);
            $stmt->execute();
            $message2 = '<div class="alert alert-success">Utilisateur modifié avec succès.</div>';
        }catch(PDOException $e){
            $message2 = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
      
}

?>
<body>
<?php
require 'inc/navigation.php';
require 'inc/footer.php';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-2">
            <h1 class="my-4"></h1>
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">

                <a class="nav-link <?php if($modifier!=1) echo "active"?>" id="v-pills-dashbord-tab" data-toggle="pill" href="#v-pills-dashbord" role="tab" aria-controls="v-pills-dashbord" aria-selected="false"><i class="fas fa-home"></i> Dashbord</a>

                <a class="nav-link" id="v-pills-item-tab" data-toggle="pill" href="#v-pills-item" role="tab" aria-controls="v-pills-item" aria-selected="true"><i class="fas fa-user"></i> Utilisateurs</a>

                <a class="nav-link <?php if($modifier==1) echo "active"?>" id="v-pills-search-tab" data-toggle="pill" href="#v-pills-search" role="tab" aria-controls="v-pills-search" aria-selected="false"><i class="fas fa-search"></i> Rechercher</a>
            </div>
        </div>
        <div class="col-lg-10">
            <div class="tab-content" id="v-pills-tabContent">
                
                <!-- Dashbord -->
                <div class="tab-pane fade show <?php if($modifier!=1) echo "active"?>" id="v-pills-dashbord" role="tabpanel"  aria-labelledby="v-pills-dashbord-tab">
                    <div class="card card-outline-secondary my-4">
                        <div class="card-header"><i class="fas fa-home"></i> Dashbord</div>
                        <div class="container-fluid">
                            					
<div class="container-fluid my-4">
    <div class="row row-cols-1 row-cols-md-2 g-4">
         <div class="col-md-3">
            <div class="card">
                <div class="card-icon bg-primary text-white d-flex justify-content-center align-items-center p-3">
                 <i class="fas fa-user fa-4x"></i>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                 <h5 class="card-title" style="color: black;font-weight: bold;font-size: 35px;"><?php echo nbreusers()?></h5>
                 <p class="card-text" style="color: gray;text-decoration: none;">Utilisateurs</p>
                </div>
            </div>
         </div>
		<!--
        <div class="col">
            <div class="card">
                <div class="card-icon bg-success text-white d-flex justify-content-center align-items-start p-3">
                    <i class="fas fa-heart fa-4x"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Card title</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-icon bg-warning text-white d-flex justify-content-center align-items-start p-3">
                    <i class="fas fa-globe fa-4x"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Card title</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to additional content.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-icon bg-danger text-white d-flex justify-content-center align-items-start p-3">
                    <i class="fas fa-cogs fa-4x"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Card title</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                </div>
            </div>
        </div>

        -->
    </div>
</div>
                            
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="v-pills-item" role="tabpanel" aria-labelledby="v-pills-item-tab">
                    <div class="card card-outline-secondary my-4">
                        <div class="card-header"><i class="fas fa-user"></i> Détails des utilisateurs</div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#utilisateurTab">Ajouter un utilisateur</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="utilisateurTab" class="container-fluid tab-pane active">
                                    <br>
                                    <div id="itemDetailsMessage"><?php echo $message1?></div>
                                    <form  action="" method="post">
                                        <div class="form-row">
                                            <div class="form-group col-md-3" style="display:inline-block">
                                                <label for="utilisateurNom">Nom <span class="requiredIcon">*</span></label>
                                                <input type="text" class="form-control" name="utilisateurNom" id="utilisateurNom" autocomplete="off">
                                                <div id="itemDetailsItemNumberSuggestionsDiv" class="customListDivWidth"></div>
                                            </div>
                                            <div class="form-group col-md-3" style="display:inline-block">
                                                <label for="utilisateurEmail">Email<span class="requiredIcon">*</span></label>
                                                <input type="text" class="form-control" name="utilisateurEmail" id="utilisateurEmail" autocomplete="off">
                                                <div id="itemDetailsItemNumberSuggestionsDiv" class="customListDivWidth"></div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="utilisateurID">ID d'utilisateur</label>
                                                <input class="form-control invTooltip" type="number" readonly  id="utilisateurID" name="utilisateurID" title="This will be auto-generated when you add a new user">
                                            </div>
                                        </div>
                                       <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="utilisateurMotdepasse">Mot de passe<span class="requiredIcon">*</span></label>
                                                <input type="password" class="form-control" name="utilisateurMotdepasse" id="utilisateurMotdepasse" autocomplete="off">
                                                <div id="itemDetailsItemNameSuggestionsDiv" class="customListDivWidth"></div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="utilisateurType">Type d'utilisateur</label>
                                                 <select id="utilisateurType" name="utilisateurType" class="form-control chosenSelect">
                                                     <option value="user">User</option>
                                                     <option value="admin">Admin</option>
                                                 </select>
                                            </div>
                                        </div>
                                        <button type="submit" name="ajouter" class="btn btn-success">Ajouter</button>
                                    </form>
                               </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade show <?php if($modifier==1) echo "active"?>" id="v-pills-search" role="tabpanel" aria-labelledby="v-pills-search-tab">
                <div class="card card-outline-secondary my-4">
                    <div class="card-header"><i class="fas fa-search"></i> Recherche d'utilisateurs<button id="searchTablesRefresh" name="searchTablesRefresh" class="btn btn-warning float-right btn-sm">Refresh</button></div>
                    <div class="card-body">                                        
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#itemSearchTab">Utilisateurs</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="itemSearchTab" class="container-fluid tab-pane active"><br>
                            <div class="tab-content">
                                    <div id="utilisateurTab" class="container-fluid tab-pane active">
                                       <br>
                                    <div id="itemDetailsMessage"><?php echo $message2?></div>
                                <?php
                                    if(isset($_SESSION['id_m']) && $modifier == 1){
                                       ?>
                                    <form  action="" method="post">
                                        <div class="form-row">
                                            <div class="form-group col-md-3" style="display:inline-block">
                                                <label for="utilisateurNom">Nom <span class="requiredIcon">*</span></label>
                                                <input type="text" class="form-control" name="utilisateurNom" id="utilisateurNom" autocomplete="off"  value="<?php echo $_SESSION['u_m']?>">
                                                <div id="itemDetailsItemNumberSuggestionsDiv" class="customListDivWidth"></div>
                                            </div>
                                            <div class="form-group col-md-3" style="display:inline-block">
                                                <label for="utilisateurEmail">Email<span class="requiredIcon">*</span></label>
                                                <input type="text" class="form-control" name="utilisateurEmail" id="utilisateurEmail" autocomplete="off" value="<?php echo $_SESSION['e_m']?>">
                                                <div id="itemDetailsItemNumberSuggestionsDiv" class="customListDivWidth"></div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="utilisateurID">ID d'utilisateur</label>
                                                <input class="form-control invTooltip" type="number" readonly  id="utilisateurID" name="utilisateurID" title="This will be auto-generated when you add a new user">
                                            </div>
                                        </div>
                                       <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="utilisateurMotdepasse">Mot de passe<span class="requiredIcon">*</span></label>
                                                <input type="text" class="form-control" name="utilisateurMotdepasse" id="utilisateurMotdepasse" autocomplete="off" value="<?php echo $_SESSION['p_m']?>">
                                                <div id="itemDetailsItemNameSuggestionsDiv" class="customListDivWidth"></div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="utilisateurType">Type d'utilisateur</label>
                                                 <select id="utilisateurType" name="utilisateurType" class="form-control chosenSelect">
                                                     <option value="user">User</option>
                                                     <option value="admin">Admin</option>
                                                 </select>
                                            </div>
                                        </div>
                                        <button type="submit" name="modify" class="btn btn-success">Modifier</button>
                                    </form>
                                    </div>
                                    </div>

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
                                                            <div id="itemDetailsMessage"><?php echo $message?></div>
                                                            <form action="" method="get">   
                                                                <table id="example" style="width:100%" class="table table-striped table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>ID</th>
                                                                            <th>Username</th>
                                                                            <th>Password</th>
                                                                            <th>Email</th>
                                                                            <th>Role</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        if (isset($_SESSION['valid'])) {
                                                                            try {
                                                                                $sql = "SELECT * FROM users";
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();

                                                                                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo $row[0]; ?></td>
                                                                                        <td><?php echo $row[1]; ?></td>
                                                                                        <td><?php echo $row[2]; ?></td>
                                                                                        <td><?php echo $row[3]; ?></td>
                                                                                        <td><?php echo $row[4]; ?></td>
                                                                                        <td>
                                                                                            <form action="admin.php" method="post">
                                                                                                <input type="hidden" name="id" value="<?php echo $row[0]; ?>">
                                                                                                <button type="submit" class="btn btn-primary btn-sm" name="action" value="modifier">Modifier</button>
                                                                                                <button type="submit" class="btn btn-danger btn-sm" name="action" value="supprimer">Supprimer</button>
                                                                                            </form>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <?php
                                                                                }
                                                                            } catch (PDOException $e) {
                                                                                echo "Error: " . $e->getMessage();
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </tbody>
                                                                </table>
                                                            </form>
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
        </div>
    </div>
</div>
</div>
</div>
<?php
?>
</body>
</html>