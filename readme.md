# Watching under PHP Hood

This is a quick tool to install in your deployment of PHP, to get an idea of what's
happening.

## Security

*This tool exposes sensitive data, please take precautions like restricting access to it*

## Prepare Apache

Most of web servers follow the same logic. Please adapt to your use case.

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

## Configuration

By default, PHP hood will try to load a file `config.php` in the parent directory to load
its configuration. If it fails, it will look for `config.php-dist` and copy it into
`config.php`. This file is then editable.

If you mount a readonly filesystem, please copy manually the file and adapt it to your
need.

If those filenames and locations don't suit your needs, you can provide them through
environment variables.

| Name | Default | Description |
| --- | --- | --- |
| `HOOD_DIST_CONFIG` | `../config.php-dist` | the distribution configuration file path |
| `HOOD_CONFIG` | `../config.php` | configuration's file path |
| `HOOD_TEMPLATE_PATH` | `../templates` | where we find the templates |

Paths are relative to where `index.php` is ran from (originally
[`public_html`](./public_html)).

Beware to allow them to trickle down your execution environment
([`variables_order`](https://www.php.net/manual/en/ini.core.php#ini.variables-order)
directive containing `E`; deactivate `clear_env` in PHP FPM, etc).
