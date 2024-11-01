=== Database Table Overview and Logs ===
Contributors: ninetyninew
Tags: database table, database log, database, table, log
Donate link: https://ko-fi.com/ninetyninew
Stable tag: 1.3.0
Tested up to: 6.6.1
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Lists and logs all database tables including name, size and rows.

== Description ==

Displays an overview of all database tables, showing table name, total size, number of rows and a preview of the data stored in each table. You can review this data historically by selecting a past date, providing useful insights into your database.

In addition to listing all database tables, it also displays the name, total size and number of rows of the entire database. The premium version also includes automatic daily logging, email reports, print and export.

= Features =

- List of all database tables including name, size and rows
- Preview a record from a specific table
- Search and sort the data
- Shows the database name, total size and rows
- Clear logs greater than 1, 3, 6, 12 months or all
- Manual logging of tables each time the dashboard is accessed
- Automatic daily logging (Premium)
- Daily email reports to one or more recipients (Premium)
- Print an overview of database tables (Premium)
- Export an overview of database tables (Premium)

= Usage =

From the WordPress dashboard menu navigate to Database Table Overview and Logs. By default the data shown is for today, you can view historic data using the date picker.

= Donate =

If this product has helped you, please consider [making a donation](https://ko-fi.com/ninetyninew).

== Screenshots ==

1. Dashboard
2. Selecting a date to view historic data
3. Table information and random row preview
4. Email report

== Frequently Asked Questions ==

= How often are the database tables logged? =

The logs are updated each time the dashboard is accessed, with the premium version logs are automatically generated every day.

= Can I view historic logs of the database tables? =

Yes, use the date picker. Data is only available from the date this plugin was activated. Logging is done each time the dashboard is accessed, the premium version automatically logs the data every day.

= Can I search/sort the list of database tables, size, rows? =

Yes, use the search field to search and click a column heading to sort.

= Can I view the data stored in a table? =

Yes, if you click a database table name a modal appears which includes a random row from the database table, to view every row you will need to use a database management tool such as [phpMyAdmin](https://www.phpmyadmin.net/).

= Can I get an email report of the data? =

Yes, the premium version sends an email report daily to the email report recipient(s) set.

= Can I print/export the data? =

Yes, the premium version includes print and export functionality, just click the buttons.

== Installation ==

Before using this product, please ensure you review and accept our [terms and conditions](https://99w.co.uk/#terms-conditions) and [privacy policy](https://99w.co.uk/#privacy-policy).

Before using this product on a production website you should thoroughly test it on a staging/development environment, including all aspects of your website and potential data volumes, even if not directly related to the functionality the product provides.

The same process should also be completed when updating any aspect of your website in future, such as performing installations/updates, making changes to any configuration, custom web development, etc.

Always refer to the changelog before updating.

= Installation =

Please see [this documentation](https://wordpress.org/support/article/managing-plugins/#installing-plugins-1).

= Updates =

Please see [this documentation](https://wordpress.org/documentation/article/manage-plugins/#updating-plugins).

= Minimum Requirements =

* PHP 7.4.0
* WordPress 6.4.0

= BETA Functionality =

We may occasionally include BETA functionality, this is highlighted with a `(BETA)` label. Functionality with this label should be used with caution and is only recommended to be tested on a staging/development environment. The functionality is included so users can test the functionality/provide feedback before it becomes stable, at which point the `(BETA)` label will be removed. Note that there may be occasions where BETA functionality is determined unsuitable for use and removed entirely.

= Caching =

If you are using any form of caching then it is recommended that the cache lifespan/expiry should be set to 10 hours or less. This is recommended by most major caching solutions to avoid potential issues with WordPress nonces.

= Screen Sizes =

- Frontend: Where elements may be displayed on the frontend they will fit within the screen width
- Backend: Where interfaces may be displayed it is recommended to use a desktop computer with a resolution of 1920x1080 or higher, for lower resolutions any interfaces will attempt to fit within the screen width but some elements may be close together and/or larger than the screen width

= Translation =

We generally recommend [Loco Translate](https://wordpress.org/plugins/loco-translate/) to translate and/or adapt text strings within this product.

= Works With =

Where we have explicitly stated this product works with another product, this should only be assumed accurate if you are using the version of the other product which was the latest at the time the latest version of this product was released. This is because, while usually unlikely, the other product may have changed functionality which effects this product.

== Changelog ==

= 1.3.0 - 2024-08-23 =

* Add: .pot to languages folder
* Add: Requires plugins dependency header
* Update: Freemius SDK
* Update: WordPress requires at least 6.4.0
* Update: WordPress tested up to 6.6.1
* Fix: Scheduled hook wpdtol_database_table_overview_logs_update not clearing on deactivation

= 1.2.1 - 2024-07-10 =

* Update: composer.json and composer.lock to woocommerce/woocommerce-sniffs 1.0.0
* Update: Installation and updates information in readme.txt
* Update: phpcs.xml codesniffs
* Update: Freemius SDK
* Update: WordPress tested up to 6.5.5

= 1.2.0 - 2024-04-10 =

* Add: Translation information in readme.txt
* Update: Table size column to include (MB) suffix
* Update: Freemius SDK
* Update: WordPress tested up to 6.5.2
* Fix: Table size column does not sort correctly

= 1.1.5 - 2024-03-08 =

* Add: BETA functionality information to readme.txt
* Add: Caching information to readme.txt
* Add: Donation information to readme.txt
* Add: Works with information to readme.txt
* Update: Screen sizes information in readme.txt
* Update: WordPress tested up to 6.4.3

= 1.1.4 - 2024-01-17 =

* Update: Changelog consistency
* Update: Freemius SDK

= 1.1.3 - 2023-12-15 =

* Update: Code consistency
* Update: Development assets
* Update: Screen sizes typo in readme.txt
* Update: Freemius SDK
* Update: WordPress requires at least 6.3.0
* Update: WordPress tested up to 6.4.2
* Fix: Log data not inserted in database if MySQL config has NO_AUTO_VALUE

= 1.1.2 - 2023-09-19 =

* Update: Freemius SDK
* Update: WordPress tested up to 6.3.1

= 1.1.1 - 2023-08-04 =

* Update: Development assets
* Update: PHP requires at least 7.4.0
* Update: WordPress requires at least 6.1.0

= 1.1.0 - 2023-07-06 =

* Update: Freemius SDK
* Update: WordPress tested up to 6.2.2

= 1.0.0 - 2023-03-07 =

* New: Initial release