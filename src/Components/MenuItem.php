<?php 

namespace Src\Components;

class MenuItem 
{
    const T_HEADING = 'heading';
    const T_ITEM = 'item';

    private ?string $type = null;
    private ?int $level = null;
    private ?string $icon = null;
    private ?string $url = null;
    private ?string $text = null;
    private ?array $metadata = null;

    public function __construct() 
    {
        $this->type = self::T_ITEM;
        $this->level = 1;
        $this->url = '#';
    }

    public function setType(string $type): self 
    {
        $this->type = $type;
        return $this;
    }

    public function setLevel(int $level): self 
    {
        $this->level = $level;
        return $this;
    }

    public function setIcon(string $icon): self 
    {
        $this->icon = $icon;
        return $this;
    }

    public function setURL(string $url): self 
    {
        $this->url = $url;
        return $this;
    }

    public function setText(string $text): self 
    {
        $this->text = $text;
        return $this;
    }

    public function setMetadata(array $metadata): self 
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getType(): string 
    {
        return $this->type ?? self::T_ITEM;
    }

    public function getLevel(): int 
    {
        return $this->level ?? 1;
    }

    public function getIcon(): string 
    {
        return $this->icon ?? '';
    }

    public function getURL(): string 
    {
        return $this->url ?? '';
    }

    public function getText(): string 
    {
        return $this->text ?? '';
    }

    public function getMetadata(): array 
    {
        return $this->metadata ?? [];
    }

    public function isHeading(): bool 
    {
        return $this->getType() == self::T_HEADING;
    }

    public function isItem(): bool 
    {
        return $this->getType() == self::T_ITEM;
    }
}