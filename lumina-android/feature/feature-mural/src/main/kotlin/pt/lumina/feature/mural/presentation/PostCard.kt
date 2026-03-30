package pt.lumina.feature.mural.presentation

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.collectIsPressedAsState
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.blur
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.role
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import pt.lumina.core.ui.theme.AmberAmber50
import pt.lumina.core.ui.theme.AmberAmber600
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.SlateSlate100
import pt.lumina.core.ui.theme.SlateSlate400
import pt.lumina.core.ui.theme.SlateSlate50
import pt.lumina.core.ui.theme.SlateSlate500
import pt.lumina.core.ui.theme.SlateSlate600
import pt.lumina.core.ui.theme.SlateSlate800

/**
 * Modelo de dados de uma publicação no Mural da Esperança.
 * Usado apenas para mock/preview até à integração com API real.
 */
data class PostMock(
    val id: String,
    val autorInicial: String,
    val autorNome: String,
    val tempoRelativo: String,
    val emocaoTag: String,
    val conteudo: String,
    val temAudio: Boolean = false,
    val isSensitive: Boolean = false,
)

/**
 * Cartão de desabafo do Mural da Esperança.
 *
 * Apresenta uma publicação de forma empática e acolhedora:
 * cabeçalho com avatar (inicial), nome, tempo e chip de emoção;
 * corpo textual (ou player de áudio se for post sussurrado);
 * rodapé com 3 reações orgânicas (🫂 🕯️ 👂).
 *
 * Conteúdo sensível: se `isSensitive` for true, o corpo fica desfocado
 * e exibe um overlay clicável — o utilizador decide quando revelar.
 *
 * @param post Dados da publicação a apresentar
 * @param modifier Modificador Compose opcional
 */
@Composable
fun PostCard(
    post: PostMock,
    modifier: Modifier = Modifier,
) {
    // Controla se o conteúdo sensível foi revelado pelo utilizador
    var reveladoSensivel by remember { mutableStateOf(false) }

    Card(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(24.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
    ) {
        Column(modifier = Modifier.padding(20.dp)) {

            // ─── Cabeçalho: avatar + nome/tempo + chip de emoção ─────────────
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                // Avatar circular com a inicial do autor
                Box(
                    modifier = Modifier
                        .size(40.dp)
                        .background(
                            color = IndigoIndigo500.copy(alpha = 0.12f),
                            shape = CircleShape,
                        ),
                    contentAlignment = Alignment.Center,
                ) {
                    Text(
                        text = post.autorInicial,
                        style = MaterialTheme.typography.titleSmall.copy(
                            fontWeight = FontWeight.Bold,
                            fontSize = 16.sp,
                        ),
                        color = IndigoIndigo500,
                    )
                }

                Spacer(modifier = Modifier.width(12.dp))

                // Nome e tempo relativo
                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = post.autorNome,
                        style = MaterialTheme.typography.titleSmall.copy(
                            fontWeight = FontWeight.SemiBold,
                        ),
                        color = SlateSlate800,
                    )
                    Text(
                        text = post.tempoRelativo,
                        style = MaterialTheme.typography.labelSmall,
                        color = SlateSlate400,
                    )
                }

                // Chip de emoção no canto superior direito
                Box(
                    modifier = Modifier
                        .background(
                            color = AmberAmber50,
                            shape = RoundedCornerShape(100.dp),
                        )
                        .border(
                            width = 1.dp,
                            color = AmberAmber600.copy(alpha = 0.3f),
                            shape = RoundedCornerShape(100.dp),
                        )
                        .padding(horizontal = 10.dp, vertical = 4.dp),
                ) {
                    Text(
                        text = post.emocaoTag,
                        style = MaterialTheme.typography.labelSmall.copy(
                            fontWeight = FontWeight.SemiBold,
                        ),
                        color = AmberAmber600,
                    )
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            // ─── Corpo: texto ou player de áudio ─────────────────────────────
            if (post.temAudio) {
                PlayerAudio()
            } else {
                // Conteúdo sensível: desfocado até o utilizador revelar
                if (post.isSensitive && !reveladoSensivel) {
                    Box(
                        contentAlignment = Alignment.Center,
                    ) {
                        Text(
                            text = post.conteudo,
                            style = MaterialTheme.typography.bodyMedium,
                            color = SlateSlate600,
                            modifier = Modifier.blur(12.dp),
                        )
                        // Overlay clicável para revelar o conteúdo
                        Box(
                            modifier = Modifier
                                .matchParentSize()
                                .background(
                                    color = Color.White.copy(alpha = 0.6f),
                                    shape = RoundedCornerShape(12.dp),
                                )
                                .clickable { reveladoSensivel = true }
                                .semantics {
                                    contentDescription = "Tocar para revelar conteúdo sensível"
                                    role = Role.Button
                                },
                            contentAlignment = Alignment.Center,
                        ) {
                            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                                Text(
                                    text = "🔒",
                                    fontSize = 24.sp,
                                )
                                Spacer(modifier = Modifier.height(6.dp))
                                Text(
                                    text = "Tocar para revelar",
                                    style = MaterialTheme.typography.labelMedium,
                                    color = SlateSlate500,
                                    textAlign = TextAlign.Center,
                                )
                                Text(
                                    text = "Conteúdo Sensível",
                                    style = MaterialTheme.typography.labelSmall,
                                    color = SlateSlate400,
                                    textAlign = TextAlign.Center,
                                )
                            }
                        }
                    }
                } else {
                    Text(
                        text = post.conteudo,
                        style = MaterialTheme.typography.bodyMedium,
                        color = SlateSlate600,
                    )
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            // ─── Divisor subtil ───────────────────────────────────────────────
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(1.dp)
                    .background(SlateSlate100),
            )

            Spacer(modifier = Modifier.height(12.dp))

            // ─── Rodapé: reações orgânicas ────────────────────────────────────
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(8.dp),
            ) {
                BotaoReacao(emoji = "🫂", descricao = "Abraço", modifier = Modifier.weight(1f))
                BotaoReacao(emoji = "🕯️", descricao = "Vela", modifier = Modifier.weight(1f))
                BotaoReacao(emoji = "👂", descricao = "Ouvir", modifier = Modifier.weight(1f))
            }
        }
    }
}

/**
 * Player de áudio minimalista para posts do Mural Sussurrado.
 *
 * Botão de play circular em Indigo100 com uma linha simulando a onda sonora.
 * Visual estático nesta fase — comportamento de playback real em iteração futura.
 */
@Composable
private fun PlayerAudio() {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                color = SlateSlate50,
                shape = RoundedCornerShape(16.dp),
            )
            .padding(horizontal = 16.dp, vertical = 12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        // Botão de play circular
        Box(
            modifier = Modifier
                .size(44.dp)
                .background(
                    color = IndigoIndigo500.copy(alpha = 0.1f),
                    shape = CircleShape,
                )
                .clickable { /* Playback em iteração futura */ }
                .semantics {
                    contentDescription = "Reproduzir sussurro de voz"
                    role = Role.Button
                },
            contentAlignment = Alignment.Center,
        ) {
            Text(text = "▶", fontSize = 16.sp, color = IndigoIndigo500)
        }

        Spacer(modifier = Modifier.width(14.dp))

        // Linha de onda sonora simulada — barras de altura variável
        Row(
            modifier = Modifier.weight(1f),
            horizontalArrangement = Arrangement.spacedBy(3.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            // Alturas em dp para simular uma onda realista (padrão visual estático)
            val alturas = listOf(8, 16, 12, 20, 10, 24, 14, 18, 8, 22, 12, 16, 10, 20, 8)
            alturas.forEach { altura ->
                Box(
                    modifier = Modifier
                        .width(3.dp)
                        .height(altura.dp)
                        .background(
                            color = IndigoIndigo500.copy(alpha = 0.5f),
                            shape = RoundedCornerShape(100.dp),
                        ),
                )
            }
        }

        Spacer(modifier = Modifier.width(14.dp))

        Text(
            text = "0:32",
            style = MaterialTheme.typography.labelSmall,
            color = SlateSlate400,
        )
    }
}

/**
 * Botão de reação orgânico — fundo Slate50 com destaque suave no press.
 *
 * Touch target garantido por padding interno generoso (≥ 44dp).
 * A cor do fundo anima entre Slate50 e Indigo50 ao pressionar.
 *
 * @param emoji Emoji da reação
 * @param descricao Descrição para acessibilidade TalkBack
 */
@Composable
private fun BotaoReacao(
    emoji: String,
    descricao: String,
    modifier: Modifier = Modifier,
) {
    val interactionSource = remember { MutableInteractionSource() }
    val isPressed by interactionSource.collectIsPressedAsState()

    val corFundo by animateColorAsState(
        targetValue = if (isPressed) IndigoIndigo500.copy(alpha = 0.08f) else SlateSlate50,
        animationSpec = tween(150),
        label = "reacao_bg_$descricao",
    )

    Box(
        modifier = modifier
            .background(corFundo, RoundedCornerShape(12.dp))
            .clickable(interactionSource = interactionSource, indication = null) { }
            .padding(vertical = 10.dp)
            .semantics {
                contentDescription = "Reagir com $descricao"
                role = Role.Button
            },
        contentAlignment = Alignment.Center,
    ) {
        Text(text = emoji, fontSize = 20.sp)
    }
}
