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
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.BasicTextField
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.SolidColor
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.SlateSlate50
import pt.lumina.core.ui.theme.SlateSlate100
import pt.lumina.core.ui.theme.SlateSlate500
import pt.lumina.core.ui.theme.SlateSlate600

/**
 * Campo de texto Lumina com foco em bem-estar emocional.
 *
 * Características:
 * - Fundo suave (Slate50) que passa a branco ao focar
 * - Anéis de foco brilhantes (border com cor Indigo)
 * - Suporte a ícone leading (emoji ou composable)
 *
 * @param value Valor atual do campo
 * @param onValueChange Callback ao mudar o texto
 * @param label Etiqueta descritiva
 * @param modifier Modificador opcional
 * @param isPassword Se true, oculta os caracteres
 * @param leadingIcon Ícone ou emoji opcional à esquerda
 */
@Composable
fun LuminaTextField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    isPassword: Boolean = false,
    leadingIcon: (@Composable () -> Unit)? = null,
) {
    val interactionSource = remember { MutableInteractionSource() }
    val isFocused = interactionSource.collectIsFocusedAsState()

    // Animação do fundo: Slate50 -> Branco ao focar
    val animatedBgColor = animateColorAsState(
        targetValue = if (isFocused.value) Color.White else SlateSlate50,
        animationSpec = tween(durationMillis = 200),
        label = "textfield_bg_color"
    )

    // Animação da borda: cinza -> Indigo ao focar
    val animatedBorderColor = animateColorAsState(
        targetValue = if (isFocused.value) IndigoIndigo500 else SlateSlate100,
        animationSpec = tween(durationMillis = 200),
        label = "textfield_border_color"
    )

    Column(
        modifier = modifier
            .fillMaxWidth()
            .semantics {
                // Acessibilidade: Descreve o propósito do campo
                this.contentDescription = "$label, campo de texto"
                if (isPassword) {
                    this.contentDescription = "$label, campo de senha"
                }
            }
    ) {
        // Label (etiqueta do campo)
        Text(
            text = label,
            style = MaterialTheme.typography.labelLarge,
            color = SlateSlate600,
            modifier = Modifier.padding(bottom = 8.dp)
        )

        // Input container com fundo e borda animados
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .background(animatedBgColor.value, RoundedCornerShape(12.dp))
                .border(1.dp, animatedBorderColor.value, RoundedCornerShape(12.dp))
                .padding(horizontal = 16.dp, vertical = 12.dp)
        ) {
            Row(
                verticalAlignment = Alignment.CenterVertically
            ) {
                if (leadingIcon != null) {
                    Box(modifier = Modifier.padding(end = 12.dp)) {
                        leadingIcon()
                    }
                }

                BasicTextField(
                    value = value,
                    onValueChange = onValueChange,
                    modifier = Modifier.fillMaxWidth(),
                    textStyle = MaterialTheme.typography.bodyLarge.copy(color = SlateSlate600),
                    cursorBrush = SolidColor(IndigoIndigo500),
                    visualTransformation = if (isPassword) PasswordVisualTransformation() else VisualTransformation.None,
                    interactionSource = interactionSource,
                    decorationBox = { innerTextField ->
                        if (value.isEmpty()) {
                            Text(
                                text = "Escreva aqui...",
                                style = MaterialTheme.typography.bodyLarge,
                                color = SlateSlate500
                            )
                        }
                        innerTextField()
                    }
                )
            }
        }
    }
}
