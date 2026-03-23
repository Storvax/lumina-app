package pt.lumina.navigation

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import pt.lumina.feature.auth.presentation.login.LoginScreen

/**
 * Configuração principal de navegação da aplicação Lumina.
 *
 * Define a estrutura de routes e destinos da navegação Compose.
 * NavGraph será populado com composables específicas durante cada QW.
 *
 * Rotas implementadas:
 * - login: Fluxo de autenticação (QW-05, feature-auth)
 *
 * TODO: Adicionar futuras rotas:
 * - home: Dashboard principal (feature-home, QW-07)
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
        // QW-05: Tela de login
        composable("login") {
            LoginScreen(
                onLoginSuccess = {
                    // Navega para dashboard quando login bem-sucedido
                    // Pop up to login para prevenir voltar
                    navController.navigate("dashboard") {
                        popUpTo("login") { inclusive = true }
                    }
                }
            )
        }

        // QW-07: Placeholder para dashboard (será adicionado depois)
        composable("dashboard") {
            // TODO: Implementar DashboardScreen em QW-07
            // Por enquanto, mostramos tela em branco com testo placeholder
            androidx.compose.material3.Text("Dashboard - Implementado em QW-07")
        }
    }
}
