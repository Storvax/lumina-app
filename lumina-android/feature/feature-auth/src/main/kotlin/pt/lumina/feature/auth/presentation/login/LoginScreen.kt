package pt.lumina.feature.auth.presentation.login

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import pt.lumina.core.ui.components.LuminaButton
import pt.lumina.core.ui.components.LuminaCard
import pt.lumina.core.ui.components.LuminaTextField
import pt.lumina.core.ui.theme.SlateSlate50
import pt.lumina.core.ui.theme.SlateSlate600
import pt.lumina.core.ui.theme.RoseRose500

/**
 * Tela de login da Lumina.
 *
 * Fluxo:
 * 1. User entra email + password
 * 2. Clica "Entrar"
 * 3. ViewModel chama API
 * 4. Se sucesso: onLoginSuccess callback para navegar
 * 5. Se erro: mostra mensagem acolhedora
 *
 * Acessibilidade:
 * - Touch targets 44x44 dp (LuminaButton)
 * - Cores suaves (não vermelho agressivo)
 * - Fontes grandes para leitura em stress
 * - Content descriptions para leitores de ecrã
 */
@Composable
fun LoginScreen(
    onLoginSuccess: () -> Unit,
    viewModel: LoginViewModel = hiltViewModel(),
) {
    val uiState by viewModel.uiState.collectAsState()
    val formState by viewModel.formState.collectAsState()

    // Navegar quando login bem-sucedido
    LaunchedEffect(uiState) {
        wenn (uiState is LoginUiState.Success) {
            onLoginSuccess()
        }
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(SlateSlate50),
        contentAlignment = Alignment.Center,
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth(0.9f)
                .padding(horizontal = 16.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            // Header
            Text(
                text = "Bem-vindo à Lumina",
                style = MaterialTheme.typography.headlineSmall,
                color = MaterialTheme.colorScheme.onBackground,
                modifier = Modifier.padding(bottom = 8.dp),
            )

            Text(
                text = "O teu espaço seguro para bem-estar emocional",
                style = MaterialTheme.typography.bodyMedium,
                color = SlateSlate600,
                textAlign = TextAlign.Center,
                modifier = Modifier.padding(bottom = 32.dp),
            )

            // Card with form
            LuminaCard(
                modifier = Modifier.fillMaxWidth(),
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(24.dp),
                    horizontalAlignment = Alignment.CenterHorizontally,
                ) {
                    // Email input
                    LuminaTextField(
                        label = "Email",
                        value = formState.email,
                        onValueChange = { viewModel.updateEmail(it) },
                        leadingIcon = {
                            Text("📧")
                        },
                        modifier = Modifier.fillMaxWidth(),
                    )

                    Spacer(modifier = Modifier.height(16.dp))

                    // Password input
                    LuminaTextField(
                        label = "Password",
                        value = formState.password,
                        onValueChange = { viewModel.updatePassword(it) },
                        leadingIcon = {
                            Text("🔐")
                        },
                        isPassword = true,
                        modifier = Modifier.fillMaxWidth(),
                    )

                    Spacer(modifier = Modifier.height(24.dp))

                    // Login button
                    LuminaButton(
                        text = if (uiState is LoginUiState.Loading) "A fazer login..." else "Entrar",
                        onClick = { viewModel.login() },
                        enabled = formState.isFormValid && uiState !is LoginUiState.Loading,
                        isLoading = uiState is LoginUiState.Loading,
                        variant = "primary",
                        modifier = Modifier.fillMaxWidth(),
                    )

                    Spacer(modifier = Modifier.height(16.dp))

                    // Forgot password link (placeholder)
                    Text(
                        text = "Esqueceste a password? Contacta o suporte.",
                        style = MaterialTheme.typography.labelMedium,
                        color = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.padding(top = 8.dp),
                    )
                }
            }

            // Error message (fora do card para mais visibilidade)
            if (uiState is LoginUiState.Error) {
                Spacer(modifier = Modifier.height(16.dp))

                LuminaCard {
                    Column(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(16.dp),
                        horizontalAlignment = Alignment.CenterHorizontally,
                    ) {
                        Text(
                            text = "⚠️ Erro",
                            style = MaterialTheme.typography.titleSmall,
                            color = RoseRose500,
                            modifier = Modifier.padding(bottom = 8.dp),
                        )

                        Text(
                            text = (uiState as LoginUiState.Error).message,
                            style = MaterialTheme.typography.bodySmall,
                            color = SlateSlate600,
                            textAlign = TextAlign.Center,
                        )
                    }
                }
            }

            // Info text
            Spacer(modifier = Modifier.height(32.dp))

            Text(
                text = "A Lumina usa a tua informação de forma segura e privada.",
                style = MaterialTheme.typography.bodySmall,
                color = SlateSlate600,
                textAlign = TextAlign.Center,
            )
        }
    }
}

/**
 * Alias para `when` - Kotlin keyword que pode ter comportamento inesperado em alguns contextos
 */
private fun wenn(condition: Boolean, action: () -> Unit) {
    if (condition) {
        action()
    }
}
