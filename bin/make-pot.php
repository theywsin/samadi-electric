<?php
/**
 * Minimal POT generator (no WP-CLI needed): php bin/make-pot.php <source-dir> <text-domain> <output.pot>
 */
if ( $argc < 4 ) {
	fwrite( STDERR, "usage: php make-pot.php <dir> <domain> <out>\n" );
	exit( 1 );
}
list( , $dir, $domain, $out ) = $argv;
$funcs   = array( '__' => 's', '_e' => 's', 'esc_html__' => 's', 'esc_html_e' => 's', 'esc_attr__' => 's', 'esc_attr_e' => 's', '_x' => 'sc', '_ex' => 'sc', 'esc_html_x' => 'sc', 'esc_attr_x' => 'sc', '_n' => 'sp' );
$entries = array();
$it      = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	if ( 'php' !== $file->getExtension() ) {
		continue;
	}
	$rel    = ltrim( str_replace( realpath( $dir ), '', $file->getRealPath() ), '/' );
	$tokens = token_get_all( file_get_contents( $file->getPathname() ) );
	$n      = count( $tokens );
	for ( $i = 0; $i < $n; $i++ ) {
		if ( ! is_array( $tokens[ $i ] ) || T_STRING !== $tokens[ $i ][0] || ! isset( $funcs[ $tokens[ $i ][1] ] ) ) {
			continue;
		}
		$line = $tokens[ $i ][2];
		$args = array();
		$j    = $i + 1;
		while ( $j < $n && is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) { $j++; }
		if ( '(' !== $tokens[ $j ] ) { continue; }
		$depth = 0; $cur = null;
		for ( $j++; $j < $n; $j++ ) {
			$t = $tokens[ $j ];
			if ( '(' === $t || '[' === $t ) { $depth++; $cur = false; continue; }
			if ( ')' === $t || ']' === $t ) { if ( 0 === $depth ) { $args[] = $cur; break; } $depth--; continue; }
			if ( ',' === $t && 0 === $depth ) { $args[] = $cur; $cur = null; continue; }
			if ( 0 === $depth && is_array( $t ) && T_CONSTANT_ENCAPSED_STRING === $t[0] && null === $cur ) { $cur = stripcslashes( substr( $t[1], 1, -1 ) ); continue; }
			if ( 0 === $depth && is_array( $t ) && T_WHITESPACE === $t[0] ) { continue; }
			if ( 0 === $depth ) { $cur = false; }
		}
		$type = $funcs[ $tokens[ $i ][1] ];
		$d    = end( $args );
		if ( $d !== $domain || ! is_string( $args[0] ) ) { continue; }
		$ctx  = ( 'sc' === $type && is_string( $args[1] ) ) ? $args[1] : '';
		$key  = $ctx . "\4" . $args[0];
		if ( ! isset( $entries[ $key ] ) ) {
			$entries[ $key ] = array( 'msgid' => $args[0], 'ctx' => $ctx, 'plural' => 'sp' === $type && is_string( $args[1] ) ? $args[1] : null, 'refs' => array() );
		}
		$entries[ $key ]['refs'][] = "$rel:$line";
	}
}
$esc = function ( $s ) { return addcslashes( $s, "\"\\\n\t" ); };
$pot = "# Copyright (C) Samadi Electric\n# This file is distributed under the GPL-2.0-or-later.\nmsgid \"\"\nmsgstr \"\"\n\"Content-Type: text/plain; charset=UTF-8\\n\"\n\"Content-Transfer-Encoding: 8bit\\n\"\n\"MIME-Version: 1.0\\n\"\n\"X-Domain: {$domain}\\n\"\n\n";
foreach ( $entries as $e ) {
	$pot .= '#: ' . implode( ' ', array_slice( $e['refs'], 0, 5 ) ) . "\n";
	if ( $e['ctx'] ) { $pot .= 'msgctxt "' . $esc( $e['ctx'] ) . "\"\n"; }
	$pot .= 'msgid "' . $esc( $e['msgid'] ) . "\"\n";
	if ( $e['plural'] ) { $pot .= 'msgid_plural "' . $esc( $e['plural'] ) . "\"\nmsgstr[0] \"\"\nmsgstr[1] \"\"\n\n"; } else { $pot .= "msgstr \"\"\n\n"; }
}
file_put_contents( $out, $pot );
echo count( $entries ) . " strings → $out\n";
