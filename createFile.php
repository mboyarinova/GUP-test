<?php

$file = fopen('file.txt', "w");
for ($i=100000000;$i<1000000000;$i++) {
  $string = "ключ".$i."\tзначение".$i."\x0A";
  fwrite($file, $string);
}
