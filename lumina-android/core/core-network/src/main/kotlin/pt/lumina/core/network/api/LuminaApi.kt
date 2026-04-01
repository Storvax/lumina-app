package pt.lumina.core.network.api

import pt.lumina.core.network.model.ApiResponse
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.GET
import retrofit2.http.POST

/**
 * Retrofit API interface para Lumina backend.
 * Define todos os endpoints que a app consome.
 */
interface LuminaApi {
    // Auth
    @FormUrlEncoded
    @POST("api/v1/auth/register")
    suspend fun register(
        @Field("name") name: String,
        @Field("email") email: String,
        @Field("password") password: String,
        @Field("password_confirmation") passwordConfirmation: String,
    ): ApiResponse<Any>

    @FormUrlEncoded
    @POST("api/v1/auth/login")
    suspend fun login(
        @Field("email") email: String,
        @Field("password") password: String,
    ): ApiResponse<Any>

    @FormUrlEncoded
    @POST("api/v1/onboarding")
    suspend fun submitOnboarding(
        @Field("intent") intent: String,
        @Field("mood") mood: String,
        @Field("preference") preference: String,
    ): ApiResponse<Any>

    @POST("api/v1/auth/logout")
    suspend fun logout(): ApiResponse<Any>

    @GET("api/v1/auth/me")
    suspend fun getMe(): ApiResponse<Any>

    // Profile
    @GET("api/v1/profile")
    suspend fun getProfile(): ApiResponse<Any>

    // Dashboard
    @GET("api/v1/dashboard")
    suspend fun getDashboard(): ApiResponse<Any>

    // Diary
    @GET("api/v1/diary")
    suspend fun getDiary(): ApiResponse<Any>

    @FormUrlEncoded
    @POST("api/v1/diary")
    suspend fun createDiary(
        @Field("mood_level") moodLevel: Int,
        @Field("note") note: String,
    ): ApiResponse<Any>

    // Missions
    @GET("api/v1/missions")
    suspend fun getMissions(): ApiResponse<Any>

    // Calm Zone Vault
    @GET("api/v1/calm-zone/vault")
    suspend fun getVault(): ApiResponse<Any>

    @FormUrlEncoded
    @POST("api/v1/calm-zone/vault")
    suspend fun createVaultItem(
        @Field("content") content: String,
    ): ApiResponse<Any>
}
