<?php

/**
 * Mensagens de validação — Português (PT-PT).
 * Subconjunto essencial para os campos mais usados na plataforma Lumina.
 * Mensagens redigidas com tom acolhedor e orientador.
 */
return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser texto.',
    'email' => 'Introduz um endereço de email válido.',
    'max' => [
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
        'file' => 'O ficheiro :attribute não pode ter mais de :max kilobytes.',
    ],
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'confirmed' => 'A confirmação do campo :attribute não coincide.',
    'unique' => 'Este :attribute já está registado.',
    'in' => 'O valor selecionado para :attribute não é válido.',
    'mimes' => 'O ficheiro :attribute deve ser do tipo: :values.',
    'file' => 'O campo :attribute deve ser um ficheiro.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'array' => 'O campo :attribute deve ser uma lista.',
    'accepted' => 'O campo :attribute deve ser aceite.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'nullable' => 'O campo :attribute pode ficar vazio.',
    'exists' => 'O :attribute selecionado não existe.',
    'date_format' => 'O campo :attribute deve seguir o formato :format.',
    'image' => 'O campo :attribute deve ser uma imagem.',

    'attributes' => [
        'name' => 'nome',
        'email' => 'email',
        'password' => 'password',
        'title' => 'título',
        'content' => 'conteúdo',
        'body' => 'mensagem',
        'note' => 'nota',
        'mood_level' => 'nível de humor',
        'tags' => 'etiquetas',
        'audio_file' => 'ficheiro de áudio',
        'reason' => 'motivo',
        'answer' => 'resposta',
    ],
];
