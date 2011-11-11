<?php

use lang-1/Greetings as Greets1;
use lang-2/Greetings as Greets2;


$greeting1 = Greets1::sayHello();

echo $greeting1 . "\n";

$greeting2 = Greets2::sayHello();

echo $greeting2 . "\n";
