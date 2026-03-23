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
import androidx.compose.ui.platform.LocalContext

private val LightColors = lightColorScheme(
    primary = LuminaPrimary,
    onPrimary = Color.White,
    primaryContainer = LuminaPrimaryLight,
    onPrimaryContainer = LuminaPrimaryDark,

    secondary = LuminaSecondary,
    onSecondary = Color.White,
    secondaryContainer = LuminaSecondaryLight,
    onSecondaryContainer = LuminaSecondaryDark,

    tertiary = WarningOrange,
    onTertiary = Color.White,

    error = ErrorRose,
    onError = Color.White,

    background = BackgroundLight,
    onBackground = TextPrimary,
    surface = SurfaceLight,
    onSurface = TextPrimary,

    outline = BorderLight,
)

private val DarkColors = darkColorScheme(
    primary = LuminaPrimaryLight,
    onPrimary = LuminaPrimaryDark,
    primaryContainer = LuminaPrimary,
    onPrimaryContainer = LuminaPrimaryLight,

    secondary = LuminaSecondaryLight,
    onSecondary = LuminaSecondaryDark,
    secondaryContainer = LuminaSecondary,
    onSecondaryContainer = LuminaSecondaryLight,

    tertiary = WarningOrange,
    onTertiary = Color.White,

    error = ErrorRose,
    onError = Color.White,

    background = BackgroundDark,
    onBackground = TextPrimaryDark,
    surface = SurfaceDark,
    onSurface = TextPrimaryDark,

    outline = BorderDark,
)

/**
 * Tema Lumina.
 * Suporta light, dark, e dynamic (Android 12+).
 * Pensado para acessibilidade e bem-estar emocional.
 */
@Composable
fun LuminaTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    dynamicColor: Boolean = true,
    content: @Composable () -> Unit,
) {
    val colorScheme = when {
        dynamicColor && Build.VERSION.SDK_INT >= Build.VERSION_CODES.S -> {
            val context = LocalContext.current
            if (darkTheme) dynamicDarkColorScheme(context) else dynamicLightColorScheme(context)
        }

        darkTheme -> DarkColors
        else -> LightColors
    }

    MaterialTheme(
        colorScheme = colorScheme,
        typography = LuminaTypography,
        content = content,
    )
}

// Import para Color
import androidx.compose.ui.graphics.Color
