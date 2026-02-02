# Per la visualizzazione da remoto: entrare nella cartella /php -> http://tecweb.studenti.math.unipd.it/nlattanz/php/index.php

# Per la visualizzazione in locale usare queste credenziali nel file dbconnection.php
private const HOST_DB = "mysql";
private const DATABASE_NAME = "user";
private const USERNAME = "user";
private const PASSWORD = "user1234";


# Per la visualizzazione in locale usare questo file .htaccess
ErrorDocument 403 /403.php

ErrorDocument 404 /404.php

ErrorDocument 500 /500.php
ErrorDocument 501 /500.php
ErrorDocument 502 /500.php
ErrorDocument 503 /500.php
ErrorDocument 504 /500.php
ErrorDocument 505 /500.php
ErrorDocument 507 /500.php