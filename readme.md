# Watching under PHP Hood

This is a quick tool to install in your deployment of PHP, to get an idea of what's
happening.

# Security

*This tool exposes sensitive data, please take precautions like restricting access to it*

## Prepare apache

First secure the location you will expose. Here an Apache configuration for your
conveniance. Save this in the Apache configuration file if possible, or default down to
a .htaccss. Be sure the `.htpasswd` is unreachable through the web.

```
Alias /var/www/php-hood/public_html /.hood
<LocationMatch "^/\.hood">
	AuthType basic
	AuthName "Under the hood"
	AuthBasicProvider file
	AuthUserFile "/var/www/.htpasswd"

	Require valid-user
</LocationMatch>
```

Generate the `htpasswd`;

```
htpasswd -B /var/www/.htpasswd red
```

## Install the good stuff

```
cd /var/www/
git clone git@github.com:LupusMichaelis/php-hood.git php-hood

# chmod and chown as required by your setup
```

You should be able to access through `https://example.org/.hood/index.php`.
