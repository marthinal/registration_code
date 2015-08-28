# Registration Code for Drupal 8

The Registration Code is a rest resource that allows anonymous users to request for a code before create
a new account. The code will be received by email and you need to insert this code when creating the new
user account using this resource https://www.drupal.org/node/2291055.

This is useful when you want to prevent fake registrations from a headless or native app  and you don't
want to use the link that redirects to the website.

Here is an example for the code request using json as format:

```json
{
  "email": [
    {
      "value": "marthinal@examplesuperpoweredbydrupal.com"
    }
  ]
}
```

Flood control exists and you can check the settings in the settings yaml.

And here is the example using hal_json as format for creating a new account:

```json
{
  "langcode": [
    {
      "value": "en"
    }
  ],
  "name": [
    {
      "value": "marthinal"
    }
  ],
  "mail": [
    {
      "value": "marthinal@examplesuperpoweredbydrupal.com"
    }
  ],
  "timezone": [
    {
      "value": "UTC"
    }
  ],
  "pass": [
    {
      "value": "superSecretPass"
    }
  ],
  "registration_code": [
    {
      "value": "10168"
    }
  ]
}
```

I think we can add this functionality to the registration form but probably for a website you can use
directly the link that Drupal generates by default.

For the moment you need to apply this patch https://www.drupal.org/node/2419825.

#### PHPUnit

    cd /path/to/drupal-8/core
    ./vendor/bin/phpunit ../modules/registration_code

