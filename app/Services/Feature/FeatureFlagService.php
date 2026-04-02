<?php

declare(strict_types=1);

namespace App\Services\Feature;

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Sistema leve de feature flags para A/B testing de intervenções.
 *
 * Permite rollout gradual por percentagem e lista de utilizadores específicos.
 * Usa cache para evitar queries repetidas em cada request.
 */
class FeatureFlagService
{
    /**
     * Verifica se uma feature está ativa para um utilizador.
     */
    public function isEnabled(string $featureName, ?User $user = null): bool
    {
        $flag = Cache::remember(
            "feature_flag:{$featureName}",
            300,
            fn () => FeatureFlag::where('name', $featureName)->first()
        );

        if (!$flag || !$flag->enabled) {
            return false;
        }

        // Flag global sem utilizador específico
        if ($user === null) {
            return true;
        }

        // Verificar lista de utilizadores permitidos
        if (!empty($flag->allowed_users) && in_array($user->id, $flag->allowed_users)) {
            return true;
        }

        // Rollout por percentagem (determinístico por user_id)
        if ($flag->rollout_percentage >= 100) {
            return true;
        }

        if ($flag->rollout_percentage > 0) {
            $bucket = crc32($featureName . ':' . $user->id) % 100;
            return $bucket < $flag->rollout_percentage;
        }

        return !empty($flag->allowed_users);
    }

    /**
     * Retorna a variante de uma feature para o user (A ou B).
     * Determinístico para o mesmo user + feature.
     */
    public function getVariant(string $featureName, User $user): string
    {
        $hash = crc32($featureName . ':variant:' . $user->id);
        return ($hash % 2 === 0) ? 'A' : 'B';
    }

    /**
     * Limpa o cache de uma flag específica.
     */
    public function clearCache(string $featureName): void
    {
        Cache::forget("feature_flag:{$featureName}");
    }
}
