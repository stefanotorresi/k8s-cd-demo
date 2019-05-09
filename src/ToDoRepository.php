<?php declare(strict_types=1);

namespace Acme;

use PDO;

class ToDoRepository
{
    public const SCHEMA = <<<SQL
CREATE TABLE IF NOT EXISTS todos (
    id uuid NOT NULL,
    content TEXT NOT NULL,
    PRIMARY KEY (id)
);
SQL;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @param $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function add(ToDo $item): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO "todos" (id, content) VALUES (:id, :content)');
        $stmt->execute(
            [
                'id'      => $item->getId(),
                'content' => $item->getContent(),
            ]
        );
    }

    public function update(ToDo $item): void
    {
        $stmt = $this->pdo->prepare('UPDATE "todos" SET "content" = :content WHERE "id" = :id');
        $stmt->execute(
            [
                'id'      => $item->getId(),
                'content' => $item->getContent(),
            ]
        );
    }

    /**
     * @return ToDo[]
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM "todos";');

        return array_map('Acme\ToDo::fromDbRow', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function find(string $id): ?ToDo
    {
        $stmt = $this->pdo->prepare('SELECT * FROM "todos" WHERE "id" = :id;');
        $stmt->execute(compact('id'));

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return ToDo::fromDbRow($row);
    }

    public function initSchema(): void
    {
        $this->pdo->exec(self::SCHEMA);
    }

    public function dropSchema(): void
    {
        $this->pdo->exec('DROP TABLE IF EXISTS "todos";');
    }
}
