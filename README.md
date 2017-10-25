Astina Form Bundle
================

Provides form builder features for AstinaWebcmsBundle. It allows CMS users to add and configure forms to pages.

**Current functionality:**
- Configure text, textarea, email, date and choice fields
- Specify email addresses for notification mail after successful submit
- Saving form submission in database

**Todo:**
- Export of form submissions (with date range filter)

### Configuration

Add the bundle to your composer.json and run `composer update astina/form-bundle`:

```json
"require": {
    "astina/form-bundle": "dev-master"
},
```

Add the following to your AppKernel.php file

```php
class AppKernel extends Kernel {
    public function registerBundles() {
        $bundles = array(
            ...
            new Astina\Bundle\FormBundle\AstinaFormBundle(),
            ...
        );
    }
}
```

Load the webcms.yml config file from the bundle:

```yml
# config.yml
imports:
    - { resource: @AstinaFormBundle/Resources/config/webcms.yml }
```

### Usage

The bundle provides a new content type "webcms_form_config". You can use/configure it like any other content type.

![astina-forms](https://cloud.githubusercontent.com/assets/886082/4114710/e91acae8-326c-11e4-8e7d-272e36929bb5.png)


### Import

You can import form configs like any other page contents:

```yml
# ...
webcms_form_config:
    name: Form
    title: My Form
    fields:
        - { name: Name, type: text, mandatory: true, defaultValue: Hans }
        - { name: Email, type: email, mandatory: true }
        - { name: Comment, type: textarea }
```

### View Form Submissions

Add this to your routing config:

```yml
# routing.yml
astina_forms:
    resource: "@AstinaFormBundle/Resources/config/routing.yml"
    prefix:   /admin/forms
```

### Migration

In order to migrate from version 1.1.x to 1.2.0, the database needs to be updated:

```sql
ALTER TABLE FormConfigField ADD help LONGTEXT DEFAULT NULL;
ALTER TABLE FormConfigField CHANGE options options1 LONGTEXT DEFAULT NULL;
ALTER TABLE FormConfigField ADD options2 LONGTEXT DEFAULT NULL;
```

In order to migrate from version 1.2.0 to 1.3.0, the database needs to be updated:

```sql
ALTER TABLE FormSubmission ADD counter INT DEFAULT NULL;
```
