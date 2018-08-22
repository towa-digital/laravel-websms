# laravel-websms

Please note that WhatsApp messages are currently not supported.

## Usage

### Step 1

Install the package

```
composer require pro-sales/laravel-websms
```

### Step 2

Publish the config file

```
php artisan vendor:publish --provider="ProSales\WebSms\WebSmsServiceProvider"
```

**Don't forget to update the access token in the config!**

### Step 3

Send your first message:

``` php
WebSms::smsMessage()
        ->to("+41791234567")
        ->text("Hallo von Laravel")
        ->send();
```