<p align="center"><img src='https://cdn.discordapp.com/attachments/479687985273503757/479688071898726420/Gitodin.jpg'/></p>

This is a package made for PHP, to interact with the GitOdin RESTful API Network Access Point to send payloads through the network to your client side in real-time!

---

# Getting Started
> Installing using Composer
>```sh
> composer require gitodin/php-client
>```

### How to use:
> Load the Package using Composer
>```php
>require_once("../vendor/autoload.php"); // Composer Method, Loading by PSR4
>use GitOdin\GitOdin;
>```

>Load the Package **not** using Composer (Downloaded from GitHub)
>```php
>require_once("../src/GitOdin_load.php"); // Manual Load, no PSR4 Autoload
>use GitOdin\GitOdin;
>```

> This will send an Payload to a Specific channel on the specified event.
>```php
>$GitOdin = GitOdin::summon('*', 'Server', 'Auth Gateway');
>
>$express_response = $GitOdin->send(new Event(
>	"channelName",
>	"eventName",
>	"someData"
>));
>echo $express_response;
>```
> For more Examples check out the /examples folder.

---
# Indepth Documentation
 Documentation for GitOdin.com can be found at https://GitOdin.com/documentation

# Contributors
- yordaDev @ https://github.com/yordadev
- nhalstead @ https://github.com/nhalstead
