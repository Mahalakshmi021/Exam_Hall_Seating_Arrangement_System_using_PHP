<?php

echo "echo this is my first php file";
 echo '<br>';
 echo "echo this is my first php file next line";

 $var1 = "Sujith";

 $var2 = 8;

 echo '<br>';
 echo $var1;
 echo '<br>';
 echo $var2;

$hashedPassword = password_hash("123456", PASSWORD_DEFAULT);
echo "New Hashed Password: " . $hashedPassword;
?>