<?php
// Change this to your desired password
$password = "owner123";

// Generate hash using bcrypt
$hash = password_hash($password, PASSWORD_DEFAULT);

// Display the hash
echo "Your hashed password is: " . $hash;
?>
