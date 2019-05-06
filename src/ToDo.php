<?php declare(strict_types=1);

namespace Acme;

use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ToDo implements JsonSerializable
{
    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var string
     */
    private $content;

    public function __construct(string $content, string $id = null)
    {
        $this->id = $id ? Uuid::fromString($id) : Uuid::uuid4();
        $this->content = $content;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'content' => $this->getContent(),
        ];
    }

    public static function fromDbRow(array $row)
    {
        return new static($row['content'], $row['id']);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id->toString();
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
