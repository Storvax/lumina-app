package pt.lumina.feature.auth.presentation.register

import pt.lumina.core.domain.model.User

/**
 * Estados da tela de registro.
 */
sealed class RegisterUiState {
    object Idle : RegisterUiState()
    object Loading : RegisterUiState()
    data class Success(val user: User) : RegisterUiState()
    data class Error(val message: String) : RegisterUiState()
}
