<?php

use App\Services\AI\CBTAnalysisService;

beforeEach(function () {
    $this->service = new CBTAnalysisService();
});

// --- detectCrisis: Camada 1 (Keywords) ---

it('deteta crise por keyword direta — suicídio', function () {
    $result = $this->service->detectCrisis('Estou a pensar em suicídio');

    expect($result['detected'])->toBeTrue()
        ->and($result['level'])->toBe('critical')
        ->and($result['type'])->toBe('keyword');
});

it('deteta crise por keyword direta — morrer', function () {
    $result = $this->service->detectCrisis('Quero morrer hoje');

    expect($result['detected'])->toBeTrue()
        ->and($result['level'])->toBe('critical');
});

it('deteta keyword sem acentuação — suicidio', function () {
    $result = $this->service->detectCrisis('pensando em suicidio');

    expect($result['detected'])->toBeTrue();
});

// --- detectCrisis: Camada 2 (Intent) ---

it('deteta crise por padrão de intenção — não aguento mais', function () {
    $result = $this->service->detectCrisis('Não aguento mais esta situação');

    expect($result['detected'])->toBeTrue()
        ->and($result['level'])->toBe('high')
        ->and($result['type'])->toBe('intent');
});

it('deteta crise por padrão de intenção — quero desaparecer', function () {
    $result = $this->service->detectCrisis('Às vezes quero desaparecer para sempre');

    expect($result['detected'])->toBeTrue()
        ->and($result['type'])->toBe('intent');
});

it('deteta crise por padrão de intenção — seria melhor sem mim', function () {
    $result = $this->service->detectCrisis('Acho que seria melhor sem mim');

    expect($result['detected'])->toBeTrue();
});

it('deteta variante sem acento — nao aguento mais', function () {
    $result = $this->service->detectCrisis('nao aguento mais');

    expect($result['detected'])->toBeTrue();
});

// --- detectCrisis: Texto neutro ---

it('nao deteta crise em texto emocional mas neutro', function () {
    $result = $this->service->detectCrisis('Hoje tive um dia difícil mas estou bem.');

    expect($result['detected'])->toBeFalse()
        ->and($result['level'])->toBe('none')
        ->and($result['type'])->toBeNull();
});

it('nao deteta crise em texto positivo', function () {
    $result = $this->service->detectCrisis('Hoje sinto-me muito grato pela minha família.');

    expect($result['detected'])->toBeFalse();
});

it('nao deteta crise em string vazia', function () {
    $result = $this->service->detectCrisis('');

    expect($result['detected'])->toBeFalse();
});

// --- Camada 1 prevalece sobre Camada 2 ---

it('classifica como critical quando há keyword mesmo com padrão de intenção presente', function () {
    $result = $this->service->detectCrisis('não aguento mais, quero morrer');

    expect($result['detected'])->toBeTrue()
        ->and($result['level'])->toBe('critical')
        ->and($result['type'])->toBe('keyword');
});

// --- analyzeForumPost (sem API key — fallback determinístico) ---

it('retorna is_sensitive true para post com keyword de crise (sem API key)', function () {
    config(['services.openai.key' => null]);

    $result = $this->service->analyzeForumPost('Tenho pensamentos de suicídio constantemente');

    expect($result['is_sensitive'])->toBeTrue()
        ->and($result['risk_level'])->toBe('high');
});

it('retorna is_sensitive false para post neutro (sem API key)', function () {
    config(['services.openai.key' => null]);

    $result = $this->service->analyzeForumPost('Hoje fiz uma caminhada e senti-me melhor.');

    expect($result['is_sensitive'])->toBeFalse()
        ->and($result['risk_level'])->toBe('low');
});

// --- analyze (diário — fallback determinístico) ---

it('retorna null para texto de diário sem distorcoes (sem API key)', function () {
    config(['services.openai.key' => null]);

    $result = $this->service->analyze('Hoje fui ao ginásio e consegui terminar o treino.');

    expect($result)->toBeNull();
});

it('deteta distorcao tudo-ou-nada com palavra nunca (sem API key)', function () {
    config(['services.openai.key' => null]);

    $result = $this->service->analyze('Eu nunca consigo fazer nada certo.');

    expect($result)->not->toBeNull()
        ->and($result['type'])->toBe('Tudo ou Nada');
});

it('retorna null para texto nulo', function () {
    $result = $this->service->analyze(null);

    expect($result)->toBeNull();
});
