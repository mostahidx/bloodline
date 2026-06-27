<?php 
    $servername="localhost";
    $username="root";
    $password="";
    $databasename="bloodline";
    $conn="";

    $conn=mysqli_connect($servername,$username,$password,$databasename);
if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
}


/*if($conn->connect_error){
    echo"Failed to connect to Database".$conn->connect_error;
}*/

?>