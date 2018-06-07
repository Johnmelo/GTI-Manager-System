<?php
namespace App\Model;

class PasswordUtil {

    // Defaults
    const OPS_LIMIT = SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE;
    const MEM_LIMIT = SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE;


    public static function hash($password) {
        return sodium_crypto_pwhash_str($password, self::OPS_LIMIT, self::MEM_LIMIT);
    }

    public static function verify($password, $hash) {
        if (sodium_crypto_pwhash_str_verify($hash, $password)) {
            sodium_memzero($password);
            return true;
        } else {
            sodium_memzero($password);
            return false;
        }
    }
}
