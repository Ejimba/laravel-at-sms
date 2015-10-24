# Laravel Africas Talking SMS
Integrate SMS messaging in your Laravel Application using Africas Talking Gateway

## Installation

Add the following to the "require" section of your `composer.json` file.

```json
"ejimba/laravel-at-sms": "0.1.x"
```
Run the `composer update` command.

Then, in your `config/app.php` add this line to your 'providers' array.

```php
'Ejimba\LaravelAtSms\LaravelAtSmsServiceProvider',
```

After installing, you can publish the package configuration file into your application by running the following command:

```php
php artisan config:publish ejimba/laravel-at-sms
```

**In the config file ensure you fill in your API KEY and USERNAME.** If you don't have the username and api key, register for a free account at [https://www.africastalking.com/account/register](https://www.africastalking.com/account/register) and get the details in the dashboard.

Then, migrate the package's table by using

```php
php artisan migrate --package=ejimba/laravel-at-sms
```

## Usage

To send an SMS use:

```php
LaravelAtSms::sendMessage($phoneNumber, $message);
```

## Authors

1. Ejimba Eric (www.ejimbaeric.com)

## To Do
- Add feedback/reponces from the api.
- Add error catching.
- Add receiving sms.
- Add delivery receipts.

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

## Changelog
### Version 0.1.0
- Add Sms sending.

## Credits
1. [Laravel](laravel.com)
2. [Africas Talking](https://www.africastalking.com/)

## License

Licensed under [The MIT License (MIT)](LICENSE).