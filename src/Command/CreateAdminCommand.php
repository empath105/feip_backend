<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Создание пользователя с правами администратора',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email администратора')
            ->addArgument('phone', InputArgument::REQUIRED, 'Телефон администратора')
            ->addArgument('name', InputArgument::REQUIRED, 'Имя администратора')
            ->addArgument('password', InputArgument::REQUIRED, 'Пароль администратора');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Создание пользователя с правами администратора');

        $email = $input->getArgument('email');
        if (empty($email)) {
            $io->error('Email обязателен!');

            return Command::FAILURE;
        }
        $phone = $input->getArgument('phone');
        if (empty($phone)) {
            $io->error('Телефон обязателен!');

            return Command::FAILURE;
        }

        $name = $input->getArgument('name');
        if (empty($name)) {
            $io->error('Имя обязательно!');

            return Command::FAILURE;
        }

        $password = $input->getArgument('password');
        if (empty($password)) {
            $io->error('Пароль обязателен!');

            return Command::FAILURE;
        }

        $existingUserByEmail = $this->userRepository->findByEmail($email);
        if ($existingUserByEmail) {
            $io->error('Пользователь с таким email уже существует');

            return Command::FAILURE;
        }

        $existingUserByPhone = $this->userRepository->findByPhone($phone);
        if ($existingUserByPhone) {
            $io->error('Пользователь с таким телефоном уже существует');

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setName($name);
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        $io->success('Администратор успешно создан!');
        $io->table(
            ['Поле', 'Значение'],
            [
                ['ID', $user->getId()],
                ['Имя', $user->getName()],
                ['Email', $user->getEmail()],
                ['Телефон', $user->getPhone()],
                ['Роли', implode(', ', $user->getRoles())],
            ]
        );

        return Command::SUCCESS;
    }
}
