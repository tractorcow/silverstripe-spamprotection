# Spam Protection Module Installation

## Install

1. Extract all files into the 'spamprotection' folder under your Silverstripe root, or install using composer

```bash
composer require silverstripe/spamprotection dev-master
```

2. Install an appropriate spam protection provider such as
[Mollom](https://github.com/silverstripe/silverstripe-mollom),
[Recaptcha](https://github.com/chillu/silverstripe-recaptcha) or
[Akismet](https://github.com/tractorcow/silverstripe-akismet)

3. Configure the site as per the [Configuring](#configuring) section.

3. Visit your SilverStripe site in a webbrowser and run www.yoursite.com/dev/build

## Configuring

Before putting the following code into your configuration make sure you have an additional protector class installed. 
The SpamProtector module only provides the backbone. To make use of the module you have to have an additional protector
such as Mollom or Recaptcha, and each may have their own configuration requirements.

Below demonstrates how a spam protector is assigned as the current manager for this module.

```yaml
---
name: spamprotection
---
SpamProtectorManager:
  spam_protector: MollomSpamProtector
# Configuration for MollomSpamProtector would go here
```

## Updating a form to include Spam Protection

This following code should appear after the form creation.

```php
// your existing form code here...
$form = new Form( .. );

// add this line
$protector = SpamProtectorManager::update_form($form, 'Message', array(
	'FirstName' => 'authorName',
	'Message' => 'postBody'
));
```

This code add an instance of a 'SpamProtectorField' class specified in SETTING UP THE MODULE section. The newly
created field will have MollomField field. The first parameter is a Form object in which the field will be
added into and the second parameter tells SpamProtectorManagor to place the new field before a field named
'Message'. The third parameter specifies the mapping from your form field names onto those identifiers specific
to the spam protection mechanism chosen. Some protectors (such as recaptcha) do not require this.


## Using Spam Protection with User Forms

