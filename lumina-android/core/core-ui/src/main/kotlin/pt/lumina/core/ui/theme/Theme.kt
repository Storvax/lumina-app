package pt.lumina.core.ui.theme

import android.os.Build
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.ColorScheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.dynamicDarkColorScheme
import androidx.compose.material3.dynamicLightColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext

/**
 * Tema Light Mode (padrão para Lumina)
 * Usa Slate para textos (não #000), Indigo para ações primárias, Rose para acolhimento
 */
private val LightColors = lightColorScheme(
    // Ação primária (Indigo - Profundidade e calma)
    primary = IndigoIndigo500,
    onPrimary = Color.White,
    primaryContainer = IndigoIndigo50,
    onPrimaryContainer = IndigoIndigo600,

    // Secundária (Violet - Toque mágico)
    secondary = VioletViolet500,
    onSecondary = Color.White,
    secondaryContainer = Color(0xFFF3E8FF),  // violet-100 (suave)
    onSecondaryContainer = VioletViolet600,

    // Terciária (Amber - aviso suave)
    tertiary = AmberAmber500,
    onTertiary = Color.White,
    tertiaryContainer = AmberAmber50,
    onTertiaryContainer = AmberAmber600,

    // Erro (Rose - acolhedor, não agressivo)
    error = RoseRose500,
    onError = Color.White,
    errorContainer = RoseRose50,
    onErrorContainer = RoseRose600,

    // Fundo e surface (Slate - Tranquilo, com toque de azul impercetível)
    background = SlateSlate50,      // Fundo geral quase branco
    onBackground = SlateSlate800,   // Texto em fundo claro
    surface = Color.White,          // Cards e surfaces
    onSurface = SlateSlate800,      // Texto em cards

    // Outline (bordas suaves)
    outline = SlateSlate100,
    outlineVariant = Color(0xFFCBD5E1),  // slate-300
)

/**
 * Tema Dark Mode
 * Usa Slate dark para textos, Indigo continua como primária
 */
private val DarkColors = darkColorScheme(
    primary = IndigoIndigo500,
    onPrimary = Color.White,
    primaryContainer = IndigoIndigo50,
    onPrimaryContainer = IndigoIndigo600,

    secondary = VioletViolet500,
    onSecondary = Color.White,
    secondaryContainer = Color(0xFF5B21B6),  // violet-700 escuro
    onSecondaryContainer = VioletViolet500,

    tertiary = AmberAmber500,
    onTertiary = Color.White,
    tertiaryContainer = Color(0xFF78350F),  // amber-900 escuro
    onTertiaryContainer = AmberAmber500,

    error = RoseRose500,
    onError = Color.White,
    errorContainer = Color(0xFF7F1D1D),  // rose-900 escuro
    onErrorContainer = RoseRose500,

    background = SlateDarkBg,       // Quase preto
    onBackground = SlateDarkText,   // Quase branco
    surface = SlateDarkSurface,     // Cards
    onSurface = SlateDarkText,

    outline = Color(0xFF475569),    // slate-600
    outlineVariant = Color(0xFF1E293B),  // slate-800
)

/**
 * LuminaTheme
 *
 * Tema Material 3 da Lumina com suporte para:
 * - Light mode (padrão)
 * - Dark mode
 * - Dynamic colors (Android 12+, respeta preferências do sistema)
 *
 * Pensado para:
 * - Acessibilidade (contraste adequado, touch targets 44dp+)
 * - Bem-estar emocional (cores acolhedoras, sem tons alarmistas)
 * - Legibilidade em contextos de stress (fontes grandes, spacing relaxado)
 *
 * @param darkTheme Se true, ativa dark mode. Padrão: siga preferência do sistema
 * @param dynamicColor Se true, usa dynamic colors do sistema (Android 12+). Padrão: true
 * @param content Composable a renderizar com o tema
 */
@Composable
fun LuminaTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    dynamicColor: Boolean = false, // Mudar o padrão para false
    content: @Composable () -> Unit,
) {
        val colorScheme = when {
        // Android 12+: Dynamic colors respeitam o wallpaper/sistema
        dynamicColor && Build.VERSION.SDK_INT >= Build.VERSION_CODES.S -> {
            val context = LocalContext.current
            if (darkTheme) dynamicDarkColorScheme(context) else dynamicLightColorScheme(context)
        }
        // Fallback para light/dark estáticos
        darkTheme -> DarkColors
        else -> LightColors
    }

    MaterialTheme(
        colorScheme = colorScheme,
        typography = LuminaTypography,
        content = content,
    )
}
