<?php
include "../classes/User.php";

#Create an obj
$user = new User;

#Call the method
$user->update($_POST, $_FILES);
?>