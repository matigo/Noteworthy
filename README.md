<h1>Noteworthy</h1>
<h4><i>A PHP-Based Blogging Tool <span style="font-size:50%">for use with</span> Evernote</i></h4>

The Following is a list of notes regarding the use and configuration of Noteworthy.

<h2>General Requirements</h2>
You will need:
<ul>
	<li>a web server running PHP 5.0 or higher</li>
	<li>MySQL 5.0 or higher</li>
</ul>

<h2>Optional Components</h2>
There are some optional pieces to the puzzle that might make things a little better. These things include:
<ul>
	<li>something to drink</li>
	<li>good music</li>
	<li>a faithful dog</li>
</ul>

<h2>Installation</h2>
<p>Installation is pretty straight-forward, as Noteworthy will do all of the work. You don't need to mess around with text files or anything like that. It's all controlled through the web administration panels. And here's the Order of Operations:</p>
<ol>
<li>Dump all the files into a directory</li>
<li>Make sure all directories (including the web root) are 755</li>
<li>Visit the website just to make sure it's working</li>
<li>Pet your dog</li>
<li>Visit {Website}/install/</li>
<li>Enter your email address (A real one is not important. This isn't for me. It's for you.)</li>
<li>Configure Noteworthy using the "Sites" and "Settings" tabs</li>
<li>Let Noteworthy download the Evernote notebooks you've selected</li>
</ol>

<h2>Disclaimers</h2>
<p>Do <b>NOT</b> share your Evernote API keys with anybody. You have been warned.</p>
<p>If you are using a shared hosting platform, you will probably want to create three other directories before visiting Noteworthy for the first time:</p>
<ul>
<li>/logs</li>
<li>/tokens</li>
<li>/users</li>
</ul>
<p>I've seen on two different shared hosting servers problems when Noteworthy creates the directories that it needs for your data. The issue seems to be with write permission to the new directories after they've been created. If you have shell access to your account, then it's not a problem. Make all directories (including the web root) 755 [chmod -R 755], and you'll be good.<p>
<p>And finally ...</p>
<p>Don't lose your admin URL. Unlike most other blogging platforms, Noteworthy does not have a static administration URL. It's unique for every person. If you lose your login, you will need to do some ugly workarounds in the filesystem in order to regain permission to the admin panel. This will be addressed in later releases.</p>

<h2>Considerations</h2>
The use of a database is a requirement at the moment. In future releases, the MySQL component will be optional.

<h2>Quick Notes</h2>
<p>Noteworthy will create any missing directories that it needs, so please ensure the files and directories are owned by Apache (or whichever user is the web server). No directory should be 777. 755 is plenty sufficient for this project.</p>
<p>There are a few issues with the JavaScript in the Admin Panel. Be sure to click the Save buttons again if an error is returned the first time. This should be resolved in future updates.</p>

<h2>Questions?</h2>
<p>Have questions about Noteworthy? Feel free to contact me on here, <a href="http://twitter.com/matigo">Twitter</a>, or <a href="http://jasonirwin.ca/contact/">my website</a>. I respond to all email.</p>