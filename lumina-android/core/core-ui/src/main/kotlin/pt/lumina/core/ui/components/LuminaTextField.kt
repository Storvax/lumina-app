package pt.lumina.core.ui.components

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.collectIsFocusedAsState
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.BasicTextField
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.ExperimentalComposeUiApi
import androidx.compose.ui.Modifier
import androidx.compose.ui.autofill.AutofillNode
import androidx.compose.ui.autofill.AutofillType
import androidx.compose.ui.draw.clip
import androidx.compose.ui.focus.onFocusChanged
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.SolidColor
import androidx.compose.ui.layout.boundsInWindow
import androidx.compose.ui.layout.onGloballyPositioned
import androidx.compose.ui.platform.LocalAutofill
import androidx.compose.ui.platform.LocalAutofillTree
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import pt.lumina.core.ui.theme.EmeraldEmerald500
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.SlateSlate100
import pt.lumina.core.ui.theme.SlateSlate400
import pt.lumina.core.ui.theme.SlateSlate50
import pt.lumina.core.ui.theme.SlateSlate500
import pt.lumina.core.ui.theme.SlateSlate800

/**
 * Tipos de autofill suportados pelo LuminaTextField.
 * Encapsula a API experimental do Compose para que os ecrãs
 * não precisem de @OptIn(ExperimentalComposeUiApi::class).
 */
enum class LuminaAutofillHint {
    None,
    EmailAddress,
    Password,
    NewPassword,
    PersonFirstName,
}

/**
 * Campo de texto Lumina — estilo outlined, inspirado no Ahead.
 *
 * Design:
 * - Fundo sempre branco (não preenchido com cinza)
 * - Border: slate-100 → indigo-500 ao focar (2dp)
 * - Label uppercase com tracking largo (estilo Flinch)
 * - Trailing icon opcional: ✓ verde quando campo é válido
 * - Placeholder subtil em slate-400
 *
 * Funcionalidades:
 * - Teclado contextual via keyboardOptions (ex: KeyboardType.Email)
 * - Autofill nativo para gestores de passwords (Google, Samsung, etc.)
 * - ImeAction configurável (Next / Done) com ações personalizadas
 *
 * @param value          Valor atual do campo
 * @param onValueChange  Callback ao mudar o texto
 * @param label          Etiqueta acima do campo (uppercase automático)
 * @param modifier       Modificador Compose opcional
 * @param isPassword     Se true, oculta os caracteres
 * @param isValid        Se true, mostra ✓ verde no trailing
 * @param leadingIcon    Ícone ou emoji opcional à esquerda
 * @param placeholder    Texto de placeholder
 * @param keyboardOptions Opções do teclado (tipo, IME action)
 * @param keyboardActions Ações do teclado (onDone, onNext, etc.)
 * @param autofillHint   Sugestão de autofill para gestores de passwords
 */
@OptIn(ExperimentalComposeUiApi::class)
@Composable
fun LuminaTextField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    isPassword: Boolean = false,
    isValid: Boolean = false,
    leadingIcon: (@Composable () -> Unit)? = null,
    placeholder: String = "Escreve aqui…",
    keyboardOptions: KeyboardOptions = KeyboardOptions.Default,
    keyboardActions: KeyboardActions = KeyboardActions.Default,
    autofillHint: LuminaAutofillHint = LuminaAutofillHint.None,
) {
    val interactionSource = remember { MutableInteractionSource() }
    val isFocused = interactionSource.collectIsFocusedAsState()

    // Mapear hint interno para AutofillType — evita expor API experimental aos ecrãs
    val autofillTypes: List<AutofillType> = when (autofillHint) {
        LuminaAutofillHint.EmailAddress    -> listOf(AutofillType.EmailAddress)
        LuminaAutofillHint.Password        -> listOf(AutofillType.Password)
        LuminaAutofillHint.NewPassword     -> listOf(AutofillType.NewPassword)
        LuminaAutofillHint.PersonFirstName -> listOf(AutofillType.PersonFirstName)
        LuminaAutofillHint.None            -> emptyList()
    }

    // Nó de autofill — regista o campo no sistema de autofill do Android
    // Necessário para compatibilidade com Google Password Manager, Samsung Pass, etc.
    val autofillNode = if (autofillTypes.isNotEmpty()) {
        AutofillNode(autofillTypes = autofillTypes, onFill = { onValueChange(it) })
            .also { node -> LocalAutofillTree.current += node }
    } else null
    val autofill = LocalAutofill.current

    // Border: slate-100 → indigo-500 ao focar; verde quando válido
    val borderColor = animateColorAsState(
        targetValue = when {
            isValid         -> EmeraldEmerald500
            isFocused.value -> IndigoIndigo500
            else            -> SlateSlate100
        },
        animationSpec = tween(200),
        label = "border",
    )
    val borderWidth = if (isFocused.value || isValid) 2.dp else 1.dp

    // Fundo: Slate50 → branco ao focar (espelha o comportamento web: bg-slate-50 + focus:bg-white)
    val fieldBackground = animateColorAsState(
        targetValue = if (isFocused.value) Color.White else SlateSlate50,
        animationSpec = tween(200),
        label = "field_bg",
    )

    Column(
        modifier = modifier
            .fillMaxWidth()
            .let { m ->
                // Posição global necessária para que o sistema de autofill saiba onde está o campo
                if (autofillNode != null) {
                    m.onGloballyPositioned { coords ->
                        autofillNode.boundingBox = coords.boundsInWindow()
                    }
                } else m
            }
            .semantics {
                this.contentDescription = if (isPassword) "$label, campo de senha" else "$label, campo de texto"
            },
    ) {
        // Label uppercase — estilo Flinch
        Text(
            text = label.uppercase(),
            style = MaterialTheme.typography.labelLarge.copy(
                fontSize = 10.sp,
                fontWeight = FontWeight.Bold,
                letterSpacing = 1.2.sp,
            ),
            color = if (isFocused.value) IndigoIndigo500 else SlateSlate500,
            modifier = Modifier.padding(bottom = 6.dp),
        )

        // Campo outlined
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .background(fieldBackground.value, RoundedCornerShape(12.dp))
                .border(borderWidth, borderColor.value, RoundedCornerShape(12.dp))
                .padding(horizontal = 14.dp, vertical = 14.dp),
        ) {
            Row(
                verticalAlignment = Alignment.CenterVertically,
            ) {
                // Leading icon (emoji ou composable)
                if (leadingIcon != null) {
                    Box(modifier = Modifier.padding(end = 10.dp)) {
                        leadingIcon()
                    }
                }

                // Input
                BasicTextField(
                    value = value,
                    onValueChange = onValueChange,
                    modifier = Modifier
                        .weight(1f)
                        .let { m ->
                            // Solicitar/cancelar autofill ao entrar/sair do campo
                            if (autofillNode != null) {
                                m.onFocusChanged { state ->
                                    autofill?.run {
                                        if (state.isFocused) requestAutofillForNode(autofillNode)
                                        else cancelAutofillForNode(autofillNode)
                                    }
                                }
                            } else m
                        },
                    textStyle = MaterialTheme.typography.bodyLarge.copy(
                        color = SlateSlate800,
                        fontSize = 15.sp,
                    ),
                    cursorBrush = SolidColor(IndigoIndigo500),
                    visualTransformation = if (isPassword) PasswordVisualTransformation() else VisualTransformation.None,
                    interactionSource = interactionSource,
                    singleLine = true,
                    keyboardOptions = keyboardOptions,
                    keyboardActions = keyboardActions,
                    decorationBox = { innerTextField ->
                        if (value.isEmpty()) {
                            Text(
                                text = placeholder,
                                style = MaterialTheme.typography.bodyMedium.copy(
                                    color = SlateSlate400,
                                    fontSize = 15.sp,
                                ),
                            )
                        }
                        innerTextField()
                    },
                )

                // Trailing validation icon — ✓ estilo Ahead
                if (isValid) {
                    Box(
                        modifier = Modifier
                            .size(22.dp)
                            .clip(CircleShape)
                            .background(EmeraldEmerald500),
                        contentAlignment = Alignment.Center,
                    ) {
                        Text(
                            text = "✓",
                            color = Color.White,
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Bold,
                        )
                    }
                }
            }
        }
    }
}
