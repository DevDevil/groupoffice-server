Install for development
=======================

Example API server with Intermesh framework

## Installation
To install this example follow these steps:

1. Make sure you have the required software:
   ``````````````````````````````````````````````````````````````````
   $ sudo apt-get install git curl
   ``````````````````````````````````````````````````````````````````

2. clone the repository:

   ``````````````````````````````````````````````````````````````````
   $ git clone https://github.com/intermesh/groupoffice-server.git
   ``````````````````````````````````````````````````````````````````
3. Install composer if you haven't done that already. On Ubuntu do:

   ```````````````````````````````````````````````````
    $ curl -sS https://getcomposer.org/installer | php
    $ sudo mv composer.phar /usr/local/bin/composer
   ```````````````````````````````````````````````````
4. Run composer in the working directory:

   ``````````````````````````
   $ cd groupoffice-server
   $ composer install
   ``````````````````````````
5. Put the index.php in a web server root and make sure the path to autoload.php
is correct:

   ```````````````````````````````````
   require("../vendor/autoload.php");
   ```````````````````````````````````
   Also adjust the path to config.php that you will create in step 7:
   `````````````````````````````````````````````````````
   App::init(require('../config.php'));
   `````````````````````````````````````````````````````
6. Create a MySQL database called "go7" and load "go7.sql" into it.
7. Copy config.php.example to config.php and adjust it with the correct database parameters.

Now it should work. Launch index.php:

/index.php

This will redirect to a system test page:

/index.php/modules/check

Optionally you can create an alias for apache:

Alias /api /path/to/index.php

This allows pretty URL's like /api/system/check

It should output that all is OK ;). It doesn't look pretty but it's not meant to
be because it's just an API.

The default login is:

Username: admin
Password: Admin1!





