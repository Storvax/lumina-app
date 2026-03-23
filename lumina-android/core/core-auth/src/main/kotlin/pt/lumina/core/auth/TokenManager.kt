package pt.lumina.core.auth

import android.content.Context
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey

/**
 * TokenManager: Gerir tokens Sanctum de forma segura.
 * Usa EncryptedSharedPreferences para armazenar dados sensíveis.
 */
class TokenManager(context: Context) {
    private val masterKey = MasterKey.Builder(context)
        .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
        .build()

    private val encryptedSharedPreferences = EncryptedSharedPreferences.create(
        context,
        "lumina_auth",
        masterKey,
        EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
        EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
    )

    companion object {
        private const val KEY_TOKEN = "auth_token"
        private const val KEY_USER_ID = "user_id"
    }

    /**
     * Guardar token e user ID.
     */
    fun saveToken(token: String, userId: Int) {
        encryptedSharedPreferences.edit().apply {
            putString(KEY_TOKEN, token)
            putInt(KEY_USER_ID, userId)
            apply()
        }
    }

    /**
     * Recuperar token.
     */
    fun getToken(): String? {
        return encryptedSharedPreferences.getString(KEY_TOKEN, null)
    }

    /**
     * Recuperar user ID.
     */
    fun getUserId(): Int {
        return encryptedSharedPreferences.getInt(KEY_USER_ID, -1)
    }

    /**
     * Limpar token (logout).
     */
    fun clearToken() {
        encryptedSharedPreferences.edit().apply {
            remove(KEY_TOKEN)
            remove(KEY_USER_ID)
            apply()
        }
    }

    /**
     * Verificar se token existe.
     */
    fun hasToken(): Boolean {
        return getToken() != null
    }
}
