<img src="https://tonisfeltavern.com/vendor/forestJPG%20banner.png">
<h1>Tonisfel Tavern</h1>
<p>New content manage system</p>

This project was developed by me using only native PHP, HTML, CSS, but with jQuery framework for JavaScript. It's huge 
experience that has though me a lot of things. This CMS is my first time that I completed development the big complicated project
which you can use. I have fixed bugs very quickly but its are little count. You can create issue thread in CMS repository
then I can talk with you about it. I'm in touch for 24/7. 

I want tell you that if bug is seriously then after fixing it I'll pay you some money... let us say... 100$. Deal?

<h2>How to install?</h2>
I completed development installer but it need improve so it's only one way to install.
First, I should tell about compatibility. You need these:
<div class="requirements" style="background-color: #9f9f9f; color: black; padding: 10px; border-radius: 8px; margin-bottom: 10px">
<li>Apache2</li>
<li>MySQL >= 5.7</li>
<li>PHP >= 7.4</li>
</div>
I'll make compatibility with PostgreSQL but it's a long process so I think you just to wait.
After clone repository you need create 2 files in engine/config, one need name "<strong>config.sfc</strong>", second is "<strong>dbconf.sfc</strong>".
The first not need to edit and the second need. Paste in it this line:
<blockquote>{
    "dbName":"cms",
    "dbLogin":"tonisfel",
    "dbPass":"somebeermeneed",
    "dbHost":"localhost",
    "dbPort":"3306",
    "dbDriver":"1"}
</blockquote>
<strong>DO NOT CHANGE dbDriver parameter. It's lever that change engine of your database, 1 - MySQL, 2 - PostgreSQL.</strong>
<br>
Paste your data of database in this JSON object, save and forget about this file. Because of settings in .htaccess
no one can get data from config directory.
<h2>Skills</h2>
Tonisfel Tavern CMS was created as personal blog. But when I added some features I throug about ordinary user, so 
I began deploy design constructor, rules editor and plugin system. These skills has been implemented to CRM:
<ul>
    <li>Rules editor;</li>
    <li>Navigation bar editor;</li>
    <li>Banner editor;</li>
    <li>Custom pages;</li>
    <li>User system with groups;</li>
    <li>Group permission system;</li>
    <li>Censor;</li>
    <li>Forum system;</li>
    <li>Topic category manager;</li>
    <li>Mensions system as in Twitter;</li>
    <li>Mensions system for any user;</li>
    <li>Personal message system;</li>
    <li>Friend system;</li>
    <li>Template system;</li>
    <li>Topics and comments have evaluation system (likes and dislikes);</li>
    <li>System has template code editor with syntax fleshlight for professionals;</li>
    <li>Unique report system, this system has no analog in any other CRM;</li>
    <li>Some base CRM settings can be showed in pages of selected template;</li>
    <li>Banhammer system;</li>
    <li>Plugin system;</li>
    <li>Logging manager;</li>
    <li>Postman for all users;</li>
    <li>Etc.</li>
</ul>

<h2>License</h2>
The CMS is open-source project and licensed by GNU General Public License (GPLv3).