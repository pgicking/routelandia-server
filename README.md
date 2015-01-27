# Routelandia Server
This is the server component of the Routelandia project. (To describe which would be redundant here.)

If you don't have a copy of, or access to, the [Portal](http://portal.its.pdx.edu) database this project isn't
going to do you much good. (We will make no obvious effort to reproduce or describe that database, as this project
is destined to be owned by the team that manages it.)


## Technologies Used
* PHP as our scripting language as it is known by the dev/ops team that will be maintaining the project.
* Our database engine is Postgres, since we don't control the database we're displaying data from.
* [Restler](https://github.com/Respect/Relational) framework to automate routing and JSON creation. Chosen because who wants to handle these manually, and the code style is very clean!
* [Respect/Relational](https://github.com/Luracast/Restler) framework for database/ORM. Chosen because it inflects models and such automatically, which since we're using a database we don't control is very handy.



## Installation
* Clone a copy of the repo.
* Make sure you have [Composer](https://getcomposer.org) installed.
* In the project directory: Install project PHP dependecies with composer. (`composer install` if installed globally, or if composer.phar is in the local directory then `php composer.phar install`)
* Create local_config.php file in the main project directory (next to database.php) with the database connection details. This should look something like:

  ```php
  <?php
    $DB_HOST = "localhost";
    $DB_NAME = "portal_staging";
    $DB_USER = "username";
    $DB_PASSWORD = "password";
  ```
* If you want to have the /explorer website available create a symlink in the public folder to vendor/luracast/explorer/dist
* Configure your web server to serve the public/ folder of the project. (The project is designed to run from http://server/api/ but we'll try never to assume the /api/ part in the code.)

