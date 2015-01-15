# GroupOffice platform

What should it do?

## Server side
- Database ORM
- Database Patching
- JSON REST API (Routing, Saving, fetching, filtering etc.)
- User authentication
- Roles and permissions
- Tagging system. 
	Tags can be global or module specific. For example "Important" could be a global tag but "Customers" would only be relevant for the contacts module.
	There should also be module specific dynamic tags that can implement specific database queries like age > 35 in the contacts module for example.
- Custom fields
- Module store. Everybody can create modules and deliver them to Intermesh to put them in our module store. Users can browse the store from within Group-Office and click on an install button to install them.
- File uploads
- Image thumbnailing
- E-mail sending
- Reminder / alerts


## Client side
1. Angular JS core components
2. HTML with SCSS / CSS components