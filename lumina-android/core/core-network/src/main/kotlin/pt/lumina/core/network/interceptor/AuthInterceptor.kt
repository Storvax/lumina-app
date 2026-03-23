package pt.lumina.core.network.interceptor

import okhttp3.Interceptor
import okhttp3.Response
import pt.lumina.core.auth.TokenManager

/**
 * AuthInterceptor: Adicionar Bearer token a todas as requisições.
 */
class AuthInterceptor(private val tokenManager: TokenManager) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val originalRequest = chain.request()

        // Se não há token, deixa passar sem auth header
        val token = tokenManager.getToken() ?: return chain.proceed(originalRequest)

        // Adicionar header Authorization
        val requestWithAuth = originalRequest.newBuilder()
            .header("Authorization", "Bearer $token")
            .header("Accept", "application/json")
            .build()

        return chain.proceed(requestWithAuth)
    }
}
