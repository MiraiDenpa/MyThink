server{
	root <?php echo ROOT_PATH;?>;
	index index.php;
	server_name <?php echo $GLOBALS['URL_MAP'][APP_NAME];?>;
	
	location ~ ^/[^\/]+\.(png|ico|gif|html|css|js|txt)$ {
		rewrite .* <?php echo PUBLIC_URL; ?>$uri;
		break;
	}
	
	location ~ ^/[^\/]+\.php$ {
		include fastcgi_params;
		fastcgi_pass  unix:/var/run/php-fpm/<?php echo APP_NAME;?>.sock;
	}
	
	location / {
		fastcgi_param  PATH_INFO          $uri;
		fastcgi_param  PATH_TRANSLATED    <?php echo ROOT_PATH;?>$uri;
		
		fastcgi_param  QUERY_STRING       $query_string;
		fastcgi_param  REQUEST_METHOD     $request_method;
		fastcgi_param  CONTENT_TYPE       $content_type;
		fastcgi_param  CONTENT_LENGTH     $content_length;
		
		fastcgi_param  SCRIPT_FILENAME    <?php echo ROOT_PATH;?><?php echo PHP_SELF;?>;
		fastcgi_param  SCRIPT_NAME        <?php echo PHP_SELF;?>;
		fastcgi_param  REQUEST_URI        $request_uri;
		fastcgi_param  DOCUMENT_URI       $document_uri;
		fastcgi_param  DOCUMENT_ROOT      <?php echo ROOT_PATH;?>;
		
		fastcgi_param  SERVER_PROTOCOL    $server_protocol;
		fastcgi_param  SERVER_SOFTWARE    nginx/$nginx_version;
		fastcgi_param  GATEWAY_INTERFACE  CGI/1.1;
		
		fastcgi_param  REMOTE_ADDR        $remote_addr;
		fastcgi_param  REMOTE_PORT        $remote_port;
		fastcgi_param  SERVER_ADDR        $server_addr;
		fastcgi_param  SERVER_PORT        $server_port;
		fastcgi_param  SERVER_NAME        $server_name;
		
		
		# PHP only, required if PHP was built with --enable-force-cgi-redirect
		fastcgi_param  REDIRECT_STATUS    200;
		
		fastcgi_pass  unix:/var/run/php-fpm/<?php echo APP_NAME;?>.sock;
		break;
	}
	
	error_page 404 /Error/http/404.html;
	error_page 400 /Error/http/400.html;
	
	error_log <?php echo LOG_PATH; ?>error.log;
	access_log <?php echo LOG_PATH; ?>access.log main;
}
