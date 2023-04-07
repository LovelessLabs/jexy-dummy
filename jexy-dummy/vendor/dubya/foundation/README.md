# foundation
Anonymous classes to get stuff done without worrying about the namespace challenges of WordPress.

## What sort of nonsense is this?

WordPress has a well-known global namespace problem. People work around this in a variety of ways. This is one of those ways.

## The Goal: Unobtrusive inclusion in your plugin's vendor directory

Many WordPress plugin developers leverage the Composer autoloader, but do so without including anything else but their own classes. If you start including things like the AWS SDK for PHP, and another plugin has a Guzzle dependency that is at a different version, chaos quickly ensues.

To avoid the overhead of `function_exists` and `class_exists` checks, these classes are provided as anonymous classes returned by including the file you need.

Yeah, you can't really leverage autoloading with these directly. Tradeoffs, right? You can install them and update them with the convenience that Composer provides, while including them in your plugin without worrying about new namespace headaches.

## This breaks all the conventions!

Sort of, yeah. It's a collection of compromises that may prove beneficial to you, or may not.
