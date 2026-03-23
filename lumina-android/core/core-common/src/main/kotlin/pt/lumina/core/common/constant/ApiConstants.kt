package pt.lumina.core.common.constant

object ApiConstants {
    // Muda isto para o URL correto em produção
    const val BASE_URL = "http://10.0.2.2:8000/" // 10.0.2.2 = localhost no emulador

    // API endpoints
    object Endpoints {
        const val LOGIN = "api/v1/auth/login"
        const val LOGOUT = "api/v1/auth/logout"
        const val ME = "api/v1/auth/me"
        const val PROFILE = "api/v1/profile"
        const val DASHBOARD = "api/v1/dashboard"
        const val DIARY = "api/v1/diary"
        const val MISSIONS = "api/v1/missions"
        const val CALM_ZONE_VAULT = "api/v1/calm-zone/vault"
    }
}
