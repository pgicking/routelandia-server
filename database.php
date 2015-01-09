<?php

require_once 'PortalStyle.php';

use Respect\Relational\Mapper;

class DB {
  private static $database_handle;
  /*
   * A singleton to get an instance 
   */
  public static function instance() {

    ############################################################
    # Set the following variables for your database
    ############################################################

    $DB_HOST = "localhost";
    $DB_NAME = "portal_staging";
    $DB_USER = "";
    $DB_PASSWORD = "";

    ############################################################
    # Don't adjust anything below this
    ############################################################


    if(DB::$database_handle == NULL) {
      //try {
        DB::$database_handle = new Mapper(new PDO("pgsql:host=$DB_HOST;dbname=$DB_NAME;user=$DB_USER;password=$DB_PASSWORD"));
        DB::$database_handle->setStyle(new Portal\Data\Styles\PortalStyle);
      //} catch (PDOException e) {
      //  throw new DatabaseErrorException($e->getMessage());
      //}
    }

    return DB::$database_handle;
  }

}
