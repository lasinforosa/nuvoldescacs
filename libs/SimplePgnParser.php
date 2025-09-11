<?php

class SimplePgnParser
{
    private $pgnContent;
    private $games = [];

    public function __construct(string $pgnContent)
    {
        $this->pgnContent = trim($pgnContent);
        $this->parse();
    }

    /**
     * Retorna un array de totes les partides trobades.
     * Cada partida és un array amb 'headers' i 'moves'.
     */
    public function getGames(): array
    {
        return $this->games;
    }

    private function parse()
    {
        // Separem el fitxer en partides individuals. Cada partida comença amb [Event "..."].
        $gameChunks = preg_split('/\[Event "/m', $this->pgnContent, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($gameChunks as $chunk) {
            // Tornem a afegir la capçalera que hem perdut en el split
            $fullGameText = '[Event "' . trim($chunk);
            
            $headers = [];
            $moves = '';
            
            $lines = explode("\n", $fullGameText);
            $readingHeaders = true;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                if ($readingHeaders && strpos($line, '[') === 0) {
                    // És una capçalera, per exemple: [Site "Paris"]
                    preg_match('/\[([A-Za-z0-9]+)\s+"(.*?)"\]/', $line, $matches);
                    if (count($matches) === 3) {
                        $headers[$matches[1]] = $matches[2];
                    }
                } else {
                    // Ja no estem llegint capçaleres, la resta són les jugades.
                    $readingHeaders = false;
                    $moves .= $line . ' ';
                }
            }

            // Netegem el text de les jugades de possibles resultats al final
            $moves = trim(str_replace(['1-0', '0-1', '1/2-1/2', '*'], '', $moves));

            if (!empty($headers) && !empty($moves)) {
                $this->games[] = [
                    'headers' => $headers,
                    'moves' => $moves,
                ];
            }
        }
    }
}