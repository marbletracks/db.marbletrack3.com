<?php

namespace Physical;

class PhraseBuilder
{
    /**
     * Groups tokens into phrases based on their Y position.
     *
     * @param Token[] $tokens Assumes tokens are pre-sorted by y_pos, then x_pos.
     * @param int $y_tolerance The tolerance for grouping tokens on the same line.
     * @return Phrase[]
     */
    public static function groupTokensIntoPhrases(array $tokens, int $y_tolerance = 5): array
    {
        if (empty($tokens)) {
            return [];
        }

        $phrases = [];
        if (count($tokens) > 0) {
            $currentPhrase = new Phrase();
            $currentPhrase->addToken($tokens[0]);
            $phrases[] = $currentPhrase;
            $last_y = $tokens[0]->token_y_pos;

            for ($i = 1; $i < count($tokens); $i++) {
                $currentToken = $tokens[$i];

                if (abs($currentToken->token_y_pos - $last_y) <= $y_tolerance) {
                    // Same phrase
                    $currentPhrase->addToken($currentToken);
                } else {
                    // New phrase
                    $currentPhrase = new Phrase();
                    $currentPhrase->addToken($currentToken);
                    $phrases[] = $currentPhrase;
                    $last_y = $currentToken->token_y_pos;
                }
            }
        }

        return $phrases;
    }
}
