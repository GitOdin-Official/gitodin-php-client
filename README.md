<p align="center"><img src='https://media.discordapp.net/attachments/450813119921389568/451492395561910292/logo-final-with-text.png'/></p>

# GitOdin PHP Package
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
>require_once("autoload.php");
>use GitOdin\GitOdin;
>```

>Load the Package **not** using Composer (Downloaded from Github)
>```php
>require_once("GitOdin-php-http/src/GitOdin.php");
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
