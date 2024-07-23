BigBlueButton Extension - FlexURL
=======================
* Copyright: Blindside Networks Inc
* License:  GNU GENERAL PUBLIC LICENSE Version 3

Overview
===========
The FlexURL subplugin enhances the BigBlueButtonBN module by passing additional parameters when a BigBlueButtonBN session is created, joined or both. The parameters can be custom defined, or information from user, course and activity module.

Features
===========
* **Pass parameters on create and join:** Add extra parameters to create and join action url and pass additional information to BigBlueButton.
* **Parameter Information Management:** Manage information that teachers can use as a parameter.

Installation
============
Prerequisites
------------
* Moodle environment with BigBlueButtonBN module installed.

Git installation
------------
1. Clone the repository:

`git clone https://github.com/blindsidenetworks-ps/moodle-bbbext_flexurl.git`

2. Rename the downloaded directory:

`mv moodle-bbbext_flexurl flexurl`

3. Move the folder to the Moodle BigBlueButtonBN extensions directory:

`mv flexurl /var/www/html/moodle/mod/bigbluebuttonbn/extension/`

4. Run the Moodle upgrade script:

`sudo /usr/bin/php /var/www/html/moodle/admin/cli/upgrade.php`

Manual installation
------------
1. Download the sub plugin zip file and extract it.
2. Place the extracted folder into `mod/bigbluebuttonbn/extension/`
3. Rename the folder `flexurl`
4. Access Moodle's Admin UI at `Site administration > Plugins > Install plugins` to complete the installation.

Configuration
============
Access the subplugin configuration under
`Site Administration > Plugins > BigBlueButton > Manage BigBlueButton extension plugins`

Here, admins can enable/disable the subplugin, manage settings, or uninstall it.


Usage
============
Create a new parameter
------------
From the BigBlueButton activity settings under the ‘Extra parameters’ section, use the ‘Add a new parameter’ button. For each parameter, a name and value must be entered in the corresponding text fields. Teachers can configure whether to pass the parameter when a meeting is created, joined, or for both actions.

Configure activity module, course and user information as parameters
------------
Moodle data can be used for parameter values, provided the Site Administrator has selected the relevant category in the subplugin settings (see Manage information that can be used as a parameter). To use this data, select the desired information from the dropdown list.

Pass custom parameters and meta parameters
------------
Meta parameters follow the notation `meta_KEY=VALUE`. To pass meta parameters, the `KEY` must be anything that can be a parameter in a standard URL, such as the following:
* meta_paramname=paramvalue

For custom parameters, the following example format is supported:
* paramname=paramvalue

Manage information used as a parameter
------------
From the subplugin settings, Site Administrators can select data available as parameter values.
The available categories are:
* **Activity Information:** Details about the BigBlueButton activity such as name and activity ID.
* **Course Information:** Overall course structure and content.
* **Basic User Information:** User details, such as name and email address.

Troubleshooting
============
* Parameters missing when passed: The subplugin supports parameter names that BigBlueButton accepts, which must conform to the pattern `[a-zA-Z][a-zA-Z0-9-]*$` Also note that parameter names containing uppercase will be converted to lowercase by BigBlueButton.


Requirements
============
Requires BigBlueButtonBN module version > 2022112802

For more detailed updates and support, visit the [FlexURL Subplugin GitHub Repository](https://github.com/blindsidenetworks-ps/moodle-bbbext_flexurl)