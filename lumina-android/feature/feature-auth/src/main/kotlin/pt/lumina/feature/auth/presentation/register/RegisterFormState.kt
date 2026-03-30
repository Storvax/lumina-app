package pt.lumina.feature.auth.presentation.register

/**
 * Estado do formulário de registro.
 */
data class RegisterFormState(
    val name: String = "",
    val email: String = "",
    val password: String = "",
    val passwordConfirmation: String = "",
    val isNameValid: Boolean = false,
    val isEmailValid: Boolean = false,
    val isPasswordValid: Boolean = false,
    val passwordsMatch: Boolean = false,
) {
    val isFormValid: Boolean = isNameValid && isEmailValid && isPasswordValid && passwordsMatch

    val passwordStrength: Int
        get() = when {
            password.isEmpty() -> 0
            password.length < 8 -> 1
            !password.any { it.isUpperCase() } || !password.any { it.isDigit() } -> 2
            !password.any { !it.isLetterOrDigit() } -> 3
            else -> 4
        }

    companion object {
        fun isValidEmail(email: String): Boolean =
            email.matches(Regex("^[A-Za-z0-9+_.-]+@([A-Za-z0-9.-]+\\.[A-Za-z]{2,})$"))

        fun isValidPassword(password: String): Boolean =
            password.length >= 8 &&
            password.any { it.isUpperCase() } &&
            password.any { it.isDigit() } &&
            password.any { !it.isLetterOrDigit() }

        fun isValidName(name: String): Boolean = name.length >= 2
    }
}
