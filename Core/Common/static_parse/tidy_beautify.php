<?php
/**
 * 美化HTML代码
 *
 * @param string $content
 * @param bool   $doctype
 *
 * @return string
 */
function tidy_beautify($content, $doctype = false){
	$config = array(
		'add-xml-decl'                => false,
		'add-xml-space'               => false,
		'alt-text'                    => '',
		'anchor-as-name'              => true,
		'bare'                        => false,
		'clean'                       => true,
		'css-prefix'                  => '',
		'decorate-inferred-ul'        => false,
		'doctype'                     => 'omit',
		'drop-empty-paras'            => false,
		'drop-font-tags'              => false,
		'drop-proprietary-attributes' => false,
		'enclose-block-text'          => false,
		'enclose-text'                => false,
		'escape-cdata'                => false,
		'fix-backslash'               => true,
		'fix-bad-comments'            => true,
		'fix-uri'                     => true,
		'hide-comments'               => true,
		'hide-endtags'                => true,
		'indent-cdata'                => true,
		'input-xml'                   => false,
		'join-classes'                => false,
		'join-styles'                 => true,
		'literal-attributes'          => false,
		'logical-emphasis'            => false,
		'lower-literals'              => true,
		'merge-divs'                  => false,
		'merge-spans'                 => false,
		'ncr'                         => true,
		'new-blocklevel-tags'         => '',
		'new-empty-tags'              => '',
		'new-inline-tags'             => '',
		'new-pre-tags'                => '',
		'numeric-entities'            => false,
		'output-html'                 => false,
		'output-xhtml'                => true,
		'output-xml'                  => false,
		'preserve-entities'           => false,
		'quote-ampersand'             => true,
		'quote-marks'                 => false,
		'quote-nbsp'                  => true,
		'repeated-attributes'         => 'keep-last',
		'replace-color'               => true,
		'show-body-only'              => !$doctype,
		'uppercase-attributes'        => false,
		'uppercase-tags'              => false,
		'word-2000'                   => false,
		//Diagnostics Options	
		'accessibility-check'         => 0,
		'show-errors'                 => 6,
		'show-warnings'               => true,
		//Pretty Print Options	
		'break-before-br'             => true,
		'indent'                      => true,
		'indent-attributes'           => false,
		'indent-spaces'               => 2,
		'markup'                      => true,
		'sort-attributes'             => 'alpha',
		'tab-size'                    => 8,
		'vertical-space'              => true,
		'wrap'                        => 120,
		'wrap-asp'                    => true,
		'wrap-attributes'             => false,
		'wrap-jste'                   => true,
		'wrap-php'                    => true,
		'wrap-script-literals'        => false,
		'wrap-sections'               => true,
		//Character Encoding Options	
		'ascii-chars'                 => false, // 只使用ascii字符，消除utf8
		'char-encoding'               => 'UTF-8',
		'input-encoding'              => 'UTF-8',
		'language'                    => '',
		'newline'                     => 'LF',
		'output-bom'                  => false,
		'output-encoding'             => 'UTF-8',
		////Miscellaneous Options	
		'tidy-mark'                   => false,
	);

	$tidy = new tidy();
	$out  = $tidy->repairString($content, $config, 'UTF8');
	if($tidy->errorBuffer){
		trace($tidy->errorBuffer, 'TidyError', 'NOTIC');
	}
	if($doctype){
		$out = str_replace('<html xmlns="http://www.w3.org/1999/xhtml">', '', $out);
		$out = "<!DOCTYPE html>\n<html>" . $out;
	}
	return $out;
}
