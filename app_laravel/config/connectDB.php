<?php
    $conn =mysqli_connect('localhost','root','','moviewebsite');
    if(!$conn){
        die("Connection failed". mysqli_connect_error());
    }
?>