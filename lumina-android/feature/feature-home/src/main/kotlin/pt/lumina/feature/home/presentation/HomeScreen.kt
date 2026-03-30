package pt.lumina.feature.home.presentation

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
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
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.role
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import pt.lumina.core.ui.theme.EmeraldEmerald50
import pt.lumina.core.ui.theme.EmeraldEmerald500
import pt.lumina.core.ui.theme.EmeraldEmerald600
import pt.lumina.core.ui.theme.IndigoIndigo50
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.RoseRose500
import pt.lumina.core.ui.theme.SlateSlate100
import pt.lumina.core.ui.theme.SlateSlate400
import pt.lumina.core.ui.theme.SlateSlate500
import pt.lumina.core.ui.theme.SlateSlate600
import pt.lumina.core.ui.theme.SlateSlate800

/**
 * Ecrã principal (dashboard) da Lumina.
 *
 * Apresenta uma saudação empática personalizada, o widget de streak de
 * resiliência, um seletor compacto de humor em modo horizontal e dois
 * cards de acesso rápido às features principais: Mural da Esperança e
 * Zona Calma. Toda a interface segue as diretrizes emocionais do design
 * system Lumina: cores acolhedoras, sem tons agressivos, acessível.
 *
 * @param nomeUtilizador Nome para personalizar a saudação (default: "Alexandre")
 * @param streakDias Número de dias consecutivos de uso (Fogo da Resiliência)
 * @param onMuralClick Callback de navegação para o Mural da Esperança
 * @param onZonaCalmClick Callback de navegação para a Zona Calma
 * @param modifier Modificador Compose opcional (recebe padding do Scaffold)
 */
@Composable
fun HomeScreen(
    nomeUtilizador: String = "Alexandre",
    streakDias: Int = 7,
    onMuralClick: () -> Unit = {},
    onZonaCalmClick: () -> Unit = {},
    modifier: Modifier = Modifier,
) {
    var moodSelecionado by remember { mutableStateOf<String?>(null) }

    Column(
        modifier = modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(horizontal = 20.dp, vertical = 24.dp),
    ) {
        // Topo: Saudação personalizada + Widget de streak
        SecaoTopo(
            nomeUtilizador = nomeUtilizador,
            streakDias = streakDias,
        )

        Spacer(modifier = Modifier.height(28.dp))

        // Seletor de humor horizontal compacto (5 chips em LazyRow)
        SecaoMoodCompacto(
            moodSelecionado = moodSelecionado,
            onMoodSelecionado = { moodSelecionado = it },
        )

        Spacer(modifier = Modifier.height(28.dp))

        // Cards de acesso rápido: Mural e Zona Calma
        SecaoCards(
            onMuralClick = onMuralClick,
            onZonaCalmClick = onZonaCalmClick,
        )
    }
}

/**
 * Secção de topo com saudação empática e widget de streak.
 *
 * A saudação usa HeadlineMedium para boa legibilidade em momentos de stress.
 * O streak fica no canto superior direito para não competir com a saudação.
 */
@Composable
private fun SecaoTopo(
    nomeUtilizador: String,
    streakDias: Int,
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.Top,
    ) {
        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = "Olá, $nomeUtilizador. 👋",
                style = MaterialTheme.typography.headlineMedium,
                color = SlateSlate800,
            )
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = "Como te sentes agora?",
                style = MaterialTheme.typography.bodyLarge,
                color = SlateSlate600,
            )
        }

        Spacer(modifier = Modifier.width(12.dp))

        WidgetStreak(dias = streakDias)
    }
}

/**
 * Widget de streak — Fogo da Resiliência.
 *
 * Usa Rose500 para transmitir calor e motivação sem ser alarmista.
 * Fundo com opacidade muito baixa para ser subtil e não distrair.
 *
 * @param dias Número de dias consecutivos de uso
 */
@Composable
private fun WidgetStreak(dias: Int) {
    Column(
        modifier = Modifier
            .background(
                color = RoseRose500.copy(alpha = 0.08f),
                shape = RoundedCornerShape(16.dp),
            )
            .padding(horizontal = 12.dp, vertical = 10.dp)
            .semantics {
                // Acessibilidade: leitor de ecrã anuncia o streak completo
                contentDescription = "Fogo da Resiliência: $dias dias consecutivos"
            },
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Text(
            text = "🔥",
            style = MaterialTheme.typography.headlineSmall,
        )
        Spacer(modifier = Modifier.height(2.dp))
        Text(
            text = "$dias dias",
            style = MaterialTheme.typography.labelMedium,
            color = RoseRose500,
        )
    }
}

/**
 * Modelo de uma opção de humor para o seletor compacto do dashboard.
 */
private data class MoodOpcao(
    val id: String,
    val emoji: String,
    val label: String,
    val cor: Color,
    val corClara: Color,
)

/**
 * Seletor de humor encapsulado num card acolhedor.
 *
 * O card branco com cantos de 24dp e sombra suave cria uma "bolha" visual
 * que enquadra a secção de humor — tornando-a mais íntima e convidativa.
 * Os chips lá dentro mantêm o comportamento animado já existente.
 */
@Composable
private fun SecaoMoodCompacto(
    moodSelecionado: String?,
    onMoodSelecionado: (String) -> Unit,
) {
    val opcoes = listOf(
        MoodOpcao("esperanca", "🌟", "Esperança", EmeraldEmerald500, EmeraldEmerald50),
        MoodOpcao("calma", "🧘", "Calma", IndigoIndigo500, IndigoIndigo50),
        MoodOpcao("tristeza", "💙", "Tristeza", Color(0xFF3B82F6), Color(0xFFEFF6FF)),
        MoodOpcao("ansiedade", "⚡", "Ansiedade", Color(0xFFF59E0B), Color(0xFFFFFBEB)),
        MoodOpcao("raiva", "🔥", "Raiva", RoseRose500, Color(0xFFFFF1F2)),
    )

    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(24.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        // Elevação suave — sem sombra preta pesada (guideline Lumina)
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Como me sinto agora",
                style = MaterialTheme.typography.titleMedium,
                color = SlateSlate800,
            )

            Spacer(modifier = Modifier.height(4.dp))

            Text(
                text = "Toca numa emoção para registar o teu estado.",
                style = MaterialTheme.typography.bodySmall,
                color = SlateSlate400,
            )

            Spacer(modifier = Modifier.height(16.dp))

            LazyRow(
                horizontalArrangement = Arrangement.spacedBy(10.dp),
                contentPadding = PaddingValues(horizontal = 2.dp),
            ) {
                items(opcoes) { opcao ->
                    ChipMood(
                        opcao = opcao,
                        selecionado = moodSelecionado == opcao.id,
                        aoSelecionar = { onMoodSelecionado(opcao.id) },
                    )
                }
            }
        }
    }
}

/**
 * Chip individual de humor — compacto, animado e acessível.
 *
 * Estados com animação suave (200ms):
 * - Selecionado: fundo colorido, borda destacada, texto com cor
 * - Não selecionado: fundo branco, borda cinza suave
 */
@Composable
private fun ChipMood(
    opcao: MoodOpcao,
    selecionado: Boolean,
    aoSelecionar: () -> Unit,
) {
    val corFundo by animateColorAsState(
        targetValue = if (selecionado) opcao.corClara else Color.White,
        animationSpec = tween(200),
        label = "chip_bg_${opcao.id}",
    )
    val corBorda by animateColorAsState(
        targetValue = if (selecionado) opcao.cor else SlateSlate100,
        animationSpec = tween(200),
        label = "chip_border_${opcao.id}",
    )
    val corTexto by animateColorAsState(
        targetValue = if (selecionado) opcao.cor else SlateSlate600,
        animationSpec = tween(200),
        label = "chip_text_${opcao.id}",
    )

    Column(
        modifier = Modifier
            .size(width = 72.dp, height = 80.dp)
            .background(corFundo, RoundedCornerShape(16.dp))
            .border(1.5.dp, corBorda, RoundedCornerShape(16.dp))
            .clip(RoundedCornerShape(16.dp))
            .clickable(role = Role.RadioButton, onClick = aoSelecionar)
            .padding(vertical = 10.dp, horizontal = 8.dp)
            .semantics {
                contentDescription = "${opcao.label}${if (selecionado) ", selecionado" else ""}"
            },
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center,
    ) {
        Text(
            text = opcao.emoji,
            style = MaterialTheme.typography.titleLarge,
            textAlign = TextAlign.Center,
        )
        Spacer(modifier = Modifier.height(4.dp))
        Text(
            text = opcao.label,
            style = MaterialTheme.typography.labelSmall,
            color = corTexto,
            textAlign = TextAlign.Center,
            maxLines = 1,
        )
    }
}

/**
 * Secção dos dois cards de acesso rápido às features principais.
 *
 * Layout em Row com weight(1f) para os dois cards ficarem lado a lado
 * com largura igual, independentemente do tamanho do ecrã.
 */
@Composable
private fun SecaoCards(
    onMuralClick: () -> Unit,
    onZonaCalmClick: () -> Unit,
) {
    Text(
        text = "O que queres explorar?",
        style = MaterialTheme.typography.titleMedium,
        color = SlateSlate800,
    )

    Spacer(modifier = Modifier.height(14.dp))

    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(14.dp),
    ) {
        // Card: Mural da Esperança — tons Indigo para transmitir profundidade e calma
        CardAcaoRapida(
            titulo = "Mural da\nEsperança",
            emoji = "🌸",
            descricao = "Partilha e lê mensagens de apoio",
            gradiente = Brush.verticalGradient(
                colors = listOf(IndigoIndigo500, Color(0xFF4338CA)),
            ),
            onClick = onMuralClick,
            modifier = Modifier.weight(1f),
            descricaoAcessibilidade = "Aceder ao Mural da Esperança",
        )

        // Card: Zona Calma — tons Emerald para transmitir esperança e crescimento
        CardAcaoRapida(
            titulo = "Zona\nCalma",
            emoji = "🌿",
            descricao = "Exercícios de respiração e meditação",
            gradiente = Brush.verticalGradient(
                colors = listOf(EmeraldEmerald500, EmeraldEmerald600),
            ),
            onClick = onZonaCalmClick,
            modifier = Modifier.weight(1f),
            descricaoAcessibilidade = "Aceder à Zona Calma",
        )
    }
}

/**
 * Card de acesso rápido com gradiente e ripple effect nativo.
 *
 * Touch target garantido por height(160.dp) — bem acima do mínimo de 44dp.
 * Cantos arredondados (24.dp) para uma estética empática e acolhedora.
 * Sem sombras pretas pesadas — usa gradiente de cor para profundidade.
 *
 * @param titulo Título do card (em PT-PT)
 * @param emoji Emoji decorativo no canto superior direito
 * @param descricao Texto de apoio descritivo
 * @param gradiente Brush de gradiente vertical com cores do design system
 * @param onClick Callback ao clicar no card
 * @param modifier Modificador Compose (deve incluir weight(1f) em Row)
 * @param descricaoAcessibilidade Texto lido pelo TalkBack
 */
@Composable
private fun CardAcaoRapida(
    titulo: String,
    emoji: String,
    descricao: String,
    gradiente: Brush,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    descricaoAcessibilidade: String,
) {
    Box(
        modifier = modifier
            .height(160.dp)
            .clip(RoundedCornerShape(24.dp))
            .background(gradiente)
            .clickable(onClick = onClick)
            .semantics {
                contentDescription = descricaoAcessibilidade
                role = Role.Button
            }
            .padding(18.dp),
    ) {
        // Emoji decorativo no canto superior direito
        Text(
            text = emoji,
            style = MaterialTheme.typography.displaySmall,
            modifier = Modifier.align(Alignment.TopEnd),
        )

        // Título e descrição no canto inferior esquerdo
        Column(modifier = Modifier.align(Alignment.BottomStart)) {
            Text(
                text = titulo,
                style = MaterialTheme.typography.titleMedium,
                color = Color.White,
            )
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = descricao,
                style = MaterialTheme.typography.labelSmall,
                color = Color.White.copy(alpha = 0.85f),
            )
        }
    }
}
