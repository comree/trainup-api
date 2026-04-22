<?php

class PasswordHasher {
    
    /**
     * Hash a password string
     * @param string $password The plain text password to hash
     * @return string The hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
}

?>
