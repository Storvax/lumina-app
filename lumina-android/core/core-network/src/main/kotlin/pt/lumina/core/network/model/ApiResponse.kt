package pt.lumina.core.network.model

/**
 * Wrapper genérico para respostas da API.
 */
data class ApiResponse<T>(
    val data: T? = null,
    val token: String? = null,
    val user: T? = null,
    val message: String? = null,
    val error: ApiError? = null,
)

/**
 * Modelo de erro padronizado.
 */
data class ApiError(
    val code: String? = null,
    val message: String = "Erro desconhecido",
    val errors: Map<String, List<String>>? = null,
)
