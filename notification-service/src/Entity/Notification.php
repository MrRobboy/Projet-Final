<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $emailRecipient = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    private ?string $sujet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailRecipient(): ?string
    {
        return $this->emailRecipient;
    }

    public function setEmailRecipient(string $emailRecipient): static
    {
        $this->emailRecipient = $emailRecipient;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): static
    {
        $this->sujet = $sujet;

        return $this;
    }
}
