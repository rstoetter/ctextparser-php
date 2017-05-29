<?php

//  Description   : Die Klasse 'cTextParser' mplementiert einen rudimentären Parser für eine Textdatei

// cTextParser.class.php

namespace \rstoetter\cTextParser;

/**
  *
  * The class cTextParser implements a rudimentary parser for a text file. The namespace is \rstoetter\cTextParser.
  * Normally you derive another child class, which specializes the parsing process. Therefore the methods are
  * kept protected
  *
  * @author Rainer Stötter
  * @copyright 2010-2017 Rainer Stötter
  * @license MIT
  * @version =1.0
  *
  */


class cTextParser {

    /**
      * The path of the text file
      *
      * @var string
      *
      *
      */

    protected $m_scriptfilename = '';

    /**
      * The buffered textual content of the text file
      *
      * @var string
      *
      *
      */

    protected $m_script = '';

    /**
      * The length of the text buffer $m_script in bytes
      *
      * @var int
      *
      *
      */

    protected $m_scriptlen = 0;

    /**
      * The actual position in the text buffer $m_script. -1 if the parsing has not started yet.
      *
      * @var int
      *
      *
      */


    protected $m_scriptpos = -1;

    /**
      * whether debugging is active. defaults to false
      *
      * @var bool
      *
      *
      */

    public $m_debug = false;

    protected $m_nextline = '';


    /**
      *
      * The constructor of cTextParser
      *
      * Example:
      *
      *
      * class cParser extends  \rstoetter\cTextParser\cTextParser {
      *   public function __construct( $fname, $debug = false ) {
      *      parent::__construct( $fname, $debug );
      *      $this->Parse( );
      *   }
      *   protected function Parse( ) {
      *
      *   }
      * }
      *
      * $parser = new cParser( '/tmp/tst.txt', false );
      *
      * @param string $fname is the path of the text file to parse
      * @param bool $debug whether debugging mode should be activated - defaults to false
      *
      * @return cTextParser a new instance of cTextParser
      *
      */

    protected function __construct( $fname, $debug = false ) {

        assert( strlen($fname) );

        $this->m_scriptfilename = $fname;
        $this->m_debug = $debug;

        if ( file_exists( $fname ) ) {
            $this->m_script = file_get_contents  ( $fname );
            $this->m_scriptlen = strlen( $this->m_script);

            // if ($this->m_debug) echo "<br>$this->m_script<br>";
        } else {
	    throw new \Exception( "The file '$fname' does not exist" );
        }

    }   // function __construct( )


    /**
      *
      * Rewind the internal pointer to the text buffer. It is set to -1
      *
      * Example:
      *
      *
      * $this->Rewind( )
      *
      */

    protected function Rewind( ) {
        $this->m_scriptpos = -1;
    }   // function Rewind( )


    /**
      *
      * Returns the next character of the internal text buffer or null.
      *
      * Example:
      *
      *
      * $this->GetChar( );
      *
      * @return string|null the next character in the text buffer or null, if the end of the buffer has been reached
      *
      */

    protected function GetChar( ) {

        $this->m_scriptpos++;
        $chr= substr( $this->m_script, $this->m_scriptpos, 1 );

        assert( $this->m_scriptpos >= 0);
        assert( $this->m_scriptpos <= $this->m_scriptlen);
        // echo "\n<br>GetChar() liefert '$chr'";
        return $chr;
    }


    /**
      *
      * Set the internal pointer to the text buffer one character back
      *
      * Example:
      *
      *
      * $this->UngetChar( )
      *
      */


    protected function UngetChar( ) {

	if ( $this->m_scriptpos > -1 ) {
	    $this->m_scriptpos--;
	}

        // assert( $this->m_scriptpos >= 0);
    }


    /**
      *
      * Returns the next character of the internal text buffer or null without incrementing the internal pointer to the text buffer .
      *
      * Example:
      *
      *
      * $chr = $this->NextChar( );
      *
      * @return string|null the next character in the text buffer or null, if the end of the buffer has been reached
      *
      */


    protected function NextChar( ) {
        return substr( $this->m_script, $this->m_scriptpos +1 , 1 );
    }

    /**
      *
      * Returns the actual character of the internal text buffer or null without incrementing the internal pointer to the text buffer .
      *
      * Example:
      *
      *
      * $chr = $this->ActChar( );
      *
      * @return string|null the next character in the text buffer or null, if the end of the buffer has been reached
      *
      */


    protected function ActChar( ) {
        // assert( $this->m_scriptpos >= 0);
        return substr( $this->m_script, $this->m_scriptpos, 1 );
    }

    /**
      *
      * Discards the actual line and positions the pointer to the internal text buffer on the start of the next line .
      *
      * Example:
      *
      *
      * $this->SkipLine( );
      *
      * @return bool $ret is true, if lines were skipped
      *
      */


    protected function SkipLine( ) {

        // echo "\nskipping EOL";

        $ret = false;

        while ( ( ( $ch = $this->ActChar( ) ) != "\n" ) && ( $ch != "\r" ) && ( ! $this->EOT( ) ) ) {
            $this->GetChar( );
            $ret = true;
        }

        // $this->GetChar( );

        // echo "\nskipped EOL";

        return $ret;

    }   // function SkipLine( )


    /**
      *
      * Discards all whitespaces following to the pointer to the internal text buffer on the start
      *
      * Example:
      *
      *
      * $this->SkipWhitespaces( );
      *
      * @return bool $ret is true, if whitespaces were found
      *
      */


    protected function SkipWhitespaces( ) {

        // echo "\nskipping whites";

        $fertig = false;
        $ret = false;

        $str = '';

        if ( $this->m_scriptpos == -1 ) $this->GetChar( );

        while ( $this->IsWhitespace( $this->ActChar( ) ) && ( !$this->EOT( )) ) {
            $ret = true;
            $this->GetChar();
        }

        assert( ! $this->IsWhitespace( $this->ActChar( ) ) );

        return $ret;
        // echo "\nskipped whites";

    }   // function SkipWhitespaces()

    protected function SkipWhitespacesLine( ) {

        // skip all following whitespaces BUT NOT "\n"

        $fertig = false;
        $ret = false;

        $str = '';

        if ( $this->m_scriptpos == -1 ) $this->GetChar( );

        while ( $this->IsWhitespaceLine( $this->ActChar( ) ) && ( !$this->EOT( )) ) {
            $ret = true;
            $this->GetChar();
        }

        assert( ! $this->IsWhitespaceLine( $this->ActChar( ) ) );

        return $ret;
        // echo "\nskipped whites";

    }   // function SkipWhitespaces()

    /**
      *
      * Tests, whether the text $txt is found in the text buffer from the actual position on.
      *
      * The internal pointer will not be moved forward.
      *
      * Example:
      *
      *
      * if ( $this->FollowsText( '/*' ) )$this->SkipComment( );
      *
      * @param string $txt is the text to search for
      * @param bool $ignorecase is true, if the case of $txt does not matter. Defaults to false.
      *
      * @return bool true if the text $txt was found
      *
      */


    protected function FollowsText( $txt, $ignorecase = false ) {

        $tx = substr( $this->m_script, $this->m_scriptpos, strlen( $txt ) );

        if ( $ignorecase ) {
            $tx = strtoupper($tx);
            $txt = strtoupper($txt);
        }

        return ( $tx == $txt );

    }   // function FollowsText



    /**
      *
      * Discards the following comment which is embraced by '/' . '*' and '*' . '/'
      *
      * Example:
      *
      *
      * if ( $this->FollowsText( '/*' ) )$this->SkipComment( );
      *
      */


    protected function SkipComment( ) {

        assert( $this->ActChar() == '/' && $this->NextChar() == '*' );

        $pos = $this->m_scriptpos;

        $this->GetChar();
//        $this->GetChar();

        $fertig = false;
        while ( ! $fertig ){

            $chr = $this->GetChar();

            if ( $chr == '*' && $this->NextChar() == '/') {
                $this->GetChar();
                $fertig = true;
            }

            if ($this->m_scriptpos > $this->m_scriptlen) {
                $fertig = true;
            }

        }

        if ($this->m_debug) echo "<br>Kommentar gefunden : " . substr( $this->m_script, $pos, $this->m_scriptpos - $pos + 1 ) . "<br>";

    }   // function SkipComment()

     /**
      *
      * Returns true, if the character $chr is a whitespace charecter ( \n \r ' ' \t )
      *
      * Example:
      *
      *
      * if ( $this->IsWhitespace( $chr ) ) echo "\n whitespace found";
      *
      * @return bool true, if $chr is a whitespace
      *
      */


    protected function IsWhitespace( $chr ) {

        if (!strlen($chr)) return false;

        return ( ($chr == ' ') || ( $chr == "\n") || ( $chr == "\t") || ( $chr == "\r") );
    }

    protected function IsWhitespaceLine( $chr ) {
        // dasselbe wie IsWhitespace nur ohne "\n"

        if (!strlen($chr)) return false;

        return ( ($chr == ' ') || ( $chr == "\t") || ( $chr == "\r") );
    }


     /**
      *
      * Discard all characters up to the character $begrenzer
      *
      * Example:
      *
      *
      * if ( $this->IsWhitespace( $chr ) == '"' ) $this->FollowBegrenzer( $chr );
      *
      */


    protected function FollowBegrenzer( $begrenzer ) {
        // $this->m_nextline .= $this->ActChar();
        $pos = $this->m_scriptpos;
        while ( ($chr = $this->GetChar() ) != $begrenzer ) $this->m_nextline .= $chr;

        $this->m_nextline .= $chr;
        // echo "<br>FollowBegrenzer endet mit " . substr($this->m_nextline, $pos, $this->m_scriptpos - $pos);
    }

     /**
      *
      * Discard all characters up to the character $begrenzer
      * same as FollowBegrenzer( )
      *
      * Example:
      *
      *
      * if ( $this->IsWhitespace( $chr ) == '"' ) $this->FollowDelimiter( $chr );
      *
      */


    protected function FollowDelimiter( $delimiter ) {

        $this->FollowBegrenzer( $delimiter );
    }


     /**
      *
      * EOL aka "End of line" Test, whether there is a end of line at the current position of the text pointer
      *
      * Example:
      *
      * @return bool true if there is a end of line at the current position of the text pointer
      *
      * if ( $this->EOL( ) ) echo "\n CRLF";
      *
      */

    protected function EOL( ) {

        return ( $this->ActChar( ) == "\n" || $this->ActChar( ) == "\r" );

    }  // function EOL( )

     /**
      *
      * EOT aka "End of text" Test, whether the end of the internal text buffer has been reached
      *
      * Example:
      *
      * @return bool true if the end of the internal text buffer has been reached
      *
      * if ( $this->EOT( ) ) echo "\n done";
      *
      */

    protected function EOT( ) {
        return ( $this->m_scriptpos >= $this->m_scriptlen );
    }

     /**
      *
      * BOT aka "Bottom of text" Test, whether the parsing has started with the first character
      *
      * @return bool true, if the first character has not been read
      *
      * Example:
      *
      *
      * assert ( $this->BOT( ) ) ;
      *
      */


    protected function BOT( ) {
        return ( $this->m_scriptpos <= 0 );
    }

     /**
      *
      * Returns true, if the character $chr is alphabetic
      *
      * @return bool true, if the $chr is alphabetic
      *
      * Example:
      *
      * assert ( $this->isalpha( 'A' ) ) ;
      *
      */

    protected function isalpha( $chr ) {

        return ( ( $chr >= 'a' ) && ( $chr <= 'z' ) ) || ( ( $chr >= 'A' ) && ( $chr <= 'Z' ) ) ;

    } // function isalpha();


     /**
      *
      * Returns true, if the character $chr is a digit
      *
      * @return bool true, if the $chr is a digit
      *
      * Example:
      *
      * assert ( $this->isdigit( '1' ) ) ;
      *
      */


    protected function isdigit( $chr ) {

        return ( ( $chr >= '0' ) && ( $chr <= '9' ) );

    } // function isdigit();

     /**
      *
      * Returns true, if the character $chr is an underscore
      *
      * @return bool true, if the $chr is an underscore
      *
      * Example:
      *
      * assert ( $this->isunderscore( '_' ) ) ;
      *
      */


    protected function isunderscore( $chr ) {

        return (  $chr == '_' ) ;

    } // function isdigit();

     /**
      *
      * Returns true, if the character $chr is alphabetic or a digit
      *
      * @return bool true, if $chr is alphabetic or a digit
      *
      * Example:
      *
      * assert ( $this->isalnum( 'A' ) ) ;
      *
      */



    protected function isalnum( $chr ) {

        return $this->isalpha( $chr ) || $this->isdigit( $chr );

    } // function isalnum();

     /**
      *
      * Returns true, if the character $chr is valid for the second and later character of an identifier
      *
      * @return bool true, if the $chr is valid for the second and later character of an identifier
      *
      * Example:
      *
      * assert ( $this->isidnext( 'A' ) ) ;
      *
      */


    protected function isidnext ($chr ) {

        return $this->isidstart( $chr ) || $this->isalnum( $chr ) || ( $chr == '$' );

    } // function isidnext();


     /**
      *
      * Returns true, if the character $chr is valid for the first character of an identifier
      *
      * @return bool true, if the $chr is valid for the first character of an identifier
      *
      * Example:
      *
      * assert ( $this->isidstart( '_' ) ) ;
      *
      */


    protected function isidstart ($chr ) {

        return $this->isunderscore( $chr ) || $this->isalpha( $chr ) || ( $chr == '$' );

    } // function isidstart();


     /**
      *
      * Returns true, if the character $chr is a quotation mark ('"`)
      *
      * @return bool true, if the $chr is a quotation mark
      *
      * Example:
      *
      * assert ( $this->isquotationmark( '"' ) ) ;
      *
      */


    protected function isquotationmark ($chr ) {

        return ( $chr == "'" ) || ( $chr == '"') || ( $chr == '`' );

    } // function isidstart();

     /**
      *
      * Returns the next identifier in the buffer starting from the internal buffer pointer
      *
      *  An identifier starts with a character of class isidstart() and ends up with characters of class isidstart()
      *
      * @return string the identifier
      *
      * Example:
      *
      * $id = $this->ParseIdentifier( ) ;
      *
      */


    protected function ParseIdentifier( ) {

        $id = '';

        if ( $this->IsWhitespace( $this->ActChar( ) ) ) $this->SkipWhitespaces( );

        if ( $this->isidstart( $chr = $this->ActChar( ) ) ) {

            while ( ( $this->isidnext( $chr = $this->ActChar( ) ) ) && ( ! $this->EOT( ) ) ) {

                    if ( $this->EOT( ) ) break;

                    $id .= $chr;
                    $chr = $this->GetChar( );
            }

        }

        return $id;

    }   // function ParseIdentifier( )


}   // class cTextParser


?>