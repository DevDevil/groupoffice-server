# Installing and upgrading the database

Each module can install and upgrade database tables. The Group-Office framework 
will automatically apply patch files.

Database files must be put in this path:

\<Namespace>\<ModuleName>\Database\Install

You can put in php or sql files and the name must be in this format:

YYYYMMDD-HHMM.sql or YYYYMMDD-HHMM.php

For example:

20141223-1033.sql

When the upgrade process starts it will gather all module patch files and they 
will be applied in chronological order. This is done so because a patch file might
rely on other module updates when they depend on eachother.

The number of applied patches per module are stored in the modulesModule.version
 column.

Installing and upgrading is basically the same process. The first patch file is
the initial database.