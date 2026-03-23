package pt.lumina.feature.auth.presentation.login

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import pt.lumina.core.auth.TokenManager
import pt.lumina.core.domain.model.User
import pt.lumina.core.network.api.LuminaApi
import javax.inject.Inject

/**
 * ViewModel para a tela de login.
 *
 * Responsabilidades:
 * - Gerenciar estado da UI (loading, erro, sucesso)
 * - Gerenciar estado do formulário (email, password)
 * - Chamar API de login e guardar token
 * - Tratar erros de forma amigável (PT-PT)
 */
@HiltViewModel
class LoginViewModel @Inject constructor(
    private val luminaApi: LuminaApi,
    private val tokenManager: TokenManager,
) : ViewModel() {

    // Estado da UI (loading, erro, sucesso)
    private val _uiState = MutableStateFlow<LoginUiState>(LoginUiState.Idle)
    val uiState: StateFlow<LoginUiState> = _uiState.asStateFlow()

    // Estado do formulário (email, password, validação)
    private val _formState = MutableStateFlow(LoginFormState())
    val formState: StateFlow<LoginFormState> = _formState.asStateFlow()

    /**
     * Atualizar email e re-validar formulário
     */
    fun updateEmail(email: String) {
        val isValid = LoginFormState.isValidEmail(email)
        _formState.update { form ->
            form.copy(
                email = email,
                isEmailValid = isValid,
            )
        }
    }

    /**
     * Atualizar password e re-validar formulário
     */
    fun updatePassword(password: String) {
        val isValid = LoginFormState.isValidPassword(password)
        _formState.update { form ->
            form.copy(
                password = password,
                isPasswordValid = isValid,
            )
        }
    }

    /**
     * Fazer login:
     * 1. Chamar API com email/password
     * 2. Se sucesso: guardar token + navegar
     * 3. Se erro: mostrar mensagem acolhedora em PT-PT
     */
    fun login() {
        val form = _formState.value

        // Validação básica
        if (!form.isFormValid) {
            _uiState.value = LoginUiState.Error("Por favor, preenche todos os campos corretamente")
            return
        }

        viewModelScope.launch {
            try {
                _uiState.value = LoginUiState.Loading

                // Chamar API - não precisa de token (AuthInterceptor deixa passar requests públicas)
                val response = luminaApi.login(
                    email = form.email,
                    password = form.password,
                )

                // Verificar resposta
                if (response.token != null && response.user != null) {
                    // Extrair userId do user object
                    // TODO: Melhorar quando temos response model tipado
                    val userId = try {
                        (response.user as? Map<*, *>)?.get("id").toString().toInt()
                    } catch (e: Exception) {
                        -1
                    }

                    // Guardar token em EncryptedSharedPreferences
                    tokenManager.saveToken(response.token!!, userId)

                    // User model para success state
                    // TODO: Usar response.user tipado quando disponível
                    val user = User(
                        id = userId,
                        name = (response.user as? Map<*, *>)?.get("name").toString(),
                        email = (response.user as? Map<*, *>)?.get("email").toString(),
                        pseudonym = (response.user as? Map<*, *>)?.get("pseudonym").toString(),
                        avatar = (response.user as? Map<*, *>)?.get("avatar") as? String,
                        flames = ((response.user as? Map<*, *>)?.get("flames") as? Number)?.toInt() ?: 0,
                        flame_level = (response.user as? Map<*, *>)?.get("flame_level") as? String,
                        current_streak = ((response.user as? Map<*, *>)?.get("current_streak") as? Number)?.toInt() ?: 1,
                        role = (response.user as? Map<*, *>)?.get("role").toString(),
                        onboarding_completed = (response.user as? Map<*, *>)?.get("onboarding_completed") as? Boolean ?: false,
                        bio = (response.user as? Map<*, *>)?.get("bio") as? String,
                        created_at = (response.user as? Map<*, *>)?.get("created_at").toString(),
                        updated_at = (response.user as? Map<*, *>)?.get("updated_at").toString(),
                    )

                    _uiState.value = LoginUiState.Success(user)
                } else {
                    // API retornou resposta vazia
                    val errorMsg = response.error?.message ?: "Erro ao fazer login. Tenta novamente."
                    _uiState.value = LoginUiState.Error(errorMsg)
                }
            } catch (e: Exception) {
                // Tratar diferentes tipos de erro com mensagens amigáveis em PT-PT
                val errorMessage = when {
                    e.message?.contains("401") == true -> "Credenciais incorretas. Tenta novamente."
                    e.message?.contains("422") == true -> "Email ou password inválido."
                    e.message?.contains("Network") == true -> "Sem conexão. Verifica tua internet."
                    e.message?.contains("timeout") == true -> "Pedido demorou muito. Tenta novamente."
                    else -> "Erro ao fazer login. Tenta novamente mais tarde."
                }
                _uiState.value = LoginUiState.Error(errorMessage)
            }
        }
    }

    /**
     * Fazer logout:
     * 1. Chamar API (com token automático do interceptor)
     * 2. Limpar token localmente (mesmo se API falhar)
     * 3. Mostrar success state
     */
    fun logout() {
        viewModelScope.launch {
            try {
                luminaApi.logout()  // AuthInterceptor injeta token automaticamente
            } catch (e: Exception) {
                // Ignorar erros da API - continuamos com logout local
            } finally {
                // Sempre limpar token localmente
                tokenManager.clearToken()
                _uiState.value = LoginUiState.Idle
                _formState.value = LoginFormState()
            }
        }
    }

    /**
     * Limpar mensagem de erro (para dismissar error card)
     */
    fun clearError() {
        if (_uiState.value is LoginUiState.Error) {
            _uiState.value = LoginUiState.Idle
        }
    }
}
