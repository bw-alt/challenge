<?php
    /**
     * Assumptions / Notes:
     * - Max 120 char per line policy
     * - Create table and file can be used together
     * - Testing has not been automated as a proper framework is not set up
     */

    handleInputs();

    /**
     * Handles the provided arguments and calls the correct function
     */
    function handleInputs() {
        $dryRun = false;
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

        // Make sure we have required arguments if we need a db connection
        if (array_key_exists("dry_run", $options)) {
            $dryRun = true;
        } else {
            $requiredArguments = ["u", "h", "p"]; // need for db connection
            if (array_diff($requiredArguments, array_keys($options)) !== []) { // missing required args
                echo "Error: Missing required argument(s). Please check --help for more details.\n";
                return;
            }
        }

        // Create table
        if (array_key_exists("create_table", $options)){
            if ($dryRun) {
                echo "Error: Can't create table on a dry run.\n";
                return;
            }
            createTable();
        }

        // Process file
        if(array_key_exists("file", $options)) {
            $file = $options["file"];
            if (isset($file) && $file != false) {
                processFile($file, $dryRun);
            } else {
                echo "Error: File path was invalid.\n";
            }
        }
    }

    /**
     * Processes a file, including formatting of validation
     * @param string $path the path of the file
     * @param bool $dryRun if true, do not save to database
     */
    function processFile(string $path, bool $dryRun) {
    }
    
    /**
     * Creates a users table to insert data into
     */
    function createTable() {
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
