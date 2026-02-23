<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AvisDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Range(min: 1, max: 5)]
        public readonly int $note,

        public readonly ?string $commentaire = null,
    ) {
    }
}
