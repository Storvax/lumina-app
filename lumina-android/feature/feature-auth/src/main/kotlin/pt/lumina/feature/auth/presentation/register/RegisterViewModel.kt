package pt.lumina.feature.auth.presentation.register

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

@HiltViewModel
class RegisterViewModel @Inject constructor(
    private val luminaApi: LuminaApi,
    private val tokenManager: TokenManager,
) : ViewModel() {

    private val _uiState = MutableStateFlow<RegisterUiState>(RegisterUiState.Idle)
    val uiState: StateFlow<RegisterUiState> = _uiState.asStateFlow()

    private val _formState = MutableStateFlow(RegisterFormState())
    val formState: StateFlow<RegisterFormState> = _formState.asStateFlow()

    fun updateName(name: String) {
        val isValid = RegisterFormState.isValidName(name)
        _formState.update { it.copy(name = name, isNameValid = isValid) }
    }

    fun updateEmail(email: String) {
        val isValid = RegisterFormState.isValidEmail(email)
        _formState.update { it.copy(email = email, isEmailValid = isValid) }
    }

    fun updatePassword(password: String) {
        val isValid = RegisterFormState.isValidPassword(password)
        _formState.update {
            val newState = it.copy(password = password, isPasswordValid = isValid)
            newState.copy(passwordsMatch = newState.password == newState.passwordConfirmation)
        }
    }

    fun updatePasswordConfirmation(passwordConfirmation: String) {
        _formState.update {
            it.copy(
                passwordConfirmation = passwordConfirmation,
                passwordsMatch = it.password == passwordConfirmation
            )
        }
    }

    fun register() {
        val form = _formState.value
        if (!form.isFormValid) return

        viewModelScope.launch {
            try {
                _uiState.value = RegisterUiState.Loading

                val response = luminaApi.register(
                    name = form.name,
                    email = form.email,
                    password = form.password,
                    password_confirmation = form.passwordConfirmation
                )

                if (response.token != null && response.user != null) {
                    val userId = try {
                        (response.user as? Map<*, *>)?.get("id").toString().toDouble().toInt()
                    } catch (e: Exception) {
                        -1
                    }

                    tokenManager.saveToken(response.token!!, userId)

                    // TODO: Map properly when domain models are stable
                    val user = User(
                        id = userId,
                        name = (response.user as? Map<*, *>)?.get("name").toString(),
                        email = (response.user as? Map<*, *>)?.get("email").toString(),
                        pseudonym = (response.user as? Map<*, *>)?.get("pseudonym").toString(),
                        role = (response.user as? Map<*, *>)?.get("role").toString(),
                        created_at = (response.user as? Map<*, *>)?.get("created_at").toString(),
                        updated_at = (response.user as? Map<*, *>)?.get("updated_at").toString(),
                    )

                    _uiState.value = RegisterUiState.Success(user)
                } else {
                    val errorMsg = response.error?.message ?: "Erro ao criar conta."
                    _uiState.value = RegisterUiState.Error(errorMsg)
                }
            } catch (e: Exception) {
                _uiState.value = RegisterUiState.Error(e.message ?: "Ocorreu um erro inesperado")
            }
        }
    }
}
