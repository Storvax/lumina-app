package pt.lumina.feature.mural.presentation

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.unit.dp
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.SlateSlate400
import pt.lumina.core.ui.theme.SlateSlate800

// ─── Dados mock para visualização no Android Studio Preview ──────────────────
// Substituir por dados reais do ViewModel quando a API estiver integrada
private val postsMock = listOf(
    PostMock(
        id = "1",
        autorInicial = "M",
        autorNome = "Mariana",
        tempoRelativo = "há 2h",
        emocaoTag = "Ansiedade",
        conteudo = "Hoje foi um dia muito difícil. Sinto que não consigo parar de pensar em tudo o que pode correr mal. Mas estou aqui, a respirar, a tentar.",
    ),
    PostMock(
        id = "2",
        autorInicial = "J",
        autorNome = "João",
        tempoRelativo = "há 4h",
        emocaoTag = "Esperança",
        conteudo = "Depois de semanas muito pesadas, hoje consegui sair de casa e caminhar 10 minutos. Para mim, isso foi enorme. Obrigado a quem me ouviu.",
    ),
    PostMock(
        id = "3",
        autorInicial = "S",
        autorNome = "Sofia",
        tempoRelativo = "há 5h",
        emocaoTag = "Tristeza",
        temAudio = true,
        conteudo = "Sussurro de voz — 0:32",
    ),
    PostMock(
        id = "4",
        autorInicial = "R",
        autorNome = "Ricardo",
        tempoRelativo = "há 7h",
        emocaoTag = "Ansiedade",
        conteudo = "Às vezes o peso que carrego parece não ter fim. Preciso de partilhar isto com alguém que entenda.",
        isSensitive = true,
    ),
    PostMock(
        id = "5",
        autorInicial = "A",
        autorNome = "Ana",
        tempoRelativo = "há 9h",
        emocaoTag = "Calma",
        conteudo = "Encontrei um sítio tranquilo perto de casa. Sentei-me 20 minutos a ouvir os pássaros. Recomendo a todos.",
    ),
    PostMock(
        id = "6",
        autorInicial = "T",
        autorNome = "Tomás",
        tempoRelativo = "há 12h",
        emocaoTag = "Esperança",
        conteudo = "Primeira semana sem falhar o sono à mesma hora. Pequenas vitórias também contam.",
    ),
)

/**
 * Ecrã principal do Mural da Esperança.
 *
 * Feed comunitário de partilhas emocionais numa `LazyColumn` com
 * cards acolhedores. O FAB flutuante no fundo permite criar um novo desabafo.
 *
 * Dados mock temporários — serão substituídos pelo ViewModel com API real.
 *
 * @param onEscreverClick Callback ao clicar no FAB de nova publicação
 * @param modifier Modificador Compose (recebe padding do Scaffold externo)
 */
@Composable
fun WallScreen(
    onEscreverClick: () -> Unit = {},
    modifier: Modifier = Modifier,
) {
    Scaffold(
        modifier = modifier.fillMaxSize(),
        containerColor = Color(0xFFF8FAFC), // Slate50 — fundo limpo e suave
        floatingActionButton = {
            // FAB orgânico para criar novo desabafo
            FloatingActionButton(
                onClick = onEscreverClick,
                shape = RoundedCornerShape(24.dp),
                containerColor = IndigoIndigo500,
                contentColor = Color.White,
                modifier = Modifier.semantics {
                    contentDescription = "Escrever novo desabafo no Mural"
                },
            ) {
                Text(
                    text = "✍  Escrever",
                    style = MaterialTheme.typography.titleSmall,
                    modifier = Modifier.padding(horizontal = 8.dp),
                )
            }
        },
    ) { paddingValues ->
        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues),
            contentPadding = PaddingValues(horizontal = 20.dp, vertical = 24.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp),
        ) {
            // ─── Cabeçalho do feed ────────────────────────────────────────────
            item {
                CabecalhoMural()
                Spacer(modifier = Modifier.height(8.dp))
            }

            // ─── Feed de publicações ──────────────────────────────────────────
            items(postsMock, key = { it.id }) { post ->
                PostCard(post = post)
            }

            // Espaço extra no final para o FAB não tapar o último card
            item { Spacer(modifier = Modifier.height(80.dp)) }
        }
    }
}

/**
 * Cabeçalho do Mural com título principal e subtítulo acolhedor.
 *
 * Tom deliberadamente empático — não é um "feed social" genérico,
 * é um espaço seguro de partilha emocional.
 */
@Composable
private fun CabecalhoMural() {
    Text(
        text = "Mural da Esperança",
        style = MaterialTheme.typography.headlineMedium,
        color = SlateSlate800,
    )
    Spacer(modifier = Modifier.height(6.dp))
    Text(
        text = "Um espaço seguro para partilhar, ouvir e sentir que não estás sozinho.",
        style = MaterialTheme.typography.bodyMedium,
        color = SlateSlate400,
    )
}
