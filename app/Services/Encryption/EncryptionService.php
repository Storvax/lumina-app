<?php

declare(strict_types=1);

namespace App\Services\Encryption;

use App\Models\User;

/**
 * Serviço de encriptação E2E para mensagens privadas (Buddy sessions).
 *
 * Usa libsodium (nativo no PHP 8.2+) para geração de chaves,
 * encriptação assimétrica e troca de chaves Diffie-Hellman.
 * O servidor gere as chaves públicas mas NUNCA tem acesso às chaves privadas
 * em plaintext — a chave privada é encriptada com uma key derivada da password do utilizador.
 */
class EncryptionService
{
    /**
     * Gera um par de chaves (pública + privada) para o utilizador.
     * A chave privada é encriptada com o segredo fornecido (derivado da password do user).
     */
    public function generateKeyPair(User $user, string $passphrase): array
    {
        $keyPair = sodium_crypto_box_keypair();
        $publicKey = sodium_crypto_box_publickey($keyPair);
        $secretKey = sodium_crypto_box_secretkey($keyPair);

        // Encriptar a chave privada com uma chave derivada da passphrase (Argon2id)
        $salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
        $derivedKey = sodium_crypto_pwhash(
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES,
            $passphrase,
            $salt,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
        );

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encryptedSecretKey = sodium_crypto_secretbox($secretKey, $nonce, $derivedKey);

        // Compactar: salt + nonce + ciphertext
        $encryptedBundle = base64_encode($salt . $nonce . $encryptedSecretKey);

        $user->update([
            'public_key' => base64_encode($publicKey),
            'encrypted_private_key' => $encryptedBundle,
        ]);

        sodium_memzero($secretKey);
        sodium_memzero($derivedKey);

        return [
            'public_key' => base64_encode($publicKey),
            'encrypted_private_key' => $encryptedBundle,
        ];
    }

    /**
     * Encripta uma mensagem para o destinatário usando a sua chave pública.
     */
    public function encryptMessage(string $plaintext, string $recipientPublicKeyB64, string $senderSecretKeyB64): string
    {
        $recipientPublicKey = base64_decode($recipientPublicKeyB64);
        $senderSecretKey = base64_decode($senderSecretKeyB64);

        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        $keyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey($senderSecretKey, $recipientPublicKey);
        $encrypted = sodium_crypto_box($plaintext, $nonce, $keyPair);

        sodium_memzero($senderSecretKey);

        return base64_encode($nonce . $encrypted);
    }

    /**
     * Desencripta uma mensagem recebida.
     */
    public function decryptMessage(string $ciphertextB64, string $senderPublicKeyB64, string $recipientSecretKeyB64): ?string
    {
        $ciphertext = base64_decode($ciphertextB64);
        $senderPublicKey = base64_decode($senderPublicKeyB64);
        $recipientSecretKey = base64_decode($recipientSecretKeyB64);

        $nonce = substr($ciphertext, 0, SODIUM_CRYPTO_BOX_NONCEBYTES);
        $encrypted = substr($ciphertext, SODIUM_CRYPTO_BOX_NONCEBYTES);

        $keyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey($recipientSecretKey, $senderPublicKey);
        $decrypted = sodium_crypto_box_open($encrypted, $nonce, $keyPair);

        sodium_memzero($recipientSecretKey);

        return $decrypted === false ? null : $decrypted;
    }

    /**
     * Verifica se o utilizador tem chaves E2E configuradas.
     */
    public function hasKeys(User $user): bool
    {
        return !empty($user->public_key) && !empty($user->encrypted_private_key);
    }
}
