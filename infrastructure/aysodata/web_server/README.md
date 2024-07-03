# Deploy and configure EC2 instances for aysodata
## Prerequsites
1. See bootstrap (should already be in place)
2. See network (should already be in place)
3. Configure TF_VAR_AWS_ACCESS_KEY_ID and TF_VAR_AWS_SECRET_ACCESS_KEY environment
variables (or you will be prompted for their values when you run `terraform apply`)

## Files
| File Name        | Description |
| ------------- |-------------|
| main.tf           | remote state, region and availability zone configuration |
| data.ami.tf       | EC2 ami definition |
| remote_state.tf   | external resource definitions |
| variables.tf      | variable definitions and assignment based on workspace |
| workspace.tf      | variable overrides based on workspace |
| instance.tf       | EC2 instance creation and configuration |
| output.tf         | remote state output displayed after run |

## Quick Start
1. For production environments
```bash
$ terraform init
$ terraform workspace select prod
$ terraform apply
```

## Connecting
To ssh into an existing web_server instance, get the public_ip address from the `terraform output` command

You must be on a white listd IP Address to connect (see network/variables.tf allowed_ips)

Run `ssh -i ~/.ssh/aysodata_rsa.pem admin@<PUBLIC_IP>`
Use `sudo -i` to access the root user

## Complete configuration
ssh and run the following

1. Install MariaDB
    See [MariaDb for Debian 10 - works for 12 too](https://www.digitalocean.com/community/tutorials/how-to-install-mariadb-on-debian-10)
2. Install Apache2
    See [Apache2 for Debian 12](https://reintech.io/blog/installing-apache-on-debian-12-step-by-step-guide)
3. Install PHP
    See [PHP 8.2 for Debian 12](https://tecadmin.net/how-to-install-php-on-debian-12/)
4. Install M4
    See [MR for Debian 12](https://debian.pkgs.org/12/debian-main-amd64/m4_1.4.19-3_amd64.deb.html)
3. Configure apache
```bash
$ cd /etc/apache2/modes-enabled
$ sudo cp ../mods-available/rewrite.load .
$ # enable Listen on port 8080 in ports.conf
$ # copy in ./etc/region122.aysodata.com.conf to /etc/apache2/sites-enabled
```
4. Install software
```bash
$ sudo apt install git
$ cd /usr/local/src
$ sudo chmod 777 .
$ git clone https://github.com/giannini5/fields.git
$ git config --global --add safe.directory /usr/local/src/fields
```
5. Configure software
```bash
$ cd /usr/local/src/fields/trunk/src/lib
$ # create defines.m4 from defines_template.m4
$ cd /usr/local/src/fields/trunk/src/db/helpers
$ # create defines.m4 from defines_template.m4
$ cd /usr/local/src/fields/trunk/src/scripts
$ sudo ./upgrade.sh -u
4. Verify
