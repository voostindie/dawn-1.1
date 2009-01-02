<?php
/***
 * Class <code>ConfigReader</code> reads configuration files and stores their
 * contents in an associative array.
 * <p>
 * The syntax of configuration files is like that of Cascading Style
 * Sheets (CSS). Here's the BNF:
 * </p>
 * <pre><code>    CONFIG  ::= FIELD*
 *    FIELD   ::= SECTION | NAME ":" VALUE ";"
 *    SECTION ::= NAME "{" FIELD* "}"
 *    NAME    ::= STRING
 *    VALUE   ::= STRING
 *    STRING  ::= string | "'" string "'" | "\"" string "\""</code></pre>
 * <p>
 * If an error occurs during the parsing process an exception will be thrown and
 * the application will be halted immediately. This is intentional, because the
 * application can only run properly if all configuration files can be parsed
 * and read completely.
 * </p>
 * <p>
 * Note that this class doesn't support escape sequences in string values (yet).
 * If you need both a ' and a " in a single value, you're screwed...
 * </p>
 * <p><b>Example</b>: Parsing this configuration:</p>
 * <pre><code>    section {
 *        field1: short;
 *        subsection {
 *            field2: 'long value';
 *        }
 *    }</code></pre>
 * <p>...will result in the array:</p>
 * <pre><code>    array(
 *        'section' => array(
 *            'field1'     => 'short',
 *            'subsection' => array(
 *                'field2' => 'long value'
 *            )
 *        )
 *    )</code></pre>
 * <p>
 * This parser is by no means as efficient as it could be, but this is not a 
 * problem: this class is only included when a configuration file has to be 
 * parsed because the cache is invalid. Once an application is finished and
 * the configuration files are cached, this class isn't used anymore.
 * </p>
 ***/

class ConfigReader
{
    // DATA MEMBERS

    /***
     * The internal state
     * @type ConfigReaderState
     ***/
    var $state;

    /***
     * The result
     * @type array
     ***/
    var $sections;

    // CREATORS

    /***
     * Create a <code>ConfigReader</code> object and parse the specified file
     * @param $filename the name of the configuration file to parse
     ***/
    function ConfigReader($filename) 
    {
        assert('Debug::log("ConfigReader: parsing file \'$filename\'")');
        $this->state    =& new ConfigReaderState($filename);
        $this->sections =  $this->parseFields();
    }
    
    // MANIPULATORS

    /***
     * Skip whitespace and comments until the next token is found
     * @returns void
     * @private
     ***/
    function findNextToken() 
    {
        if ($this->state->isEof()) 
        {
            return;
        }
        $char = $this->state->getCharacter();
        // Skip leading white space
        while ($this->isSpace($char) && !$this->state->isEof()) 
        {
            $char = $this->state->getCharacter();
        }
        $this->state->moveBack();
        // Check if this is a comment
        if ($char == '#') 
        {
            // Skip until the end of the line is reached
            while ($char != "\n" && !$this->state->isEof()) 
            {
                $char = $this->state->getCharacter();
            }
            // And find the next valid token
            $this->findNextToken();
        }
    }

    /***
     * Skip an expected character (probably a non-terminal symbol). If the
     * expected character isn't found, an exception will be thrown
     * @param $char the (expected) character to skip
     * @returns void
     * @private
     ***/
    function skipCharacter($char) 
    {
        $this->findNextToken();
        if (!$this->state->isEof()) 
        {
            $this->state->getCharacter($char);
        }
    }

    /***
     * Parse a complete string and return it. A string is either an unquoted
     * word (no whitespace!), or a quoted sentence. Quotation can be done with
     * ' and ".
     * @returns string
     * @private
     ***/
    function parseString() 
    {
        $this->findNextToken();
        $result    = '';
        $delimiter = '';
        $char      = $this->state->getCharacter();
        // Check if the string starts with a delimiter
        if ($char == '"' || $char == "'") 
        {
            $delimiter = $char;
        }
        // If there's a delimiter, read until the next one and return
        if ($delimiter != '') 
        {
            $char = $this->state->getCharacter();
            while ($char != $delimiter) 
            {
                $result .= $char;
                $char    = $this->state->getCharacter();
            }
            return $result;
        }
        // Add characters to the result unless it's whitespace or special
        while (!$this->isSpecial($char) && !$this->isSpace($char))
        {
            $result .= $char;
            $char    = $this->state->getCharacter();
        }
        $this->state->moveBack();
        return $result;
    }

    /***
     * Parse a section and add it to the array of sections; if no section
     * could be parsed, <code>false</code> is returned, and <code>true</code>
     * otherwise.
     * @param $sections the array of sections to add the new section to
     * @param $title the title of the new section in case it was already parsed
     * @returns bool
     * @private
     ***/
    function parseSection(&$sections, $title = '') 
    {
        $this->findNextToken();
        if (!$this->state->isEof()) 
        {
            if ($title == '') 
            {
                $title = $this->parseString();
                $this->skipCharacter('{');
            }
            $fields = $this->parseFields();
            $this->skipCharacter('}');
            $sections[$title] = $fields;
            return true;
        }
        return false;
    }

    /***
     * Parse the fields in a section and return them
     * @returns array
     * @private
     ***/
    function parseFields() 
    {
        $this->findNextToken($this->state);
        $fields = array();
        $char   = $this->state->getCurrent();
        // Read all fields until the end of the section is found
        while ($char != '}' && !$this->state->isEof()) 
        {
            $this->parseField($fields);
            $this->findNextToken();
            if (!$this->state->isEof()) 
            {
                $char = $this->state->getCurrent();
            }
            else 
            {
                $char = '}';
            }
        }
        return $fields;
    }

    /***
     * Parse a single field and add it to the array of fields
     * @param $fields the array of fields to add the new field to
     * @returns void
     * @private
     ***/
    function parseField(&$fields) 
    {
        $title = $this->parseString();
        $this->findNextToken();
        $char = $this->state->getCharacter();
        if ($char == ':') 
        {
            // Read a value
            $value = $this->parseString();
            $this->skipCharacter(';');
            $fields[$title] = $value;
        }
        elseif ($char == '{') 
        {
            // Read a section
            $this->parseSection($fields, $title);
        }
        else 
        {
            // Force an exception
            $this->state->moveBack();
            $this->skipCharacter(': or {');
        }
    }

    // ACCESSORS

    /***
     * Return the result of a parsed configuration file
     * @returns array
     ***/
    function getConfig() 
    {
        return $this->sections;
    }

    /***
     * Check if some character is whitespace
     * @param $char the character to check
     * @returns bool
     * @private
     ***/
    function isSpace($char) 
    {
        return $char == ' '  || $char == "\n" 
            || $char == "\r" || $char == "\t";
    }

    /***
     * Check if some character is a special character (a non-terminal)
     * @param $char the character to check
     * @returns bool
     * @private
     ***/
    function isSpecial($char) 
    {
        return $char == '{' || $char == '}' || $char == ':' 
            || $char == ';' || $char == '#';
    }
}

/***
 * Class <code>ConfigReaderState</code> is used internally in class
 * <code>ConfigReader</code> and should not be used by other classes.
 * <p>
 * This class reads the contents of a file into memory and maintains a cursor
 * on these contents. The cursor can be moved back and forth in the file, but
 * must stay within legal limits or else an exception will be thrown.
 * </p>
 * <p>
 * Exceptions are automatically thrown, but only for three reasons:
 * </p>
 * <ol>
 *   <li><b>BOF</b>: the cursor is placed before the first character in the 
 *       file.</li>
 *   <li><b>EOF</b>: the cursor is placed after the last character in the 
 *       file.</li>
 *   <li><b>UNEXPECTED</b>: instead of finding a specified character, some other
 *       character was found at the current position.</li>
 * </ol>
 * <p>
 * This class also keeps track of the current line and column in the file. When
 * an <b>UNEXPECTED</b> exception is thrown, this information is shown in the
 * exception description, making it much easier to debug configuration files.
 * </p>
 ***/

class ConfigReaderState
{

    // DATA MEMBERS

    /***
     * The name of the file being parsed
     * @type string
     ***/
    var $filename;

    /***
     * The contents of the file
     * @type array
     ***/
    var $contents;

    /***
     * The index of the current line in the file
     * @type int
     ***/
    var $line;

    /***
     * The index of the current column in the current line
     * @type int
     ***/
    var $column;

    /***
     * The length of the current line in the file
     * @type int
     ***/
    var $length;

    /***
     * The total number of lines in the file
     * @type int
     ***/
    var $size;

    // CREATORS

    /***
     * Create a new <code>ConfigReaderState</code> object for a file. The file
     * is read into memory immediately, and the internal cursor is set at the
     * first character in the file. (Note: this is not <b>BOF</b>!)
     * @param $filename to name of the file to read
     ***/
    function ConfigReaderState($filename) 
    {
        $this->filename = $filename;
        if (!file_exists($filename))
        {
            include_once(DAWN_EXCEPTIONS . 'FileNotFoundException.php');
            $exception =& new FileNotFoundException($filename);
            $exception->halt();
        }
        $this->contents = file($filename);
        $this->line     = 0;
        $this->column   = 0;
        $this->length   = strlen($this->contents[0]);
        $this->size     = count($this->contents);
    }

    /***
     * Move the internal cursor one position back in the file.
     * @returns void
     ***/
    function moveBack() 
    {
        $this->column--;
        if ($this->column == -1) 
        {
            $this->line--;
            if ($this->line > -1) 
            {
                $this->length = strlen($this->contents[$this->line]);
                $this->column = $this->length - 1;
            }
        }
    }

    /***
     * Move the internal cursor one position forward in the file. 
     * @returns void
     ***/
    function moveNext() 
    {
        $this->column++;
        // Set the cursor to the next line if necessary
        if ($this->column == $this->length) 
        {
            $this->line++;
            if ($this->line < $this->size) 
            {
                $this->length = strlen($this->contents[$this->line]);
                $this->column = 0;
            }
        }
    }

    // ACCESSORS

    /***
     * Check if the cursor is placed before the beginning of the file
     * @returns bool
     ***/
    function isBof() 
    {
        return ($this->line < 0);
    }

    /***
     * Check if the cursor is placed at the end of the file
     * @returns bool
     ***/
    function isEof() 
    {
        return ($this->line == $this->size);
    }

    /***
     * Return the current character. If the current character is invalid because
     * the cursor is at an invalid position (<b>BOF</b> or <b>EOF</b>), an 
     * exception will be thrown.
     * @returns string
     ***/
    function getCurrent() 
    {
        if ($this->isBof()) 
        {
            include_once(DAWN_EXCEPTIONS . 'ConfigReaderStateException.php');
            $exception =& new ConfigReaderStateException($this, 'BOF');
            $exception->halt();
        }
        if ($this->isEof()) 
        {
            include_once(DAWN_EXCEPTIONS . 'ConfigReaderStateException.php');
            $exception =& new ConfigReaderStateException($this, 'EOF');
            $exception->halt();
        }
        return $this->contents[$this->line][$this->column];
    }

    /***
     * Get the current character and move on to the next. If the expected
     * character is specified, it is compared with the character found and an
     * exception is thrown if they aren't equal. 
     * @param $expected the optional expected character
     * @returns string
     ***/
    function getCharacter($expected = '') 
    {
        $char = $this->getCurrent();
        if ($expected != '' && $expected != $char)
        {
            include_once(DAWN_EXCEPTIONS . 'ConfigReaderStateException.php');
            $exception =& new ConfigReaderStateException($this, 'UNEXPECTED');
            $exception->halt();
        }
        $this->moveNext();
        return $char;
    }
}
?>
