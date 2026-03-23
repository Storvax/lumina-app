package pt.lumina.core.domain.model

/**
 * Domain entity: User
 * Representa um utilizador da Lumina.
 */
data class User(
    val id: Int,
    val name: String,
    val email: String,
    val pseudonym: String,
    val avatar: String?,
    val flames: Int,
    val flame_level: String?,
    val current_streak: Int,
    val role: String,
    val onboarding_completed: Boolean,
    val bio: String?,
    val created_at: String,
    val updated_at: String,
)
