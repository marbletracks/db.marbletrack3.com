<?php

namespace Physical;

class Token implements \JsonSerializable
{
    /**
     * Tokens are the individual pieces of content that are written on a Column.
     * The often have dates, but not always.
     * Tokens together will be used to create Phrases, which are abbreviations
     * explaining what Workers are doing.
     *
     * @param int $token_id The DB unique identifier for the token.
     * @param int $column_id The DB ID of the column this token belongs to.
     * @param string $token_string The string content of the token.
     * @param string $token_date The (optional) date the token was written on the page.
     * @param int $token_x_pos Used for placement on Column edit page.
     * @param int $token_y_pos Used for placement on Column edit page.
     * @param int $token_width The width of the token on Column edit page.
     * @param int $token_height The height of the token on Column edit page.
     * @param string $token_color Red, Blue, or Black
     * @param string $created_at The DB creation timestamp of the token.
     */
    public function __construct(
        public int $token_id,
        public int $column_id,
        public string $token_string,
        public string $token_date,
        public int $token_x_pos,
        public int $token_y_pos,
        public int $token_width,
        public int $token_height,
        public string $token_color,
        public string $created_at,
        public bool $is_permanent = false,
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
