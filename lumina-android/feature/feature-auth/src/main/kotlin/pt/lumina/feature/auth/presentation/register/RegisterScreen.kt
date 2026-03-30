package pt.lumina.feature.auth.presentation.register

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
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
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.SpanStyle
import androidx.compose.ui.text.buildAnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.withStyle
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.ui.focus.FocusDirection
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.platform.LocalUriHandler
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import pt.lumina.core.ui.components.LuminaAutofillHint
import pt.lumina.core.ui.components.LuminaButton
import pt.lumina.core.ui.components.LuminaTextField
import pt.lumina.core.ui.theme.AmberAmber500
import pt.lumina.core.ui.theme.EmeraldEmerald500
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.RoseRose100
import pt.lumina.core.ui.theme.RoseRose500
import pt.lumina.core.ui.theme.SlateSlate100
import pt.lumina.core.ui.theme.SlateSlate400
import pt.lumina.core.ui.theme.SlateSlate500
import pt.lumina.core.ui.theme.SlateSlate800

/**
 * Ecrã de criação de conta — redesenhado ao estilo do Ahead.
 *
 * Princípios:
 * - Hierarquia tipográfica forte: headline Nunito Black 36sp vs body DM Sans 14sp
 * - Campos outlined (sem fundo preenchido) com ✓ de validação trailing
 * - Labels uppercase com tracking largo (estilo Flinch)
 * - Indicador de força da password limpo (4 segmentos, 3dp)
 * - Erro inline suave, sem cards com blur
 *
 * @param onRegisterSuccess Navegar para o onboarding
 * @param onBack            Voltar ao ecrã de boas-vindas
 * @param onLoginClick      Navegar para login (link "Já tens conta?")
 */
@Composable
fun RegisterScreen(
    onRegisterSuccess: () -> Unit,
    onBack: () -> Unit,
    onLoginClick: () -> Unit = {},
    viewModel: RegisterViewModel = hiltViewModel(),
) {
    val uiState by viewModel.uiState.collectAsState()
    val formState by viewModel.formState.collectAsState()
    val focusManager = LocalFocusManager.current
    val uriHandler = LocalUriHandler.current

    LaunchedEffect(uiState) {
        if (uiState is RegisterUiState.Success) {
            onRegisterSuccess()
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(Color.White)
            .verticalScroll(rememberScrollState()),
    ) {
        // ─── Header ──────────────────────────────────────────────────────────
        Spacer(modifier = Modifier.height(20.dp))

        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 20.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            IconButton(onClick = onBack, modifier = Modifier.size(44.dp)) {
                Text(
                    text = "←",
                    fontSize = 22.sp,
                    color = SlateSlate500,
                )
            }
        }

        Spacer(modifier = Modifier.height(12.dp))

        // ─── Headline forte — hierarquia estilo Ahead ─────────────────────────
        Column(modifier = Modifier.padding(horizontal = 28.dp)) {
            Text(
                text = "Começar jornada.",
                style = MaterialTheme.typography.headlineMedium,   // Nunito Black 32sp
                color = SlateSlate800,
            )

            Spacer(modifier = Modifier.height(6.dp))

            // Subtítulo com link para login — espelha a versão web
            Text(
                text = buildAnnotatedString {
                    withStyle(SpanStyle(color = SlateSlate500)) {
                        append("Já tens uma conta? ")
                    }
                    withStyle(SpanStyle(color = IndigoIndigo500, fontWeight = FontWeight.Bold)) {
                        append("Entrar aqui")
                    }
                },
                style = MaterialTheme.typography.bodyMedium,
                modifier = Modifier.clickable(onClick = onLoginClick),
            )

            // ─── Formulário ───────────────────────────────────────────────────
            Spacer(modifier = Modifier.height(36.dp))

            // Nome — label empática, exatamente como na versão web
            LuminaTextField(
                label = "Como gostarias de ser chamado(a)?",
                value = formState.name,
                onValueChange = { viewModel.updateName(it) },
                isValid = formState.isNameValid,
                placeholder = "O teu nome ou apelido",
                modifier = Modifier.fillMaxWidth(),
                keyboardOptions = KeyboardOptions(
                    keyboardType = KeyboardType.Text,
                    imeAction = ImeAction.Next,
                ),
                keyboardActions = KeyboardActions(
                    onNext = { focusManager.moveFocus(FocusDirection.Down) },
                ),
                autofillHint = LuminaAutofillHint.PersonFirstName,
            )

            Spacer(modifier = Modifier.height(18.dp))

            // Email
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

            // Password
            LuminaTextField(
                label = "Password",
                value = formState.password,
                onValueChange = { viewModel.updatePassword(it) },
                isPassword = true,
                isValid = formState.isPasswordValid,
                modifier = Modifier.fillMaxWidth(),
                keyboardOptions = KeyboardOptions(
                    keyboardType = KeyboardType.Password,
                    imeAction = ImeAction.Next,
                ),
                keyboardActions = KeyboardActions(
                    onNext = { focusManager.moveFocus(FocusDirection.Down) },
                ),
                autofillHint = LuminaAutofillHint.NewPassword,
            )

            // Indicador de força
            if (formState.password.isNotEmpty()) {
                Spacer(modifier = Modifier.height(8.dp))
                PasswordStrengthBar(strength = formState.passwordStrength)
            }

            // Hint de requisitos — só aparece quando campo ativo mas não válido
            if (formState.password.isNotEmpty() && !formState.isPasswordValid) {
                Spacer(modifier = Modifier.height(6.dp))
                Text(
                    text = "8+ caracteres, 1 maiúscula, 1 número, 1 símbolo",
                    style = MaterialTheme.typography.labelSmall.copy(fontSize = 11.sp),
                    color = SlateSlate400,
                )
            }

            Spacer(modifier = Modifier.height(18.dp))

            // Confirmação de password
            LuminaTextField(
                label = "Confirmar password",
                value = formState.passwordConfirmation,
                onValueChange = { viewModel.updatePasswordConfirmation(it) },
                isPassword = true,
                isValid = formState.passwordsMatch && formState.passwordConfirmation.isNotEmpty(),
                modifier = Modifier.fillMaxWidth(),
                keyboardOptions = KeyboardOptions(
                    keyboardType = KeyboardType.Password,
                    imeAction = ImeAction.Done,
                ),
                keyboardActions = KeyboardActions(
                    onDone = {
                        focusManager.clearFocus()
                        if (formState.isFormValid) viewModel.register()
                    },
                ),
                autofillHint = LuminaAutofillHint.NewPassword,
            )

            // Erro de passwords não coincidem
            if (formState.passwordConfirmation.isNotEmpty() && !formState.passwordsMatch) {
                Spacer(modifier = Modifier.height(6.dp))
                Text(
                    text = "As passwords não coincidem.",
                    style = MaterialTheme.typography.labelSmall.copy(fontSize = 11.sp),
                    color = RoseRose500,
                )
            }

            // ─── Erro inline ──────────────────────────────────────────────────
            if (uiState is RegisterUiState.Error) {
                Spacer(modifier = Modifier.height(16.dp))
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(RoseRose100, RoundedCornerShape(10.dp))
                        .padding(horizontal = 14.dp, vertical = 11.dp),
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Text(
                        text = "⚠️  ${(uiState as RegisterUiState.Error).message}",
                        style = MaterialTheme.typography.bodySmall,
                        color = RoseRose500,
                    )
                }
            }

            Spacer(modifier = Modifier.height(32.dp))

            // ─── Botão principal ──────────────────────────────────────────────
            LuminaButton(
                text = if (uiState is RegisterUiState.Loading) "A criar conta…" else "Criar conta",
                onClick = { viewModel.register() },
                enabled = formState.isFormValid && uiState !is RegisterUiState.Loading,
                isLoading = uiState is RegisterUiState.Loading,
                variant = "primary",
                modifier = Modifier.fillMaxWidth(),
            )

            Spacer(modifier = Modifier.height(28.dp))

            // ─── Nota de privacidade ──────────────────────────────────────────
            Text(
                text = "A tua identidade é mantida estritamente privada.",
                style = MaterialTheme.typography.labelSmall.copy(fontSize = 11.sp),
                color = SlateSlate400,
                textAlign = TextAlign.Center,
                modifier = Modifier.fillMaxWidth(),
            )

            Spacer(modifier = Modifier.height(16.dp))

            // Link de emergência — sempre visível em ecrãs de entrada
            Text(
                text = buildAnnotatedString {
                    withStyle(SpanStyle(color = SlateSlate500)) {
                        append("Em momento de crise aguda? ")
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

            Spacer(modifier = Modifier.height(20.dp))
        }
    }
}

// ─── Barra de força da password ──────────────────────────────────────────────

/**
 * 4 segmentos animados — Rose (fraca) → Amber → Emerald (excelente).
 * Minimalista: 3dp de altura, sem sombra, transição 200ms.
 */
@Composable
private fun PasswordStrengthBar(strength: Int) {
    val strengthColors = listOf(RoseRose500, AmberAmber500, EmeraldEmerald500, EmeraldEmerald500)
    val strengthLabels = listOf("Muito fraca", "Razoável", "Boa", "Excelente")
    val activeColor = if (strength in 1..4) strengthColors[strength - 1] else SlateSlate100
    val label = if (strength in 1..4) strengthLabels[strength - 1] else ""

    Row(
        modifier = Modifier.fillMaxWidth(),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(4.dp),
    ) {
        repeat(4) { index ->
            val segmentColor by animateColorAsState(
                targetValue = if (index < strength) activeColor else SlateSlate100,
                animationSpec = tween(200),
                label = "seg_$index",
            )
            Box(
                modifier = Modifier
                    .weight(1f)
                    .height(3.dp)
                    .clip(RoundedCornerShape(2.dp))
                    .background(segmentColor),
            )
        }
        Spacer(modifier = Modifier.width(8.dp))
        Text(
            text = label,
            style = MaterialTheme.typography.labelSmall.copy(fontSize = 10.sp),
            color = activeColor,
        )
    }
}
