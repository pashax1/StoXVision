<?php
$c = @new mysqli("127.0.0.1", "root", "");
if ($c->connect_error) echo "127.0.0.1 failed: " . $c->connect_error . "\n"; else echo "127.0.0.1 OK\n";

$c2 = @new mysqli("localhost", "root", "");
if ($c2->connect_error) echo "localhost failed: " . $c2->connect_error . "\n"; else echo "localhost OK\n";
