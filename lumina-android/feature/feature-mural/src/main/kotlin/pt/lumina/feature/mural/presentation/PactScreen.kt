package pt.lumina.feature.mural.presentation

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.role
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.font.FontStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.IndigoIndigo600
import pt.lumina.core.ui.theme.SlateSlate400
import pt.lumina.core.ui.theme.SlateSlate500
import pt.lumina.core.ui.theme.SlateSlate600
import pt.lumina.core.ui.theme.SlateSlate800
import pt.lumina.core.ui.theme.VioletViolet500

// ─── Cores locais do Casulo — não existem no Color.kt global ─────────────────
// O fundo radial é exclusivo desta experiência de grupo privado
private val Violet50 = Color(0xFFF5F3FF)
private val Indigo100 = Color(0xFFE0E7FF)

// ─── Dados mock dos membros do Casulo ────────────────────────────────────────
// 12 membros — limite máximo de um Casulo da Resiliência
private data class MembroCasulo(val inicial: String, val corFundo: Color)

private val membrosMock = listOf(
    MembroCasulo("M", Color(0xFFDDD6FE)),  // violet-200
    MembroCasulo("J", Color(0xFFBFDBFE)),  // blue-200
    MembroCasulo("S", Color(0xFFA7F3D0)),  // emerald-200
    MembroCasulo("R", Color(0xFFFECDD3)),  // rose-200
    MembroCasulo("A", Color(0xFFFDE68A)),  // amber-200
    MembroCasulo("T", Color(0xFFDDD6FE)),  // violet-200
    MembroCasulo("C", Color(0xFFBFDBFE)),  // blue-200
    MembroCasulo("L", Color(0xFFA7F3D0)),  // emerald-200
    MembroCasulo("F", Color(0xFFFECDD3)),  // rose-200
    MembroCasulo("P", Color(0xFFFDE68A)),  // amber-200
    MembroCasulo("B", Color(0xFFDDD6FE)),  // violet-200
    MembroCasulo("I", Color(0xFFBFDBFE)),  // blue-200
)

/**
 * Ecrã do Casulo da Resiliência — grupo privado de apoio mútuo.
 *
 * Experiência íntima de 12 pessoas num espaço seguro e fechado.
 * Design: gradiente radial muito suave (Violet50 + Slate50) para
 * criar sensação de abrigo e calor — contraste com o Mural aberto.
 *
 * Secções:
 * 1. Título e subtítulo acolhedor
 * 2. Círculo de membros — avatares sobrepostos (overlap de -12dp)
 * 3. Cartão da Missão do Dia com Reflexão e botão de partilha
 *
 * @param onPartilharClick Callback ao clicar em "Partilhar no Casulo"
 * @param modifier Modificador Compose (recebe padding do Scaffold)
 */
@Composable
fun PactScreen(
    onPartilharClick: () -> Unit = {},
    modifier: Modifier = Modifier,
) {
    Box(
        modifier = modifier
            .fillMaxSize()
            .background(
                // Gradiente radial muito suave — mistura Violet50 com Slate50
                brush = Brush.radialGradient(
                    colors = listOf(Violet50, Color(0xFFF8FAFC)),
                    radius = 1200f,
                ),
            ),
    ) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
                .padding(horizontal = 24.dp, vertical = 28.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {

            // ─── Cabeçalho ────────────────────────────────────────────────────
            CabecalhoCasulo()

            Spacer(modifier = Modifier.height(32.dp))

            // ─── Círculo de membros ───────────────────────────────────────────
            CirculoDeMembros(membros = membrosMock)

            Spacer(modifier = Modifier.height(10.dp))

            Text(
                text = "${membrosMock.size} pessoas neste Casulo",
                style = MaterialTheme.typography.labelMedium,
                color = SlateSlate400,
            )

            Spacer(modifier = Modifier.height(36.dp))

            // ─── Cartão da Missão do Dia ──────────────────────────────────────
            CartaoMissaoDia(onPartilharClick = onPartilharClick)

            Spacer(modifier = Modifier.height(24.dp))

            // ─── Nota de privacidade ──────────────────────────────────────────
            Text(
                text = "🔒  O que acontece no Casulo, fica no Casulo.",
                style = MaterialTheme.typography.bodySmall.copy(fontStyle = FontStyle.Italic),
                color = SlateSlate400,
                textAlign = TextAlign.Center,
            )
        }
    }
}

/**
 * Cabeçalho com título do Casulo e subtítulo empático.
 */
@Composable
private fun CabecalhoCasulo() {
    // Badge de privacidade
    Box(
        modifier = Modifier
            .background(
                color = VioletViolet500.copy(alpha = 0.10f),
                shape = RoundedCornerShape(100.dp),
            )
            .padding(horizontal = 14.dp, vertical = 6.dp),
    ) {
        Text(
            text = "✦  Grupo Privado",
            style = MaterialTheme.typography.labelMedium.copy(
                letterSpacing = 1.2.sp,
            ),
            color = VioletViolet500,
        )
    }

    Spacer(modifier = Modifier.height(16.dp))

    Text(
        text = "Casulo da\nResiliência",
        style = MaterialTheme.typography.headlineMedium,
        color = SlateSlate800,
        textAlign = TextAlign.Center,
    )

    Spacer(modifier = Modifier.height(10.dp))

    Text(
        text = "Um espaço íntimo de confiança e apoio mútuo.\nCada voz aqui importa.",
        style = MaterialTheme.typography.bodyMedium,
        color = SlateSlate500,
        textAlign = TextAlign.Center,
    )
}

/**
 * Círculo de membros com avatares sobrepostos.
 *
 * Usa offset negativo (-12dp por avatar) para simular a sobreposição
 * característica dos "face piles" — padrão de UI que transmite comunidade.
 * Cada avatar tem uma borda branca de 2dp para separação visual.
 *
 * @param membros Lista de membros do Casulo (máximo 12)
 */
@Composable
private fun CirculoDeMembros(membros: List<MembroCasulo>) {
    Row(
        horizontalArrangement = Arrangement.spacedBy((-12).dp),
        verticalAlignment = Alignment.CenterVertically,
        modifier = Modifier.semantics {
            contentDescription = "${membros.size} membros neste Casulo"
        },
    ) {
        membros.forEach { membro ->
            Box(
                modifier = Modifier
                    .size(44.dp)
                    .clip(CircleShape)
                    .background(membro.corFundo)
                    .border(2.dp, Color.White, CircleShape),
                contentAlignment = Alignment.Center,
            ) {
                Text(
                    text = membro.inicial,
                    style = MaterialTheme.typography.labelLarge.copy(
                        fontWeight = FontWeight.Bold,
                        fontSize = 15.sp,
                    ),
                    color = SlateSlate600,
                )
            }
        }
    }
}

/**
 * Cartão central da Missão do Dia — fundo Indigo500, texto branco.
 *
 * Destaca-se visualmente como o coração da experiência do Casulo.
 * Cantos muito arredondados (32dp) para reforçar a sensação de acolhimento.
 * Contém a reflexão do dia e o botão de partilha.
 *
 * @param onPartilharClick Callback ao clicar em "Partilhar no Casulo"
 */
@Composable
private fun CartaoMissaoDia(onPartilharClick: () -> Unit) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                brush = Brush.verticalGradient(
                    colors = listOf(IndigoIndigo500, IndigoIndigo600),
                ),
                shape = RoundedCornerShape(32.dp),
            )
            .padding(28.dp),
    ) {
        Column {
            // Label da missão
            Box(
                modifier = Modifier
                    .background(
                        color = Color.White.copy(alpha = 0.15f),
                        shape = RoundedCornerShape(100.dp),
                    )
                    .padding(horizontal = 12.dp, vertical = 5.dp),
            ) {
                Text(
                    text = "✦  REFLEXÃO DO DIA",
                    style = MaterialTheme.typography.labelMedium.copy(
                        letterSpacing = 1.5.sp,
                    ),
                    color = Color.White.copy(alpha = 0.85f),
                )
            }

            Spacer(modifier = Modifier.height(20.dp))

            // Citação da reflexão (mock — será dinâmica quando houver API)
            Text(
                text = "\"Não precisas de ter tudo resolvido hoje.\nBasta um passo de cada vez.\"",
                style = MaterialTheme.typography.titleMedium.copy(
                    fontStyle = FontStyle.Italic,
                    lineHeight = 28.sp,
                ),
                color = Color.White,
            )

            Spacer(modifier = Modifier.height(8.dp))

            Text(
                text = "— Reflexão do Casulo, 30 Mar 2026",
                style = MaterialTheme.typography.labelSmall,
                color = Color.White.copy(alpha = 0.65f),
            )

            Spacer(modifier = Modifier.height(28.dp))

            // Prompt de partilha
            Text(
                text = "Como é que esta reflexão ressoa contigo hoje?",
                style = MaterialTheme.typography.bodyMedium.copy(
                    color = Color.White.copy(alpha = 0.85f),
                ),
            )

            Spacer(modifier = Modifier.height(20.dp))

            // Botão de partilha
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(
                        color = Color.White,
                        shape = RoundedCornerShape(16.dp),
                    )
                    .then(
                        Modifier.semantics {
                            contentDescription = "Partilhar reflexão no Casulo"
                            role = Role.Button
                        }
                    )
                    .padding(vertical = 16.dp),
                contentAlignment = Alignment.Center,
            ) {
                Text(
                    text = "Partilhar no Casulo",
                    style = MaterialTheme.typography.titleSmall.copy(
                        fontWeight = FontWeight.Bold,
                    ),
                    color = IndigoIndigo600,
                )
            }
        }
    }
}
