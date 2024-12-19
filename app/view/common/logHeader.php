<?php

session_start();
include('../../model/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$UserID = $_SESSION["user_id"];
$fname = $_SESSION["user_name"];
$acctype =  $_SESSION["Acc_type"];

if($acctype != "Admin" && $acctype != "Support"){

    echo "<script> alert ('You are not an Admin') 
        location.href='../login.php';
    </script> " ;
}

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        
		<link rel="stylesheet" href="../../../assets/CSS/logheaderAdmin.css">
    </head>
    <body>
	
		<header>
            
            <img src="../../../assets/Images/logo.png" alt="logo" />
            <h1>Welcome</h1>

            <nav>
                <ul>
                    <a href="#"><img id="notify" src="../../../assets/images/notifi.png" alt="logo"/></a>
                    <a href="../pages/support_profile.php" class="profile-icon"><img src="../../../assets/Images/profile.png" alt="logo"/></a>
                    <a href="../auth/logout.php"><button type="button" id="lout">Logout</button></a>
                    
                
                </ul>
            </nav>
        </header>

       
        

    </body>  
</html>          