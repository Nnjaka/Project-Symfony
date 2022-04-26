<?php

namespace App\Validator;

use App\Validator\UserExists;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use App\Repository\UserRepository;

class UserExistsValidator extends ConstraintValidator
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function validate($email, Constraint $constraint)
    {
        if (!$constraint instanceof UserExists) {
            throw new UnexpectedTypeException($constraint, UserAccountExists::class);
        }

        if (null === $email || '' === $email) {
            return;
        }

        if (!is_string($email)) {
            throw new UnexpectedValueException($email, 'string');
        }

        if (!$this->userExists($email)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function userExists(string $email): bool
    {
        $user = $this->repository->findOneBy(array('email' => $email));

        return null !== $user;
    }
}
