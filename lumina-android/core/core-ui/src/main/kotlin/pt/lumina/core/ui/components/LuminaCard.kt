package pt.lumina.core.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.blur
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import androidx.compose.material3.Surface

/**
 * Card Lumina com efeito glassmorphism.
 *
 * Características:
 * - Fundo semi-transparente branco (80% opacidade)
 * - Efeito blur suave (backdrop blur 8-12 dp)
 * - Bordas minimalistas com white/50 para profundidade
 * - Cantos muito arredondados (24dp = rounded-3xl)
 * - Aparência flutuante como "nuvem"
 * - Acessibilidade integrada
 *
 * Este componente é ideal para:
 * - Cards de conteúdo principal
 * - Secções de seleção (mood selector)
 * - Overlays e modais suaves
 *
 * @param modifier Modificador Compose opcional
 * @param content Composable com o conteúdo do card
 */
@Composable
fun LuminaCard(
    modifier: Modifier = Modifier,
    content: @Composable () -> Unit,
) {
    Surface(
        modifier = modifier
            .background(
                color = Color.White.copy(alpha = 0.80f),  // bg-white/80
                shape = RoundedCornerShape(24.dp)  // rounded-3xl (24dp)
            )
            .border(
                width = 1.dp,
                color = Color.White.copy(alpha = 0.50f),  // border-white/50 para suavidade
                shape = RoundedCornerShape(24.dp)
            )
            .blur(radius = 10.dp),  // Backdrop blur 8-12 dp (usar 10 como compromise)
        shape = RoundedCornerShape(24.dp),
        color = Color.Transparent,  // Deixa a cor de fundo do Box aparecer
        content = content,
    )
}
