<?php
	
	namespace App\Controller;

	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;

	use phpseclib\Net\SFTP;

	class sftpController extends Controller
	{

		protected $sftp = "";

	    public function sftpRender()
	    {
	        $number = mt_rand(0, 100);

	        return $this->render( 'sftp.html.twig', [ ] );
	    }

	    /**
	    *
	    * ToDO: Tijdelijk even de direct $_POST pvangen om te weten waar de variable komen.
	    *
	    **/
	    public function connection( )
	    {
	    	$this->sftp = new SFTP( $this->hostCheck( $_POST[ 'host' ] ) );
	    	if ( !$this->sftp->login( $_POST[ 'username' ], $_POST[ 'password' ] ) ) {
	    		throw new Exeption( 'FTP Login failed' );
	    	}
	    	if ( !$file = $this->findFile( $_POST[ 'filename' ] ) ){
	    		throw new Exeption( 'File not found' );
	    	}
	    	return $this->render( 'sftp.html.twig', [
	        	"lines" => $this->countRows( $file )
	        ] );

	    }

	    private function countRows( string $file )
	    {
	    	$lines = 0;
	    	$fp = fopen( $file, 'r' );
	    	while( !feof( $fp ) ){
	    		$lines += substr_count( fread( $fp, 2048 ), "\n" );
	    	}
	    	fclose( $fp );
	    	return $lines;
	    }

	    private function findFile( string $filename, string $directory = "" )
	    {

	    	!file_exists( "./var/temp" ) ? mkdir( "./var/temp" ) : true;

	    	if( !empty( $directory ) ){
	    		$this->chdir( $directory );
	    	}
	    	$files = $this->sftp->nlist();
	    	foreach( $files as $file ){
	    		if ( is_array( $file ) ){
	    			return $this->findFile( $filename, $file );
	    		}
	    		if ( $file == $filename ){
	    			$this->sftp->get( $file, "./var/temp/" . $file );
	    			return "./var/temp/" . $file;
	    		}
	    	}
	    	return false;
	    }

	    /**
	    *
	    * Simple, quick and dirty ip host check.
	    *
	    **/
	    private function hostCheck( string $host )
	    {
	    	$parts = explode( ".", $host );
	    	if ( count( $parts ) == 4 ){
	    		foreach ( $parts as $part ) {
	    			if ( !is_numeric( $pary ) ){
	    				$parsedURL = parse_url( $host );
	    				$domain = isset( $pieces[ 'host' ] ) ? $pieces[ 'host' ] : $pieces[ 'path' ];
	    				if ( preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs ) ) {
	    					return ( gethostbyname( $regs['domain'] ) );
	    				}
	    			}
	    		}
	    		return true;
	    	}
	    	return false;
	    }

	}