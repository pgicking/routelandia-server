<?php

namespace Routelandia;

// Include the Respect/Relational "style" for the portal database
require_once 'PortalStyle.php';

// Require our database models
// note that the path has to be ../ because the execution context will be
// in public/
foreach (glob("../entities/*.php") as $filename)
{
    include $filename;
}

// Make "Mapper" easily accessible locally.
use Respect\Relational\Mapper;

use \PDO;

/**
 * Represents a connection to our application database.
 *
 * Allows you to easily access the database using a singleton
 * method, preventing the same script from opening multiple
 * connections to the database.
 * (This also makes configuration easy to change.)
 */
class DB {
  // The internal variable used to track the database handle
  private static $database_handle;
  private static $sql;
  private static $mapper;


  /**
   * A singleton to get a database handle.
   */
  private static function get_database_handle() {
    if(DB::$database_handle == NULL) {
      /* Attempt to include the local config file which is not
       * in git. (So that we don't check in configuration stuff
       * such as database passwords.
       *
       * If the file does not exist we can't continue until it
       * has been created on the machine this script is running
       * on.
       *
       * Local config is expected to set the following:
       *
       *   $DB_HOST = "";
       *   $DB_NAME = "";
       *   $DB_USER = "";
       *   $DB_PASSWORD = "";
       *
       * NOTE: We have to go up a directory to ../local_config.php because
       * database.php will be called from some script that lives in public/
       * therefore the current execution context will be in public/
       * Also note that this is included inside the call to get an instance
       * because PHP includes the contenst of the includes in a literal sense,
       * so this maintains correct variable scope.
       */
      if (!file_exists('../local_config.php'))
        throw new Exception ('local_config.php does not exist and must be created!');
      else
        require_once('../local_config.php' );

      //try {
        DB::$database_handle = new PDO("pgsql:host='$DB_HOST';dbname='$DB_NAME';user='$DB_USER';password='$DB_PASSWORD'");
      //} catch (PDOException e) {
      //  throw new DatabaseErrorException($e->getMessage());
      //}
    }

    return DB::$database_handle;
  }

  /**
   * A singleton method to get the database as a Mapper object
   */
  public static function mapper() {
    if(DB::$database_handle == null) {
      $h = DB::get_database_handle();

      DB::$mapper = new Mapper($h);
      DB::$mapper->setStyle(new \Routelandia\Data\Styles\PortalStyle);
      DB::$mapper->entityNamespace = 'Routelandia\\Entities\\';
    }

    return DB::$mapper;
  }

  /**
   * A singleton method to get the database as a SQL object
   */
  public static function sql() {
    if(DB::$sql == null) {
      $h = DB::get_database_handle();

      DB::$sql = new \Respect\Relational\Db($h);
    }

    return DB::$sql;
  }

}
