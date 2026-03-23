package pt.lumina.feature.auth.presentation.login

import pt.lumina.core.domain.model.User

/**
 * Estados da tela de login.
 * Representa os diferentes estados em que a UI pode estar durante o fluxo de autenticação.
 */
sealed class LoginUiState {
    /**
     * Estado inicial - nenhuma ação em progresso
     */
    object Idle : LoginUiState()

    /**
     * Login em progresso - requisição à API enviada
     */
    object Loading : LoginUiState()

    /**
     * Login bem-sucedido - token guardado, pronto para navegar
     */
    data class Success(val user: User) : LoginUiState()

    /**
     * Erro durante login - mostrar mensagem ao utilizador
     */
    data class Error(val message: String) : LoginUiState()
}
