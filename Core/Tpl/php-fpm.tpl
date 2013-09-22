[<?php echo APP_NAME;?>]
listen = <?php echo FPM_SOCK_PATH;?><?php echo APP_NAME;?>.sock

;listen.backlog = -1
;listen.allowed_clients = 127.0.0.1
listen.owner = root
listen.group = root
listen.mode = 0666

user = nginx
group = nginx

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 35
pm.max_requests = 1000

;pm.status_path = /status
;ping.path = /ping
;ping.response = pong
 
;request_terminate_timeout = 0
request_slowlog_timeout = 2
slowlog = <?php echo LOG_PATH; ?>fpm-slow.log

;rlimit_files = 1024
;rlimit_core = 0
 
;chroot = 
chdir = <?php echo ROOT_PATH; ?>

;catch_workers_output = yes
security.limit_extensions = .php

;env[HOSTNAME] = $HOSTNAME
;env[PATH] = /usr/local/bin:/usr/bin:/bin
;env[TMP] = /tmp
;env[TMPDIR] = /tmp
;env[TEMP] = /tmp

;php_admin_value[sendmail_path] = /usr/sbin/sendmail -t -i -f www@my.domain.com
php_flag[display_errors] = <?php echo (APP_DEBUG?'on':'off'); ?>

php_admin_value[error_log] = <?php echo LOG_PATH; ?>fpm-error.log

php_admin_flag[log_errors] = <?php echo APP_DEBUG?'off':'on'; ?>

;php_admin_value[memory_limit] = 128M

php_value[session.save_handler] = memcached
php_value[session.save_path] = 127.0.0.1:11211

php_admin_value[hidef.ini_path] = <?php echo $ini_path; ?>

php_admin_value[hidef.data_path] = <?php echo $data_path; ?>

php_admin_value[session.cookie_lifetime] = 604800
php_admin_value[session.cookie_path] = /
;php_admin_value[session.cookie_domain] = 
;php_admin_value[session.cookie_httponly] = 

<?php echo $FPM_CONF; ?>
