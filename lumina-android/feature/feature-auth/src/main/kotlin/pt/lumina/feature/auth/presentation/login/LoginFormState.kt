package pt.lumina.feature.auth.presentation.login

/**
 * Estado do formulário de login.
 * Representa os valores dos inputs e o estado de validação em tempo real.
 */
data class LoginFormState(
    val email: String = "",
    val password: String = "",
    val isEmailValid: Boolean = false,
    val isPasswordValid: Boolean = false,
) {
    /**
     * Botão está habilitado quando email E password são válidos
     */
    val isFormValid: Boolean = isEmailValid && isPasswordValid

    companion object {
        /**
         * Validar email com regex RFC 5322 simplificado
         */
        fun isValidEmail(email: String): Boolean =
            email.matches(Regex("^[A-Za-z0-9+_.-]+@([A-Za-z0-9.-]+\\.[A-Za-z]{2,})$"))

        /**
         * Validar password - mínimo 8 caracteres
         */
        fun isValidPassword(password: String): Boolean =
            password.length >= 8
    }
}
