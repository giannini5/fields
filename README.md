# fields
Grasslands in the midwest that need trimming after a thunderstorm.

# Macbook Pro Development Environment
- See [PHP on MacBook Pro M1](https://medium.com/@ahmedazier/the-ultimate-guide-to-installing-php-on-macbook-pro-m1-21ff9173eb3d)
- See [Apache in macOS M1](https://dev.to/hte305/how-to-install-apache-in-macos-m1-montery-12xx-2k51)
- See [Installing MariaDB Server on macOS](https://mariadb.com/kb/en/installing-mariadb-on-macos-using-homebrew/)
- See [How to Create a New User and Grant Permissions in MySQL](https://www.digitalocean.com/community/tutorials/how-to-create-a-new-user-and-grant-permissions-in-mysql)
- See [Composer Getting Started](https://getcomposer.org/doc/00-intro.md) and [Composer Download](https://getcomposer.org/doc/00-intro.md)
- See [PHPMailer Guide](https://mailtrap.io/blog/phpmailer/)
- See [Xdebug](https://dev.to/scriptmint/installing-xdebug-3-on-macos-and-debug-in-vs-code-3l5h)
- Install VSCode extensions to support PHP (PHP, PHP Profiler, )

# Additional PHP setup
The php.ini and php-fpm.ini file can be found in:
    /usr/local/etc/php/8.2/

php@8.2 is keg-only, which means it was not symlinked into /usr/local,
because this is an alternate version of another formula.

If you need to have php@8.2 first in your PATH, run:
  echo 'export PATH="/usr/local/opt/php@8.2/bin:$PATH"' >> ~/.zprofile
  echo 'export PATH="/usr/local/opt/php@8.2/sbin:$PATH"' >> ~/.zprofile

For compilers to find php@8.2 you may need to set in your ~/.zprofile:
  export LDFLAGS="-L/usr/local/opt/php@8.2/lib"
  export CPPFLAGS="-I/usr/local/opt/php@8.2/include"

To start php@8.2 now and restart at login:
  brew services start php@8.2

Add XDebug to php.ini file (be sure to verify location of the xdebug.so file):
```
[XDebug]
zend_extension="/usr/local/Cellar/php@8.2/8.2.28_1/pecl/20220829/xdebug.so"
xdebug.mode=debug
xdebug.start_with_request=yes
```

# To start/stop apache
```
# brew services start httpd
# brew services stop httpd
# brew services restart httpd
sudo apachectl <start | stop | restart>
```

# Configure PHP w/ Apache
To enable PHP in Apache add the following to httpd.conf (found here: /usr/local/etc/httpd) and restart Apache:
    LoadModule php_module /usr/local/opt/php@8.2/lib/httpd/modules/libphp.so

Uncomment the following line:
    #LoadModule rewrite_module lib/httpd/modules/mod_rewrite.so

Uncomment the following line:
    #Include /private/etc/apache2/extra/httpd-vhosts.conf

Add the following lines
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

Add Listen on 8081 and comment out all other Listen
    Listen 8081

Finally, check DirectoryIndex includes index.php
    DirectoryIndex index.php index.html

Find the Directory setting and change to:
```
<Directory /> 
    AllowOverride none
    Require all granted
</Directory>
```

Restart apache2 (httpd)
```
# brew services restart httpd
sudo apachectl restart
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

# For Development on MacBook Pro: Configure /etc/apache2/extra/httpd-vhosts.conf by adding the following:
Update the ServerAdmin, DocumentRoot, ErrorLog and CustomLog as necessary
```
<VirtualHost *:8081>
  ServerAdmin dave.giannini@gmail.com
  DocumentRoot /Users/davegiannini/ayso/fields/trunk/src/
  ServerName localhost
  ErrorLog /var/log/apache2/fields_error.log
  CustomLog /var/log/apache2/fields_access.log common
  <Directory /Users/dave.giannini/ayso/fields/trunk/src/>
    Allow from all
    AllowOverride All
    Require all granted
    Options Indexes FollowSymLinks Includes ExecCGI
    # Options -Indexes +FollowSymLinks
  </Directory>
</VirtualHost>
```

Restart apache2 (httpd)
```
# brew services restart httpd
sudo apachectl restart
```

See infrastructure/aysodata/web_server/etc/region122.aysodata.com.conf for prduction example of the above 
