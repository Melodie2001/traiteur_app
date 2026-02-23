<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MenuDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3)]
        public readonly string $titre,

        public readonly ?string $description = null,

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public readonly float $prix,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $min = 1,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $max = 100,
    ) {
    }
}
