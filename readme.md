Aligent Feeds Extension
=====================

Facts
-----
- version: 0.1.1
- extension key: Aligent_Feeds

Description
-----------
Configurable data feeds (e.g. Google Shopping) extension.  Optimised for large catalogs.

All attributes in the data feed must be stored in Magento's Flat tables.

Installation Instructions
-------------------------
1. Install this module via modman or composer
2. create folder `/feeds` in your magento root 

Extending
-------------------------
1. In your local module config add `<feeds></feed>` and this config will be merged
2. Inside each field it calculates `<attribute> <value> <special> <singleton> and concats the result`
3. Product attributes must exist in flat table
4. For dropdown attribute i.e `gshopping_category` use `gshopping_category_value`. In flat table that will hold the actual value 

Uninstallation
--------------
1. Delete .modman/Aligent_Feeds and run "modman repair"

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/aligent/Aligent_Feeds/issues).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Jim O'Halloran <jim@aligent.com.au>

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2013 Aligent Consulting
