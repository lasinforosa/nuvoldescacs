<?php

namespace App\Services;

class PgnParserService
{
    // private $games = [];
    private $headers = [];
    private $movetext = '';

    // Llista de capçaleres estàndard que volem extreure
    private const KNOWN_HEADERS = [
        'Event', 'Site', 'Date', 'Round', 'White', 'Black', 'Result', 'ECO',
        'WhiteElo', 'BlackElo', 'WhiteTitle', 'BlackTitle', 'WhiteTeam', 'BlackTeam'
    ];

    public function __construct(string $singlePgnText)
    {
        $this->parseSingleGame(trim($singlePgnText));
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getMovetext(): string
    {
        return $this->movetext;
    }

    private function parseSingleGame(string $pgn)
    {
        $lines = explode("\n", str_replace("\r", "", $pgn));
        $headerLines = [];
        $moveLines = [];
        $readingHeaders = true;

        // Pas 1: Separem el bloc de capçaleres del de jugades
        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Si la línia està buida, és el separador
            if (empty($trimmedLine)) {
                if ($readingHeaders && !empty($headerLines)) {
                    $readingHeaders = false; // Deixem de llegir capçaleres
                }
                // Si ja estem llegint jugades, un salt de línia és part del PGN
                if (!$readingHeaders) {
                    $moveLines[] = $line;
                }
                continue;
            }

            if ($readingHeaders && strpos($trimmedLine, '[') === 0) {
                $headerLines[] = $trimmedLine;
            } else {
                // Hem trobat la primera línia que no és capçalera
                $readingHeaders = false;
                $moveLines[] = $line;
            }
        }
        
        
        // Processem les capçaleres que hem trobat
        $this->parseHeaders($headerLines);

        // Unim les línies de jugades, preservant el format original
        $this->movetext = trim(implode("\n", $moveLines));
    }

    private function parseHeaders(array $headerLines)
    {
        $unknownHeaders = [];
        foreach ($headerLines as $line) {
            if (preg_match('/\[([A-Za-z]+)\s+"([^"]*)"\]/', $line, $matches)) {
                $tag = $matches[1];
                $value = $matches[2];
                if (in_array($tag, self::KNOWN_HEADERS)) {
                    $this->headers[$tag] = $value;
                } else {
                    $unknownHeaders[] = $line;
                }
            }
        }
        if (!empty($unknownHeaders)) {
            $this->headers['camps_extra'] = implode("\n", $unknownHeaders);
        }
    }
}
    