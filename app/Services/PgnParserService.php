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
        // Utilitzem una expressió regular per capturar els dos blocs principals
        $pattern = '/
            ( # Grup 1: Bloc de capçaleres (tot el que comença amb [ i acaba amb ])
                (?: \s* \[ [^\]]+ \] \s* )+
            )
            ( # Grup 2: Bloc de jugades (tota la resta)
                .*
            )
        /msx';

        if (preg_match($pattern, $pgn, $matches)) {
            $headerBlock = $matches[1];
            $this->movetext = trim($matches[2]);

            // Ara processem només el bloc de capçaleres
            $headerPattern = '/\[([A-Za-z]+)\s+"([^"]*)"\]/';
            if (preg_match_all($headerPattern, $headerBlock, $headerMatches)) {
                $unknownHeaders = [];
                for ($i = 0; $i < count($headerMatches[1]); $i++) {
                    $tag = $headerMatches[1][$i];
                    $value = $headerMatches[2][$i];
                    if (in_array($tag, self::KNOWN_HEADERS)) {
                        $this->headers[$tag] = $value;
                    } else {
                        $unknownHeaders[] = "[$tag \"$value\"]";
                    }
                }
                if (!empty($unknownHeaders)) {
                    $this->headers['camps_extra'] = implode("\n", $unknownHeaders);
                }
            }
        }
    }

    /*
    private function parseFullPgn(string $pgn)
    {
        $lines = explode("\n", str_replace("\r", "", $pgn));
        $currentGame = null;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (strpos($trimmedLine, '[Event ') === 0) {
                // Comença una nova partida. Guardem l'anterior si n'hi havia una.
                if ($currentGame !== null) {
                    $this->finalizeGame($currentGame);
                }
                // Iniciem la nova partida
                $currentGame = ['raw' => ''];
            }

            if ($currentGame !== null) {
                // Afegim la línia actual a la partida que estem construint
                $currentGame['raw'] .= $line . "\n";
            }
        }

        // Guardem l'última partida del fitxer
        if ($currentGame !== null) {
            $this->finalizeGame($currentGame);
        }
    }

    private function finalizeGame(array $gameData)
    {
        $pgn = trim($gameData['raw']);
        $headers = [];
        $movetext = '';
        $unknownHeaders = [];

        // Separem capçaleres de jugades de manera robusta
        $lastHeaderPos = strrpos($pgn, ']');
        if ($lastHeaderPos !== false) {
            // Tot el que hi ha fins a l'últim ']' (més un salt de línia) són capçaleres
            $headerBlock = substr($pgn, 0, $lastHeaderPos + 1);
            $movetext = trim(substr($pgn, $lastHeaderPos + 1));

            // Extraiem les capçaleres del bloc
            $headerPattern = '/\[([A-Za-z]+)\s+"([^"]*)"\]/';
            if (preg_match_all($headerPattern, $headerBlock, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $tag = $matches[1][$i];
                    $value = $matches[2][$i];
                    if (in_array($tag, self::KNOWN_HEADERS)) {
                        $headers[$tag] = $value;
                    } else {
                        $unknownHeaders[] = "[$tag \"$value\"]";
                    }
                }
            }
        } else {
            // No hi ha capçaleres, tot són jugades
            $movetext = $pgn;
        }

        if (!empty($unknownHeaders)) {
            $headers['camps_extra'] = implode("\n", $unknownHeaders);
        }

        // Només afegim la partida si té jugadors i jugades
        if (!empty($movetext) && isset($headers['White']) && isset($headers['Black'])) {
            $this->games[] = [
                'headers' => $headers,
                'movetext' => $movetext,
                'original' => $pgn // Guardem el text original per al log d'errors
            ];
        }
    }
    */
}
