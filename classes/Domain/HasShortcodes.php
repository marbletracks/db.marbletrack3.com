<?php

namespace Domain;

trait HasShortcodes
{
    abstract protected function getSelectPrefix(): string;
    abstract protected function getTableAlias(): string;
    abstract protected function getDb(): \Database\DbInterface;

    public function searchByShortcodeOrName(string $like, string $lang, int $limit = 10): array
    {
        $db = $this->getDb();
        $alias = $this->getTableAlias();

        $sql = $this->getSelectPrefix() . " WHERE $alias.part_alias LIKE ? OR pt.part_name LIKE ? LIMIT ?";

        $res = $db->fetchResults(
            $sql,
            'sssi',
            [
                $lang,
                "%{$like}%",
                "%{$like}%",
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
                'expansion' => "<a href=\"/parts/part.php?part_id={$res->data['id']}\">{$res->data['name']}</a>",
            ];
        }
        return $results;
    }
}
