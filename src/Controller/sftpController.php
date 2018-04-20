<?php
	
	namespace App\Controller;

	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;

	use phpseclib\Net\SFTP;

	class sftpController extends Controller
	{

		protected 	$connection = false,
					$ftp 		= true;

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
	    	$host = $this->hostCheck( $_POST[ 'host' ] );
	    	// ToDo: juiste exeption afhandeling
	    	if ( $host ){
	    		if ( isset( $_POST[ 'sftp' ] ) ){
	    			$this->ftp = false;
			    	$this->connection = new SFTP( $_POST[ 'host' ] );
			    	if ( !$this->connection->login( $_POST[ 'username' ], $_POST[ 'password' ] ) ) {
			    		//throw new Exeption( "FTP Login failed" );
			    		die( "FTP Login failed" );
			    	}
			    	if ( !$file = $this->findFile( $_POST[ 'filename' ] ) ){
			    		//throw new Exeption( "File not found" );
			    		die( "File (" . $_POST[ 'filename' ] . ") not found" );
			    	}
			    	return $this->render( 'sftp.html.twig', [
			        	"lines" => $this->countRows( $file )
			        ] );
			    } else {
			    	$this->connection = ftp_connect( $_POST[ 'host' ] );
			    	if ( !ftp_login( $this->connection, $_POST[ 'username' ], $_POST[ 'password' ] ) ) {
			    		//throw new Exeption( "FTP Login failed" );
			    		die( "FTP Login failed" );
			    	}
			    	if ( !$file = $this->findFile( $_POST[ 'filename' ] ) ){
			    		//throw new Exeption( "File not found" );
			    		die( "File (" . $_POST[ 'filename' ] . ") not found" );
			    	}
			    	return $this->render( "connect.html.twig", [
			        	"lines" 	=> $this->countRows( $file ),
			        	"filename"	=> $file
			        ] );
			    }
		    }
		    return false;
	    }

	    private function countRows( string $file )
	    {
	    	$lines = 0;
	    	$fp = fopen( $file, 'r' );
	    	while( !feof( $fp ) ){
	    		$lines += substr_count( fread( $fp, 2048 ), "\n" );
	    	}
	    	fclose( $fp );
	    	unlink( $file );
	    	return $lines;
	    }

	    private function findFile( string $filename, string $directory = "/" )
	    {
	    	$this->ftp ? ftp_chdir( $this->connection, $directory ) : $this->chdir( $directory );
	    	$files = $this->ftp ? ftp_nlist( $this->connection, '/' ) : $this->connection->nlist();
	    	foreach( $files as $file ){
	    		if ( is_array( $file ) ){
	    			return $this->findFile( $filename, $file );
	    		}
	    		if ( $file == $filename ){
	    			$this->ftp ? ftp_get( $this->connection, $file, $file, FTP_ASCII ) : $this->connection->get( $file, $file );
	    			return $file;
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
	    			if ( !is_numeric( $part ) ){
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