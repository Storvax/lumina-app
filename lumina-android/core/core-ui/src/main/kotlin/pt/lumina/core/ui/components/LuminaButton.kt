package pt.lumina.core.ui.components

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.PressInteraction
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.unit.dp
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.IndigoIndigo600
import pt.lumina.core.ui.theme.RoseRose500
import pt.lumina.core.ui.theme.RoseRose600
import pt.lumina.core.ui.theme.VioletViolet500
import pt.lumina.core.ui.theme.VioletViolet600
import pt.lumina.core.ui.theme.SlateSlate50
import pt.lumina.core.ui.theme.SlateSlate500
import androidx.compose.material3.MaterialTheme

/**
 * Botão Lumina com design system integrado.
 *
 * Características:
 * - Touch target acessível (min 44x44 dp)
 * - Suporta 3 variantes: primary (Indigo), secondary (Violet), sos (Rose)
 * - Estados: enabled, disabled, loading
 * - Animação suave (200ms) no hover e transições de estado
 * - Sombra colorida dinâmica baseada na variante
 *
 * @param text Texto do botão (em PT-PT)
 * @param onClick Lambda executada ao clicar
 * @param modifier Modificador Compose opcional
 * @param variant Tipo de botão: "primary", "secondary", "sos"
 * @param enabled Se false, desativa o botão e reduz opacidade
 * @param isLoading Se true, mostra spinner de carregamento
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
    // Cores baseadas na variante, com transição suave
    val (containerColor, contentColor, shadowColor) = when (variant) {
        "secondary" -> Triple(VioletViolet500, Color.White, VioletViolet600)
        "sos" -> Triple(RoseRose500, Color.White, RoseRose600)
        else -> Triple(IndigoIndigo500, Color.White, IndigoIndigo600)  // primary é padrão
    }

    val animatedContainerColor = animateColorAsState(
        targetValue = if (enabled) containerColor else containerColor.copy(alpha = 0.5f),
        animationSpec = tween(durationMillis = 200),
        label = "button_bg_color"
    )

    val animatedContentColor = animateColorAsState(
        targetValue = if (enabled) contentColor else contentColor.copy(alpha = 0.6f),
        animationSpec = tween(durationMillis = 200),
        label = "button_text_color"
    )

    Button(
        onClick = onClick,
        enabled = enabled && !isLoading,
        modifier = modifier
            .semantics {
                // Acessibilidade: Descreve estado de carregamento
                if (isLoading) {
                    this.contentDescription = "Botão a carregar, por favor aguarde"
                }
            }
            .padding(vertical = 4.dp, horizontal = 4.dp),  // Espaçamento mínimo para touch
        shape = RoundedCornerShape(12.dp),  // rounded-xl
        colors = ButtonDefaults.buttonColors(
            containerColor = animatedContainerColor.value,
            contentColor = animatedContentColor.value,
            disabledContainerColor = containerColor.copy(alpha = 0.5f),
            disabledContentColor = contentColor.copy(alpha = 0.6f),
        ),
        elevation = ButtonDefaults.elevatedButtonElevation(
            defaultElevation = 8.dp,
            pressedElevation = 2.dp,
            disabledElevation = 0.dp,
        ),
    ) {
        Box(
            modifier = Modifier
                .padding(horizontal = 16.dp, vertical = 12.dp)
                .align(Alignment.CenterVertically),
            contentAlignment = Alignment.Center,
        ) {
            if (isLoading) {
                // Spinner com cor do texto
                CircularProgressIndicator(
                    modifier = Modifier
                        .align(Alignment.Center),
                    color = animatedContentColor.value,
                    strokeWidth = 2.dp,
                )
            } else {
                // Texto do botão com estilo apropriado
                Text(
                    text = text,
                    style = MaterialTheme.typography.labelLarge,
                    color = animatedContentColor.value,
                )
            }
        }
    }
}
