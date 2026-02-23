<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        public readonly string $password,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['traiteur', 'client'], message: 'Le rôle doit être "traiteur" ou "client"')]
        public readonly string $role,
    ) {
    }
}
