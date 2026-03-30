package pt.lumina.navigation

import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Spa
import androidx.compose.material.icons.filled.Group
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.unit.dp
import pt.lumina.core.ui.theme.IndigoIndigo500
import pt.lumina.core.ui.theme.SlateSlate400
import pt.lumina.feature.home.presentation.HomeScreen
import pt.lumina.feature.home.presentation.ZonaCalmaScreen
import pt.lumina.feature.mural.presentation.PactScreen
import pt.lumina.feature.mural.presentation.WallScreen

/**
 * Modelo de um separador da barra de navegação inferior.
 *
 * @param rota Identificador interno da rota (usado como chave de estado)
 * @param icone Ícone Material 3
 * @param iconeDescricao Descrição do ícone para acessibilidade
 * @param rotulo Rótulo visível em PT-PT
 */
private data class SeparadorNav(
    val rota: String,
    val icone: ImageVector,
    val iconeDescricao: String,
    val rotulo: String,
)

/**
 * Ecrã principal pós-autenticação com navegação inferior.
 *
 * Gere o Scaffold com NavigationBar (4 separadores: Home, Mural, Casulo, Zona Calma)
 * e renderiza o conteúdo do separador ativo. Usa rememberSaveable para
 * preservar o separador ativo em rotações de ecrã sem ViewModel dedicado.
 *
 * Design:
 * - NavigationBar sem elevação (tonalElevation = 0) — sem sombras pesadas
 * - Fundo branco para contraste limpo com o conteúdo
 * - Indicador de item ativo com Indigo a 12% de opacidade
 */
@Composable
fun MainScreen() {
    val separadores = listOf(
        SeparadorNav(
            rota = "home",
            icone = Icons.Default.Home,
            iconeDescricao = "Início",
            rotulo = "Início",
        ),
        SeparadorNav(
            rota = "mural",
            icone = Icons.Default.Favorite,
            iconeDescricao = "Mural da Esperança",
            rotulo = "Mural",
        ),
        SeparadorNav(
            rota = "casulo",
            icone = Icons.Default.Group,
            iconeDescricao = "Casulo da Resiliência",
            rotulo = "Casulo",
        ),
        SeparadorNav(
            rota = "zona_calma",
            icone = Icons.Default.Spa,
            iconeDescricao = "Zona Calma",
            rotulo = "Calma",
        ),
    )

    // rememberSaveable preserva o separador ativo após rotação de ecrã
    var separadorAtivo by rememberSaveable { mutableStateOf("home") }

    Scaffold(
        bottomBar = {
            NavegacaoInferior(
                separadores = separadores,
                separadorAtivo = separadorAtivo,
                aoSelecionarSeparador = { separadorAtivo = it },
            )
        },
    ) { paddingValues ->
        when (separadorAtivo) {
            "home" -> HomeScreen(
                onMuralClick = { separadorAtivo = "mural" },
                onCasuloClick = { separadorAtivo = "casulo" },
                onZonaCalmClick = { separadorAtivo = "zona_calma" },
                modifier = Modifier.padding(paddingValues),
            )

            "mural" -> WallScreen(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues),
            )

            "casulo" -> PactScreen(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues),
            )

            "zona_calma" -> ZonaCalmaScreen(
                onBack = { separadorAtivo = "home" },
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues),
            )
        }
    }
}

/**
 * Barra de navegação inferior limpa, sem sombras pesadas.
 *
 * Segue as diretrizes de acessibilidade:
 * - Touch targets nativos do NavigationBarItem (mínimo 48dp gerido pelo M3)
 * - Ripple effect nativo ao clicar
 * - Semântica de seleção anunciada pelo TalkBack
 *
 * @param separadores Lista de separadores a apresentar
 * @param separadorAtivo Rota do separador atualmente visível
 * @param aoSelecionarSeparador Callback com a rota do separador clicado
 */
@Composable
private fun NavegacaoInferior(
    separadores: List<SeparadorNav>,
    separadorAtivo: String,
    aoSelecionarSeparador: (String) -> Unit,
) {
    NavigationBar(
        containerColor = Color.White,
        // Sem elevação tonal — evita sombras pretas pesadas (guideline Lumina)
        tonalElevation = 0.dp,
        modifier = Modifier.semantics {
            contentDescription = "Navegação principal"
        },
    ) {
        separadores.forEach { separador ->
            val ativo = separador.rota == separadorAtivo

            NavigationBarItem(
                selected = ativo,
                onClick = { aoSelecionarSeparador(separador.rota) },
                icon = {
                    Icon(
                        imageVector = separador.icone,
                        contentDescription = separador.iconeDescricao,
                    )
                },
                label = {
                    Text(
                        text = separador.rotulo,
                        style = MaterialTheme.typography.labelMedium,
                    )
                },
                colors = NavigationBarItemDefaults.colors(
                    selectedIconColor = IndigoIndigo500,
                    selectedTextColor = IndigoIndigo500,
                    unselectedIconColor = SlateSlate400,
                    unselectedTextColor = SlateSlate400,
                    // Indicador subtil: Indigo com baixa opacidade, sem cor sólida
                    indicatorColor = IndigoIndigo500.copy(alpha = 0.12f),
                ),
            )
        }
    }
}

/**
 * Ecrã placeholder para features ainda em construção.
 * Apresenta uma mensagem neutra e empática ao utilizador.
 */
@Composable
private fun EcraConstrucao(
    mensagem: String,
    modifier: Modifier = Modifier,
) {
    Box(
        modifier = modifier,
        contentAlignment = Alignment.Center,
    ) {
        Text(
            text = mensagem,
            style = MaterialTheme.typography.bodyLarge,
            color = SlateSlate400,
        )
    }
}
