# GTI-Manager-System

### Requirements

* Composer (can be installed in the project directory, check the 2nd step of installation)
* PHP >= 7.0
* MySQL >= 5.6
* HTTPS (highly recommended, but if not possible check the 6th step of Installation)

### Instalation

1. Clone the project to a directory which is available to be accessed via browser (don't forget to allow the use of .htaccess on the public folder, or include its directives directly in the VirtualHost configurations, and updating the "RewriteBase" line accordingly if necessary);
2. If Composer is not installed in the system, follow [this tutorial](https://getcomposer.org/download/) while in the project directory;
3. Create a database and run (import) the script vendor/SON/SQL/gtidb.sql (it contains a default admin account with "admin" as both login and password);
4. Run `composer install` (or, if composer was installed locally in the project directory, `php composer.phar install`);
5. The program uses a socket server to sync events and send notifications to logged in users. It can be run with nohup command or cron jobs, but we recommend creating a service for it. To do that, create the file `/lib/systemd/system/gtic-socket-server.service` with the following content:
```
[Unit]
Description=GTI Chamados PHP Socket Server
After=network.target
StartLimitIntervalSec=0

[Service]
Type=simple
Restart=always
RestartSec=1
ExecStart=/usr/bin/env php /path/to/project/dir/App/SocketServer.php start

[Install]
WantedBy=multi-user.target
```
Update the "ExecStart" line accordingly.
Make sure the port 5530 is available to be used by the socket server and it isn't blocked by the firewall. If it's necessary to change the port, search for all occurrences of the code ``io(`wss://${window.location.host}:5530`)`` in the project and replace the port for one which is available, as well as the line `$io = new SocketIO(5530, $context);` in the file `App/SocketServer.php`.
Then start the service and make it automatically start at boot with respectively:
```
systemctl start gtic-socket-server
systemctl enable gtic-socket-server
```
6. Notice that the connection to the websocket server is made through HTTPS by default. It's necessary to edit the file `App/SocketServer.php` to include the location of the certificate and key files:
```
// SSL context
$context = [
    'ssl' => [
        'local_cert' => '/path/to/cert.pem',
        'local_pk' => '/path/to/key.pem',
        'verify_peer' => false
    ]
];

// Set the websocket port
$io = new SocketIO(5530, $context);
```
In cases where this is not possible (such as when running the software locally), it's necessary to find all occurrences of ``io(`wss://${window.location.host}`` and replace `wss` with `ws`. Also, replace all the code in the code block above with only: `$io = new SocketIO(5530);`

### Setup

#### Edit some settings

1. Edit the file vendor/SON/Db/DBConnector.php to edit the database connection settings;
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
