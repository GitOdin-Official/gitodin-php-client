```
  //      ____                     __     __     __      _                _        
  //     / __ \  __  __   _____   / /_   / /_   / /_    (_)   _____      (_)  ____ 
  //    / /_/ / / / / /  / ___/  / __ \ / __/  / __ \  / /   / ___/     / /  / __ \
  //   / ____/ / /_/ /  (__  )  / / / // /_   / / / / / /   (__  )  _  / /  / /_/ /
  //  /_/      \__,_/  /____/  /_/ /_/ \__/  /_/ /_/ /_/   /____/  (_)/_/   \____/ 
```

# Pushthis PHP Package
This is a package made for PHP, to interact with the Pushthis RESTful API Network Access Point to send payloads through the network to your client side in real-time!

---

# Getting Started
> Installing using Composer
>```sh
>composer require pushthis/pushthis
>```

### How to use:
> Load the Package using Composer
>```php
>require_once("autoload.php");
>use Pushthis\Pushthis;
>```

>Load the Package **not** using Composer (Downloaded from Github)
>```php
>require_once("pushthis-php-http/src/pushthis.php");
>use Pushthis\Pushthis;
>```

### Starting an Instance
> Define your Key, Secret and Access Point
>```php
>$pushthis = new Pushthis('key', 'secret', 'Access Point');
>```

### Sending Single Payload Requests
> Below is the Code that will allow you send a Single Payload to Pushthis.io
>```php
>$pushthis = new Pushthis('key', 'secret', 'Access Point');
>$pushthis->setChannel('channel');
>$pushthis->setEvent('event');
>$pushthis->send(array(
>    'username' => 'john_doe',
>    'message'  => 'Hello Everyone!'
>));
>```

### Sending Multi-Payload Requests
> Below you can send multiple payloads at once using the Queue
>```php
>$pushthis = new Pushthis('key', 'secret', 'Access Point');
>$pushthis->add(array(
>	'channel' => 'the_chat',
>	'event' => 'new_message',
>	'data' => array(
>		'username' => 'john_doe',
>		'file_name'  => 'Hello Everyone!'
>	)
>));
>$pushthis->add(array(
>	'channel' => 'system',
>	'event' => 'alert',
>	'data' => array(
>		'username' => 'john_doe',
>		'message'  => 'THE INTERNET IS OFFLINE!'
>	)
>));
>$pushthis->send();
>```

### Authorizing Access to a Channel 
> Authorizing Payload Request
>```php
>$pushthis = new Pushthis('key', 'secret', 'Access Point');
>$pushthis->authorize(true, "channel", "socketId");
>```

### Tracing Errors
> Add this to your code after you have attempted to send something to see the errors
>```php
>print_r($pushthis->errors);
>```

---
# Indepth Documentation
 Documentation for Pushthis.io can be found at https://pushthis.io/documentation

# Contributors & Honorable Mentions
- Devitgg @ https://github.com/devitgg
- Nhalstead @ https://github.com/nhalstead
