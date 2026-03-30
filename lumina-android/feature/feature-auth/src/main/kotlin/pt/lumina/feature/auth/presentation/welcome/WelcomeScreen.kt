package pt.lumina.feature.auth.presentation.welcome

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
 * Ecrã de boas-vindas (Welcome) — Placeholder.
 * Apresenta a mascote Lumi e as opções principais de entrada.
 */
@Composable
fun WelcomeScreen(
    onGetStarted: () -> Unit,
    onLogin: () -> Unit,
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(Color.White)
            .padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        // Placeholder para a mascote Lumi
        Box(
            modifier = Modifier
                .size(120.dp)
                .background(Color.LightGray),
            contentAlignment = Alignment.Center
        ) {
            Text("Lumi")
        }

        Spacer(modifier = Modifier.height(32.dp))

        Text(
            text = "Olá, eu sou a Lumi.",
            style = MaterialTheme.typography.headlineMedium.copy(fontWeight = FontWeight.Bold),
            color = SlateSlate800,
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(8.dp))

        Text(
            text = "Estou aqui para te acompanhar na tua jornada de resiliência.",
            style = MaterialTheme.typography.bodyLarge,
            color = SlateSlate500,
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(48.dp))

        LuminaButton(
            text = "Começar jornada",
            onClick = onGetStarted,
            modifier = Modifier.fillMaxWidth()
        )

        Spacer(modifier = Modifier.height(16.dp))

        LuminaButton(
            text = "Já tenho conta",
            onClick = onLogin,
            variant = "secondary",
            modifier = Modifier.fillMaxWidth()
        )
    }
}
