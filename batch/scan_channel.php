<?php
for ($i = 13; $i < 60; $i++) {
    $c = "/home/ha1t/bin/recfriio --b25 {$i} 3 {$i}.ts";
    echo $c;
    exec($c);
}
