<?php
include_once '../localhost/vendor/autoload.php';

print_r(new \Krugozor\Pagination\Manager(1,2, $_POST));