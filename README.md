#Noteworthy
####A PHP-Based Blogging Tool for use with Evernote

The Following is a list of notes regarding the use and configuration of Noteworthy.
For more documentation, be sure to [check out the Wiki](https://github.com/matigo/Noteworthy/wiki).

##General Requirements
You will need:
* a web server running PHP 5.0 or higher
* MySQL 5.0 or higher

##Optional Components
There are some optional pieces to the puzzle that might make things a little better. These things include:
* something to drink
* good music
* a faithful dog

##Installation
Installation is pretty straight-forward, as Noteworthy will do all of the work. You don't need to mess around with text files or anything like that. It's all controlled through the web administration panels. And here's the Order of Operations:

* Dump all the files into a directory
* Make sure all directories (including the web root) are 755
* Visit the website just to make sure it's working
* Pet your dog
* Visit {Website}/install/
* Enter your email address (A real one is not important. This isn't for me. It's for you.)
* Configure Noteworthy using the "Sites" and "Settings" tabs
* Let Noteworthy download the Evernote notebooks you've selected

Noteworthy can be installed in either a root domain (website.com), subdomain (abc.website.com), or a subfolder (website.com/blog)

##Disclaimers
Do **NOT** share your Evernote API keys with anybody. Sharing your API Key grants that person full access to your Evernote account which, if you use Evernote like I do, could be a terrifying prospect. This is why I'm sharing Noteworthy with you rather than providing a hosted solution that you could use.
And finally ...
Don't lose your admin URL. Unlike most other blogging platforms, Noteworthy does not have a static administration URL. It's unique for every person. If you lose your login, you will need to do some ugly workarounds in the filesystem in order to regain permission to the admin panel. This will be addressed in later releases.

##Considerations
The use of a MySQL database is a requirement at the moment. In future releases, the MySQL component will be optional.

##Quick Notes
Noteworthy will create any missing directories that it needs, so please ensure the files and directories are owned by Apache (or whichever user is the web server). No directory should be 777. 755 is plenty sufficient for this project.
The API Keys that Evernote provides are only good for one year, and there is no way (that I know of) to check their expiration date aside from logging in to Evernote and checking. As a result, if Noteworthy stops working after one year, you'll need to get a new API key and enter it in the administration screens. None of your data will be lost when this happens.
There are a few issues with the JavaScript in the Admin Panel. Be sure to click the Save buttons again if an error is returned the first time. This should be resolved in future updates.

##Questions?
Have questions about Noteworthy? Feel free to contact me on here, [Twitter](http://twitter.com/matigo), or [my website](http://jasonirwin.ca/contact/). I respond to all email.