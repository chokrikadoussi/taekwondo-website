<?php
require './fonctions.php';

/* 
TEST DE VERIFICATION EMAIL
*/
$emails = ['', 'foo', 'foo@bar', 'foo@bar.com'];
echo "=== Tests e-mail ===<br>";
foreach ($emails as $e) {
    printf("%-15s : %s\n", $e, estValideMail($e) ? 'OK' : 'KO');
    echo "<br>";
}

/* 
TEST DE VERIFICATION MDP
*/
$passwords = [
  'Short1!',    // trop court
  'nouppercase1!', 
  'NOLOWERCASE1!',  // si tu veux exiger une minuscule, ajoute le test
  'NoNumber!', 
  'NoSpecial1', 
  'Valid1!a', 
];
echo "\n=== Tests mot de passe ===<br>";
foreach ($passwords as $p) {
    printf("%-15s : %s\n", $p, estValideMotdepasse($p) ? 'OK' : 'KO');
    echo "<br>";
}
