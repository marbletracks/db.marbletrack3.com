<?php

namespace Domain;

trait HasShortcodes
{
    abstract protected function getSELECTExactAlias(): string;
    abstract protected function getSELECTLikeAlias(): string;
    abstract protected function getSELECTForShortcodeExpansion(string $langCode): string;
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
     * Summary of expandShortcodesForFrontend
     * @param string $text
     * @param string $type "part" or "worker"
     * @return string
     */
    public function expandShortcodesForFrontend(string $text, string $type, string $langCode): string
    {
        // Match [type:slug] and [type:slug|Display Name]
        preg_match_all("/\\[{$type}:([\\w-]+)(?:\\|([^\\]]+))?\\]/", $text, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return $text;
        }

        // Collect unique slugs for DB lookup
        $slugs = array_unique(array_map(fn($m) => $m[1], $matches));
        $placeholders = [];
        $params = [];

        foreach ($slugs as $slug) {
            $placeholders[] = '?';
            $params[] = $slug;
        }

        $db = $this->getDb();
        $tableAlias = $this->getTableAlias();
        $inClause = implode(',', $placeholders);

        $sql = $this->getSELECTForShortcodeExpansion($langCode) . " WHERE $tableAlias.slug IN ($inClause)";

        $res = $db->fetchResults($sql, str_repeat('s', count($params)), $params);

        // Build slug → name lookup
        $nameMap = [];
        for ($i = 0; $i < $res->numRows(); $i++) {
            $res->setRow($i);
            $nameMap[$res->data['slug']] = $res->data['name'];
        }

        // Build replacements for each match (including display name overrides)
        $replacements = [];
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $slug = $match[1];
            $displayName = $match[2] ?? null;

            if (!isset($nameMap[$slug])) {
                continue;
            }

            $url = "/{$type}s/{$slug}/";
            $label = $displayName ?? $nameMap[$slug];
            $replacements[$fullMatch] = "<a href=\"{$url}\">{$label}</a>";
        }

        return strtr($text, $replacements);
    }

    public function expandShortcodesForBackend(string $text, string $type, string $langCode): string
    {
        // Match [type:slug] and [type:slug|Display Name]
        preg_match_all("/\\[{$type}:([\\w-]+)(?:\\|([^\\]]+))?\\]/", $text, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return $text;
        }

        $slugs = array_unique(array_map(fn($m) => $m[1], $matches));
        $placeholders = [];
        $params = [];

        foreach ($slugs as $slug) {
            $placeholders[] = '?';
            $params[] = $slug;
        }

        $db = $this->getDb();
        $tableAlias = $this->getTableAlias();
        $inClause = implode(',', $placeholders);

        $sql = $this->getSELECTForShortcodeExpansion($langCode) . " WHERE $tableAlias.slug IN ($inClause)";

        $res = $db->fetchResults($sql, str_repeat('s', count($params)), $params);

        $idMap = [];
        $nameMap = [];
        for ($i = 0; $i < $res->numRows(); $i++) {
            $res->setRow($i);
            $idMap[$res->data['slug']] = $res->data['id'];
            $nameMap[$res->data['slug']] = $res->data['name'];
        }

        $replacements = [];
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $slug = $match[1];
            $displayName = $match[2] ?? null;

            if (!isset($idMap[$slug])) {
                continue;
            }

            $url = "/admin/{$type}s/{$type}.php?id={$idMap[$slug]}";
            $label = $displayName ?? $nameMap[$slug];
            $replacements[$fullMatch] = "<a href=\"{$url}\">{$label}</a>";
        }

        return strtr($text, $replacements);
    }

    public function extractShortcodes(string $text, string $type, string $langCode): array
    {
        preg_match_all("/\\[{$type}:([\\w-]+)(?:\\|[^\\]]+)?\\]/", $text, $matches);

        if (empty($matches[1])) {
            return [];
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

        $sql = $this->getSELECTForShortcodeExpansion($langCode) . " WHERE $tableAlias.slug IN ($inClause)";

        $res = $db->fetchResults($sql, str_repeat('s', count($params)), $params);

        $results = [];
        for ($i = 0; $i < $res->numRows(); $i++) {
            $res->setRow($i);
            $results[] = [
                'id' => (int) $res->data['id'],
                'type' => $type,
                'alias' => $res->data['alias'],
                'name' => $res->data['name'],
            ];
        }
        return $results;
    }
}
