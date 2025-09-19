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
        $isReadingHeaders = true;
        
        // Separem el text en línies
        $lines = explode("\n", str_replace("\r", "", $pgn));

        // Pas 1: Separem el bloc de capçaleres del de jugades
        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Si la línia està buida i ja hem llegit alguna capçalera, és el separador
            if (empty($trimmedLine) && !empty($headerLines) && $isReadingHeaders) {
                $isReadingHeaders = false;
                continue; // No afegim la línia en blanc al movetext
            }

            // Si la línia no és una capçalera, comencen les jugades
            if ($isReadingHeaders && strpos($trimmedLine, '[') !== 0) {
                 if(strlen($trimmedLine) > 0) $isReadingHeaders = false;
            }

            if ($isReadingHeaders) {
                $headerLines[] = $trimmedLine;
            } else {
                // Afegim la línia al movetext, fins i tot si està buida (pot ser part del format)
                $moveLines[] = $line;
            }
        }
        
        $this->parseHeaders($headerLines);
       
        // Unim les línies de jugades i netegem el resultat final
        $movetext = implode("\n", $moveLines);
        $this->movetext = trim(preg_replace('/\s*(1-0|0-1|1\/2-1\/2|\*)\s*$/', '', $movetext));

        // DEBUG
        //dd($this->headers, $this->movetext);
    }

    private function parseHeaders(array $headerLines)
    {
        $unknownHeaders = [];
        // L'expressió regular més tolerant
        $headerPattern = '/\[\s*([A-Za-z]+)\s*"(.*?)"\s*\]/';

        foreach ($headerLines as $line) {
            if (preg_match($headerPattern, $line, $matches)) {
                $tag = $matches[1];
                $value = $matches[2];
                if (in_array($tag, self::KNOWN_HEADERS)) {
                    // Especial per a la data: netegem caràcters estranys abans de guardar
                    
                    if ($tag === 'Date') {
                        $value = preg_replace('/\?+/', '01', $value); // Reemplacem '?' per '01'
                        // DEBUG
                        // dd($value);
                    }
                    
                    $this->headers[$tag] = $value;
                } else {
                    $unknownHeaders[] = trim($line);
                }
            }
        }
        if (!empty($unknownHeaders)) {
            $this->headers['camps_extra'] = implode("\n", $unknownHeaders);
        }
    }
}
    