# CiviCRM Clone Contribution

![Screenshot](/images/screenshot.png)

This extension provides:

* UI controls to clone a particular contribution, wherever contributions are listed
(e.g., a contact's Contributions tab; contributions search results).
* An API Contribution.clone for cloning any number of contributions.

The extension is licensed under [GPL-3.0](LICENSE.txt).

## Installation

### Web UI

This extension has not yet been published for installation via the web UI.

### CLI, Zip

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl clonecontrib@https://github.com/twomice/clonecontrib/archive/master.zip
```

## CLI, Git

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git)
repo for this extension and install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/twomice/clonecontrib.git
cv en clonecontrib
```

## Usage

### In general

The newly created clone contribution will be created for the current date and time,
and attributed to the same contact. Soft credits are also cloned.

Because Invoice ID must be unique, this value is never copied to the new clone.
See [Configuration](#configuration) below for ways to omit other contribution
properties from the clone.

### UI

A "clone" link is provided adjacent to CiviCRM core's links for "View", "Edit",
and "Delete" in listings of contributions (e.g., a contact's Contributions tab;
contributions search results).

A confirmation pop-up allows you to confirm before cloning.

Immediately upon cloning, the newly created clone contribution is opened for editing,
on the assumption that you'll want to edit at least some properties. You may save
or cancel, but not that "cancel" does not undo the cloning -- the clone already
exists.

### API

```php
$params = array (
  // Any params appropriate for Contribution.get.
);
civicrm_api3('Contribution', 'clone', $params);
```

## Configuration
Navigate to `Administer` > `CiviContribute` > `Clone Settings` to configure.

### Skipped contribution properties
This setting allows you to indicate which properties should NOT be copied to the cloned contribution. All contribution fields are listed, including custom fields and an additional option labeled '(All Soft Credits)'.

By default, all properties are copied (except for Invoice ID, which must be unique per contribution).


## Support
Paid support is available for urgent fixes or large; occasional free support for
easy bug fixes and "great ideas I like and have time for". Very likely to answer
questions about what's possible and to provide pointers if you have any trouble.
For any of the above, please create a ticket in the
[issue queue](https://github.com/twomice/civicrm-clonecontrib/issues).