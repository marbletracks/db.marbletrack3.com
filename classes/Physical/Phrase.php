<?php

namespace Physical;

class Phrase
{
    /**
     * @var Token[]
     */
    public array $tokens = [];

    public function __construct(array $tokens = [])
    {
        $this->tokens = $tokens;
    }

    public function addToken(Token $token): void
    {
        $this->tokens[] = $token;
    }

    public function getPhraseString(): string
    {
        $strings = array_map(fn($token) => $token->token_string, $this->tokens);
        return implode(' ', $strings);
    }
}
