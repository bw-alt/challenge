<?php
    /**
     * Assumptions / Notes:
     * - Max 120 char per line policy
     * - Create table and file can be used together
     * - If email is invalid, Do not insert the user into the database at all
     * - The database name is assumed to be 'Users' for now
     * - Duplicates will be handled my MySQL primary key
     * - In line mysqli has been used for simplicity. A larger program would have dedicated db conn handling.
     * - Testing has not been automated as a proper framework is not set up
     */

    require_once("user.php");

    handleInputs();

    /**
     * Handles the provided arguments and calls the correct function
     */
    function handleInputs() {
        $dbName = 'Users';
        $shortOpts ="u:p:h:";
        $longOpts = [
            "file:",
            "create_table",
            "dry_run",
            "help",
        ];
        $options = getopt($shortOpts, $longOpts);

        if (array_key_exists("help", $options)) {
            help();
            return; // no more processing after help
        }

        // Get db connection if it's a real run
        if (!array_key_exists("dry_run", $options)) {
            $requiredArguments = ["u", "h"]; // need a db connection - pw can theoretically by blank
            if (array_diff($requiredArguments, array_keys($options)) !== []) { // missing required args
                echo "Error: Missing required argument(s). Please check --help for more details.\n";
                return;
            }
            try {
                $conn = new mysqli($options["h"], $options["u"], $options["p"] ?? '', $dbName);
            } catch (Exception $e) {
                echo "Error: Could not connect to database. Please check your directives.\n";
                return;
            }
        }

        // Create table
        if (array_key_exists("create_table", $options)){
            if (!isset($conn)) {
                echo "Error: Can't create table on a dry run.\n";
                return;
            }
            createTable($conn);
        }

        // Process file
        if(array_key_exists("file", $options)) {
            $file = $options["file"];
            if (isset($file) && $file != false) {
                processFile($file, $conn ?? null); // conn will never exist in dry run
            } else {
                echo "Error: File path was invalid.\n";
            }
        }

        if (isset($conn)) {
            $conn->close();
        }
    }

    /**
     * Processes a file, including formatting of validation
     * @param string $path the path of the file
     * @param ?mysqli $conn mysqli connection, or null if dry run
     */
    function processFile(string $path, ?mysqli $conn) {
        $row = 0;
        if (($handle = fopen($path, "r")) !== FALSE) {
            $records = [];
            while (($data = fgetcsv($handle)) !== FALSE) {
                $row++;
                $user = new user($data);
                $user->format();
                if (!$user->isEmailValid()) {
                    if ($row > 1) { // don't report error for title line - but allow it to process if valid email
                        echo "Invalid Email for row " . $row . ': ' . $user->getFullName()  . ".\n";
                    }
                    continue;
                }
                $records[] = $user;
            }
            fclose($handle);
            if ($conn) {
                saveUsers($conn, $records);
            } else {
                echo "Dry run completed.\n";
            }
        } else {
            echo "Error: Unable to open provided file.\n";
        }
    }

    /**
     * Saves users to the user table in the database
     * @param mysqli|null $conn mysqli connection, or null if dry run
     * @param array $records an array of user classes
     */
    function saveUsers(?mysqli $conn, array $records) {
        if(!$conn->query("DESCRIBE `users`")) { // table doesn't exist
            echo "Error: Table 'users' does not exist. Please create it using --create table.\n";
            return;
        }

        $query = "INSERT INTO Users (name, surname, email) VALUES (?, ?, ?)";
        try {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $name, $surname, $email); // will be available when executed

            $conn->query("START TRANSACTION"); // more efficient as they will be processed together
            foreach ($records as $user) {
                $name = $user->name;
                $surname = $user->surname;
                $email = $user->email;
                $stmt->execute();
            }
            $stmt->close();
            $conn->query("COMMIT");
            echo "Users added to the database.\n";
        } catch (Exception $e) {
            echo "Error: Unable to save users to the database.\n";
        }
    }

    /**
     * Creates a users table to insert data into
     * @param mysqli $conn mysqli connection
     */
    function createTable(mysqli $conn) {
        $query = "CREATE TABLE users(
            name VARCHAR(50) NOT NULL,
            surname VARCHAR(50) NOT NULL,
            email VARCHAR(50) PRIMARY KEY
            )";
        try {
            $stmt = $conn->prepare($query);
            $stmt->execute();
            echo "Table created.\n";
        } catch (Exception $e) {
            echo "Error: Unable to create Users Table.\n";
        }
    }

    /**
     * Prints out the help documentation
     */
    function help() {
        echo <<<END
        Directives:
        --file [csv file name] – the name of the CSV to be parsed
        --create_table – build the MySQL users table 
        --dry_run – runs the script but doesn't insert into the DB. All other functions will be executed.
        -u – MySQL username
        -p – MySQL password
        -h – MySQL host
        --help – will output the list of directives with details.
        END;
    }
