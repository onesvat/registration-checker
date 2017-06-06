# registration-checker

A Telegram Bot that checks your Boğaziçi grades and alerts when they are changed.

## Usage

### /auth [username] [password]

Auth route saves your username and password,

Example: /auth 2010400000 YourFancyPassword

### /grades

Gets the current grades of your id

### /delete

Delete user data permanently


## Setup

After installing the dependencies via "`composer install`", you must set access URL to webhok-set.php to .env file then you must run it. It enables to telegram-bot-core find your webserver.

For any details to setup, please contact me "onurnesvat@gmail.com"