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

To send an SMS to a single recipient:

```php
$phoneNumber = '+254712345678';
$message = 'This is a test message to a single recipient';
LaravelAtSms::sendMessage($phoneNumber, $message);
```

To send an SMS to multiple SMS recipients:

```php
$phoneNumbers = '+254712345678, +123456789, +256712345678';
// or
$phoneNumbers = array('+254712345678', '+123456789', '+256712345678');
$message = 'This is a test message to multiple recipients';
LaravelAtSms::sendMessage($phoneNumbers, $message);
```

To call a method after a successful message sent/message receive, add the method to your config file at `app/config/packages/ejimba/laravel-at-sms/config.php`

The format is `ClassName@method` This can be a controller method or a custom class method.

```php
<?

// app/controllers/UsersController.php

class UsersController extends BaseController {

	// other stuff

	// call this method after a message is received into the system
	public function update_mobile_no($incoming_sms)
	{
		// do stuff

		$mobile_no = $incoming_sms->source;

		// do stuff
	}

	// call this method after a message is sent from the system
	public function update_two_factor_authentication_token($outgoing_sms)
	{
		// do stuff

		$token = $outgoing_sms->text;

		// do stuff
	}

}

?>
```

The above methods will be added as

```php
<?

return array(

	// other config

	'incoming_sms' => array(

        'model' => 'Ejimba\LaravelAtSms\Models\IncomingSms',

        // this is the callback method we created above
        'callback' => 'UsersController@update_mobile_no',

    ),

    'outgoing_sms' => array(

        'model' => 'Ejimba\LaravelAtSms\Models\OutgoingSms',

        // this is the callback method we created above
        'callback' => 'UsersController@update_two_factor_authentication_token',

    ),
);

}

?>
```

Retrieve the incoming sms and outgoing sms by calling the model classes directly:

```php
<?
// app/controllers/SmsController.php

// models for fetching sms
use Ejimba\LaravelAtSms\Models\IncomingSms;
use Ejimba\LaravelAtSms\Models\OutgoingSms;

class SmsController extends BaseController {

	// other stuff

	public function incoming_sms_index()
	{
		// fetch messages
		$incoming_sms = IncomingSms::all();
		// display to your view
		return View::make('sms.incoming', compact('incoming_sms'));
	}

	public function outgoing_sms_index()
	{
		// fetch messages
		$outgoing_sms = OutgoingSms::all();
		// display to your view
		return View::make('sms.outgoing', compact('outgoing_sms'));
	}

}

?>
```

or

Create models of your own that extend the package's models

```php
<?
// app/models/Incoming.php

use Ejimba\LaravelAtSms\Models\IncomingSms;

class Incoming extends IncomingSms {

	// other stuff

	// override methods here too!
}

?>
```

```php
<?
// app/models/Outgoing.php

use Ejimba\LaravelAtSms\Models\OutgoingSms;

class Outgoing extends OutgoingSms {

	// other stuff

	// override methods here too!
}

?>
```

You can also override the models used for the incoming and outgoing sms tables from config file

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

## Credits
1. [Laravel](laravel.com)
2. [Africas Talking](https://www.africastalking.com/)

## License

Licensed under [The MIT License (MIT)](LICENSE).