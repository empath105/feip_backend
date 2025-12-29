<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Booking;
use App\Entity\House;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Feip Backend');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Главная', 'fa fa-home');
        yield MenuItem::section('Управление');
        yield MenuItem::linkToCrud('Пользователи', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Дома', 'fa fa-home', House::class);
        yield MenuItem::linkToCrud('Бронирования', 'fa fa-calendar', Booking::class);
        yield MenuItem::section();
        yield MenuItem::linkToUrl('API Документация', 'fa fa-book', '/api/docs');
        yield MenuItem::linkToLogout('Выход', 'fa fa-sign-out');
    }
}
