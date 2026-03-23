package pt.lumina.navigation

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.rememberNavController

/**
 * Configuração principal de navegação da aplicação Lumina.
 *
 * Define a estrutura de routes e destinos da navegação Compose.
 * NavGraph será populado com composables específicas durante QW-05.
 *
 * TODO (QW-05): Adicionar composable routes:
 * - login: Fluxo de autenticação (core-auth)
 * - home: Dashboard principal (feature-home)
 * - mood_tracking: Tracking de estado emocional (feature-mood)
 * - mural: Comunidade e posts (feature-mural)
 * - profile: Perfil e configurações (feature-profile)
 */
@Composable
fun LuminaNavHost(
    navController: NavHostController = rememberNavController(),
    startDestination: String = "login"
) {
    NavHost(
        navController = navController,
        startDestination = startDestination
    ) {
        // Routes serão adicionadas em QW-05
        // exemplo: authGraph(navController)
    }
}
