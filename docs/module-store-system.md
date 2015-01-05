# Module store
Everybody can create modules and deliver them to Intermesh to put them in our module store. Users can browse the store from within Group-Office and click on an install button to install them.
Developers can either build the AngularJS client with own script themselves or put the scripts on the server.

## Store module install

Intermesh modules are put in the "Intermesh\Modules" namespace. Custom modules should have the "Store\Modules" namespace. The PHP autoloader function can look in the application data folder for these modules so there are no issues with file permissions. So when a user clicks on "Install" in the store the tarred file "module-name.gom" file is downloaded with "curl" and unpacked in the application data folder.

## Client scripts
The server is able to deliver module scripts to clients. A script can be included:

```````````````````````````````````````````````````````````````````````````````````````````````````````
<script type="text/javascript" src="/groupoffice/api/clientscripts?type=js&client=AngularJS"></script>
```````````````````````````````````````````````````````````````````````````````````````````````````````

This script will gather all *.js files in the installed modules' "ClientScripts/AngularJS" folder and minify them.
CSS files should also be supported.