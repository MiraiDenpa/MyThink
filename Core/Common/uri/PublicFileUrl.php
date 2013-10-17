<?php
/**
 *
 *
 * @param $path
 *
 * @return string
 */
function PathToUrl($path){
	return str_replace([PUBLIC_PATH, STATIC_PATH, ROOT_PATH],
					   [PUBLIC_URL . '/', STATIC_URL . '/', ROOT_URL . '/'],
					   $path
	);
}
