# GTI-Manager-System

### Requirements

* Composer (can be installed in the project directory, check the 2nd step of installation);
* PHP >= 7.0.
* MySQL >= 5.6

### Instalation

1. Clone the project to a directory which is available to be accessed via browser (don't forget to allow the use of .htaccess on the public folder, or include its directives directly in the VirtualHost configurations, and updating the "RewriteBase" line accordingly if necessary);
2. If Composer is not installed in the system, follow [this tutorial](https://getcomposer.org/download/) while in the project directory;
3. Create a database and run (import) the script vendor/SON/SQL/gtidb.sql (it contains a default admin account with "admin" as both login and password);

### Setup

#### Edit some settings

1. Edit the file App/Init.php to edit the database host, name, user and password, in the getDb function;
2. Edit the file App/Model/Email.php and set the values of the consts SMTP_HOST, FROM_EMAIL, FROM_NAME and PASSWORD;

#### Replace the placeholders

Bellow it's listed the files which contains placeholders to be replaced, and which ones. One recommended way to replace them is using a text editor with Find/Replace feature.

| File | Placeholders |
| ---  |     ---      |
| App/View/emailcontroller/email_footer.phtml | PLACEHOLDER_ENTITY_NAME |
