# campus-ui

Fundamentos visuais compartilhados: tokens de cor, forma e tipografia, mais os
poucos padrões de tela que já se provaram em produção.

## O que é, e o que deliberadamente não é

**É** um conjunto pequeno de decisões que não vale a pena tomar duas vezes: qual
é o verde, quanto arredonda, que sombra usa, qual a altura mínima de um alvo de
toque.

**Não é** uma biblioteca de componentes. Os padrões aqui saíram de telas que já
rodam, não de suposição sobre o que talvez seja útil. Componente extraído cedo
demais vira contorção: cada tela seguinte precisa de "só mais um parâmetro" até
o componente virar algo que ninguém quer tocar. Quando um padrão se repetir em
duas telas reais, ele entra. Antes disso, não.

## A regra que sustenta tudo

**Nada aqui toca seletor de elemento.** Sem `body`, sem `button`, sem `input`,
sem `*`. Toda variável começa com `--campus-`, toda classe com `campus-`.

O motivo é prático: as aplicações usam bases diferentes — uma tem Bootstrap 4,
outra tem Tailwind — e um seletor de elemento brigaria com elas em toda página.
Variável de cor não colide com nada; classe prefixada também não. É isso que
permite adotar o pacote sem reescrever o que já existe.

Um teste guarda essa regra. Se ele falhar, o pacote deixou de poder ser adotado
sem estrago.

## Instalação

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/ronaldoifce/campus-ui.git" }
    ]
}
```

```bash
composer require ronaldoifce/campus-ui
```

Como o `vendor/` não é servido pela web, publique o CSS por uma rota — a mesma
abordagem usada para o JavaScript do `push-pwa`. Copiar o arquivo para dentro do
projeto é justamente o que faz as versões divergirem.

```php
$app->get('/ui/{arquivo}', function ($request, $response, $args) {
    $recurso = campusui\Recursos::entregar($args['arquivo']);
    return $response->withHeader('Content-Type', $recurso['tipo'])
                    ->withHeader('ETag', $recurso['etag'])
                    ->write($recurso['conteudo']);
});
```

```html
<link rel="stylesheet" href="{{ base_url }}/ui/campus-ui.css?v={{ ui_versao }}">
```

`Recursos::versao()` muda quando o pacote muda, e só então — sem isso o
navegador serve o CSS antigo depois de uma atualização.

## Adoção em duas velocidades

**1. Só os tokens.** Use `var(--campus-*)` no CSS que você já tem. Ganha
consistência de cor e de forma sem mudar uma linha de marcação. É por aqui que
sistemas existentes devem começar.

**2. Os padrões.** Use as classes. **Elas só valem abaixo de 768px** — descrevem
uma tela de aplicativo em celular, e em tela larga essa composição desperdiçaria
o espaço. É esse escopo que permite adotá-las num sistema que já tem layout de
desktop: basta usar a classe, sem escrever media query local nem reimplementar o
padrão.

Se o conteúdo do cartão já traz o próprio respiro — comum ao adaptar tela
existente — use `campus-cartao campus-cartao--liso`, senão o respiro dobra.

## Tokens

| Grupo | Variáveis |
|---|---|
| Marca | `--campus-verde`, `--campus-verde-escuro`, `--campus-verde-claro` |
| Texto | `--campus-tinta`, `--campus-suave` |
| Superfícies | `--campus-fundo`, `--campus-superficie`, `--campus-campo`, `--campus-borda` |
| Estados | `--campus-erro`, `--campus-erro-fundo` |
| Forma | `--campus-raio-p/m/g`, `--campus-sombra-baixa/media/alta` |
| Tipografia | `--campus-fonte`, `--campus-texto-p/m/g` |
| Espaço | `--campus-esp-p/m/g`, `--campus-toque` |

A escala de raios tem três valores de propósito: telas ficam parecidas sem
ninguém precisar combinar nada.

As sombras são tingidas de verde, não de cinza — sombra neutra sobre o fundo
esverdeado suja a cor.

## Padrões

### Faixa de marca com cartão sobreposto

```html
<div class="campus-tela">
  <div class="campus-tela-corpo">
    <header class="campus-faixa">
      <div class="campus-marca"><img src="..." alt=""></div>
      <h1 class="campus-titulo">Restaurante Acadêmico</h1>
      <p class="campus-subtitulo">IFCE Campus Tianguá</p>
    </header>

    <div class="campus-cartao"> ... </div>
  </div>
  <nav class="campus-barra"> ... </nav>
</div>
```

Duas caixas flutuando separadas leem como página web. A faixa encostando nas
bordas, com o conteúdo subindo por cima dela, lê como aplicativo: uma superfície
só.

### Hierarquia de botão

```html
<button class="campus-botao campus-botao--principal">Entrar</button>
<a class="campus-botao campus-botao--secundario">Continuar com Google</a>
```

Uma ação principal sólida, as demais com contorno. Dois botões sólidos coloridos
na mesma tela obrigam a pessoa a escolher sem pista de qual é o caminho
esperado.

### Campos

```html
<label class="campus-rotulo">Matrícula</label>
<input class="campus-campo" type="text">
```

O `font-size: 16px` não é escolha estética: abaixo disso o iOS dá zoom sozinho
ao focar o campo, e a tela salta na cara de quem digita.

### Barra inferior

Fixa no rodapé, com o respiro do gesto de navegação do aparelho. Alcance do
polegar é o motivo — navegação no topo obriga a trocar a mão de posição.

## Testes

```bash
composer install
composer test
```

Os testes guardam o que não pode regredir sem alguém perceber: a regra de
convivência, o prefixo das variáveis, os 16px do campo, a altura mínima de
toque e o respeito às bordas do aparelho.

## Contato

Manutenção: Ronaldo Ribeiro — <ronaldo.ribeiro@ifce.edu.br>
Dúvidas e problemas: <https://github.com/ronaldoifce/campus-ui/issues>

## Licença

[PolyForm Noncommercial 1.0.0](LICENSE) — copyright do Instituto Federal de
Educação, Ciência e Tecnologia do Ceará (IFCE). Permitido: pesquisa, ensino, uso
pessoal e uso por instituições públicas, de ensino e sem fins lucrativos.
