# GroupOffice platform

What should it do?

## Server side
1. Database ORM
2. JSON REST API (Routing, Saving, fetching, filtering etc.)
3. User authentication
4. Roles and permissions
5. Tagging system. 
	Tags can be global or module specific. For example "Important" could be a global tag but "Customers" would only be relevant for the contacts module.
	There should also be module specific dynamic tags that can implement specific database queries like age > 35 in the contacts module for example.
6. Custom fields
7. Module store. Everybody can create modules and deliver them to Intermesh to put them in our module store. Users can browse the store from within Group-Office and click on an install button to install them.
8. File uploads
9. Image thumbnailing
10. E-mail sending
11. Reminder / alerts

## Client side
1. Angular JS core components
2. HTML with SCSS / CSS components