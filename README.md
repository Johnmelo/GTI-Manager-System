# GTI-Manager-System

### Requirements

* Composer (can be installed in the project directory, check the 2nd step of installation);
* PHP >= 7.0.
* MySQL >= 5.6

### Instalation

1. Clone the project to a directory which is available to be accessed via browser (don't forget to allow the use of .htaccess on the public folder, or include its directives directly in the VirtualHost configurations, and updating the "RewriteBase" line accordingly if necessary);
2. If Composer is not installed in the system, follow [this tutorial](https://getcomposer.org/download/) while in the project directory;
3. Create a database and run (import) the script vendor/SON/SQL/gtidb.sql (it contains a default admin account with "admin" as both login and password);
4. Run `composer install` (or, if composer was installed locally in the project directory, `php composer.phar install`);

### Setup

#### Edit some settings

1. Edit the file App/Init.php to edit the database host, name, user and password, in the getDb function;
2. Edit the file App/Model/Email.php and set the values of the consts SMTP_HOST, FROM_EMAIL, FROM_NAME and PASSWORD (make sure port 465 is allowed to send emails, or if [SELinux could be preventing the PHP to send emails](https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting#selinux-blocking));

#### Add places in the database

When registered clients make service requests, they need to specify the responsible place for the request. Those places are registered in the "locais" table in the DB. You must "INSERT INTO" the values "nome" and "tipo" for each location. In the form for requesting a service, the places will appear in a autocomplete textbox comparing what's typed with the data from "nome" column, and (for the sake of visual orgnization) those places are grouped by the "tipo" column. The column "ativo" determines which locations will be searchable and choosable in the service request form.

#### Replace the placeholders

Bellow it's listed the files which contains placeholders to be replaced, and which ones. Some placeholders of img src are supposed to be replaced by full URL addresses, to make users able to view the images when they receive the email (if the URL of the images in the email are relative paths, they won't load).
One recommended way to replace them is using a text editor with Find/Replace feature.

| File | Placeholders |
| ---  |     ---      |
| App/View/emailcontroller/email_footer.phtml | PLACEHOLDER_ENTITY_NAME; PLACEHOLDER_SHADOW_IMG_EAST_URL; PLACEHOLDER_SHADOW_IMG_SOUTHWEST_URL; PLACEHOLDER_SHADOW_IMG_SOUTH_URL; PLACEHOLDER_SHADOW_IMG_SOUTHEAST_URL; PLACEHOLDER_TELEPHONE_NUMBER; PLACEHOLDER_TELEPHONE_EXTENSION; PLACEHOLDER_EMAIL; PLACEHOLDER_URL; PLACEHOLDER_LOGO_ALT; PLACEHOLDER_LOGO |
| App/View/emailcontroller/email_header.phtml | PLACEHOLDER_SHADOW_IMG_NORTHWEST_URL; PLACEHOLDER_SHADOW_IMG_NORTH_URL; PLACEHOLDER_SHADOW_IMG_NORTHEAST_URL; PLACEHOLDER_SHADOW_IMG_WEST_URL |
| App/View/emailcontroller/request_granted_notification_email.phtml | PLACEHOLDER_ENTITY_LOGO; PLACEHOLDER_ENTITY_LOGO_ALT; PLACEHOLDER_BANNER_REQUEST_GRANTED_IMG; PLACEHOLDER_PLATFORM_URL |
| App/View/emailcontroller/request_notification_email.phtml | PLACEHOLDER_ENTITY_LOGO; PLACEHOLDER_ENTITY_LOGO_ALT; PLACEHOLDER_PLATFORM_NAME |
| App/View/emailcontroller/request_refused_notification_email.phtml | PLACEHOLDER_ENTITY_LOGO; PLACEHOLDER_ENTITY_LOGO_ALT |

Some placeholders are meant to be replaced by the full URL addresses of files already included in the project. Check below.

| Placeholder | File from where the address come |
|     ---     |                ---               |
| PLACEHOLDER_BANNER_REQUEST_GRANTED_IMG | App/View/img/email/banner-account-granted.png |
| PLACEHOLDER_SHADOW_IMG_EAST_URL | App/View/img/email/shadow_east.png |
| PLACEHOLDER_SHADOW_IMG_SOUTHWEST_URL | App/View/img/email/shadow_southwest.png |
| PLACEHOLDER_SHADOW_IMG_SOUTH_URL | App/View/img/email/shadow_south.png |
| PLACEHOLDER_SHADOW_IMG_SOUTHEAST_URL | App/View/img/email/shadow_southeast.png |
| PLACEHOLDER_SHADOW_IMG_NORTHWEST_URL | App/View/img/email/shadow_northwest.png |
| PLACEHOLDER_SHADOW_IMG_NORTH_URL | App/View/img/email/shadow_north.png |
| PLACEHOLDER_SHADOW_IMG_NORTHEAST_URL | App/View/img/email/shadow_northeast.png |
| PLACEHOLDER_SHADOW_IMG_WEST_URL | App/View/img/email/shadow_west.png |
