<?php

namespace campusui;

/**
 * Acesso à folha de estilo do pacote.
 *
 * O `vendor/` não é servido pela web, então cada aplicação publica o arquivo
 * por uma rota própria em vez de copiá-lo para dentro do projeto. Copiar é
 * justamente o que faria as versões divergirem: bastaria um ajuste em um
 * sistema para os outros ficarem para trás sem ninguém perceber.
 */
class Recursos
{
    const ESTILO = 'campus-ui.css';

    /**
     * @return array conteudo, tipo, etag — o suficiente para a aplicação montar
     *               a resposta na sua própria versão de framework.
     */
    public static function entregar($nome)
    {
        if (!in_array($nome, array(self::ESTILO), true)) {
            throw new \InvalidArgumentException('Recurso de interface desconhecido.');
        }

        $conteudo = @file_get_contents(self::caminho($nome));
        if ($conteudo === false) {
            throw new \RuntimeException('Recurso de interface indisponível.');
        }

        return array(
            'conteudo' => $conteudo,
            'tipo' => 'text/css; charset=utf-8',
            // Muda quando o pacote é atualizado, o que permite responder 304 sem
            // reler o arquivo e sem servir versão velha.
            'etag' => '"' . substr(hash('sha256', $conteudo), 0, 32) . '"'
        );
    }

    public static function caminho($nome)
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $nome;
    }

    /**
     * Serve para versionar a URL do `<link>`: muda quando o pacote muda, e só
     * então. Sem isso o navegador serve o CSS antigo depois de uma atualização.
     */
    public static function versao()
    {
        return substr(hash('sha256', (string)@filemtime(self::caminho(self::ESTILO))), 0, 12);
    }
}
