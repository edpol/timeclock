<?php

/**
 * These constants are defined at runtime by EnvConstants::load() from the .env file.
 * The declarations here exist solely to suppress "undefined constant" IDE warnings.
 *
 * @define DB_HOST
 * @define DB_USER
 * @define DB_PASS
 * @define DB_NAME
 * @define APP_ENV
 */

class Common {

    protected ?mysqli $connection = null;
    protected bool $magic_quotes_active;
    protected bool $real_escape_string_exists;

    function __construct() {
        $this->magic_quotes_active = false; //get_magic_quotes_gpc();
        $this->real_escape_string_exists = function_exists( "mysqli_real_escape_string" );
    }

    public function openConnection(): void
    {
        if (!extension_loaded('mysqli')) {
            throw new RuntimeException("mysqli extension is missing.");
        }
        // Tell mysqli to throw exceptions instead of warnings
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        } catch (mysqli_sql_exception $e) {
            error_log("DB Connection Failed: " . $e->getMessage());

            if (defined('APP_ENV') && APP_ENV === 'development') {
                echo "<pre style='background: #fee; padding: 10px; border: 1px solid red;'>";
                echo "Dev Error: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")";
                echo "</pre>";
            }

            exit("Database connection could not be established.");
        }
    }

    /**
     * @throws RuntimeException
     */
    function db_query($sql, $params = []): bool|mysqli_result
    {
        $mysqli = $this->connection;

        $stmt = $mysqli->prepare($sql);

        if (!$stmt) {
            error_log("SQL Prepare Error: " . $mysqli->error . " | Query: " . $sql);
            @throw new RuntimeException("A database error occurred while processing your request.");
        }

        if (!empty($params)) {
            // Generate the type string (i = integer, d = double, s = string)
            $types = "";
            foreach ($params as $param) {
                $types .= match(true) {
                    is_int($param)   => "i",
                    is_float($param) => "d",
                    default          => "s",
                };

            }

            // Use the splat operator (...) to unpack the array into arguments
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function fetchFields($result_set) {
        return $result_set->fetch_fields();        
    }

    // "database-neutral" methods
    public function fetchArray($result_set) {
//        return mysqli_fetch_array($result_set, MYSQLI_ASSOC);
        return $result_set->fetch_array(MYSQLI_ASSOC);
    }

    public function numRows($result_set) {
//        return mysqli_num_rows($result_set);
        return $result_set->num_rows;
    }

    public function insertId() {
        // get the last id inserted over the current db connection
//        return mysqli_insert_id($this->connection);
        return $this->connection->insert_id;
    }

    public function affectedRows() {
//        return mysqli_affected_rows($this->connection);
        return $this->connection->affected_rows;
    }

    public function escapeValue( $value ) {
        if( $this->real_escape_string_exists ) { // PHP v4.3.0 or higher
            // undo any magic quote effects so mysqli_real_escape_string can do the work
            if( $this->magic_quotes_active ) { $value = stripslashes( $value ); }
            $value = mysqli_real_escape_string( $this->connection, $value );
        } else { // before PHP v4.3.0
            // if magic quotes aren't already on then add slashes manually
            if( !$this->magic_quotes_active ) { $value = addslashes( $value ); }
            // if magic quotes are active, then the slashes already exist
        }
        return $value;
    }


}