<?php

namespace Domain;

trait HasShortcodes
{
    abstract protected function getSELECTExactAlias(): string;
    abstract protected function getSELECTLikeAlias(): string;
    abstract protected function getTableAlias(): string;
    abstract protected function getAliasType(): string;     // e.g., "part" or "worker"
    abstract protected function getDb(): \Database\DbInterface;

    public function searchByShortcodeOrName(
        string $like,
        string $lang,
        bool $exact = false,
        int $limit = 10
    ): array
    {
        $db = $this->getDb();
        $type = $this->getAliasType();

        if($exact) {
            // If exact match is requested, we use '=' instead of 'LIKE'
            $sql = $this->getSELECTExactAlias();
            $like = trim($like);
        } else {
            // For partial matches, we use 'LIKE'
            $sql = $this->getSELECTLikeAlias();
            $like = '%' . trim($like) . '%';
        }

        $res = $db->fetchResults(
            $sql,
            'sssi',
            [
                $lang,
                "$like",
                "$like",
                $limit
            ]
        );

        $results = [];
        for ($i = 0; $i < $res->numRows(); $i++) {
            $res->setRow($i);
            $results[] = [
                'id' => (int) $res->data['id'],
                'alias' => $res->data['alias'],
                'name' => $res->data['name'],
                'expansion' => "[{$type}:{$res->data['slug']}]",
            ];
        }
        return $results;
    }

    /**
     * Summary of expandShortcodes
     * @param string $text
     * @param string $type "part" or "worker"
     * @return string
     */
    public function expandShortcodes(string $text, string $type): string
    {
        // This regex finds all occurrences of [part:some_slug]
        preg_match_all("/\\[{$type}:([\\w-]+)\\]/", $text, $matches);

        if (empty($matches[1])) {
            return $text;
        }

        $slugs = array_unique($matches[1]);
        $placeholders = [];
        $params = [];

        foreach ($slugs as $slug) {
            $placeholders[] = '?';
            $params[] = $slug;
        }

        $db = $this->getDb();
        $tableAlias = $this->getTableAlias();
        $inClause = implode(',', $placeholders);

        $sql = $this->getSELECTExactAlias() . " WHERE $tableAlias.slug IN ($inClause)";

        $res = $db->fetchResults($sql, 's' . str_repeat('s', count($params)), ['en', ...$params]);

        $replacements = [];
        for ($i = 0; $i < $res->numRows(); $i++) {
            $res->setRow($i);
            $name = $res->data['name'];
            $slug = $res->data['slug'];

            $url = "/{$type}s/{$slug}/";
            $replacements["[{$type}:{$slug}]"] = "<a href=\"{$url}\">{$name}</a>";
        }

        return strtr($text, $replacements);
    }
}
