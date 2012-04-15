# oBugger 0.23a Release Notes

oBugger is a fast, lean, bug tracker for developers who want a no-nonsense approach to tracking tickets. Basic functionality is supported out-of-the-box, with additional functionality (hopefully) provided by sets of plugins in the future.

Requirements:

- Web server with PHP5 support (i.e. Apache, nginx)
- A MySQL Database

Installation:

1. Configure `lib/config.inc.php` to contain required values (public access links, local paths, database servers and logins, etc). A sample file is provided in config.sample.inc.php. Simply copy this file to config.inc.php and edit its values.
2. Connect to mysql server and create a new database table (with name matching the one defined in `lib/config.inc.php`)
3. Dump the base table structure into the newly created database.
    - `mysql -h HOSTNAME -u USERNAME -p DATABASENAME < SQLFILE`
    - e.g. If the local database's table name is `obugger`, with username `foo`, and the sql file located in the current working directory:
        - `mysql -h localhost -u foo -p obugger < ./obugger.sql`
4. Test configuration by visiting new installation.
5. ???
6. Profit!

Support:
	1.  [GitHub Project Issue Tracker](https://github.com/julianlam/oBugger/issues)
	2.  Questions? Send me (Julian Lam) a message bia GitHub
