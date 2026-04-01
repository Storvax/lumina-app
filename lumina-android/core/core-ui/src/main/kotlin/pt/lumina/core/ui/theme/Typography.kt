package pt.lumina.core.ui.theme

import androidx.compose.material3.Typography
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.googlefonts.Font
import androidx.compose.ui.text.googlefonts.GoogleFont
import androidx.compose.ui.unit.sp
import pt.lumina.core.ui.R

/**
 * Tipografia Lumina — Nunito + DM Sans via Google Fonts.
 *
 * Escolhas deliberadamente não-genéricas:
 * - Nunito: terminais redondas, quente, acolhedor — perfeito para saúde mental.
 *   Não é Inter. Não é Roboto. Tem carácter próprio.
 * - DM Sans: geométrica limpa, formas de 'a' e 'g' distintas — legível em stress.
 *
 * Hierarquia inspirada no Flinch:
 * - Headlines: Black/ExtraBold, tracking negativo, grande contraste de tamanho
 * - Body: Regular, espaçamento relaxado
 * - Labels: Bold, uppercase, tracking largo (estilo Flinch)
 *
 * Fontes via GMS — sem CDN, sem bundle de assets, ~0kb no APK.
 * Requerem internet no primeiro uso; depois ficam em cache.
 */

private val googleFontProvider = GoogleFont.Provider(
    providerAuthority = "com.google.android.gms.fonts",
    providerPackage  = "com.google.android.gms",
    certificates     = R.array.com_google_android_gms_fonts_certs,
)

// Nunito: 9 pesos disponíveis (300–900). Usar ExtraBold/Black para impacto.
private val nunitoFont = GoogleFont("Nunito")
val NunitoFontFamily = FontFamily(
    Font(googleFont = nunitoFont, fontProvider = googleFontProvider, weight = FontWeight.Normal),
    Font(googleFont = nunitoFont, fontProvider = googleFontProvider, weight = FontWeight.Medium),
    Font(googleFont = nunitoFont, fontProvider = googleFontProvider, weight = FontWeight.SemiBold),
    Font(googleFont = nunitoFont, fontProvider = googleFontProvider, weight = FontWeight.Bold),
    Font(googleFont = nunitoFont, fontProvider = googleFontProvider, weight = FontWeight.ExtraBold),
    Font(googleFont = nunitoFont, fontProvider = googleFontProvider, weight = FontWeight.Black),
)

// DM Sans: limpa, moderna, com letterforms distintos. Boa para textos corridos.
private val dmSansFont = GoogleFont("DM Sans")
val DmSansFontFamily = FontFamily(
    Font(googleFont = dmSansFont, fontProvider = googleFontProvider, weight = FontWeight.Normal),
    Font(googleFont = dmSansFont, fontProvider = googleFontProvider, weight = FontWeight.Medium),
    Font(googleFont = dmSansFont, fontProvider = googleFontProvider, weight = FontWeight.SemiBold),
    Font(googleFont = dmSansFont, fontProvider = googleFontProvider, weight = FontWeight.Bold),
)

val LuminaTypography = Typography(
    // ── HEADLINES ─────────────────────────────────────────────────────────────
    // Nunito Black com tracking negativo: impacto máximo, zero genérico
    headlineLarge = TextStyle(
        fontFamily   = NunitoFontFamily,
        fontWeight   = FontWeight.Black,       // 900 — contraste forte com body
        fontSize     = 40.sp,
        lineHeight   = 46.sp,
        letterSpacing = (-0.8).sp,             // tracking-tight: compacto, editorial
    ),
    headlineMedium = TextStyle(
        fontFamily   = NunitoFontFamily,
        fontWeight   = FontWeight.Black,
        fontSize     = 32.sp,
        lineHeight   = 38.sp,
        letterSpacing = (-0.5).sp,
    ),
    headlineSmall = TextStyle(
        fontFamily   = NunitoFontFamily,
        fontWeight   = FontWeight.ExtraBold,   // 800
        fontSize     = 26.sp,
        lineHeight   = 32.sp,
        letterSpacing = (-0.3).sp,
    ),

    // ── TITLES ────────────────────────────────────────────────────────────────
    // Nunito Bold para sub-headings, secções, cards
    titleLarge = TextStyle(
        fontFamily   = NunitoFontFamily,
        fontWeight   = FontWeight.Bold,
        fontSize     = 22.sp,
        lineHeight   = 28.sp,
        letterSpacing = 0.sp,
    ),
    titleMedium = TextStyle(
        fontFamily   = NunitoFontFamily,
        fontWeight   = FontWeight.SemiBold,
        fontSize     = 18.sp,
        lineHeight   = 24.sp,
        letterSpacing = 0.sp,
    ),
    titleSmall = TextStyle(
        fontFamily   = NunitoFontFamily,
        fontWeight   = FontWeight.SemiBold,
        fontSize     = 16.sp,
        lineHeight   = 22.sp,
        letterSpacing = 0.1.sp,
    ),

    // ── BODY ──────────────────────────────────────────────────────────────────
    // DM Sans Regular com espaçamento relaxado — confortável em estados de stress
    bodyLarge = TextStyle(
        fontFamily   = DmSansFontFamily,
        fontWeight   = FontWeight.Normal,
        fontSize     = 16.sp,
        lineHeight   = 26.sp,                 // 1.625x — espaçamento generoso
        letterSpacing = 0.1.sp,
        color        = SlateSlate600,
    ),
    bodyMedium = TextStyle(
        fontFamily   = DmSansFontFamily,
        fontWeight   = FontWeight.Normal,
        fontSize     = 14.sp,
        lineHeight   = 22.sp,
        letterSpacing = 0.1.sp,
        color        = SlateSlate600,
    ),
    bodySmall = TextStyle(
        fontFamily   = DmSansFontFamily,
        fontWeight   = FontWeight.Normal,
        fontSize     = 12.sp,
        lineHeight   = 18.sp,
        letterSpacing = 0.1.sp,
        color        = SlateSlate500,
    ),

    // ── LABELS ────────────────────────────────────────────────────────────────
    // DM Sans Bold uppercase com tracking largo — estilo Flinch
    // Usado em etiquetas de campos, categorias, badges
    labelLarge = TextStyle(
        fontFamily   = DmSansFontFamily,
        fontWeight   = FontWeight.Bold,
        fontSize     = 11.sp,
        lineHeight   = 16.sp,
        letterSpacing = 1.0.sp,              // tracking-widest — estilo Flinch
        color        = SlateSlate600,
    ),
    labelMedium = TextStyle(
        fontFamily   = DmSansFontFamily,
        fontWeight   = FontWeight.Bold,
        fontSize     = 10.sp,
        lineHeight   = 14.sp,
        letterSpacing = 1.2.sp,
        color        = SlateSlate500,
    ),
    labelSmall = TextStyle(
        fontFamily   = DmSansFontFamily,
        fontWeight   = FontWeight.Medium,
        fontSize     = 10.sp,
        lineHeight   = 14.sp,
        letterSpacing = 0.5.sp,
        color        = SlateSlate500,
    ),
)
