User Group Admin
============

User Group Admin is an Owncloud app that allows users to manage groups.
This app is an extension of Group custom - written
by Jorge Rafael Garcia Ramos,
http://apps.owncloud.com/content/show.php?content=156032

Information on the groups defined by users is kept in the two tables:

"user_group_admin_groups", "user_group_admin_group_user",

corresponding to the two tables files_sharing uses for the purpose:

"groups", "group_user"

Compared to the files_sharing tables, the user_group_admin tables add one
column, `owner`, to keep track of who created and owns a given group. As of
now, only the owner is allowed to manage the groups he has created.

The information in the tables is integrated with the ownCloud sharing mechanism
by means of calls to `OC_Group::useBackend()` and `OCP\Util::connectHook()`.

Our extensions introduce groups hidden from the ownCloud sharing mechamisn,
members of which can only be added programatically, not from the ownCloud
files web interface or the web interface of this app. These groups also do not
appear in the drop-downs when sharing files or directories in the files web
interface.

We have extended the app user_saml to use such hidden groups with the name of
the home institution of users when autocreating them, thus preventing that
normal users can share files with e.g. a whole university.

