# Forked to my Repo for immortialization in case PORTAL works on the project. 


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
    $GLOBALS['RUN_IN_PRODUCTION'] = true; // Only if you're running in production mode.
  ```
* Run the migrations.sql file to add routelandia specific content to the production database.
NOTE: The views/functions added to the production database in the next step are put into a schema called "routelandia", so you'll need to make sure that the database user that is connecting has this schema in their search path.
* If you want to have the /explorer website available create a symlink in the public folder to vendor/luracast/explorer/dist
* Configure your web server to serve the public/ folder of the project. (The project is designed to run from http://server/api/ but we'll try never to assume the /api/ part in the code.)


## Testing Database Installation
* Important! You must add the following section of text to your apache2.conf file! Otherwise the URLs the tests use will not work!
* It is at /etc/apache2/ 

Alias /api-test "/var/www/capstone_2014/routelandia-server/public/"
<Directory "/var/www/capstone_2014/routelandia-server/public">
	Options FollowSymLinks
	AllowOverride All
	Order allow,deny
	Allow from all
	Require all granted
</Directory> 

* If the test database is being set up on a Linux or Mac system, run the script testing_database_setup_script and it will set everything up, notes are in the script itself
* If you are setting up the system on Windows use the following instructions:
* Create local database in PostgreSQL called portal_testing
* Run the following command to copy data into portal_testing: 
 	psql portal_testing < testingdb.sql
* Run the following command to create the views needed for the backend in portal_testing: 
	psql portal_testing < migrations.sql
	
	
	
	
