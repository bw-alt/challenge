<?php

    /**
     * Class user
     * Used to contain, format and validate a users information obtained from a row in a csv file
     */
    class user {
        public $name;
        public $surname;
        public $email;

        public function __construct(array $row){
            $this->name = $row[0] ?? '';
            $this->surname = $row[1] ?? '';
            $this->email = $row[2] ?? '';
        }

        /**
         * Formats the user variables to be standardised and ready to be inserted into the database
         * Name and surname fields will have their first letter capitalised, and rest lowercase
         * Emails will be all lowercase
         */
        public function format() {
            $this->name = ucfirst(strtolower(trim($this->name)));
            $this->surname = ucfirst(strtolower(trim($this->surname)));
            $this->email = strtolower(trim($this->email));
        }

        /**
         * Returns the full name of the user
         * @return string the full name of the user
         */
        public function getFullName(): string {
            return trim($this->name . ' ' . $this->surname);
        }

        /**
         * Returns whether or not the email is valid
         * @return bool true if email exists and is valid
         */
        public function isEmailValid(): bool {
            return filter_var($this->email, FILTER_VALIDATE_EMAIL) && !empty($this->email);
        }
    }