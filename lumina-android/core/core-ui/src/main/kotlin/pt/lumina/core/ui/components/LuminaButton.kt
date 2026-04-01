package pt.lumina.core.ui.components

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.unit.dp
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.RoseRose500
import pt.lumina.core.ui.theme.VioletViolet500

/**
 * Botão Lumina com design system integrado.
 *
 * Características:
 * - Touch target acessível (mínimo de 48x48 dp gerido nativamente pelo Material 3)
 * - Suporta 3 variantes: primary (Indigo), secondary (Violet), sos (Rose)
 * - Estados: enabled, disabled, loading
 * - Animação suave (200ms) nas transições de estado (cor e opacidade)
 * - Elevação dinâmica para dar feedback de toque físico
 *
 * @param text Texto do botão (em PT-PT, tom empático)
 * @param onClick Lambda executada ao clicar
 * @param modifier Modificador Compose opcional
 * @param variant Tipo de botão: "primary", "secondary", "sos"
 * @param enabled Se false, desativa o botão e reduz opacidade visualmente
 * @param isLoading Se true, substitui o texto por um spinner de carregamento
 */
@Composable
fun LuminaButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    variant: String = "primary",
    enabled: Boolean = true,
    isLoading: Boolean = false,
) {
    val (containerColor, contentColor) = when (variant) {
        "secondary" -> VioletViolet500 to Color.White
        "sos" -> RoseRose500 to Color.White
        else -> IndigoIndigo500 to Color.White
    }

    val animatedContainerColor by animateColorAsState(
        targetValue = if (enabled) containerColor else containerColor.copy(alpha = 0.5f),
        animationSpec = tween(durationMillis = 200),
        label = "button_bg_color"
    )

    val animatedContentColor by animateColorAsState(
        targetValue = if (enabled) contentColor else contentColor.copy(alpha = 0.6f),
        animationSpec = tween(durationMillis = 200),
        label = "button_text_color"
    )

    Button(
        onClick = onClick,
        enabled = enabled && !isLoading,
        modifier = modifier.semantics {
            if (isLoading) {
                // Acessibilidade: O leitor de ecrã anuncia o estado de carregamento
                contentDescription = "A processar, por favor aguarde"
            }
        },
        shape = RoundedCornerShape(12.dp),
        colors = ButtonDefaults.buttonColors(
            containerColor = animatedContainerColor,
            contentColor = animatedContentColor,
            // Sobrescrevemos as cores de disabled padrão do Material para usarmos a nossa animação de opacidade
            disabledContainerColor = animatedContainerColor,
            disabledContentColor = animatedContentColor
        ),
        elevation = ButtonDefaults.buttonElevation(
            defaultElevation = 8.dp,
            pressedElevation = 2.dp,
            disabledElevation = 0.dp,
        ),
    ) {
        if (isLoading) {
            CircularProgressIndicator(
                modifier = Modifier.size(24.dp), // Tamanho fixo evita que o botão mude de altura
                color = animatedContentColor,
                strokeWidth = 2.dp,
            )
        } else {
            Text(
                text = text,
                style = MaterialTheme.typography.labelLarge,
                color = animatedContentColor,
            )
        }
    }
}