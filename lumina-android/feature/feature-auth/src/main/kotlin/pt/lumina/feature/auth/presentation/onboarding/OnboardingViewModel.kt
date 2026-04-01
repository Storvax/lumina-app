package pt.lumina.feature.auth.presentation.onboarding

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import pt.lumina.core.network.api.LuminaApi
import javax.inject.Inject

/**
 * ViewModel do onboarding.
 *
 * Gere os 3 passos (intent → mood → preference) e submete para a API.
 * Devolve o redirect sugerido com base na intenção do utilizador.
 */
@HiltViewModel
class OnboardingViewModel @Inject constructor(
    private val luminaApi: LuminaApi,
) : ViewModel() {

    private val _uiState = MutableStateFlow<OnboardingUiState>(OnboardingUiState.Idle)
    val uiState: StateFlow<OnboardingUiState> = _uiState.asStateFlow()

    // Passo atual (0-indexed: 0 = intent, 1 = mood, 2 = preference)
    private val _currentStep = MutableStateFlow(0)
    val currentStep: StateFlow<Int> = _currentStep.asStateFlow()

    // Respostas selecionadas
    private val _selectedIntent = MutableStateFlow<String?>(null)
    val selectedIntent: StateFlow<String?> = _selectedIntent.asStateFlow()

    private val _selectedMood = MutableStateFlow<String?>(null)
    val selectedMood: StateFlow<String?> = _selectedMood.asStateFlow()

    private val _selectedPreference = MutableStateFlow<String?>(null)
    val selectedPreference: StateFlow<String?> = _selectedPreference.asStateFlow()

    fun selectIntent(intent: String) {
        _selectedIntent.value = intent
    }

    fun selectMood(mood: String) {
        _selectedMood.value = mood
    }

    fun selectPreference(preference: String) {
        _selectedPreference.value = preference
    }

    fun nextStep() {
        if (_currentStep.value < 2) {
            _currentStep.value++
        }
    }

    fun previousStep() {
        if (_currentStep.value > 0) {
            _currentStep.value--
        }
    }

    /** Verifica se o passo atual tem seleção válida para prosseguir */
    fun canProceed(): Boolean = when (_currentStep.value) {
        0 -> _selectedIntent.value != null
        1 -> _selectedMood.value != null
        2 -> _selectedPreference.value != null
        else -> false
    }

    fun submit() {
        val intent = _selectedIntent.value ?: return
        val mood = _selectedMood.value ?: return
        val preference = _selectedPreference.value ?: return

        viewModelScope.launch {
            try {
                _uiState.value = OnboardingUiState.Loading

                val response = luminaApi.submitOnboarding(
                    intent = intent,
                    mood = mood,
                    preference = preference,
                )

                val redirect = (response.data as? Map<*, *>)?.get("redirect")?.toString() ?: "dashboard"
                _uiState.value = OnboardingUiState.Success(redirect)
            } catch (e: Exception) {
                // Em caso de erro de rede, avançar para dashboard na mesma —
                // o onboarding é informativo e não deve bloquear o utilizador
                _uiState.value = OnboardingUiState.Success("dashboard")
            }
        }
    }
}
