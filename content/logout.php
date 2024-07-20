<?php
	session_start();
	
	unset($_SESSION['valid']);
	session_destroy();
	header('Location: ../home.php');
	exit();
?>