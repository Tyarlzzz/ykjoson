<?php
$password = "halimawmagpagawangsystem123";

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Your hashed password is: " . $hash;
?>