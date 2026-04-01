package pt.lumina.core.ui.theme

import androidx.compose.ui.graphics.Color

/**
 * Paleta de cores Lumina.
 * Tons suaves, acolhedores, pensados para bem-estar emocional.
 */

// ===== CORE BRAND COLORS =====
// Indigo: Profundidade, espiritualidade e calma (cor principal)
val IndigoIndigo500 = Color(0xFF6366F1)      // Indigo-500 - Ação primária, links
val IndigoIndigo600 = Color(0xFF4F46E5)      // Indigo-600 - Foco, hover
val IndigoIndigo50 = Color(0xFFF0F4FF)       // Indigo-50 - Fundos suaves

// Violet: Toque mágico e acolhedor (secundária)
val VioletViolet500 = Color(0xFFA855F7)      // Violet-500 - Gradientes, destaque
val VioletViolet600 = Color(0xFF9333EA)      // Violet-600 - Hover

// ===== EMOTIONAL FEEDBACK COLORS =====
// Rose: Acolhimento, amor, SOS (não agressivo como vermelho)
val RoseRose50 = Color(0xFFFFF1F2)           // Rose-50 - Fundos
val RoseRose100 = Color(0xFFFFE4E6)          // Rose-100 - Fundo de erros inline suaves
val RoseRose500 = Color(0xFFF43F5E)          // Rose-500 - Botão SOS, coração, amor
val RoseRose600 = Color(0xFFE11D48)          // Rose-600 - Hover

// Emerald: Esperança, crescimento, sucesso
val EmeraldEmerald50 = Color(0xFFF0FDF4)     // Emerald-50 - Fundos de sucesso
val EmeraldEmerald500 = Color(0xFF10B981)    // Emerald-500 - Sucesso, check
val EmeraldEmerald600 = Color(0xFF059669)    // Emerald-600 - Hover

// Amber: Aviso suave, ansiedade (não alarmista)
val AmberAmber50 = Color(0xFFFFFBEB)         // Amber-50 - Fundos de aviso
val AmberAmber500 = Color(0xFFF59E0B)        // Amber-500 - Aviso, ansiedade
val AmberAmber600 = Color(0xFFD97706)        // Amber-600 - Hover

// ===== NEUTRAL COLORS (Slate - Para texto e fundos) =====
// IMPORTANTE: Lumina não usa #000000 puro (causa fadiga). Usa Slate (cinza com toque azul).
val SlateSlate50 = Color(0xFFF8FAFC)         // Fundo geral (quase branco)
val SlateSlate100 = Color(0xFFE2E8F0)        // Fundos inputs, cards secundários
val SlateSlate400 = Color(0xFF94A3B8)        // Placeholder text, ícones subtis
val SlateSlate500 = Color(0xFF64748B)        // Texto secundário (light mode)
val SlateSlate600 = Color(0xFF475569)        // Texto corpo (light mode)
val SlateSlate800 = Color(0xFF1E293B)        // Headings (light mode)
val SlateSlate900 = Color(0xFF0F172A)        // Headings fortes (light mode)

// ===== DARK MODE =====
val SlateDarkBg = Color(0xFF0F172A)          // Fundo app (dark mode)
val SlateDarkSurface = Color(0xFF1E293B)     // Cards/Surfaces (dark mode)
val SlateDarkText = Color(0xFFF1F5F9)        // Texto primário (dark mode)
val SlateDarkTextSecondary = Color(0xFFCBD5E1)  // Texto secundário (dark mode)
