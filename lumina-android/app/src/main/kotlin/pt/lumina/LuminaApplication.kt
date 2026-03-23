package pt.lumina

import android.app.Application
import dagger.hilt.android.HiltAndroidApp

/**
 * Classe Application principal da Lumina com inicialização Hilt.
 *
 * Configuração automática de dependency injection através do Hilt
 * para toda a aplicação. Estende a integração com módulos core.
 */
@HiltAndroidApp
class LuminaApplication : Application() {
    override fun onCreate() {
        super.onCreate()
        // Inicializações globais podem ser adicionadas aqui se necessário
        // (e.g., configuração de logging, analytics, crash reporting)
    }
}
