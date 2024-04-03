BigblueButton Extension - FlexURL
=======================
* Maintained by: Laurent David
* Copyright: Blindside Networks Inc
* License:  GNU GENERAL PUBLIC LICENSE Version 3

This is an extension subplugin for the BigBlueButtonBN module. This subplugin will allow a user to add parameters to a create and join action url.
The parameters are customizable, but can also be fixed on entities like user, course and module to address the most common use cases while
keeping some control on the parameters that are passed to the BigBlueButton server.


Description
===========
This subplugin extends the BigBluebButtonBN module to:
* Add a new parameter to an Action URL (create, join or both)
* Pass basic activity, course and user information as a parameter
* Pass custom parameters and meta (meta_KEY=VALUE notation) parameters

The new feature is located in the activity edit form under Extra parameters. Parameter name and parameter value must be entered.
Users can specify whether to pass the parameter name and value on create, join, or both actions.

The subplugin allows you to choose what information fields can be used in an activity. These fields will determine what information available in Moodle can be passed as a parameter, such as activity name or userid.


Installation
============
Drop the module in the mod/bigbluebuttonbn/extension folder or install from zip file under Site administration > Plugins > Install plugins
The Moodle site will automatically detect the change. Confirm the upgrade. After installation, subplugin configuration can be found under the Manage BigBlueButton extension plugins subcategory of the BigBlueButtonBN plugin


Requirements
------------
BigblueButtonBN module version > 2022112802

Code from ticket https://tracker.moodle.org/browse/MDL-78960