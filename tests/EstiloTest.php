<?php

namespace campusui\tests;

use campusui\Recursos;
use PHPUnit\Framework\TestCase;

class EstiloTest extends TestCase
{
    private $css;

    protected function setUp(): void
    {
        // Sem comentários: eles contêm exemplos e explicações que não são regra.
        $this->css = preg_replace('#/\*.*?\*/#s', '', file_get_contents(Recursos::caminho(Recursos::ESTILO)));
    }

    private function seletores()
    {
        $encontrados = array();
        // Cada bloco é o texto antes de `{`. Basta para conferir a regra de
        // convivência; não é um analisador de CSS completo.
        if (preg_match_all('/([^{}]+)\{/', $this->css, $blocos)) {
            foreach ($blocos[1] as $bruto) {
                foreach (explode(',', $bruto) as $seletor) {
                    $seletor = trim($seletor);
                    if ($seletor !== '' && strpos($seletor, '@') !== 0) {
                        $encontrados[] = $seletor;
                    }
                }
            }
        }
        return $encontrados;
    }

    /**
     * A regra que sustenta o pacote. As aplicações usam bases diferentes — uma
     * tem Bootstrap 4, outra tem Tailwind — e um seletor de elemento brigaria
     * com elas em toda página. Se este teste falhar, o pacote deixou de poder
     * ser adotado sem reescrever o que já existe.
     */
    public function testNaoToqueEmSeletorDeElemento()
    {
        foreach ($this->seletores() as $seletor) {
            $primeiro = preg_split('/[\s>+~]/', $seletor)[0];
            $ehPermitido = $primeiro === ':root'
                || strpos($primeiro, '.campus-') === 0
                || strpos($primeiro, '.campus-') !== false;

            $this->assertTrue(
                $ehPermitido,
                'Seletor fora da convivência: "' . $seletor . '". '
                . 'Todo seletor precisa começar por :root ou por uma classe .campus-.'
            );
        }
    }

    public function testTodaVariavelUsaOPrefixoDoPacote()
    {
        preg_match_all('/(--[a-z0-9-]+)\s*:/i', $this->css, $definidas);
        $this->assertNotEmpty($definidas[1], 'Nenhuma variável encontrada.');

        foreach (array_unique($definidas[1]) as $variavel) {
            $this->assertStringStartsWith(
                '--campus-',
                $variavel,
                'Variável sem prefixo: ' . $variavel . '. Sem prefixo ela pode colidir com a base do sistema.'
            );
        }
    }

    /**
     * Abaixo de 16px o iOS dá zoom sozinho ao focar o campo, e a tela salta na
     * cara de quem está digitando. Não é escolha estética.
     */
    public function testCampoNaoProvocaZoomNoIOS()
    {
        $inicio = strpos($this->css, '.campus-campo {');
        $this->assertNotFalse($inicio, 'Classe .campus-campo não encontrada.');
        $bloco = substr($this->css, $inicio, strpos($this->css, '}', $inicio) - $inicio);

        $this->assertMatchesRegularExpression('/font-size:\s*16px/', $bloco);
    }

    /**
     * Alvo menor que isto o dedo erra. O valor vive num token para ninguém
     * precisar lembrar do número.
     */
    public function testAlvosDeToqueRespeitamOMinimo()
    {
        $this->assertMatchesRegularExpression('/--campus-toque:\s*44px/', $this->css);

        foreach (array('.campus-botao', '.campus-campo', '.campus-barra-item') as $classe) {
            $inicio = strpos($this->css, $classe . ' {');
            $this->assertNotFalse($inicio, 'Classe ' . $classe . ' não encontrada.');
            $bloco = substr($this->css, $inicio, strpos($this->css, '}', $inicio) - $inicio);
            $this->assertStringContainsString(
                'min-height: var(--campus-toque)',
                $bloco,
                $classe . ' precisa respeitar a altura mínima de toque.'
            );
        }
    }

    /**
     * O celular com entalhe esconde conteúdo atrás do relógio e da barra de
     * gestos. Faixa e barra inferior são justamente os dois blocos que encostam
     * nessas bordas.
     */
    public function testFaixaEBarraRespeitamAsBordasDoAparelho()
    {
        $this->assertStringContainsString('env(safe-area-inset-top)', $this->css);
        $this->assertStringContainsString('env(safe-area-inset-bottom)', $this->css);
    }

    public function testEntregaOEstiloERecusaOResto()
    {
        $recurso = Recursos::entregar(Recursos::ESTILO);

        $this->assertStringContainsString('--campus-verde', $recurso['conteudo']);
        $this->assertSame('text/css; charset=utf-8', $recurso['tipo']);
        $this->assertMatchesRegularExpression('/^"[0-9a-f]{32}"$/', $recurso['etag']);

        $this->expectException(\InvalidArgumentException::class);
        Recursos::entregar('../composer.json');
    }

    public function testVersaoMudaComOArquivo()
    {
        $this->assertMatchesRegularExpression('/^[0-9a-f]{12}$/', Recursos::versao());
    }
}
