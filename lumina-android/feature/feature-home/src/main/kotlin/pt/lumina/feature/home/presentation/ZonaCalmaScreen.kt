package pt.lumina.feature.home.presentation

import android.content.Context
import android.os.Build
import android.os.VibrationEffect
import android.os.Vibrator
import android.os.VibratorManager
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.alpha
import androidx.compose.ui.draw.scale
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.role
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.font.FontStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import kotlinx.coroutines.delay

// ─── Paleta escura imersiva — rose-950 / rose-900 ────────────────────────────
// Não existe no Color.kt global pois é exclusiva desta experiência imersiva
private val Rose950 = Color(0xFF4C0519)
private val Rose900 = Color(0xFF881337)
private val Rose700 = Color(0xFFBE123C)
private val Rose500 = Color(0xFFF43F5E)
private val RoseTexto = Color(0xFFFFCDD5)  // rose-100 (quase branco rosado)

/**
 * Estados possíveis da Sintonia.
 *
 * - IDLE:       Ecrã de apresentação, botão "Sintonizar" visível
 * - COUNTDOWN:  Contagem 3→2→1 com instrução de encostar ao peito
 * - ACTIVE:     Coração a pulsar em sincronia com o haptic (60 BPM)
 */
private enum class EstadoSintonia { IDLE, COUNTDOWN, ACTIVE }

/**
 * Zona Calma — Sintonia (batimento cardíaco comunitário).
 *
 * Experiência imersiva de regulação somática:
 * 1. Utilizador encosta o telemóvel ao peito
 * 2. Sente o padrão "Lub-Dub" de um coração em repouso (60 BPM)
 * 3. Sincroniza a respiração com o ritmo visual e tátil
 *
 * Design deliberadamente escuro (rose-950) para criar um espaço
 * de recuo e intimidade — contraste com o fundo branco do resto da app.
 *
 * Háptico — padrão Lub-Dub por batimento (a cada 1000ms):
 *   Vibrar 80ms (Lub) → pausar 150ms → vibrar 40ms (Dub) → aguardar resto
 *
 * @param onBack Navegar para a Zona Calma (ou ecrã anterior)
 * @param modifier Modificador Compose (recebe padding do Scaffold)
 */
@Composable
fun ZonaCalmaScreen(
    onBack: () -> Unit = {},
    modifier: Modifier = Modifier,
) {
    val context = LocalContext.current
    var estado by remember { mutableStateOf(EstadoSintonia.IDLE) }
    var contador by remember { mutableIntStateOf(3) }

    // Obtém Vibrator de forma compatível com API 26+ (nosso minSdk) e API 31+
    val vibrator = remember {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            (context.getSystemService(Context.VIBRATOR_MANAGER_SERVICE) as VibratorManager)
                .defaultVibrator
        } else {
            @Suppress("DEPRECATION")
            context.getSystemService(Context.VIBRATOR_SERVICE) as Vibrator
        }
    }

    // ─── Loop de batimento cardíaco ──────────────────────────────────────────
    // 60 BPM = 1 ciclo a cada 1000ms
    // Padrão Lub-Dub: vibra 80ms, pausa 150ms, vibra 40ms, espera ~730ms
    LaunchedEffect(estado) {
        if (estado == EstadoSintonia.ACTIVE) {
            while (true) {
                vibrator.vibrate(
                    VibrationEffect.createWaveform(
                        longArrayOf(0L, 80L, 150L, 40L),   // delays e durações
                        intArrayOf(0, 200, 0, 120),         // amplitudes (0=silêncio)
                        -1,                                  // -1 = não repetir internamente
                    )
                )
                delay(1_000L) // Aguardar 1 segundo até ao próximo batimento
            }
        } else {
            // Garantir que a vibração pára ao sair do estado ativo
            vibrator.cancel()
        }
    }

    // ─── Contagem regressiva 3→2→1 ───────────────────────────────────────────
    LaunchedEffect(estado) {
        if (estado == EstadoSintonia.COUNTDOWN) {
            contador = 3
            repeat(3) {
                delay(1_000L)
                contador--
            }
            estado = EstadoSintonia.ACTIVE
        }
    }

    // ─── Animação contínua do coração (rememberInfiniteTransition) ───────────
    // Anima a escala de forma suave e orgânica, independentemente do haptic
    val pulso = rememberInfiniteTransition(label = "pulso_coracao")
    val escalaCoracao by pulso.animateFloat(
        initialValue = 1f,
        targetValue = 1.08f,
        animationSpec = infiniteRepeatable(
            animation = tween(durationMillis = 500, easing = FastOutSlowInEasing),
            repeatMode = RepeatMode.Reverse,
        ),
        label = "escala_coracao",
    )
    // Escala dos anéis concêntricos de "onda de pulso"
    val escalaAnel by pulso.animateFloat(
        initialValue = 1f,
        targetValue = 1.6f,
        animationSpec = infiniteRepeatable(
            animation = tween(durationMillis = 1_000, easing = FastOutSlowInEasing),
            repeatMode = RepeatMode.Restart,
        ),
        label = "escala_anel",
    )
    val opacidadeAnel by pulso.animateFloat(
        initialValue = 0.4f,
        targetValue = 0f,
        animationSpec = infiniteRepeatable(
            animation = tween(durationMillis = 1_000, easing = FastOutSlowInEasing),
            repeatMode = RepeatMode.Restart,
        ),
        label = "opacidade_anel",
    )

    // Ativa animação do coração apenas no modo ativo
    val escalaEfetiva = if (estado == EstadoSintonia.ACTIVE) escalaCoracao else 1f
    val opacidadeAnimacaoAnel = if (estado == EstadoSintonia.ACTIVE) opacidadeAnel else 0f

    Box(
        modifier = modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(colors = listOf(Rose900, Rose950))
            ),
        contentAlignment = Alignment.Center,
    ) {

        // ─── OVERLAY DE CONTAGEM REGRESSIVA ──────────────────────────────────
        if (estado == EstadoSintonia.COUNTDOWN) {
            Column(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(horizontal = 32.dp),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center,
            ) {
                Text(text = "📱", style = MaterialTheme.typography.displayMedium)

                Spacer(modifier = Modifier.height(24.dp))

                Text(
                    text = "Encosta o telemóvel\nao teu peito...",
                    style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Black),
                    color = Color.White,
                    textAlign = TextAlign.Center,
                )

                Spacer(modifier = Modifier.height(40.dp))

                // Dígito da contagem regressiva
                Text(
                    text = "$contador",
                    style = MaterialTheme.typography.headlineLarge.copy(fontSize = 80.sp),
                    color = Rose500,
                )
            }

        } else {
            // ─── ECRÃ PRINCIPAL (IDLE + ACTIVE) ──────────────────────────────
            Column(
                modifier = Modifier.fillMaxSize(),
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {

                // Navegação de retorno (oculta durante sessão ativa)
                if (estado == EstadoSintonia.IDLE) {
                    Text(
                        text = "← Zona Calma",
                        style = MaterialTheme.typography.labelLarge,
                        color = Rose500,
                        modifier = Modifier
                            .align(Alignment.Start)
                            .clickable(onClick = onBack)
                            .padding(start = 20.dp, top = 20.dp, bottom = 8.dp, end = 20.dp)
                            .semantics {
                                contentDescription = "Voltar à Zona Calma"
                                role = Role.Button
                            },
                    )
                } else {
                    Spacer(modifier = Modifier.height(60.dp))
                }

                Spacer(modifier = Modifier.weight(1f))

                // ─── Texto de apresentação (apenas IDLE) ─────────────────────
                if (estado == EstadoSintonia.IDLE) {
                    // Badge de categoria
                    Box(
                        modifier = Modifier
                            .background(
                                color = Rose500.copy(alpha = 0.12f),
                                shape = RoundedCornerShape(100.dp),
                            )
                            .border(
                                width = 1.dp,
                                color = Rose500.copy(alpha = 0.25f),
                                shape = RoundedCornerShape(100.dp),
                            )
                            .padding(horizontal = 14.dp, vertical = 6.dp),
                    ) {
                        Text(
                            text = "♥  Sincronia Somática",
                            style = MaterialTheme.typography.labelMedium.copy(
                                letterSpacing = 1.5.sp,
                            ),
                            color = Rose500,
                        )
                    }

                    Spacer(modifier = Modifier.height(20.dp))

                    Text(
                        text = "Um coração emprestado.",
                        style = MaterialTheme.typography.headlineMedium,
                        color = Color.White,
                        textAlign = TextAlign.Center,
                        modifier = Modifier.padding(horizontal = 32.dp),
                    )

                    Spacer(modifier = Modifier.height(12.dp))

                    Text(
                        text = "Usa a vibração do telemóvel para sentir um\nbatimento cardíaco em repouso (60 bpm).\nSincroniza a respiração com o ritmo.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = RoseTexto.copy(alpha = 0.7f),
                        textAlign = TextAlign.Center,
                        modifier = Modifier.padding(horizontal = 40.dp),
                    )

                    Spacer(modifier = Modifier.height(40.dp))
                }

                // ─── Coração visual central ───────────────────────────────────
                Box(
                    contentAlignment = Alignment.Center,
                    modifier = Modifier.size(192.dp),
                ) {
                    // Anel exterior — onda de pulso que expande e desvanece
                    Box(
                        modifier = Modifier
                            .size(192.dp)
                            .scale(escalaAnel)
                            .alpha(opacidadeAnimacaoAnel)
                            .background(
                                color = Rose500.copy(alpha = 0.15f),
                                shape = CircleShape,
                            ),
                    )

                    // Anel intermédio — estático, define o "aura" do coração
                    Box(
                        modifier = Modifier
                            .size(160.dp)
                            .background(
                                color = Rose700.copy(alpha = 0.25f),
                                shape = CircleShape,
                            ),
                    )

                    // Coração físico — círculo com gradiente rose-700→rose-500
                    Box(
                        modifier = Modifier
                            .size(128.dp)
                            .scale(escalaEfetiva)
                            .background(
                                brush = Brush.radialGradient(
                                    colors = listOf(Rose500, Rose700),
                                ),
                                shape = CircleShape,
                            )
                            .semantics {
                                contentDescription = when (estado) {
                                    EstadoSintonia.ACTIVE -> "Coração a pulsar. Sincroniza a respiração."
                                    else -> "Coração em repouso."
                                }
                            },
                        contentAlignment = Alignment.Center,
                    ) {
                        Text(
                            text = "❤",
                            fontSize = 52.sp,
                            color = Color.White.copy(alpha = 0.9f),
                        )
                    }
                }

                Spacer(modifier = Modifier.height(48.dp))

                // ─── Botão "Sintonizar" (apenas IDLE) ────────────────────────
                if (estado == EstadoSintonia.IDLE) {
                    Box(
                        modifier = Modifier
                            .background(
                                color = Rose700,
                                shape = RoundedCornerShape(100.dp),
                            )
                            .clickable { estado = EstadoSintonia.COUNTDOWN }
                            .padding(horizontal = 32.dp, vertical = 18.dp)
                            .semantics {
                                contentDescription = "Iniciar Sintonia — batimento cardíaco"
                                role = Role.Button
                            },
                    ) {
                        Text(
                            text = "▶  Sintonizar",
                            style = MaterialTheme.typography.titleSmall.copy(
                                fontWeight = FontWeight.Bold,
                            ),
                            color = Color.White,
                        )
                    }
                }

                Spacer(modifier = Modifier.weight(1f))

                // ─── Texto "Não estás sozinho." (apenas ACTIVE) ──────────────
                if (estado == EstadoSintonia.ACTIVE) {
                    Text(
                        text = "Não estás sozinho.",
                        style = MaterialTheme.typography.titleSmall.copy(
                            fontStyle = FontStyle.Italic,
                        ),
                        color = RoseTexto.copy(alpha = 0.6f),
                        textAlign = TextAlign.Center,
                    )

                    Spacer(modifier = Modifier.height(6.dp))

                    // Contagem de pessoas — valor estático até à existência de API real-time
                    Text(
                        text = "847 pessoas sincronizadas agora",
                        style = MaterialTheme.typography.labelSmall,
                        color = Rose500.copy(alpha = 0.7f),
                        textAlign = TextAlign.Center,
                    )

                    Spacer(modifier = Modifier.height(32.dp))

                    // Botão "Terminar" — posicionado deliberadamente longe do centro
                    // para evitar toques acidentais durante a sessão
                    Box(
                        modifier = Modifier
                            .background(
                                color = Rose950.copy(alpha = 0.8f),
                                shape = RoundedCornerShape(100.dp),
                            )
                            .border(
                                width = 1.dp,
                                color = Rose500.copy(alpha = 0.3f),
                                shape = RoundedCornerShape(100.dp),
                            )
                            .clickable {
                                estado = EstadoSintonia.IDLE
                                vibrator.cancel()
                            }
                            .padding(horizontal = 28.dp, vertical = 14.dp)
                            .semantics {
                                contentDescription = "Terminar sessão de Sintonia"
                                role = Role.Button
                            },
                    ) {
                        Text(
                            text = "Terminar Sintonia",
                            style = MaterialTheme.typography.labelLarge,
                            color = RoseTexto.copy(alpha = 0.8f),
                        )
                    }

                    Spacer(modifier = Modifier.height(24.dp))
                } else {
                    Spacer(modifier = Modifier.height(24.dp))
                }
            }
        }
    }
}
