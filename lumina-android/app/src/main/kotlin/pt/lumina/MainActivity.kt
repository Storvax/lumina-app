package pt.lumina

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import dagger.hilt.android.AndroidEntryPoint
import pt.lumina.core.ui.theme.LuminaTheme
import pt.lumina.navigation.LuminaNavHost

/**
 * Activity principal da aplicação Lumina.
 *
 * Ponto de entrada da UI Compose com integração de navegação
 * e tema design system Lumina. Gerida por Hilt para injeção de dependências.
 */
@AndroidEntryPoint
class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            LuminaTheme {
                LuminaNavHost()
            }
        }
    }
}
