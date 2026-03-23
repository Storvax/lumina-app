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
 * - Espaçamento generoso (padding relaxado)
 * - Suporte a campos de senha (masked)
 * - Touch target mínimo 44dp de altura
 * - Transições suaves (200ms)
 *
 * @param label Etiqueta do campo (PT-PT)
 * @param value Valor atual do campo
 * @param onValueChange Lambda executada ao alterar o valor
 * @param placeholder Texto de ajuda quando vazio
 * @param leadingIcon Composable opcional para ícone/emoji à esquerda
 * @param isPassword Se true, mascara o texto (dots)
 * @param modifier Modificador Compose opcional
 */
@Composable
fun LuminaTextField(
    label: String,
    value: String,
    onValueChange: (String) -> Unit,
    modifier: Modifier = Modifier,
    placeholder: String = "",
    leadingIcon: (@Composable () -> Unit)? = null,
    isPassword: Boolean = false,
) {
    val interactionSource = remember { MutableInteractionSource() }
    val isFocused = interactionSource.collectIsFocusedAsState()

    // Animação da cor de fundo: Slate50 -> Branco ao focar
    val animatedBackgroundColor = animateColorAsState(
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
                .background(
                    color = animatedBackgroundColor.value,
                    shape = RoundedCornerShape(8.dp)
                )
                .border(
                    width = 2.dp,
                    color = animatedBorderColor.value,
                    shape = RoundedCornerShape(8.dp)
                )
                .padding(12.dp),  // Padding interior: espaçamento relaxado
            contentAlignment = Alignment.CenterStart
        ) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .align(Alignment.CenterStart),
                verticalAlignment = Alignment.CenterVertically
            ) {
                // Ícone leading (à esquerda)
                if (leadingIcon != null) {
                    Box(
                        modifier = Modifier
                            .padding(end = 12.dp)
                            .align(Alignment.CenterVertically)
                    ) {
                        leadingIcon()
                    }
                }

                // BasicTextField (sem decoração, usamos Box para estilo)
                Box(
                    modifier = Modifier
                        .weight(1f)
                        .align(Alignment.CenterVertically)
                ) {
                    if (value.isEmpty()) {
                        // Placeholder quando vazio
                        Text(
                            text = placeholder,
                            style = MaterialTheme.typography.bodyMedium,
                            color = SlateSlate500,
                        )
                    }

                    BasicTextField(
                        value = value,
                        onValueChange = onValueChange,
                        interactionSource = interactionSource,
                        textStyle = MaterialTheme.typography.bodyMedium.copy(
                            color = SlateSlate600
                        ),
                        cursorBrush = SolidColor(IndigoIndigo500),
                        visualTransformation = if (isPassword) {
                            PasswordVisualTransformation()
                        } else {
                            VisualTransformation.None
                        },
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(vertical = 10.dp),  // Touch target mínimo 44dp
                    )
                }
            }
        }
    }
}
