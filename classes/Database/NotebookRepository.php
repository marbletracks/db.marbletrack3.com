<?php
namespace Database;

use Domain\HasPhotos;
use Physical\Notebook;

class NotebookRepository
{
    use HasPhotos;
    private DbInterface $db;
    private string $photoLinkingTable = 'notebooks_2_photos';
    private string $primaryKeyColumn = 'notebook_id';
    private int $notebook_id;  // Must be set when the Notebook is loaded

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }
    public function getId(): int
    {
        return $this->notebook_id;
    }
    // must do this before saving photos
    // so that HasPhotos knows which notebook to save photos for
    public function setNotebookId(int $notebook_id): void
    {
        $this->notebook_id = $notebook_id;
    }
    public function getDb(): DbInterface
    {
        return $this->db;
    }
    public function getPhotoLinkingTable(): string
    {
        return $this->photoLinkingTable;
    }
    public function getPrimaryKeyColumn(): string
    {
        return $this->primaryKeyColumn;
    }



    public function findById(int $notebook_id): ?Notebook
    {
        $results = $this->db->fetchResults(
            "SELECT notebook_id, title, created_at FROM notebooks WHERE notebook_id = ?",
            'i',
            [$notebook_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }
        $this->notebook_id = $notebook_id; // Set the notebook_id for HasPhotos

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            "SELECT notebook_id, title, created_at FROM notebooks ORDER BY notebook_id ASC"
        );

        $notebooks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->notebook_id = (int) $results->data['notebook_id']; // Set the notebook_id for HasPhotos
            $notebooks[] = $this->hydrate($results->data);
        }

        return $notebooks;
    }

    public function insert(string $title = null, string $created_at = null): int
    {
        return $this->db->insertFromRecord(
            'notebooks',
            'ss',
            [
                'title' => $title,
                'created_at' => $created_at
            ]
        );
    }

    private function hydrate(array $row): Notebook
    {
        $notebook = new Notebook(
            notebook_id: (int) $row['notebook_id'],
            title: $row['title'] ?? null,
            created_at: $row['created_at'] ?? null
        );
        $this->loadPhotos();  // defined in HasPhotos trait
        $notebook->photos = $this->getPhotos();  // return an array of photos
        return $notebook;
    }
}
