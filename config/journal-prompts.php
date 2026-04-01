<?php

/**
 * Prompts terapêuticos contextuais para o Diário Emocional.
 *
 * Selecionados dinamicamente com base no humor e tags do registo atual.
 * Baseados em técnicas de Terapia Cognitivo-Comportamental (CBT),
 * Terapia de Aceitação e Compromisso (ACT) e práticas de gratidão.
 *
 * Grupos:
 *  - distress:  humor 1-2 — foco em regulação emocional e validação
 *  - neutral:   humor 3   — foco em introspecção e consciência
 *  - positive:  humor 4-5 — foco em gratidão e ancoragem de emoções positivas
 *  - anxiety:   tag "Ansiedade" ou "Pânico" — técnicas de grounding cognitivo
 *  - fatigue:   tag "Cansaço" — foco em recuperação e autocompaixão
 *  - loneliness:tag "Solidão" — foco em conexão e pertença
 */
return [
    'distress' => [
        'O que é que estás a sentir agora mesmo? Tenta nomear essa emoção sem a julgar.',
        'O que é que precisas de ouvir neste momento — que palavras te consolariam?',
        'Existe algo que possas fazer por ti próprio/a nos próximos 5 minutos para te cuidares?',
        'O que é que este dia difícil te está a ensinar sobre o que é importante para ti?',
        'Que parte deste peso não é tua responsabilidade carregar?',
        'Se um amigo teu estivesse a sentir o mesmo, o que lhe dirias com gentileza?',
    ],

    'neutral' => [
        'Qual foi a melhor coisa (mesmo que pequena) que aconteceu hoje?',
        'O que é que está a ocupar demasiado espaço na tua cabeça agora?',
        'Escreve sobre um momento em que te sentiste em paz hoje.',
        'O que aprendeste sobre ti próprio/a esta semana?',
        'Existe algo que queiras mudar no modo como respondes às situações?',
        'Descreve um momento desta semana que mereça ser lembrado.',
    ],

    'positive' => [
        'Pelo que é que te sentes genuinamente grato(a) hoje?',
        'O que é que está a correr bem e que por vezes esqueces de valorizar?',
        'Como podes guardar este sentimento bom para os dias mais difíceis?',
        'Quem ou o quê contribuiu para o teu bem-estar hoje? Como podes agradecer?',
        'Que versão de ti próprio/a se mostrou hoje? Como te sentes sobre isso?',
        'O que é que este momento de bem-estar te diz sobre o que precisas na tua vida?',
    ],

    'anxiety' => [
        'Descreve exatamente o que está a preocupar-te. Escreve os factos — sem interpretações.',
        'Qual é a probabilidade real de o pior cenário acontecer? O que aconteceria se acontecesse?',
        'O que já ultrapassaste antes que parecia impossível? O que aprendeste disso?',
        'Quais são os 5 sentidos neste momento? O que vês, ouves, sentes, cheiras, provas?',
        'Que pensamento ansioso se repete mais? Consegues encontrar uma perspetiva alternativa?',
        'O que está fora do teu controlo agora? O que está dentro?',
    ],

    'fatigue' => [
        'O que te esgotou hoje? Existe algo que possas delegar ou eliminar?',
        'Que tipo de descanso precisas — físico, mental, emocional ou social?',
        'Que pequena coisa faria a diferença na tua energia amanhã de manhã?',
        'O que é que o teu corpo ou mente te pede quando te sentes assim?',
        'O cansaço que sentes — de onde vem? É de dar demasiado ou de não receber o suficiente?',
    ],

    'loneliness' => [
        'Quando foi a última vez que te sentiste verdadeiramente compreendido/a? O que criou esse momento?',
        'Existe alguém a quem poderias enviar uma mensagem hoje sem razão especial?',
        'O que é que a solidão te diz sobre o que precisas de mais conexão na tua vida?',
        'Como é que te relacionas contigo próprio/a quando estás sozinho/a?',
        'Que comunidade, espaço ou pessoa te faz sentir que pertences?',
    ],
];
