<?php

namespace App\Event;

/**
 * Implementation of CloudEvents specification @see https://github.com/cloudevents/spec
 */
class CloudEvent
{
    public const SPECVERSION = "1.0";
    public const REQUIRED_ATTRIBUTES = ['id', 'source', 'specversion', 'type'];

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $datacontenttype = "application/json";

    /**
     * @var string
     */
    private $dataschema;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var \DateTimeInterface
     */
    private $time;

    /**
     * @var string
     */
    private $data;

    public function __construct()
    {
        $this->time = new \DateTime();
    }

    public static function create($id, $source, $type): self
    {
        $event = new self();
        $event->id = $id;
        $event->source = $source;
        $event->type = $type;

        return $event;
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'source'          => $this->source,
            'specversion'     => self::SPECVERSION,
            'type'            => $this->type,
            'datacontenttype' => $this->datacontenttype,
            'dataschema'      => $this->dataschema,
            'subject'         => $this->subject,
            'time'            => $this->time->format(\DateTimeInterface::RFC3339),
            'data'            => $this->data
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDatacontenttype(): string
    {
        return $this->datacontenttype;
    }

    public function setDatacontenttype(string $datacontenttype): void
    {
        $this->datacontenttype = $datacontenttype;
    }

    public function getDataschema(): string
    {
        return $this->dataschema;
    }

    public function setDataschema(string $dataschema): void
    {
        $this->dataschema = $dataschema;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getTime(): \DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): void
    {
        $this->time = $time;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }
}