<?php

// $dbh = new PDO('psql:host=localhost;dbname=portal_staging;user=joshproehl', $user, $pass, array(
//    PDO::ATTR_PERSISTENT => true
//));

require_once "../database.php";

class Highway {
  function index() {
    return DB::instance()->highways->fetchAll();
  }
}
