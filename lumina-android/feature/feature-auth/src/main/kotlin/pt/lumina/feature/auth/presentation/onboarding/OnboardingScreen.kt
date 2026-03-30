package pt.lumina.feature.auth.presentation.onboarding

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import pt.lumina.core.ui.components.LuminaButton
import pt.lumina.core.ui.theme.SlateSlate500
import pt.lumina.core.ui.theme.SlateSlate800

/**
 * Ecrã de Onboarding (3 passos) — Placeholder.
 * Gere a introdução à app e configuração inicial do utilizador.
 */
@Composable
fun OnboardingScreen(
    onComplete: (String?) -> Unit,
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(Color.White)
            .padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(
            text = "Bem-vindo à Lumina",
            style = MaterialTheme.typography.headlineMedium.copy(fontWeight = FontWeight.Bold),
            color = SlateSlate800,
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(16.dp))

        Text(
            text = "Vamos configurar o teu espaço seguro em apenas 3 passos.",
            style = MaterialTheme.typography.bodyLarge,
            color = SlateSlate500,
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(48.dp))

        // Botão para completar o onboarding (placeholder)
        LuminaButton(
            text = "Continuar",
            onClick = { onComplete(null) },
            modifier = Modifier.fillMaxWidth()
        )
    }
}
