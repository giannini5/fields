# fields
Grasslands in the midwest that need trimming after a thunderstorm.

# Macbook Pro Development Environment
- See [PHP on MacBook Pro M1](https://medium.com/@ahmedazier/the-ultimate-guide-to-installing-php-on-macbook-pro-m1-21ff9173eb3d)
- See [Apache in macOS M1](https://dev.to/hte305/how-to-install-apache-in-macos-m1-montery-12xx-2k51)
- See [Installing MariaDB Server on macOS](https://mariadb.com/kb/en/installing-mariadb-on-macos-using-homebrew/)
- See [How to Create a New User and Grant Permissions in MySQL](https://www.digitalocean.com/community/tutorials/how-to-create-a-new-user-and-grant-permissions-in-mysql)
- See [Composer Getting Started](https://getcomposer.org/doc/00-intro.md) and [Composer Download](https://getcomposer.org/doc/00-intro.md)
- See [PHPMailer Guide](https://mailtrap.io/blog/phpmailer/)

# To start/stop apache
```
brew services start httpd
brew services stop httpd
```

# Configure PHP w/ Apache
To enable PHP in Apache add the following to /opt/homebrew/etc/httpd/httpd.conf and restart Apache:
```
    LoadModule php_module /opt/homebrew/opt/php@8.2/lib/httpd/modules/libphp.so
    LoadModule rewrite_module lib/httpd/modules/mod_rewrite.so

    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
```

Verify vhosts are enabled and also edit the vhosts conf file to meet your needs for port 8080, 8081, etc.
```
Include /opt/homebrew/etc/httpd/extra/httpd-vhosts.conf
```

Finally, check DirectoryIndex includes index.php
```
    DirectoryIndex index.php index.html
```

The php.ini and php-fpm.ini file can be found in:
```
    /opt/homebrew/etc/php/8.2/
```

If you need to have php@8.2 first in your PATH, run:
```
  echo 'export PATH="/opt/homebrew/opt/php@8.2/bin:$PATH"' >> ~/.zshrc
  echo 'export PATH="/opt/homebrew/opt/php@8.2/sbin:$PATH"' >> ~/.zshrc
```

For compilers to find php@8.2 you may need to set:
```
  export LDFLAGS="-L/opt/homebrew/opt/php@8.2/lib"
  export CPPFLAGS="-I/opt/homebrew/opt/php@8.2/include"
```

To start php@8.2 now and restart at login:
```
  brew services start php@8.2
```

# Configure databse
For first time configuration:
1. Create config.php file in `<path to repo>/fields/trunk/src/lib`
2. Run database install script
```
cd <path to repo>/fields/trunk/src/db
./install.sh -c
```
3. Run `mysql` command and add the league:
```
use fields;
insert into league (name) values ('<name of league>');
use schedule;
insert into league (name) values ('<name of league>');
```
4. Run `mysql` command and add the administrators:
```
use schedule;
insert into scheduleCoordinator (leagueId, email, name, password) values (1, 'dave@giannini5.com', 'David Giannini', 'dag');
use fields;
insert into practiceFieldCoordinator (leagueId, email, name, password) values (1, 'dave@giannini5.com', 'David Giannini', 'dag');
```


# Upgrade database
```
cd <path to repo>/fields/trunk/src/db
./install.sh -u
```

# TODO: Add example http conf files for development and production
