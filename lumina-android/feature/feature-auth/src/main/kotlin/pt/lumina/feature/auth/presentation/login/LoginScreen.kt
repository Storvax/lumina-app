package pt.lumina.feature.auth.presentation.login

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.SpanStyle
import androidx.compose.ui.text.buildAnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.withStyle
import androidx.compose.ui.unit.dp
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.ui.focus.FocusDirection
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.platform.LocalUriHandler
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.hilt.navigation.compose.hiltViewModel
import pt.lumina.core.ui.components.LuminaAutofillHint
import pt.lumina.core.ui.components.LuminaButton
import pt.lumina.core.ui.components.LuminaTextField
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.RoseRose100
import pt.lumina.core.ui.theme.RoseRose500
import pt.lumina.core.ui.theme.SlateSlate500
import pt.lumina.core.ui.theme.SlateSlate600
import pt.lumina.core.ui.theme.SlateSlate800

/**
 * Ecrã de login da Lumina — redesenhado sem glassmorphism.
 *
 * Layout limpo e direto, sem camadas de blur, para máxima clareza
 * durante um momento que pode ser emocionalmente sensível para o utilizador.
 *
 * Fluxo:
 * 1. Utilizador preenche email + password
 * 2. Clica "Entrar"
 * 3. ViewModel chama a API de autenticação
 * 4. Sucesso → callback onLoginSuccess
 * 5. Erro → mensagem acolhedora inline (sem card extra)
 *
 * @param onLoginSuccess Navegar após autenticação bem-sucedida
 * @param onBack Voltar ao ecrã de boas-vindas
 */
@Composable
fun LoginScreen(
    onLoginSuccess: () -> Unit,
    onBack: (() -> Unit)? = null,
    viewModel: LoginViewModel = hiltViewModel(),
) {
    val uiState by viewModel.uiState.collectAsState()
    val formState by viewModel.formState.collectAsState()
    val focusManager = LocalFocusManager.current
    val uriHandler = LocalUriHandler.current

    // Navegar quando login bem-sucedido
    LaunchedEffect(uiState) {
        if (uiState is LoginUiState.Success) {
            onLoginSuccess()
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(Color.White)
            .verticalScroll(rememberScrollState())
            .padding(horizontal = 32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Spacer(modifier = Modifier.height(24.dp))

        // Botão de voltar (se existir navegação para trás)
        if (onBack != null) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                IconButton(
                    onClick = onBack,
                    modifier = Modifier.size(44.dp),
                ) {
                    Text(
                        text = "←",
                        style = MaterialTheme.typography.titleLarge,
                        color = SlateSlate600,
                    )
                }
            }
            Spacer(modifier = Modifier.height(8.dp))
        } else {
            Spacer(modifier = Modifier.height(32.dp))
        }

        // Header
        Text(
            text = "Bem-vindo de volta",
            style = MaterialTheme.typography.headlineMedium.copy(
                fontWeight = FontWeight.Bold,
            ),
            color = SlateSlate800,
            textAlign = TextAlign.Start,
            modifier = Modifier.fillMaxWidth(),
        )

        Spacer(modifier = Modifier.height(6.dp))

        Text(
            text = "Entra na tua conta para continuares a tua jornada.",
            style = MaterialTheme.typography.bodyMedium,
            color = SlateSlate500,
            modifier = Modifier.fillMaxWidth(),
        )

        Spacer(modifier = Modifier.height(40.dp))

        // Campo de email
        LuminaTextField(
            label = "Email",
            value = formState.email,
            onValueChange = { viewModel.updateEmail(it) },
            isValid = formState.isEmailValid,
            placeholder = "alex@exemplo.com",
            modifier = Modifier.fillMaxWidth(),
            keyboardOptions = KeyboardOptions(
                keyboardType = KeyboardType.Email,
                imeAction = ImeAction.Next,
            ),
            keyboardActions = KeyboardActions(
                onNext = { focusManager.moveFocus(FocusDirection.Down) },
            ),
            autofillHint = LuminaAutofillHint.EmailAddress,
        )

        Spacer(modifier = Modifier.height(18.dp))

        // Campo de password — ao pressionar Done faz login diretamente
        LuminaTextField(
            label = "Password",
            value = formState.password,
            onValueChange = { viewModel.updatePassword(it) },
            isPassword = true,
            isValid = formState.isPasswordValid,
            modifier = Modifier.fillMaxWidth(),
            keyboardOptions = KeyboardOptions(
                keyboardType = KeyboardType.Password,
                imeAction = ImeAction.Done,
            ),
            keyboardActions = KeyboardActions(
                onDone = {
                    focusManager.clearFocus()
                    if (formState.isFormValid) viewModel.login()
                },
            ),
            autofillHint = LuminaAutofillHint.Password,
        )

        Spacer(modifier = Modifier.height(10.dp))

        // Link "Esqueci a password"
        Text(
            text = "Esqueceste a password? Contacta o suporte.",
            style = MaterialTheme.typography.labelMedium,
            color = IndigoIndigo500,
            modifier = Modifier
                .align(Alignment.End)
                .padding(vertical = 4.dp),
        )

        // Mensagem de erro inline (sem blur, sem card)
        if (uiState is LoginUiState.Error) {
            Spacer(modifier = Modifier.height(16.dp))
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(
                        color = RoseRose100,
                        shape = androidx.compose.foundation.shape.RoundedCornerShape(12.dp),
                    )
                    .padding(horizontal = 16.dp, vertical = 12.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Text(
                    text = "⚠️  ${(uiState as LoginUiState.Error).message}",
                    style = MaterialTheme.typography.bodySmall,
                    color = RoseRose500,
                )
            }
        }

        Spacer(modifier = Modifier.height(32.dp))

        // Botão de login
        LuminaButton(
            text = if (uiState is LoginUiState.Loading) "A entrar..." else "Entrar",
            onClick = { viewModel.login() },
            enabled = formState.isFormValid && uiState !is LoginUiState.Loading,
            isLoading = uiState is LoginUiState.Loading,
            variant = "primary",
            modifier = Modifier.fillMaxWidth(),
        )

        Spacer(modifier = Modifier.height(32.dp))

        // Nota de privacidade
        Text(
            text = "A Lumina usa a tua informação de forma segura e privada.",
            style = MaterialTheme.typography.labelSmall,
            color = SlateSlate500,
            textAlign = TextAlign.Center,
        )

        Spacer(modifier = Modifier.height(16.dp))

        // Link de emergência — acessível e sempre visível (guideline CLAUDE.md)
        Text(
            text = buildAnnotatedString {
                withStyle(SpanStyle(color = SlateSlate500)) {
                    append("A precisar de ajuda imediata? ")
                }
                withStyle(SpanStyle(color = RoseRose500, fontWeight = FontWeight.Bold)) {
                    append("Liga 112")
                }
            },
            style = MaterialTheme.typography.labelMedium,
            textAlign = TextAlign.Center,
            modifier = Modifier
                .fillMaxWidth()
                .clickable { uriHandler.openUri("tel:112") }
                .padding(vertical = 14.dp),
        )

        Spacer(modifier = Modifier.height(16.dp))
    }
}
