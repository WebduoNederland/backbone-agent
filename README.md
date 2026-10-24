# Backbone Agent

Laravel package that connects a Laravel project to [Backbone](https://github.com/WebduoNederland/backbone).

## Requirements

- PHP 8.4+
- Laravel 12+
- Laravel Horizon
- MySQL with `mysqldump` available (only required for the database dumps module)

## Installation

Install the package with Composer:

```bash
composer require webduonederland/backbone-agent
```

Add the URL of your Backbone instance and the API key of the project (found in Backbone) to your `.env`:

```dotenv
BACKBONE_AGENT_BASE_URL=https://backbone.example.com
BACKBONE_AGENT_API_KEY=your-project-api-key
```

Optionally publish the config file:

```bash
php artisan vendor:publish --provider="WebduoNederland\BackboneAgent\ServiceProvider" --tag=config
```

### Optional environment variables

| Variable | Default | Description |
| --- | --- | --- |
| `BACKBONE_AGENT_MYSQL_DUMP_BINARY_PATH` | `null` | Folder containing the `mysqldump` binary, when it is not in the `PATH` |
| `BACKBONE_AGENT_MYSQL_SOCKET_PATH` | `null` | MySQL socket to use for database dumps |
| `BACKBONE_AGENT_PANIC_MODE` | `false` | Shut down the server when a PHP file is found in the public folder |
| `BACKBONE_AGENT_SHUTDOWN_COMMAND` | `sudo -n shutdown -h now` | Command used to shut down the server in panic mode |

## PHP file scanner

Every 15 seconds the agent scans the `public` folder, including all sub folders, for PHP files (`.php`, `.phtml`, `.phar`, etc.). The default `public/index.php` is allowed. Extra allowed files and extensions can be configured in the published config file.

When a PHP file is found:

1. Backbone is notified. This call times out after 5 seconds, so an unreachable Backbone never delays the next step.
2. When panic mode is enabled, the server is shut down immediately to prevent further damage or data theft.

### Panic mode

When panic mode is enabled, the server shuts down as soon as a PHP file is found. It is disabled by default.

To enable it:

1. SSH into the server and run:

   ```bash
   sudo visudo -f /etc/sudoers.d/backbone-agent-shutdown
   ```

2. Add this line, replacing `siteuser` with the user the site runs as (the isolated site user, or `forge` when the site is not isolated):

   ```
   siteuser ALL=(root) NOPASSWD: /usr/sbin/shutdown -h now
   ```

3. Save and close the file.

4. Add the following to the `.env` of the project:

   ```dotenv
   BACKBONE_AGENT_PANIC_MODE=true
   BACKBONE_AGENT_SHUTDOWN_COMMAND="sudo -n /usr/sbin/shutdown -h now"
   ```
