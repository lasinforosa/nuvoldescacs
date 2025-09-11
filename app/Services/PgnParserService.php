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
        $state = 'reading_headers';
        $unknownHeaders = [];
        $rawMovetextLines = [];

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // La teva regla: ignorem línies de "soroll"
            if (empty($trimmedLine) || strpos($trimmedLine, '"""') === 0) {
                // Si trobem una línia en blanc DESPRÉS d'haver començat a llegir jugades,
                // considerem que la partida ha acabat.
                if ($state === 'reading_moves' && empty($trimmedLine)) {
                    break;
                }
                continue;
            }

            if ($state === 'reading_headers' && strpos($trimmedLine, '[') === 0) {
                // Estem llegint una capçalera
                if (preg_match('/\[([A-Za-z]+)\s+"([^"]*)"\]/', $trimmedLine, $matches)) {
                    $tag = $matches[1];
                    $value = $matches[2];
                    // DEBUG
                    // dd($tag . ": " . $value);
                    if (in_array($tag, self::KNOWN_HEADERS)) {
                        $this->headers[$tag] = $value;
                    } else {
                        $unknownHeaders[] = $trimmedLine;
                        // DEBUG
                        // dd($trimmedLine);
                    }
                }
            } else {
                // Si la línia no és una capçalera, canviem a l'estat de llegir jugades
                $state = 'reading_moves';
                $rawMovetextLines[] = $trimmedLine;
                // DEBUG
                // dd($trimmedLine);
            }
        }

        if (!empty($unknownHeaders)) {
            $this->headers['camps_extra'] = implode("\n", $unknownHeaders);
        }
        
        // Unim totes les línies de jugades en un sol text, preservant el format
        $this->movetext = implode(" ", $rawMovetextLines);
    }
}