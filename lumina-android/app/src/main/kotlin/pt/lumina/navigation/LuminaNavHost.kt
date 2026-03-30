package pt.lumina.navigation

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import pt.lumina.feature.auth.presentation.login.LoginScreen
import pt.lumina.feature.auth.presentation.onboarding.OnboardingScreen
import pt.lumina.feature.auth.presentation.register.RegisterScreen
import pt.lumina.feature.auth.presentation.welcome.WelcomeScreen

/**
 * Configuração principal de navegação da Lumina.
 *
 * Fluxo de autenticação:
 *   welcome → register → onboarding → dashboard
 *   welcome → login → onboarding (se não completo) | dashboard (se completo)
 *
 * Rotas:
 * - welcome:    Ecrã de boas-vindas com mascote Lumi
 * - register:   Criação de conta
 * - login:      Autenticação de conta existente
 * - onboarding: Fluxo de 3 passos pós-registo/login sem onboarding
 * - dashboard:  Ecrã principal com NavigationBar (MainScreen — QW-07)
 *
 * TODO: Adicionar futuras rotas:
 * - mood_tracking: Tracking emocional (feature-mood)
 * - profile:       Perfil (feature-profile)
 */
@Composable
fun LuminaNavHost(
    navController: NavHostController = rememberNavController(),
    startDestination: String = "welcome",
) {
    NavHost(
        navController = navController,
        startDestination = startDestination,
    ) {

        // Ecrã de boas-vindas com mascote Lumi
        composable("welcome") {
            WelcomeScreen(
                onGetStarted = { navController.navigate("register") },
                onLogin = { navController.navigate("login") },
            )
        }

        // Criação de conta → onboarding
        composable("register") {
            RegisterScreen(
                onRegisterSuccess = {
                    navController.navigate("onboarding") {
                        popUpTo("welcome") { inclusive = false }
                    }
                },
                onBack = { navController.popBackStack() },
                onLoginClick = {
                    navController.navigate("login") {
                        popUpTo("register") { inclusive = true }
                    }
                },
            )
        }

        // Login — verifica se onboarding foi completo
        composable("login") {
            LoginScreen(
                onLoginSuccess = {
                    // TODO: Verificar user.onboarding_completed quando disponível
                    // Por agora navega sempre para o dashboard
                    navController.navigate("dashboard") {
                        popUpTo("welcome") { inclusive = true }
                    }
                },
                onBack = { navController.popBackStack() },
            )
        }

        // Onboarding pós-registo — 3 passos
        composable("onboarding") {
            OnboardingScreen(
                onComplete = { redirect ->
                    // Redirect sugerido pelo backend com base na intenção
                    // Por agora todos vão para dashboard até as features estarem prontas
                    navController.navigate("dashboard") {
                        popUpTo("welcome") { inclusive = true }
                    }
                },
            )
        }

        // Ecrã principal com NavigationBar (Home, Mural, Zona Calma)
        composable("dashboard") {
            MainScreen()
        }
    }
}
