<?php
 //login_success.php  
 session_start();  

 if(isset($_SESSION["username"]))  
 {  
     header("location:../dashboard.php");  
    }  
    else  
    {  
        header("location:../index.php");  
 }  