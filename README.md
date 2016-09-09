#Lazy Quest — Slack-exclusive RPG game

##Setup

Clone this repo into your project folder. Point your webroot to the `public` folder as this is the only folder that needs to be accessed from the outside.

Run `composer install` at the root of this project to install all the required components.

Create the `config.php` file (using the `config.example.php`) and fill it out with your credentials.


###Database

Create a new database and user and place those credentials in the `config.php` file. The database is generated entirely from code, and can be executed on the command line:

- `cd /your/project/folder`
- `php bin/update.php -v 0.0.0` &rarr; Select the newest version of data (you can look in the `src/Agnate/RPG/Update` folder to see the newest).


##Running the Server

You can run the server temporarily from the commandline:

- `cd /your/project/folder`
- `php bin/server.php`

To have it run continuously, I would advise installing Supervisor or something simliar.

Supervisor: https://www.digitalocean.com/community/tutorials/how-to-install-and-manage-supervisor-on-ubuntu-and-debian-vps

- `vim /etc/supervisor/conf.d/lazyquest.conf`
- Paste the following:

```
[program:lazyquest_server]
command=/usr/bin/php bin/server.php
directory=/your/project/folder
autostart=false
autorestart=false
stderr_logfile=/your/project/folder/log/server.err.log
stdout_logfile=/your/project/folder/log/server.out.log
```

- Run `supervisorctl` to launch the control panel.
- Note: if you get errors trying to launch control panel, restart the service: `sudo service supervisor restart` (might be `supervisord`).
- In the supervisor control panel, type `status` to see the list of processes.
- Type `start lazyquest_server` to start the server.