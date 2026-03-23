package pt.lumina.core.ui.components

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.role
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import pt.lumina.core.ui.theme.EmeraldEmerald50
import pt.lumina.core.ui.theme.EmeraldEmerald500
import pt.lumina.core.ui.theme.IndigoIndigo50
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.AmberAmber50
import pt.lumina.core.ui.theme.AmberAmber500
import pt.lumina.core.ui.theme.RoseRose50
import pt.lumina.core.ui.theme.RoseRose500
import pt.lumina.core.ui.theme.SlateSlate100
import pt.lumina.core.ui.theme.SlateSlate600
import pt.lumina.core.ui.theme.SlateSlate800

/**
 * Modelo de um estado emocional para o seletor.
 *
 * @param id Identificador único (ex: "esperanca", "calma", etc)
 * @param label Rótulo em PT-PT
 * @param emoji Emoji representativo
 * @param color Cor primária associada
 * @param lightColor Cor clara para fundo quando selecionado
 */
private data class MoodOption(
    val id: String,
    val label: String,
    val emoji: String,
    val color: Color,
    val lightColor: Color,
)

/**
 * Seletor de emoções Lumina.
 *
 * Características:
 * - 5 opções emocionais com cores do design system
 * - Cards clicáveis (não radio buttons)
 * - Animação suave ao selecionar (200ms)
 * - Estado selecionado: fundo colorido + texto correspondente
 * - Estado não-selecionado: fundo cinza claro + borda
 * - Touch target mínimo 44x44 dp
 * - Acessibilidade: roles semânticos, descrições de estado
 *
 * Emoções incluídas:
 * - Esperança (Emerald) 💚
 * - Calma (Indigo) 💙
 * - Tristeza (Blue/Slate) 💙
 * - Ansiedade (Amber) 💛
 * - Raiva (Rose) 🌹
 *
 * @param selectedMood ID do mood selecionado (ou null se nenhum)
 * @param onMoodSelected Lambda executada ao selecionar um mood
 * @param modifier Modificador Compose opcional
 */
@Composable
fun MoodSelector(
    selectedMood: String?,
    onMoodSelected: (String) -> Unit,
    modifier: Modifier = Modifier,
) {
    // Definição das 5 opções de mood
    val moodOptions = listOf(
        MoodOption(
            id = "esperanca",
            label = "Esperança",
            emoji = "🌟",
            color = EmeraldEmerald500,
            lightColor = EmeraldEmerald50,
        ),
        MoodOption(
            id = "calma",
            label = "Calma",
            emoji = "🧘",
            color = IndigoIndigo500,
            lightColor = IndigoIndigo50,
        ),
        MoodOption(
            id = "tristeza",
            label = "Tristeza",
            emoji = "💙",
            color = Color(0xFF3B82F6),  // Blue-500 (Tailwind)
            lightColor = Color(0xFFEFF6FF),  // Blue-50
        ),
        MoodOption(
            id = "ansiedade",
            label = "Ansiedade",
            emoji = "⚡",
            color = AmberAmber500,
            lightColor = AmberAmber50,
        ),
        MoodOption(
            id = "raiva",
            label = "Raiva",
            emoji = "🔥",
            color = RoseRose500,
            lightColor = RoseRose50,
        ),
    )

    Column(
        modifier = modifier
            .fillMaxWidth()
            .semantics {
                // Acessibilidade: descreve a função do seletor
                this.contentDescription = "Seletor de emoções. Selecione como se sente agora."
            }
    ) {
        // Título do seletor
        Text(
            text = "Como se sente agora?",
            style = MaterialTheme.typography.titleMedium,
            color = SlateSlate800,
            modifier = Modifier.padding(bottom = 16.dp)
        )

        // Grid de opções (2 por linha, com 5 opções = 3 linhas)
        for (i in moodOptions.indices step 2) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 12.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                // Primeira opção da linha
                MoodOptionCard(
                    moodOption = moodOptions[i],
                    isSelected = selectedMood == moodOptions[i].id,
                    onSelect = { onMoodSelected(moodOptions[i].id) },
                    modifier = Modifier.weight(1f)
                )

                // Segunda opção da linha (se existir)
                if (i + 1 < moodOptions.size) {
                    MoodOptionCard(
                        moodOption = moodOptions[i + 1],
                        isSelected = selectedMood == moodOptions[i + 1].id,
                        onSelect = { onMoodSelected(moodOptions[i + 1].id) },
                        modifier = Modifier.weight(1f)
                    )
                } else {
                    // Spacer se for última opção ímpar
                    Box(modifier = Modifier.weight(1f))
                }
            }
        }
    }
}

/**
 * Card individual para uma opção de mood.
 *
 * Estados:
 * - Selecionado: fundo colorido (lightColor), texto da cor (color)
 * - Não selecionado: fundo cinza claro, borda suave
 *
 * @param moodOption Dados do mood
 * @param isSelected Se true, renderiza no estado selecionado
 * @param onSelect Lambda executada ao clicar
 * @param modifier Modificador Compose opcional
 */
@Composable
private fun MoodOptionCard(
    moodOption: MoodOption,
    isSelected: Boolean,
    onSelect: () -> Unit,
    modifier: Modifier = Modifier,
) {
    // Animação da cor de fundo
    val animatedBackgroundColor = animateColorAsState(
        targetValue = if (isSelected) {
            moodOption.lightColor
        } else {
            Color.White
        },
        animationSpec = tween(durationMillis = 200),
        label = "mood_bg_color_${moodOption.id}"
    )

    // Animação da cor da borda
    val animatedBorderColor = animateColorAsState(
        targetValue = if (isSelected) {
            moodOption.color
        } else {
            SlateSlate100
        },
        animationSpec = tween(durationMillis = 200),
        label = "mood_border_color_${moodOption.id}"
    )

    // Animação da cor do texto
    val animatedTextColor = animateColorAsState(
        targetValue = if (isSelected) {
            moodOption.color
        } else {
            SlateSlate600
        },
        animationSpec = tween(durationMillis = 200),
        label = "mood_text_color_${moodOption.id}"
    )

    Box(
        modifier = modifier
            .background(
                color = animatedBackgroundColor.value,
                shape = RoundedCornerShape(12.dp)
            )
            .border(
                width = 2.dp,
                color = animatedBorderColor.value,
                shape = RoundedCornerShape(12.dp)
            )
            .clickable(
                onClick = onSelect,
                role = Role.RadioButton,  // Acessibilidade: trata como radio button
            )
            .padding(16.dp)  // Espaçamento interior
            .semantics(mergeDescendants = true) {
                // Acessibilidade: descreve o estado selecionado
                this.contentDescription = "${moodOption.label}${if (isSelected) ", selecionado" else ""}"
            },
        contentAlignment = Alignment.Center
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .align(Alignment.Center),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            // Emoji do mood
            Text(
                text = moodOption.emoji,
                style = MaterialTheme.typography.headlineLarge,
                textAlign = TextAlign.Center,
                modifier = Modifier.padding(bottom = 8.dp)
            )

            // Label do mood
            Text(
                text = moodOption.label,
                style = MaterialTheme.typography.labelLarge,
                color = animatedTextColor.value,
                textAlign = TextAlign.Center
            )
        }
    }
}
