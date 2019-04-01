#petulant-ninja

-----------------------------
EveOnline-API_esi-wallet_v1.02
-----------------------------

Eve Online API Library - fully cached, etag compliant ESI implementations of Eve Online
data for personal statistical record keeping. This project is derived from the following
works and authors:

* Master class for the battle.net WoW armory
* @author Thomas Andersen <acoon@acoon.dk>
* @copyright Copyright (c) 2011, Thomas Andersen, http://sourceforge.net/projects/wowarmoryapi
* @version 3.5.1

* db.php
* @author $Id: db.php 5283 2005-10-30 15:17:14Z acydburn $
* @copyright Copyright (c) 2001, The phpBB Group


Installation Notes -- current version is not www-portable -- wamp only please :S
-----------------------------------------------------------------------------------
1. Get a developer key from Eve Developers Portal/
2. Create encoded secret:  http://localhost/utility/base64.php
3. Create server database, and edit config.php file to match it's settings.
4. Create type-id table: http://localhost/utility/items.php


Disclaimer:
------------------------------------------------------------------------------------
Beta Only!!  If you decide to host this on the world-wide-web, you agree to be responsible
for your own data security.  There are currently no security implementations in this version.
A web-stable environment will be released in the near future.


------------------------------------------
Change log - revisions since last build
------------------------------------------
03/29/2019 - added etag headers to requests.
03/30/2019 - fixed a null header bug.
03/31/2019 - removed the SSL requirements and tweaked the callback a bit.
