=======
# org.iida.civiconfig
CiviCRM native extension to load all kinds of configuration

This extension loads configuration items from the JSON files in the resources folder and creates or updates them in the CiviCRM installation when you run the API IidaConfig Update. Obviously the extension has been developed for one specific project (thanks to Emphanos and their customer IIDA for funding!) but the principle can easily be copied and re-used.

_Note: the extensions also removes 'old' custom fields from custom groups that are in the JSON files. So if I have a custom group 'test_erik' with custom fields 'test1' and 'test2' in my CiviCRM database and the JSON custom data file has a custom group named 'test_erik' with only the custom field 'test1', running the update job will remove the field 'test2'._

The extension can deal with:
- activity types
- contact types
- custom groups with custom fields
- event types
- groups
- membership types
- option groups with option values
- relationship types
- tags

