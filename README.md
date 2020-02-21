MOVED
=====

This repository is moved to https://lab.civicrm.org/extensions/configitems


org.civicoop.configitems
========================

This CiviCRM native extension allows you to define all kinds of configuration items from JSON files, and create or update them on any CiviCRM installation using an API call (`Civiconfig.LoadJson`).  
Thanks to Emphanos and their customer IIDA for funding the original version of this extension.


### How to use

- Install the latest version of this extension (see [Releases](https://github.com/civicoop/org.civicoop.configitems/releases)).

- Create a resources directory where you'll keep your custom JSON configuration files. For instance */sites/default/modules/civicrm_resources* or */wp-content/uploads/civicrm/resources*, or a directory within another (generic) extension. It's a good idea to version your resources directory using Git.

- Make a copy of your database. Though unlikely, syntax errors in the JSON files or bugs in this extension could cause serious damage to your database.
  
- Call the API method `Civiconfig.LoadJson`, and pass the full path to your resources directory in the *`path`* parameter. You can do this via the CiviCRM API Explorer - or make an API call using Drush or WP-CLI.

- The configuration items will then be added or updated from the JSON files in this directory. See below for the items types that are supported. The API call will return an array with the loader status for each file - if everything went well you should see `SUCCESS` on every line.

### Configuration support

The extension can work with the following types of items:
- ContactTypes
- MembershipTypes
- RelationshipTypes
- OptionGroups with OptionValues
- Groups
- Tags
- FinancialAccounts
- FinancialTypes
- EventTypes
- ActivityTypes
- LocationTypes
- CaseTypes
- CustomGroups with CustomFields
- CiviCRM Settings (system settings set through the Setting.API - be especially careful with that!)

You can use the example files in the *[resources_examples](resources_examples)* 
directory as a start to create your own config files.

For some more explanation on how this extension works exactly, check 
[this blog post on civicrm.org](https://civicrm.org/blog/erikhommel/extension-to-configure-civicrm-items).


### Tips and notes

- The extensions also removes 'old' custom fields from custom groups that are in the JSON files! Example: if I have a custom group 'test_erik' with custom fields 'test1' and 'test2' in my database and the JSON file has a custom group named 'test_erik' that only contains custom field 'test1', the updater will remove the field 'test2' (and all data!).

- For backwards compatibility reasons, the API method doesn't require the *path* parameter. If you don't specify a path, the script will check if a */resources/* folder exists in the extension's own directory.

- Developers: feeling adventurous? We've made it relatively easy to add support for other file formats than JSON, or fetch the configuration in a different way entirely (database, url, smoke signals, ...). To do this, create your own class that extends `CRM_Civiconfig_ParamsProvider` to provide config item data, then call the `CRM_Civiconfig_Config->updateConfiguration` method passing an instance of your custom class.


### Ideas for future improvements

- Add support for more CiviCRM entity types. Maybe...  
  + ProfileGroup/ProfileField (may be borrowed from [this extension](https://github.com/catorghans/net.trinfinity.orgis.mi.dataquality))
  + Mapping / MappingField
  + UFGroup / UFField
  + MembershipStatus
  + ParticipantStatusType
  + MessageTemplate!
  + ...?

- Add functionality to easily export config items from an existing CiviCRM installation, for the types of data we support? (Using the API's JSON output as a starting point and slightly modifying/filtering that)

- Make an admin page that allows setting the resource directory and running a quick and easy import / export function, so users won't have to call the API anymore?

- ...anything else? Code contributions and/or funding to improve this extension are of course most welcome!
