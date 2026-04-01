package pt.lumina.feature.auth.presentation.onboarding

/**
 * Estados do fluxo de onboarding (3 passos).
 */
sealed class OnboardingUiState {
    object Idle : OnboardingUiState()
    object Loading : OnboardingUiState()
    /** Onboarding submetido — redirecionar com base no redirect recebido */
    data class Success(val redirect: String) : OnboardingUiState()
    data class Error(val message: String) : OnboardingUiState()
}
