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
        $headerLines = [];
        $moveLines = [];
        
        // Separem el text en línies
        $lines = explode("\n", str_replace("\r", "", $pgn));

        $isReadingHeaders = true;

        // Pas 1: Separem el bloc de capçaleres del de jugades
         foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Si la línia està buida i ja tenim capçaleres, canviem a llegir jugades
            if (empty($trimmedLine) && !empty($headerLines)) {
                $isReadingHeaders = false;
                continue;
            }

            if ($isReadingHeaders && strpos($trimmedLine, '[') === 0) {
                $headerLines[] = $trimmedLine;
            } elseif (!empty($trimmedLine)) {
                // Qualsevol línia amb contingut que no sigui una capçalera, inicia les jugades
                $isReadingHeaders = false;
                $moveLines[] = $trimmedLine;
            }
        }

        $this->parseHeaders($headerLines);
        
        // Unim les línies de jugades i netegem el resultat final
        $movetext = implode("\n", $moveLines);
        $this->movetext = trim(preg_replace('/\s*(1-0|0-1|1\/2-1\/2|\*)\s*$/', '', $movetext));
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
    