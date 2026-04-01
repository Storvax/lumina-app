package pt.lumina.core.ui.components

import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.geometry.Size
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.drawscope.DrawScope
import androidx.compose.ui.graphics.drawscope.Stroke
import kotlin.math.cos
import kotlin.math.sin

/**
 * Mascote "Lumi" da Lumina — pequeno ser luminoso feito de luz e estrelas.
 *
 * Animação de flutuação suave para transmitir calma e acolhimento.
 * Desenhada inteiramente em Canvas para máxima performance.
 */
@Composable
fun LumiMascot(modifier: Modifier = Modifier) {
    val infiniteTransition = rememberInfiniteTransition(label = "lumi_animation")

    // Flutuação vertical suave
    val floatY by infiniteTransition.animateFloat(
        initialValue = 0f,
        targetValue = 18f,
        animationSpec = infiniteRepeatable(
            animation = tween(durationMillis = 2400, easing = FastOutSlowInEasing),
            repeatMode = RepeatMode.Reverse,
        ),
        label = "lumi_float_y",
    )

    // Pulsação subtil do brilho exterior
    val glowAlpha by infiniteTransition.animateFloat(
        initialValue = 0.08f,
        targetValue = 0.20f,
        animationSpec = infiniteRepeatable(
            animation = tween(durationMillis = 1800, easing = FastOutSlowInEasing),
            repeatMode = RepeatMode.Reverse,
        ),
        label = "lumi_glow",
    )

    Canvas(modifier = modifier) {
        val cx = size.width / 2f
        // Centro do corpo ajustado pela animação de flutuação
        val cy = size.height * 0.46f + floatY
        val bodyR = size.width * 0.285f

        // --- Aura de brilho exterior (pulsante) ---
        drawCircle(
            brush = Brush.radialGradient(
                colors = listOf(
                    Color(0xFF6366F1).copy(alpha = glowAlpha),
                    Color.Transparent,
                ),
                center = Offset(cx, cy),
                radius = bodyR * 1.7f,
            ),
            radius = bodyR * 1.7f,
            center = Offset(cx, cy),
        )

        // --- Cauda (antes do corpo para ficar por baixo) ---
        val tailTop = cy + bodyR * 0.72f
        val tailPath = Path().apply {
            moveTo(cx - bodyR * 0.50f, tailTop)
            cubicTo(
                cx - bodyR * 0.90f, tailTop + bodyR * 0.55f,
                cx - bodyR * 0.10f, tailTop + bodyR * 0.70f,
                cx, tailTop + bodyR * 0.50f,
            )
            cubicTo(
                cx + bodyR * 0.10f, tailTop + bodyR * 0.30f,
                cx + bodyR * 0.65f, tailTop + bodyR * 0.55f,
                cx + bodyR * 0.50f, tailTop,
            )
            close()
        }
        drawPath(
            path = tailPath,
            brush = Brush.verticalGradient(
                colors = listOf(
                    Color(0xFF6366F1),
                    Color(0xFFA78BFA).copy(alpha = 0.35f),
                ),
                startY = tailTop,
                endY = tailTop + bodyR * 0.75f,
            ),
        )

        // --- Corpo principal: gradiente indigo → lavanda ---
        drawCircle(
            brush = Brush.radialGradient(
                colors = listOf(
                    Color(0xFFBDB4FE),
                    Color(0xFF6366F1),
                ),
                center = Offset(cx - bodyR * 0.18f, cy - bodyR * 0.22f),
                radius = bodyR * 1.4f,
            ),
            radius = bodyR,
            center = Offset(cx, cy),
        )

        // Highlight (brilho de luz no corpo)
        drawCircle(
            brush = Brush.radialGradient(
                colors = listOf(Color.White.copy(alpha = 0.45f), Color.Transparent),
                center = Offset(cx - bodyR * 0.20f, cy - bodyR * 0.25f),
                radius = bodyR * 0.55f,
            ),
            radius = bodyR * 0.55f,
            center = Offset(cx - bodyR * 0.20f, cy - bodyR * 0.25f),
        )

        // --- Olhos ---
        val eyeY = cy - bodyR * 0.12f
        val eyeR = bodyR * 0.125f
        // Olho esquerdo
        drawCircle(Color(0xFF1E1B4B), eyeR, Offset(cx - bodyR * 0.30f, eyeY))
        // Olho direito
        drawCircle(Color(0xFF1E1B4B), eyeR, Offset(cx + bodyR * 0.30f, eyeY))
        // Brilhos dos olhos
        drawCircle(Color.White, eyeR * 0.38f, Offset(cx - bodyR * 0.30f + eyeR * 0.35f, eyeY - eyeR * 0.35f))
        drawCircle(Color.White, eyeR * 0.38f, Offset(cx + bodyR * 0.30f + eyeR * 0.35f, eyeY - eyeR * 0.35f))

        // --- Sorriso suave ---
        val smilePath = Path().apply {
            moveTo(cx - bodyR * 0.22f, cy + bodyR * 0.10f)
            quadraticBezierTo(cx, cy + bodyR * 0.30f, cx + bodyR * 0.22f, cy + bodyR * 0.10f)
        }
        drawPath(
            path = smilePath,
            color = Color(0xFF1E1B4B),
            style = Stroke(width = size.width * 0.013f, cap = StrokeCap.Round),
        )

        // --- Coração na barriga ---
        drawHeart(
            center = Offset(cx, cy + bodyR * 0.50f),
            size = bodyR * 0.22f,
            color = Color(0xFFDDD6FE),
        )

        // ====== DECORAÇÕES ======

        // Estrela dourada grande (direita-cima)
        drawStar4(
            center = Offset(cx + size.width * 0.37f, cy - size.height * 0.26f),
            radius = size.width * 0.068f,
            color = Color(0xFFF59E0B),
        )
        // Estrela dourada média (esquerda)
        drawStar4(
            center = Offset(cx - size.width * 0.40f, cy - size.height * 0.10f),
            radius = size.width * 0.048f,
            color = Color(0xFFF59E0B),
        )
        // Estrela dourada pequena (direita-baixo)
        drawStar4(
            center = Offset(cx + size.width * 0.30f, cy + size.height * 0.20f),
            radius = size.width * 0.032f,
            color = Color(0xFFF59E0B),
        )

        // Orbe rosa (esquerda-baixo)
        drawCircle(
            color = Color(0xFFF43F5E).copy(alpha = 0.92f),
            radius = size.width * 0.058f,
            center = Offset(cx - size.width * 0.42f, cy + size.height * 0.16f),
        )

        // Orbe verde (direita-centro)
        drawCircle(
            brush = Brush.radialGradient(
                colors = listOf(Color(0xFF6EE7B7), Color(0xFF10B981)),
                center = Offset(cx + size.width * 0.41f, cy + size.height * 0.04f),
                radius = size.width * 0.058f,
            ),
            radius = size.width * 0.058f,
            center = Offset(cx + size.width * 0.41f, cy + size.height * 0.04f),
        )

        // Nuvem esquerda-cima
        drawCloud(
            center = Offset(cx - size.width * 0.28f, cy - size.height * 0.36f),
            size = size.width * 0.088f,
            color = Color(0xFFBAE6FD).copy(alpha = 0.80f),
        )
        // Nuvem direita-cima
        drawCloud(
            center = Offset(cx + size.width * 0.22f, cy - size.height * 0.40f),
            size = size.width * 0.068f,
            color = Color(0xFFBAE6FD).copy(alpha = 0.75f),
        )
    }
}

// Estrela de 4 pontas (forma ✦)
private fun DrawScope.drawStar4(center: Offset, radius: Float, color: Color) {
    val path = Path()
    for (i in 0..7) {
        val angle = Math.PI / 4.0 * i - Math.PI / 2.0
        val r = if (i % 2 == 0) radius else radius * 0.32f
        val x = center.x + (r * cos(angle)).toFloat()
        val y = center.y + (r * sin(angle)).toFloat()
        if (i == 0) path.moveTo(x, y) else path.lineTo(x, y)
    }
    path.close()
    drawPath(path, color)
}

// Coração simples com dois círculos e um triângulo
private fun DrawScope.drawHeart(center: Offset, size: Float, color: Color) {
    val r = size * 0.55f
    drawCircle(color, r, Offset(center.x - r * 0.85f, center.y - r * 0.1f))
    drawCircle(color, r, Offset(center.x + r * 0.85f, center.y - r * 0.1f))
    val p = Path().apply {
        moveTo(center.x - size * 1.4f, center.y + r * 0.55f)
        lineTo(center.x, center.y + size * 1.5f)
        lineTo(center.x + size * 1.4f, center.y + r * 0.55f)
        close()
    }
    drawPath(p, color)
}

// Nuvem com 3 círculos + rectângulo base
private fun DrawScope.drawCloud(center: Offset, size: Float, color: Color) {
    drawCircle(color, size * 0.55f, center)
    drawCircle(color, size * 0.42f, Offset(center.x - size * 0.55f, center.y + size * 0.10f))
    drawCircle(color, size * 0.42f, Offset(center.x + size * 0.55f, center.y + size * 0.10f))
    drawRect(
        color = color,
        topLeft = Offset(center.x - size * 0.97f, center.y + size * 0.10f),
        size = Size(size * 1.94f, size * 0.58f),
    )
}
