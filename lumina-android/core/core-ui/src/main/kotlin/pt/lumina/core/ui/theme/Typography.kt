package pt.lumina.core.ui.theme

import androidx.compose.material3.Typography
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.sp

/**
 * Tipografia Lumina.
 * Pensada para ser legível mesmo em estados de stress/hands trembling.
 * PT-PT friendly: spacing relaxado, word-break friendly.
 *
 * Fontes:
 * - Plus Jakarta Sans: Auth, Errors (limpa, geométrica, moderna)
 * - Figtree: Dashboard, Fórum (legível em ecrãs pequenos)
 *
 * Paleta de tamanhos:
 * - Super Headings (títulos página): 3xl-5xl, font-black (900), tracking-tight
 * - Body (desabafos): sm-base, leading-relaxed (espaçamento confortável)
 * - Labels (dicas): xs, font-bold, uppercase, tracking-widest
 */

// FontFamilies - Apontam para fontes em assets/fonts/
// TODO: Adicionar Plus Jakarta Sans e Figtree aos recursos do projeto
val PlusJakartaSansFontFamily = FontFamily.Default   // Será substituído quando fontes forem adicionadas
val FigtreeFontFamily = FontFamily.Default            // Será substituído quando fontes forem adicionadas

val LuminaTypography = Typography(
    // ===== HEADLINES (Super Headings - texto-3xl a text-5xl, font-black) =====
    headlineLarge = TextStyle(
        fontFamily = PlusJakartaSansFontFamily,
        fontWeight = FontWeight.Black,  // 900
        fontSize = 44.sp,               // ~text-5xl
        lineHeight = 52.sp,
        letterSpacing = -0.5.sp         // tracking-tight
    ),
    headlineMedium = TextStyle(
        fontFamily = PlusJakartaSansFontFamily,
        fontWeight = FontWeight.Black,  // 900
        fontSize = 36.sp,               // ~text-4xl
        lineHeight = 44.sp,
        letterSpacing = -0.3.sp
    ),
    headlineSmall = TextStyle(
        fontFamily = PlusJakartaSansFontFamily,
        fontWeight = FontWeight.Black,  // 900
        fontSize = 28.sp,               // ~text-3xl
        lineHeight = 36.sp,
        letterSpacing = -0.2.sp
    ),

    // ===== TITLES (Subtítulos e cabeçalhos de secções) =====
    titleLarge = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.Bold,   // 700
        fontSize = 24.sp,
        lineHeight = 32.sp,
        letterSpacing = 0.sp
    ),
    titleMedium = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.SemiBold, // 600
        fontSize = 20.sp,
        lineHeight = 28.sp,
        letterSpacing = 0.1.sp
    ),
    titleSmall = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.SemiBold, // 600
        fontSize = 18.sp,
        lineHeight = 24.sp,
        letterSpacing = 0.1.sp
    ),

    // ===== BODY (Desabafos, textos longos - text-sm a text-base, leading-relaxed) =====
    // Leading-relaxed no Compose = 1.75x do tamanho da fonte aprox
    bodyLarge = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.Normal,  // 400
        fontSize = 16.sp,                // ~text-base
        lineHeight = 28.sp,              // relaxed spacing
        letterSpacing = 0.3.sp,
        color = SlateSlate600
    ),
    bodyMedium = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.Normal,
        fontSize = 14.sp,                // ~text-sm
        lineHeight = 24.sp,              // relaxed
        letterSpacing = 0.2.sp,
        color = SlateSlate600
    ),
    bodySmall = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.Normal,
        fontSize = 12.sp,
        lineHeight = 20.sp,
        letterSpacing = 0.1.sp,
        color = SlateSlate500
    ),

    // ===== LABELS (Dicas, hints - text-xs, font-bold, uppercase, tracking-widest) =====
    labelLarge = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.Bold,    // 700
        fontSize = 13.sp,
        lineHeight = 20.sp,
        letterSpacing = 0.8.sp,          // tracking-widest
        color = SlateSlate600
    ),
    labelMedium = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.Bold,
        fontSize = 11.sp,                // ~text-xs
        lineHeight = 16.sp,
        letterSpacing = 1.2.sp,          // tracking-widest
        color = SlateSlate500
    ),
    labelSmall = TextStyle(
        fontFamily = FigtreeFontFamily,
        fontWeight = FontWeight.Bold,
        fontSize = 10.sp,
        lineHeight = 14.sp,
        letterSpacing = 1.5.sp,          // tracking-widest
        color = SlateSlate500
    ),
)
