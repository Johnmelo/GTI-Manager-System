# GTI-Manager-System

### Requirements

* Composer (can be installed in the project directory, check the 2nd step of installation);
* PHP >= 7.0.
* MySQL >= 5.6

### Instalation

1. Clone the project to a directory which is available to be accessed via browser;
2. If Composer is not installed in the system, follow [this tutorial](https://getcomposer.org/download/) while in the project directory;
3. Create a database and run (import) the script vendor/SON/SQL/gtidb.sql;
4. Edit the file App/Init.php to edit the database host, name, user and password, in the getDb function;
