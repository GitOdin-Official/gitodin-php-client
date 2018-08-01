<p align="center"><img src='https://media.discordapp.net/attachments/450813119921389568/451492395561910292/logo-final-with-text.png'/></p>

# GitOdin PHP Package
This is a package made for PHP, to interact with the GitOdin RESTful API Network Access Point to send payloads through the network to your client side in real-time!

---

# Getting Started
> Installing using Composer
>```sh
>composer require GitOdin/GitOdin
>```

### How to use:
> Load the Package using Composer
>```php
>require_once("autoload.php");
>use GitOdin\GitOdin;
>```

>Load the Package **not** using Composer (Downloaded from Github)
>```php
>require_once("GitOdin-php-http/src/GitOdin.php");
>use GitOdin\GitOdin;
>```

### Starting an Instance
> Define your Key, Secret and Access Point
>```php
>$git = new GitOdin('key', 'secret', 'Access Point');
>```

### Sending Single Payload Requests
> Below is the Code that will allow you send a Single Payload to GitOdin.com
>```php
>$git = new GitOdin('key', 'secret', 'Access Point');
>$git->setChannel('channel');
>$git->setEvent('event');
>$git->send(array(
>    'username' => 'john_doe',
>    'message'  => 'Hello Everyone!'
>));
>```

### Sending Multi-Payload Requests
> Below you can send multiple payloads at once using the Queue
>```php
>$git = new GitOdin('key', 'secret', 'Access Point');
>$git->add(array(
>	'channel' => 'the_chat',
>	'event' => 'new_message',
>	'data' => array(
>		'username' => 'john_doe',
>		'file_name'  => 'Hello Everyone!'
>	)
>));
>$git->add(array(
>	'channel' => 'system',
>	'event' => 'alert',
>	'data' => array(
>		'username' => 'john_doe',
>		'message'  => 'THE INTERNET IS OFFLINE!'
>	)
>));
>$git->send();
>```

### Authorizing Access to a Channel 
> Authorizing Payload Request
>```php
>$git = new GitOdin('key', 'secret', 'Access Point');
>$git->authorize(boolean, "channel", "socketId");
>```

### Tracing Errors
> Add this to your code after you have attempted to send something to see the errors
>```php
>print_r($git->errors);
>```

---
# Indepth Documentation
 Documentation for GitOdin.com can be found at https://GitOdin.com/documentation

# Contributors & Honorable Mentions
- yordaDev @ https://github.com/yordadev
- Nhalstead @ https://github.com/nhalstead
