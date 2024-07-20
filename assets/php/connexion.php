<?php

function connectdb(){
        $con = new Mysqli('localhost','root','','provisionrapide');
        if(!$con){
        echo "Connexion a la base de données a echoué : ".$con->error;
        }
    return $con;
}
?>