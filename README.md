org.civicoop.configitems
========================

This CiviCRM native extension allows you to define all kinds of configuration items from JSON files, and create or update them in CiviCRM using the *Civiconfig.LoadJson* API method.  
Thanks to Emphanos and their customer IIDA for funding the original version of this extension.


### How to use

- Install the latest version of this extension (see [Releases](releases)).
- Create a resources directory where you'll keep your custom JSON configuration files. For instance */sites/default/modules/civicrm_resources* or */wp-content/uploads/civicrm/resources*, or a directory within another (generic) extension. It's a good idea to version your resources directory using Git.  
- Call the API method Civiconfig.LoadJson, and pass the full path to your resources directory in the *path* parameter.
- The configuration items will then be added or updated from the JSON files in this directory (or you'll see an error if the parameters or configuration files weren't valid). See below for the items types that are supported. 


### Configuration support

The extension can work with the following types of items:
- activity types
- contact types
- custom groups with custom fields
- event types
- groups
- membership types
- option groups with option values
- relationship types
- tags

Refer to the example files (that contain config items for IIDA) in the 
[resources_examples](resources_examples) directory as a start to create your own configuration files.

For some more explanation on how this works exactly, check 
[this blog post on civicrm.org](https://civicrm.org/blog/erikhommel/extension-to-configure-civicrm-items).


### Tips and notes

- The extensions also removes 'old' custom fields from custom groups that are in the JSON files! So if I have a custom group 'test_erik' with custom fields 'test1' and 'test2' in my CiviCRM database and the JSON custom data file has a custom group named 'test_erik' with only the custom field 'test1', running the updater will remove the field 'test2'.

- For backwards compatibility reasons, the API method doesn't require the *path* parameter. If you don't specify a path, the script will check if a */resources* folder exists in the extension's own directory.

- Feeling adventurous? We've made it relatively easy to use a different file format instead of JSON, or fetch the configuration in a different way entirely (database, url, smoke signals, ...). If you want to do this, create your own class that extends CRM_Civiconfig_ParamsProvider to provide config item data, then call the CRM_Civiconfig_Config->updateConfiguration method passing an instance of your custom class.


### Ideas for future improvements

- Add support for more CiviCRM entity types.  
For one, support for ProfileGroups/ProfileFields could probably be borrowed from [this extension](https://github.com/catorghans/net.trinfinity.orgis.mi.dataquality). 

- Add support to set certain CiviCRM core settings automatically (using the Setting.create API).

- Add functionality to export config items from an existing CiviCRM installation, for the types of data we support?

- Make it possible to use this extension from an admin page (configuring resource path and running the loader) instead of having to call the API?

- ...and more? Code contributions and/or funding to improve this extension are of course most welcome!
